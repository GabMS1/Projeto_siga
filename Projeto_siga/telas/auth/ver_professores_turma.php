<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\ver_professores_turma.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../../negocio/TurmaServico.php';

$turma_detalhes = [];
$mensagem = '';
$id_turma_get = null;

if (isset($_GET['id_turma'])) {
    $id_turma_get = filter_input(INPUT_GET, 'id_turma', FILTER_VALIDATE_INT);
    if ($id_turma_get) {
        $turmaServico = new TurmaServico();
        // Chamando o método que retorna a lista completa
        $turma_detalhes = $turmaServico->listarProfessoresDaTurma($id_turma_get);
        
        if (empty($turma_detalhes)) {
            $mensagem = "Nenhuma disciplina ou professor encontrado para esta turma, ou a turma não existe.";
        }
    } else {
        $mensagem = "ID de turma inválido.";
    }
} else {
    header("Location: gerenciar_turmas.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Professores da Turma - SIGA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #386641;
            --secondary-color: #6A994E;
            --background-light: #FBFBFB;
            --text-color: #333;
            --white: #FFFFFF;
            --shadow-color: rgba(0, 0, 0, 0.08);
            --border-color: #E0E0E0;
        }
        body { margin: 0; font-family: 'Poppins', sans-serif; display: flex; justify-content: center; align-items: flex-start; min-height: 100vh; background-color: var(--background-light); padding: 40px; box-sizing: border-box; }
        .container { 
            background-color: white; 
            padding: 40px; 
            border-radius: 15px; 
            box-shadow: 0 5px 15px var(--shadow-color); 
            max-width: 800px; 
            width: 100%; 
        }
        h1 { color: var(--text-color); margin-bottom: 5px; text-align: center; font-weight: 600; font-size: 2em;}
        .turma-subtitulo { 
            font-size: 1.2em; 
            color: #555; 
            margin-bottom: 25px; 
            border-bottom: 1px solid var(--border-color); 
            padding-bottom: 15px;
            text-align: center;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 15px; border-bottom: 1px solid var(--border-color); text-align: left; }
        th { background-color: #f8f9fa; font-weight: 600; text-transform: uppercase; font-size: 0.9em; }
        .btn-back { 
            background-color: #6c757d; 
            color: white; 
            padding: 10px 25px; 
            text-decoration: none; 
            border-radius: 50px; 
            display: inline-block; 
            margin-top: 30px;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        .btn-back:hover { background-color: #5a6268; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 10px; background-color: #f8d7da; color: #721c24; text-align: center; }
        .footer-actions { text-align: center; }
    </style>
</head>
<body>
<div class="container">
    <h1>Professores da Turma</h1>

    <?php if (!empty($turma_detalhes)): ?>
        <p class="turma-subtitulo">
            <strong>Turma:</strong> <?= htmlspecialchars($id_turma_get) ?> | 
            <strong>Curso:</strong> <?= htmlspecialchars($turma_detalhes[0]['curso']) ?> | 
            <strong>Série:</strong> <?= htmlspecialchars($turma_detalhes[0]['serie']) ?>º Ano
        </p>
        <table>
            <thead>
                <tr>
                    <th>Disciplina</th>
                    <th>Professor Atribuído</th>
                    <th>SIAPE</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($turma_detalhes as $detalhe): ?>
                    <tr>
                        <td><?= htmlspecialchars($detalhe['nome_disciplina']) ?></td>
                        <td><?= htmlspecialchars($detalhe['nome_professor'] ?? '<em>Não atribuído</em>') ?></td>
                        <td><?= htmlspecialchars($detalhe['siape_prof'] ?? 'N/A') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <div class="footer-actions">
        <a href="gerenciar_turmas.php" class="btn-back"><i class="fas fa-arrow-left"></i> Voltar</a>
    </div>
</div>
</body>
</html>