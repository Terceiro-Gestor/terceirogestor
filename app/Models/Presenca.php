<?php
// app/Models/Presenca.php
namespace App\Models;

use App\Config\Database;
use PDO;

class Presenca
{
    /**
     * Retorna todas as presenças de uma matrícula em um mês.
     *
     * @param string $matricula  "2025-0034"
     * @param string $mesPt      "Maio"
     * @return array
     */
    public static function listaPorMatriculaMes(string $matricula, string $mesPt): array
    {
        $mesNum = self::mesPtBrToInt($mesPt);
        $db     = Database::getInstance();

        $sql = "
            SELECT
              pr.matriculas_idmatricula,
              p.nome,
              pr.data,
              pr.situacao,
              pr.mes,
              pr.ano,
              DAY(pr.data) AS dia
            FROM presencas pr
            JOIN pessoas p ON p.idpessoa = pr.pessoas_idpessoa
            WHERE pr.matriculas_idmatricula = :matricula
              AND MONTH(pr.data) = :mes
              AND YEAR(pr.data)  = YEAR(CURDATE())
            ORDER BY pr.data";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            'matricula' => $matricula,
            'mes'       => $mesNum
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private static function mesPtBrToInt(string $mes): int
    {
        $map = [
            'janeiro'=>1,'fevereiro'=>2,'março'=>3,'abril'=>4,'maio'=>5,'junho'=>6,
            'julho'=>7,'agosto'=>8,'setembro'=>9,'outubro'=>10,'novembro'=>11,'dezembro'=>12
        ];
        return $map[mb_strtolower($mes)] ?? 0;
    }
}
