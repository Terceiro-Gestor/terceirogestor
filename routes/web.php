<?php

use App\Router\Route;
use App\Controllers\AuthController;
use App\Controllers\MainController;
use App\Middleware\Middleware;

Route::get('/', function () {
    require_once __DIR__ . '/../app/Views/page/home.html';
});

Route::get('/sobre', function () {
    echo "Sobre o Terceiro Gestor.";
});

Route::get('/entrar', [AuthController::class, 'showLoginForm']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/sair', [AuthController::class, 'logout']);

// Rotas protegidas por middleware
Route::get('/painel', function () {
    Middleware::auth();
    (new MainController())->painel();
});