<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\ver_professores_turma.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteção de Rota
if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../../negocio/TurmaServico.php';

$turma_detalhes = null;
$mensagem = '';

if (isset($_GET['id_turma'])) {
    $id_turma = filter_input(INPUT_GET, 'id_turma', FILTER_VALIDATE_INT);
    if ($id_turma) {
        $turmaServico = new TurmaServico();
        $turma_detalhes = $turmaServico->listarProfessoresDaTurma($id_turma);
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
    <title>Professores da Turma</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary-color: #386641; --secondary-color: #2a9d8f; --text-color: #264653; --bg-light: #f8f9fa; }
        body { margin: 0; font-family: 'Poppins', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: var(--bg-light); }
        .container { background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); max-width: 800px; width: 100%; }
        h1 { color: var(--text-color); margin-bottom: 5px; }
        .turma-subtitulo { font-size: 1.2em; color: #555; margin-bottom: 25px; border-bottom: 1px solid #eee; padding-bottom: 10px;}
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; }
        .btn-secondary { background-color: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<div class="container">
    <h1>Professores da Turma</h1>

    <?php if (!empty($turma_detalhes)): ?>
        <p class="turma-subtitulo">
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
                        <td><?= htmlspecialchars($detalhe['nome_professor'] ?? 'Não atribuído') ?></td>
                        <td><?= htmlspecialchars($detalhe['siape_prof'] ?? 'N/A') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <a href="gerenciar_turmas.php" class="btn-secondary">Voltar</a>
</div>
</body>
</html>