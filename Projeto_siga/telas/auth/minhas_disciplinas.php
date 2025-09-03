<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\minhas_disciplinas.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../negocio/DisciplinaServico.php';

if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'professor') {
    $_SESSION['login_error'] = "Acesso negado. Faça login como professor.";
    header("Location: login.php");
    exit();
}

$siape = $_SESSION['usuario_logado'];
$disciplinaServico = new DisciplinaServico();
$disciplinas = [];
$mensagem = "";
$sucesso = false;

// Lógica para lidar com a ação de exclusão via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao']) && $_POST['acao'] === 'excluir' && isset($_POST['id_excluir'])) {
        $id_excluir = trim($_POST['id_excluir']);
        if ($disciplinaServico->excluirDisciplina($id_excluir)) {
            $mensagem = "Disciplina excluída com sucesso!";
            $sucesso = true;
        } else {
            $mensagem = "Erro ao excluir a disciplina.";
            $sucesso = false;
        }
    }
}

// Carrega a lista de disciplinas para exibição
try {
    $disciplinas = $disciplinaServico->listarDisciplinasPorProfessor($siape);
} catch (Exception $e) {
    $mensagem = "Erro ao carregar a lista de disciplinas.";
    $sucesso = false;
    error_log("Erro em minhas_disciplinas.php: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Disciplinas</title>
    <style>
        body { margin: 0; font-family: 'Segoe UI', sans-serif; display: flex; background-color: #f4f7f8; }
        .sidebar { width: 220px; height: 100vh; background-color: #386641; color: white; padding-top: 30px; position: fixed; box-shadow: 2px 0 5px rgba(0,0,0,0.1); }
        .sidebar h2 { text-align: center; margin-bottom: 20px; font-size: 22px; color: white; }
        .sidebar ul { list-style: none; padding: 0; margin: 0; }
        .sidebar li { padding: 8px 20px; margin-bottom: 5px; }
        .sidebar a { color: white; text-decoration: none; font-weight: bold; display: block; padding: 8px 12px; border-radius: 4px; transition: background-color 0.3s; }
        .sidebar a:hover { background-color: #4d774e; }
        .sidebar a.active { background-color: #2a5133; }
        .main { margin-left: 220px; padding: 30px; flex: 1; width: calc(100% - 220px); }
        .main h1 { color: #2a9d8f; margin-bottom: 20px; }
        .btn-add { padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; cursor: pointer; border: none; background-color: #386641; color: white; margin-right: 10px; }
        .btn-add:hover { background-color: #2a5133; }
        .table-container { background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.05); margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; color: #555; }
        tr:hover { background-color: #f9f9f9; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; font-weight: bold; }
        .alert-success { background-color: #d4edda; color: #155724; }
        .alert-danger { background-color: #f8d7da; color: #721c24; }
        .actions-cell { display: flex; gap: 5px; }
        .btn-edit { background-color: #007bff; color:white; padding: 5px 10px; border-radius: 4px; text-decoration: none; }
        .btn-delete { background-color: #dc3545; color:white; padding: 5px 10px; border-radius: 4px; border: none; cursor: pointer; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Professor</h2>
    <ul>
        <li><a href="principal.php">📋 Dashboard</a></li>
        <li><a href="minhas_disciplinas.php" class="active">📚 Minhas Disciplinas</a></li>
        <li><a href="turmas.php">🧑‍🏫 Minhas Turmas</a></li>
        <li><a href="logout.php">🚪 Sair</a></li>
    </ul>
</div>

<div class="main">
    <h1>Minhas Disciplinas</h1>

    <?php 
    if (isset($_SESSION['op_success'])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['op_success']) . '</div>';
        unset($_SESSION['op_success']);
    }
    if ($mensagem) {
        echo '<div class="alert ' . ($sucesso ? 'alert-success' : 'alert-danger') . '">' . htmlspecialchars($mensagem) . '</div>';
    }
    ?>

    <a href="vincular_disciplina.php" class="btn-add" style="background-color:#2a9d8f;">Vincular Nova Disciplina</a>

    <div class="table-container">
        <h2>Disciplinas Vinculadas (Total: <?php echo count($disciplinas); ?>)</h2>
        <?php if (!empty($disciplinas)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome da Disciplina</th>
                        <th>Carga Horária</th>
                        <th>Aulas Semanais</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($disciplinas as $disciplina): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($disciplina['id_disciplina']); ?></td>
                            <td><?php echo htmlspecialchars($disciplina['nome_disciplina']); ?></td>
                            <td><?php echo htmlspecialchars($disciplina['ch']); ?>h</td>
                            <td><?php echo htmlspecialchars($disciplina['aulas_semanais']); ?></td>
                            <td class="actions-cell">
                                <a href="editar_disciplina.php?id=<?php echo htmlspecialchars($disciplina['id_disciplina']); ?>" class="btn-edit">Editar</a>
                                </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nenhuma disciplina vinculada a você. Clique no botão acima para vincular uma.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>