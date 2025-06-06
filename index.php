<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


require_once __DIR__ . '/app/Config/Database.php';

use App\Router\Route;
use App\Config\Database;

try {
    $db = Database::getInstance();
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}

// Inclua o arquivo de rotas
require_once __DIR__ . '/routes/web.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

Route::resolve($uri, $method);