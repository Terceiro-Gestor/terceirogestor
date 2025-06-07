<?php
// app/Controllers/ReportController.php
namespace App\Controllers;

use App\Models\Presenca;
use App\Services\Mailer;
use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;

class ReportController
{
    private Mailer $mailer;

    public function __construct()
    {
        $this->mailer = new Mailer();
    }

    public function sendAttendanceReport(): void
    {
        set_time_limit(60);

        $req = json_decode(file_get_contents('php://input'), true) ?: [];

        [$matricula, $mes, $emailDest] = $this->validarRequisicao($req);
        $dados = Presenca::listaPorMatriculaMes($matricula, $mes);

        if (!$dados) {
            http_response_code(404);
            echo json_encode(['erro' => "Sem registros para $matricula em $mes"]);
            return;
        }

        $atendido = mb_strtoupper($dados[0]['nome']);
        $ano = $dados[0]['ano'] ?? date('Y');

        $html = $this->gerarHtmlRelatorio($dados);
        $dompdf = $this->configurarDompdf($html);
        $this->adicionarHeaderFooter($dompdf);

        $pdfBin = $dompdf->output();

        [$assunto, $mensagem, $anexos] = $this->montarEmail($atendido, $mes, $ano, $pdfBin);

        try {
            $this->enviarEmail($emailDest, $assunto, $mensagem, $anexos);
            echo json_encode([
                'ok'        => true,
                'registros' => count($dados),
                'arquivo'   => $anexos[0]['filename'],
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Falha ao enviar e-mail: ' . $e->getMessage()]);
        }
    }

    private function validarRequisicao(array $req): array
    {
        $matricula = $req['matricula'] ?? null;
        $mes = $req['mes'] ?? null;
        $email = $req['email'] ?? null;

        if (!$matricula || !$mes || !$email) {
            http_response_code(400);
            echo json_encode(['erro' => 'matricula, mes e email são obrigatórios']);
            exit;
        }

        return [$matricula, $mes, $email];
    }

    private function gerarHtmlRelatorio(array $dados): string
    {
        $totalPresente = count(array_filter($dados, fn($d) => $d['situacao'] === 'Presente'));
        $totalAusente = count(array_filter($dados, fn($d) => $d['situacao'] === 'Ausente'));
        $totalDias = count($dados);
        $frequencia = $totalDias > 0 ? round(($totalPresente / $totalDias) * 100, 1) : 0;

        extract([
            'dados' => $dados,
            'atendido' => mb_strtoupper($dados[0]['nome']),
            'matricula' => $dados[0]['matriculas_idmatricula'],
            'mes' => $dados[0]['mes'],
            'ano' => $dados[0]['ano'],
            'totalPresente' => $totalPresente,
            'totalAusente' => $totalAusente,
            'frequencia' => $frequencia,
        ]);

        // Inicia buffer e inclui o template
        ob_start();
        require __DIR__ . '/../Views/reports/reportPresenca.php';
        return ob_get_clean();
    }

    private function configurarDompdf(string $html): Dompdf
    {
        $options = new Options();
        $options->setIsRemoteEnabled(true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf;
    }

    private function adicionarHeaderFooter(Dompdf $dompdf): void
    {
        $canvas = $dompdf->getCanvas();
        $w = $canvas->get_width();
        $h = $canvas->get_height();

        $font = $dompdf->getFontMetrics()->getFont('Helvetica', 'normal');
        $size = 7;
        $color = [0, 0, 0];

        // Header com logo centralizado
        $pathLogo = __DIR__ . '/../Views/reports/logo.png';
        if (file_exists($pathLogo)) {
            [$origWidth, $origHeight] = getimagesize($pathLogo);
            $maxWidth = 70;
            $scale = $maxWidth / $origWidth;
            $logoWidth = $origWidth * $scale;
            $logoHeight = $origHeight * $scale;
            $logoX = ($w - $logoWidth) / 2;
            $logoY = 10;
            $canvas->image($pathLogo, $logoX, $logoY, $logoWidth, $logoHeight);
        }

        // Footer: esquerda (data e página)
        $leftText = "Página {PAGE_NUM} de {PAGE_COUNT} | " . (new \DateTime())->format('d/m/Y');
        $canvas->page_text(30, $h - 45, $leftText, $font, $size, $color);

        // Footer: direita (endereço da empresa)
        $rightText = "Rua Alfredo Baldo, 41 – Jardim do Trevo\nRibeirão Preto – SP – CEP 14093-174\nFone (16) 3617-0919";
        $lineHeight = 12;
        $marginRight = 30; // margem direita

        foreach (explode("\n", $rightText) as $i => $line) {
            $textWidth = $dompdf->getFontMetrics()->getTextWidth($line, $font, $size);
            $rightX = $w - $textWidth - $marginRight;
            $canvas->page_text($rightX, $h - 45 + ($i * $lineHeight), $line, $font, $size, $color);
        }
    }


    private function montarEmail(string $atendido, string $mes, string $ano, string $pdfBin): array
    {
        $filename = sprintf(
            'Relatorio_%s_%s_%s.pdf',
            preg_replace('/\s+/', '_', $atendido),
            $mes,
            $ano
        );

        $assunto = "Relatório de Presença - $atendido - $mes";
        $mensagem = "Olá,\n\nSegue em anexo o relatório de presença referente ao mês $mes para $atendido.\n\nAtenciosamente,\nEquipe Alvorada!";

        $anexos = [[
            'filename' => $filename,
            'content' => $pdfBin
        ]];

        return [$assunto, $mensagem, $anexos];
    }

    private function enviarEmail(string $para, string $assunto, string $mensagem, array $anexos): void
    {
        $this->mailer->send($para, $assunto, $mensagem, $anexos, false);
    }
}
