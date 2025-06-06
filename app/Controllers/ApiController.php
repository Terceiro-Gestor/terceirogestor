<?php
// app/Controllers/RelatorioController.php
namespace App\Controllers;

use App\Models\Presenca;
use App\Services\Mailer;
use Dompdf\Dompdf;
use InvalidArgumentException;

class ApiController
{
    public function presencaIndividual(): void
    {
        $req = json_decode(file_get_contents('php://input'), true) ?: [];
        $matricula = $req['matricula'] ?? null;
        $mes       = $req['mes']       ?? null;
        $emailDest = $req['email']     ?? null;

        if (!$matricula || !$mes || !$emailDest) {
            http_response_code(400);
            echo json_encode(['erro' => 'matricula, mes e email são obrigatórios']);
            return;
        }

        $dados = Presenca::listaPorMatriculaMes($matricula, $mes);
        if (!$dados) {
            http_response_code(404);
            echo json_encode(['erro' => "Sem registros para $matricula em $mes"]);
            return;
        }

        /* ---- métricas ---- */
        $totalPresente = array_sum(array_map(fn($r) => strtolower($r['situacao']) === 'presente', $dados));
        $totalAusente  = array_sum(array_map(fn($r) => strtolower($r['situacao']) === 'ausente',  $dados));
        $totalDias     = $totalPresente + $totalAusente;
        $frequencia    = $totalDias ? number_format($totalPresente / $totalDias * 100, 1) . '%' : '0%';

        /* ---- logo em Base64 ---- */
        $path = __DIR__ . '/../Views/page/logo.png';
        $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($path));
        
        /* ---- renderiza HTML ---- */
        $atendido = mb_strtoupper($dados[0]['nome']);
        $ano      = $dados[0]['ano'];

        ob_start();
        require __DIR__ . '/../Views/page/reportPresenca.php';
        $html = ob_get_clean();

        /* ---- HTML -> PDF ---- */
        $dompdf = new Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfBin = $dompdf->output();

        /* ---- salva em disco (sem enviar e‑mail) ---- */
        $dir = __DIR__ . '/../../storage/';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $nomeArquivo = sprintf(
            'Relatorio_%s_%s_%s.pdf',
            preg_replace('/\s+/', '_', $atendido), // remove espaços
            $mes,
            date('YmdHis')
        );

        $caminho = $dir . $nomeArquivo;
        file_put_contents($caminho, $pdfBin);

        if (!file_exists($caminho)) {
            http_response_code(500);
            echo json_encode(['erro' => 'Falha ao salvar PDF.']);
            return;
        }

        /* ---- resposta da API ---- */
        echo json_encode([
            'ok'        => true,
            'registros' => count($dados),
            'arquivo'   => $nomeArquivo,
            'caminho'   => $caminho
        ]);

        echo json_encode(['ok' => true, 'registros' => count($dados)]);
    }
}
