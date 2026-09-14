<?php
class CsrfMiddleware
{
    public function handle(): void
    {
        if (!in_array($_SERVER['REQUEST_METHOD'], ['POST','PUT','PATCH','DELETE'])) return;
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!Session::verifyCsrf($token)) {
            Session::flash('error', 'Session expired. Please try again.');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
            exit;
        }
    }
}
