<?php
class GuestMiddleware
{
    public function handle(): void
    {
        if (!Session::isLoggedIn()) return;
        $map = ['seeker' => '/seeker/dashboard', 'employer' => '/employer/dashboard', 'admin' => '/admin/dashboard'];
        header('Location: ' . BASE_URL . ($map[Session::role()] ?? ''));
        exit;
    }
}
