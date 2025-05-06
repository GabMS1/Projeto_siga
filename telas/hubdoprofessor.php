<?php
// Simulação dos dados de cada disciplina
$disciplinas = [
    ['nome' => 'Matemática', 'substituidas' => 4, 'faltas' => 2, 'carga_total' => 20],
    ['nome' => 'Português', 'substituidas' => 1, 'faltas' => 3, 'carga_total' => 20],
    ['nome' => 'História', 'substituidas' => 0, 'faltas' => 5, 'carga_total' => 20],
    ['nome' => 'Geografia', 'substituidas' => 3, 'faltas' => 0, 'carga_total' => 20],
];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Controle de Aulas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            padding: 40px;
        }

        .grafico {
            width: 150px;
            height: 150px;
            position: relative;
        }

        .grafico svg {
            transform: rotate(-90deg);
        }

        .circle-bg {
            fill: none;
            stroke: #eee;
            stroke-width: 12;
        }

        .circle-falta {
            fill: none;
            stroke: #ff4d4d; /* Vermelho para faltas */
            stroke-width: 12;
            stroke-dasharray: 314;
            stroke-dashoffset: 314;
            transition: stroke-dashoffset 1s;
        }

        .circle-subst {
            fill: none;
            stroke: #4caf50; /* Verde para substituições */
            stroke-width: 12;
            stroke-dasharray: 314;
            stroke-dashoffset: 314;
            transition: stroke-dashoffset 1s;
        }

        .grafico span {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-weight: bold;
            text-align: center;
        }

        h3 {
            text-align: center;
            font-size: 14px;
            margin-top: 5px;
        }
    </style>
</head>
<body>

<?php foreach ($disciplinas as $disciplina): 
    $falta_pct = ($disciplina['faltas'] / $disciplina['carga_total']) * 100;
    $subst_pct = ($disciplina['substituidas'] / $disciplina['carga_total']) * 100;

    $falta_offset = 314 - (314 * $falta_pct / 100);
    $subst_offset = 314 - (314 * $subst_pct / 100);
?>
    <div>
        <div class="grafico">
            <svg width="150" height="150">
                <circle class="circle-bg" cx="75" cy="75" r="50" />
                <circle class="circle-falta" cx="75" cy="75" r="50"
                        style="stroke-dashoffset: <?= $falta_offset ?>;" />
                <circle class="circle-subst" cx="75" cy="75" r="50"
                        style="stroke-dashoffset: <?= $subst_offset ?>;" />
            </svg>
            <span>
                <?= $disciplina['faltas'] ?> faltas<br>
                <?= $disciplina['substituidas'] ?> subs.
            </span>
        </div>
        <h3><?= $disciplina['nome'] ?></h3>
    </div>
<?php endforeach; ?>

</body>
</html>
