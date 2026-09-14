<?php
class AuthMiddleware
{
    public function handle(): void
    {
        if (!Session::isLoggedIn()) {
            Session::setIntended($_SERVER['REQUEST_URI'] ?? BASE_URL);
            Session::flash('error', 'Please log in to continue.');
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }
}
