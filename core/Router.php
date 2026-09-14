<?php
class Router
{
    private array $routes = [];
    private array $groupMiddleware = [];
    private string $groupPrefix = '';

    public function get(string $path, string $handler, array $mw = []): void  { $this->add('GET',  $path, $handler, $mw); }
    public function post(string $path, string $handler, array $mw = []): void { $this->add('POST', $path, $handler, $mw); }
    public function any(string $path, string $handler, array $mw = []): void  { $this->add('GET', $path, $handler, $mw); $this->add('POST', $path, $handler, $mw); }

    public function group(array $opts, callable $cb): void
    {
        $prevPrefix = $this->groupPrefix;
        $prevMw     = $this->groupMiddleware;
        $this->groupPrefix     .= ($opts['prefix'] ?? '');
        $this->groupMiddleware  = array_merge($this->groupMiddleware, $opts['middleware'] ?? []);
        $cb($this);
        $this->groupPrefix     = $prevPrefix;
        $this->groupMiddleware = $prevMw;
    }

    private function add(string $method, string $path, string $handler, array $mw): void
    {
        $full = $this->groupPrefix . $path;
        $this->routes[] = [
            'method'  => $method,
            'pattern' => '#^' . preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $full) . '$#',
            'handler' => $handler,
            'mw'      => array_merge($this->groupMiddleware, $mw),
        ];
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = $this->uri();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;
            if (!preg_match($route['pattern'], $uri, $matches)) continue;

            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            $this->runMiddleware($route['mw']);
            $this->call($route['handler'], $params);
            return;
        }

        http_response_code(404);
        $f = ROOT_PATH . '/app/views/errors/404.php';
        file_exists($f) ? require $f : print('<h1>404 — Page Not Found</h1>');
    }

    private function uri(): string
    {
        $uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // Strip known base paths so the router always sees a root-relative URI
        // Handles both /config/public/... and / contexts
        foreach (['/config/public', '/public'] as $base) {
            if (str_starts_with($uri, $base . '/') || $uri === $base) {
                $uri = substr($uri, strlen($base));
                break;
            }
        }

        // Also strip SCRIPT_NAME directory if not already handled
        $scriptBase = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/');
        if ($scriptBase && $scriptBase !== '/config/public' && $scriptBase !== '/public'
            && str_starts_with($uri, $scriptBase)) {
            $uri = substr($uri, strlen($scriptBase));
        }

        return '/' . ltrim($uri ?: '/', '/');
    }

    private function call(string $handler, array $params): void
    {
        [$class, $method] = explode('@', $handler, 2);
        $file = ROOT_PATH . "/app/controllers/{$class}.php";
        if (!file_exists($file)) throw new RuntimeException("Controller not found: {$class}.php");
        require_once $file;
        if (!class_exists($class)) throw new RuntimeException("Class {$class} not found.");
        $ctrl = new $class();
        if (!method_exists($ctrl, $method)) throw new RuntimeException("{$class}::{$method}() not found.");

        $reflection = new ReflectionMethod($ctrl, $method);
        $paramCount = $reflection->getNumberOfParameters();

        if ($paramCount === 0) {
            $ctrl->$method();
            return;
        }

        if ($paramCount === 1) {
            $parameter = $reflection->getParameters()[0];
            $type = $parameter->getType();

            if ($type instanceof ReflectionNamedType && $type->getName() === 'array') {
                $ctrl->$method($params);
                return;
            }

            if (count($params) === 1) {
                $ctrl->$method(...array_values($params));
                return;
            }

            $ctrl->$method($params);
            return;
        }

        $ctrl->$method(...array_values($params));
    }

    private function runMiddleware(array $mws): void
    {
        $map = require ROOT_PATH . '/config/middleware.php';
        foreach ($mws as $alias) {
            if (str_starts_with($alias, 'role:')) {
                $this->loadMw('RoleMiddleware');
                (new RoleMiddleware())->handle(explode(',', substr($alias, 5)));
                continue;
            }
            if (!isset($map[$alias])) throw new RuntimeException("Unknown middleware: {$alias}");
            $this->loadMw($map[$alias]);
            (new $map[$alias]())->handle();
        }
    }

    private function loadMw(string $class): void
    {
        $f = ROOT_PATH . "/app/middleware/{$class}.php";
        if (file_exists($f)) require_once $f;
    }
}
