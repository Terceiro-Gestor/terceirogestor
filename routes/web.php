<?php

require_once __DIR__ . '/../app/Route.php';
require_once __DIR__ . '/../app/Controllers/AuthController.php';

Route::get('/', function () {
    echo "Bem-vindo ao Terceiro Gestor!";
});

Route::get('/sobre', function () {
    echo "Sobre o Terceiro Gestor.";
});

Route::get('/entrar', [AuthController::class, 'showLoginForm']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/sair', [AuthController::class, 'logout']);