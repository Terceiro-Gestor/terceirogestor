<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Relatório de Presença</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: "DM Sans", sans-serif;
            color: #4f4f4f;
            background: #ffffff;
            margin: 0;
            padding: 0;
            margin-top: 60px; /* aumenta o espaço acima para a logo */
        }

        .content {
            margin-top: 40px;
            margin-bottom: 40px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        thead {
            background: #e5e5e5;
            font-weight: bold;
            -webkit-print-color-adjust: exact;
        }

        tbody tr:nth-child(even) td {
            background: #f5f5f5;
            -webkit-print-color-adjust: exact;
        }

        .text-success {
            color: green;
        }

        .text-danger {
            color: red;
        }

        .text-primary {
            color: blue;
        }
    </style>
</head>

<body>
    <main class="content">
        <p class="fs-5 fw-bolder text-center"><?= htmlspecialchars($atendido) ?></p>
        <p class="fs-3 text-center">Relatório de Presença</p>
        <p class="fs-3 text-center">
            <strong>Matrícula:</strong> <?= htmlspecialchars($matricula) ?> &nbsp;
            <strong>Referente:</strong> <?= $mes ?>/<?= $ano ?>
        </p>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Dia</th>
                    <th>Situação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dados as $r):
                    $classe = match (strtolower($r['situacao'])) {
                        'presente'    => 'text-success',
                        'ausente'     => 'text-danger',
                        'justificado' => 'text-primary',
                        default       => ''
                    };
                ?>
                    <tr>
                        <td><?= (new DateTime($r['data']))->format('d/m/Y') ?></td>
                        <td><?= $r['dia'] ?></td>
                        <td class="<?= $classe ?>"><strong><?= strtoupper(substr($r['situacao'], 0, 1)) ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p class="text-center mt-3">
            <strong>Presente:</strong> <?= $totalPresente ?> &nbsp;
            <strong>Ausente:</strong> <?= $totalAusente ?> &nbsp;
            <strong>Frequência:</strong> <?= $frequencia ?> %
        </p>
    </main>
</body>

</html>