<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    private PHPMailer $mailer;

    public function __construct()
    {
        $this->initializeMailer();
        $this->configureSMTP();
    }

    /** Cria a instância do PHPMailer */
    private function initializeMailer(): void
    {
        $this->mailer = new PHPMailer(true);
    }

    /** Configura o PHPMailer para envio via SMTP usando variáveis do .env */
    private function configureSMTP(): void
    {
        $this->mailer->isSMTP();
        $this->mailer->Host       = $_ENV['SMTP_HOST']       ?? '';
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = $_ENV['SMTP_USER']       ?? '';
        $this->mailer->Password   = $_ENV['SMTP_PASS']       ?? '';
        $this->mailer->SMTPSecure = 'tls';
        $this->mailer->Port       = $_ENV['SMTP_PORT']       ?? 587;
        $this->mailer->CharSet    = 'UTF-8';
        $this->mailer->setFrom($_ENV['SMTP_FROM_EMAIL'] ?? '', $_ENV['SMTP_FROM_NAME'] ?? 'Mailer');
    }

    /** Prepara remetente, assunto, corpo e formato (texto ou HTML) */
    private function prepareEmail(string $recipient, string $subject, string $body, bool $isHtml = false): void
    {
        $this->mailer->clearAllRecipients();
        $this->mailer->clearAttachments();
        $this->mailer->addAddress($recipient);
        $this->mailer->Subject = $subject;
        $this->mailer->Body    = $body;
        $this->mailer->isHTML($isHtml);
    }

    /**
     * Adiciona um anexo.
     * Aceita dois formatos de array:
     *   ['path' => '/caminho/arquivo.pdf']  — arquivo já existente
     *   ['content' => $binario, 'filename' => 'relatorio.pdf'] — conteúdo em memória
     */
    private function attachFile(array $file): void
    {
        if (isset($file['path'])) {
            $this->mailer->addAttachment($file['path'], $file['filename'] ?? basename($file['path']));
            return;
        }

        if (isset($file['content'], $file['filename'])) {
            $tempFile = $this->createTempFile($file['content'], $file['filename']);
            $this->mailer->addAttachment($tempFile, $file['filename']);
            register_shutdown_function(static fn() => @unlink($tempFile));
            return;
        }

        throw new \InvalidArgumentException(
            "Attachment must contain either 'path', or 'content' and 'filename'."
        );
    }

    /** Cria um arquivo temporário com conteúdo binário e devolve o caminho */
    private function createTempFile(string $content, string $filename): string
    {
        $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('attach_', true) . '_' . $filename;
        file_put_contents($tempFile, $content);
        return $tempFile;
    }

    /**
     * Envia o e‑mail.
     *
     * @param string      $recipient   Endereço de destino
     * @param string      $subject     Assunto
     * @param string      $body        Corpo do e‑mail
     * @param array|null  $attachments Cada item: ['path' => ...] ou ['content' => ..., 'filename' => ...]
     * @param bool        $isHtml      Define se o corpo é HTML
     * @throws Exception  Lança exceções do PHPMailer
     */
    public function send(
        string $recipient,
        string $subject,
        string $body,
        ?array $attachments = null,
        bool $isHtml = false
    ): void {
        $this->prepareEmail($recipient, $subject, $body, $isHtml);

        if (!empty($attachments)) {
            foreach ($attachments as $file) {
                $this->attachFile($file);   // <- passa o array completo
            }
        }

        $this->mailer->send();
    }
}
