<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Route.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Inclua o arquivo de rotas
require_once __DIR__ . '/routes/web.php';


$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

Route::resolve($uri, $method);