<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use App\Services\Mailer;

$mailer = new Mailer();

// Exemplo com um PDF fake
$attachments = [
    [
        'filename' => 'Relatorio_GABRYEL_HENRYQUE_LUCIANO_DA_SILVA_Abril_20250606183400.pdf',
        'path' => './storage/Relatorio_GABRYEL_HENRYQUE_LUCIANO_DA_SILVA_Abril_20250606183400.pdf'
    ]
];

try {
    $mailer->send(
        'wevertoncamposdev@gmail.com',
        'Relatório de Presença',
        '<strong>Este é um corpo em HTML</strong>',
        $attachments,
        true // HTML
    );
    echo "E-mail enviado com sucesso!";
} catch (Exception $e) {
    echo "Erro ao enviar e-mail: " . $e->getMessage();
}
