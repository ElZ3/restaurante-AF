<?php

require_once __DIR__ . '/../core/Menu.php';

class DashboardController
{
    public function index()
    {
        Auth::exigirRol(Menu::roles('dashboard'));

        require_once __DIR__ . '/../../public/views/dashboard.php';
    }
}
