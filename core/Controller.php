<?php
abstract class Controller
{
    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = ROOT_PATH . "/app/views/{$view}.php";
        if (!file_exists($viewFile)) throw new RuntimeException("View not found: {$view}");

        if ($layout === '') { require $viewFile; return; }

        $layoutFile = ROOT_PATH . "/app/views/layouts/{$layout}.php";
        if (!file_exists($layoutFile)) throw new RuntimeException("Layout not found: {$layout}");

        ob_start(); require $viewFile; $content = ob_get_clean();
        require $layoutFile;
    }

    protected function redirect(string $url): never { header("Location: {$url}"); exit; }
    protected function back(): never { $this->redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL); }

    protected function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function abort(int $code = 404): never
    {
        http_response_code($code);
        $f = ROOT_PATH . "/app/views/errors/{$code}.php";
        file_exists($f) ? require $f : print("<h1>{$code}</h1>");
        exit;
    }

    protected function flash(string $type, string $msg): void { Session::flash($type, $msg); }
    protected function input(string $key, string $default = ''): string { return trim((string)($_POST[$key] ?? $default)); }
    protected function query(string $key, string $default = ''): string { return trim((string)($_GET[$key] ?? $default)); }
    protected function isPost(): bool { return $_SERVER['REQUEST_METHOD'] === 'POST'; }
    protected function isAjax(): bool { return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'; }

    protected function verifyCsrf(): void
    {
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!Session::verifyCsrf($token)) {
            $this->flash('error', 'Session expired. Please try again.');
            $this->back();
        }
    }

    protected function auth(): ?array  { return Session::user(); }
    protected function isLoggedIn(): bool { return Session::isLoggedIn(); }

    protected function requireLogin(): void
    {
        if (!$this->isLoggedIn()) {
            Session::setIntended($_SERVER['REQUEST_URI'] ?? '/');
            $this->flash('error', 'Please log in to continue.');
            $this->redirect(BASE_URL . '/login');
        }
    }

    protected function requireRole(string ...$roles): void
    {
        $this->requireLogin();
        if (!in_array(Session::role(), $roles, true)) $this->abort(403);
    }

    protected function currentPage(): int { return max(1, (int)($_GET['page'] ?? 1)); }
}
