<?php

require_once __DIR__ . '/../Models/User.php';

class AuthController
{
    public function showLoginForm()
    {
        require_once __DIR__ . '/../Views/auth/login.html';
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $user = User::findByEmail($email);

            if ($user && password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['user'] = $user;
                header('Location: /');
                exit;
            } else {
                echo "Credenciais inválidas.";
            }
        }
    }

    public function logout()
    {
        session_start();
        session_destroy();
        header('Location: /entrar');
        exit;
    }
}