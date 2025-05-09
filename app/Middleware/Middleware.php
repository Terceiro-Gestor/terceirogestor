<?php

namespace App\Middleware;

class Middleware
{
    public static function auth()
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: /entrar');
            exit;
        }
    }
}