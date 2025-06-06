<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

class Mailer
{
    private PHPMailer $mail;

    public function __construct()
    {

        // Carregar as variáveis de ambiente do .env
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
        $this->mail = new PHPMailer(true);
        $this->configurarSMTP();
    }

    private function configurarSMTP(): void
    {
        $this->mail->isSMTP();
        $this->mail->Host       = $_ENV['SMTP_HOST'];
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = $_ENV['SMTP_USER'];
        $this->mail->Password   = $_ENV['SMTP_PASS'];
        $this->mail->SMTPSecure = 'tls';
        $this->mail->Port       = $_ENV['SMTP_PORT'];
        $this->mail->CharSet = 'UTF-8';
        $fromEmail = $_ENV['SMTP_FROM_EMAIL'] ?: 'default@example.com';
        $fromName = $_ENV['SMTP_FROM_NAME'] ?: 'Sistema';
        $this->mail->setFrom($fromEmail, $fromName);
    }

    /**
     * Envia e-mail com anexo
     *
     * @param string $para      E-mail de destino
     * @param string $assunto   Assunto do e-mail
     * @param string $mensagem  Corpo do e-mail (pode ser HTML)
     * @param string $pdfBin    Conteúdo binário do PDF (opcional)
     * @param string $nomePdf   Nome do arquivo do PDF (opcional)
     */
    public function enviar(
        string $para,
        string $assunto,
        string $mensagem,
        ?string $pdfBin = null,
        string $nomePdf = 'relatorio.pdf'
    ): void {
        $this->mail->clearAllRecipients();
        $this->mail->clearAttachments();

        $this->mail->addAddress($para);
        $this->mail->Subject = $assunto;
        $this->mail->Body    = $mensagem;
        $this->mail->isHTML(false);

        if (!$pdfBin) {
            throw new \Exception("PDF está vazio.");
        }

        if ($pdfBin) {
            // Define um caminho fixo para salvar o PDF
            $caminho = __DIR__ . '/../../';

            // Salva o PDF no caminho definido
            file_put_contents($caminho, $pdfBin);

            $this->mail->addAttachment($tmp, $nomePdf);
        }

        $this->mail->send();

        if (isset($tmp)) {
            @unlink($tmp);
        }
    }
}
