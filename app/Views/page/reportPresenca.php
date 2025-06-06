<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Relatório de Presença</title>

    <!-- Bootstrap 5 via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />


    <style>
        @page {
            margin: 50px 20mm 50px 20mm;
            /* top, right, bottom, left */
        }

        body {
            font-family: "DM Sans", sans-serif;
            color: #4f4f4f;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }

        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 40px;
            background-color: rgb(255, 255, 255);
            border-bottom: 1px solid #ccc;
            padding: 30px 30mm;
            box-sizing: border-box;
        }

        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 40px;
            background-color: rgb(255, 255, 255);
            border-top: 1px solid #ccc;
            font-size: 10px;
            box-sizing: border-box;
        }

        .content {
            margin-top: 100px;
            /* espaço abaixo do header */
            margin-bottom: 70px;
            /* espaço acima do footer */
        }

        .text-small {
            font-size: 10px;
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

        .presente {
            color: green;
        }

        .ausente {
            color: red;
        }

        .justificado {
            color: blue;
        }
    </style>
</head>

<body>
    <header class="">
        <div class="text-center p-2">
            <img src="<?= $logoBase64 ?>" height="60">
        </div>
    </header>

    <footer class="text-end">

        <i class="fa fa-location-arrow align-middle"></i>&nbsp;
        Rua Alfredo Baldo, 41 – Jardim do Trevo<br>
        Ribeirão Preto – SP – CEP 14093‑174<br>
        <i class="fas fa-phone"></i>&nbsp; Fone (16) 3617‑0919<br>
        <i class="fas fa-calendar-alt"></i>&nbsp; <?= (new DateTime())->format('d/m/Y') ?>

    </footer>


    <main class="content">
        <p class="fs-5 fw-bolder text-center"><?= htmlspecialchars($atendido) ?></p>
        <p class="fs-3 text-center">Relatório de Presença</p>
        <p class="fs-1 text-center">
            <strong>Matrícula:</strong> <?= $matricula ?> &nbsp;
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
            <strong>Frequência:</strong> <?= $frequencia ?>
        </p>
    </main>
</body>

</html>