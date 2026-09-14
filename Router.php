<?php
/**
 * Router.php — Production version for InfinityFree
 *
 * KEY DIFFERENCE from XAMPP version:
 *   XAMPP:       index.php lives in htdocs/job-portal/public/
 *                URI strip: removes /job-portal/public from front
 *
 *   InfinityFree: index.php lives in htdocs/ (web root)
 *                URI strip: removes nothing (RewriteBase /)
 *
 * This version auto-detects which environment it is running in.
 */
class Router
{
    private array  $routes         = [];
    private array  $groupMiddleware = [];
    private string $groupPrefix    = '';

    public function get(string $path, string $handler, array $mw = []): void
    {
        $this->add('GET',  $path, $handler, $mw);
    }

    public function post(string $path, string $handler, array $mw = []): void
    {
        $this->add('POST', $path, $handler, $mw);
    }

    public function any(string $path, string $handler, array $mw = []): void
    {
        $this->add('GET',  $path, $handler, $mw);
        $this->add('POST', $path, $handler, $mw);
    }

    public function group(array $opts, callable $cb): void
    {
        $prevPrefix = $this->groupPrefix;
        $prevMw     = $this->groupMiddleware;
        $this->groupPrefix     .= ($opts['prefix']     ?? '');
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
        $uri    = $this->parseUri();

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

    private function parseUri(): string
    {
        $uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri  = rawurldecode($uri);

        // Strip base path (works for both XAMPP /job-portal/public and InfinityFree /)
        $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        if ($scriptDir && $scriptDir !== '/' && str_starts_with($uri, $scriptDir)) {
            $uri = substr($uri, strlen($scriptDir));
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
        $ctrl->$method($params);
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
