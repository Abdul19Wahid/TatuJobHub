<?php
class RoleMiddleware
{
    public function handle(array $roles = []): void
    {
        if (!Session::isLoggedIn()) { header('Location: ' . BASE_URL . '/login'); exit; }
        if (!in_array(Session::role(), $roles, true)) {
            http_response_code(403);
            $f = ROOT_PATH . '/app/views/errors/403.php';
            file_exists($f) ? require $f : print('<h1>403 Forbidden</h1>');
            exit;
        }
    }
}
