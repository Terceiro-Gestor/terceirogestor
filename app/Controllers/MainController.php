<?php
namespace App\Controllers;

class MainController
{
    public function home()
    {
        echo "Bem-vindo ao Terceiro Gestor!";
    }

    public function sobre()
    {
        echo "Sobre o Terceiro Gestor.";
    }

    public function painel()
    {
        require_once __DIR__ . '/../Views/painel.html';
    }
}