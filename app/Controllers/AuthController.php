<?php

namespace App\Controllers;

use App\Models\User;

class AuthController
{
    public function showLoginForm()
    {
        require_once __DIR__ . '/../Views/auth/login.html';
    }

    public function login()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        // Buscar o usuário pelo e-mail
        $user = User::findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user'] = $user;
            echo json_encode(['redirect' => '/painel']);
        } else {
            http_response_code(401);
            echo json_encode(['message' => 'E-mail ou senha invalidos.']);
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
