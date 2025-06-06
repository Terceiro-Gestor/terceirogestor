<?php
namespace App\Models;

use App\Config\Database;
use PDO;
use InvalidArgumentException;

class Report
{
    /** Relatorio => tabela */
    private const MAP = [
        'presenca'     => 'presencas',
        'ocorrencias'  => 'ocorrencias',
        'suspensoes'   => 'suspensoes',
        'diario'       => 'diario',
        'indicadores'  => 'indicadores',
    ];

    /**
     * Busca registros conforme parâmetros.
     *
     * @param string      $relatorio  presenca|ocorrencias|...
     * @param string      $tipo       individual|grupo|geral
     * @param string|null $mes        Janeiro, Fevereiro, ...
     * @param int|null    $id         user_id (individual) ou group_id (grupo)
     * @return array
     */
    public static function fetch(string $relatorio, string $tipo, ?string $mes, ?int $id = null): array
    {
        $table = self::MAP[strtolower($relatorio)] ?? null;
        if (!$table) {
            throw new InvalidArgumentException("Relatório desconhecido: $relatorio");
        }

        [$ini, $fim] = self::periodoMes($mes);          // '2025-02-01', '2025-02-28'

        $db   = Database::getInstance();
        $sql  = "SELECT * FROM `$table` WHERE data BETWEEN :ini AND :fim";

        if ($tipo === 'individual') {
            $sql .= " AND user_id = :id";
        } elseif ($tipo === 'grupo') {
            $sql .= " AND group_id = :id";
        }

        $stmt = $db->prepare($sql);
        $stmt->bindValue('ini', $ini);
        $stmt->bindValue('fim', $fim);
        if ($tipo !== 'geral') {
            $stmt->bindValue('id', $id, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ---------- helpers ---------- */

    /** Converte “Fevereiro” → 2 */
    private static function mesToInt(string $mes): int
    {
        static $map = [
            'janeiro'=>1,'fevereiro'=>2,'março'=>3,'abril'=>4,'maio'=>5,'junho'=>6,
            'julho'=>7,'agosto'=>8,'setembro'=>9,'outubro'=>10,'novembro'=>11,'dezembro'=>12
        ];
        $n = $map[mb_strtolower($mes)] ?? null;
        if (!$n) {
            throw new InvalidArgumentException("Mês inválido: $mes");
        }
        return $n;
    }

    /** Retorna primeiro e último dia do mês solicitado */
    private static function periodoMes(string $mes): array
    {
        $year = date('Y');                       // ou parametrize se quiser outro ano
        $m    = self::mesToInt($mes);
        $ini  = sprintf('%04d-%02d-01', $year, $m);
        $fim  = date('Y-m-t', strtotime($ini)); // último dia do mês
        return [$ini, $fim];
    }
}
