<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\gerenciar_turmas.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Proteção de Rota
if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../../negocio/TurmaServico.php';

$turmas = [];
$turmaServico = new TurmaServico();
$turmas = $turmaServico->listarTodasAsTurmas(); // Precisaremos criar este método

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Admin: Gerenciar Turmas</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" rel="stylesheet">
    <style>
        :root { --primary-color: #386641; --secondary-color: #2a9d8f; --text-color: #264653; --bg-light: #f8f9fa; }
        body { margin: 0; font-family: 'Poppins', sans-serif; display: flex; background-color: var(--bg-light); }
        .sidebar { width: 250px; background-color: var(--primary-color); color: white; position: fixed; height: 100%; }
        .sidebar h2 { text-align: center; padding: 20px 0; margin: 0; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar a { color: white; text-decoration: none; display: flex; align-items: center; padding: 12px 25px; }
        .sidebar a i { margin-right: 10px; }
        .sidebar a:hover, .sidebar a.active { background-color: rgba(255, 255, 255, 0.2); }
        .main-content { margin-left: 250px; padding: 30px; flex: 1; }
        .container { background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; color: white; text-decoration: none; }
        .btn-success { background-color: var(--secondary-color); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background-color: #d4edda; color: #155724; }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>Admin SIGA</h2>
    <ul>
        <li><a href="principal_adm.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
        <li><a href="professores.php"><i class="fas fa-chalkboard-teacher"></i> <span>Professores</span></a></li>
        <li><a href="administradores.php"><i class="fas fa-user-shield"></i> <span>Admins</span></a></li>
        <li><a href="disciplinas.php"><i class="fas fa-book"></i> <span>Disciplinas</span></a></li>
        <li><a href="gerenciar_turmas.php" class="active"><i class="fas fa-users"></i> <span>Turmas</span></a></li>
        <li><a href="gerenciar_ausencias.php"><i class="fas fa-calendar-check"></i> <span>Ausências</span></a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Sair</span></a></li>
    </ul>
</div>

<div class="main-content">
    <div class="container">
        <div class="header">
            <h1>Gerenciar Turmas</h1>
            <a href="cadastrar_turma_adm.php" class="btn btn-success">Adicionar Nova Turma</a>
        </div>

        <?php if(isset($_SESSION['op_success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['op_success']) ?></div>
            <?php unset($_SESSION['op_success']); endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Curso</th>
                    <th>Série</th>
                    <th>Disciplina</th>
                    <th>Professor</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($turmas)): ?>
                    <?php foreach ($turmas as $turma): ?>
                        <tr>
                            <td><?= htmlspecialchars($turma['id_turma']) ?></td>
                            <td><?= htmlspecialchars($turma['curso']) ?></td>
                            <td><?= htmlspecialchars($turma['serie']) ?>º Ano</td>
                            <td><?= htmlspecialchars($turma['nome_disciplina']) ?></td>
                            <td><?= htmlspecialchars($turma['nome_professor'] ?? 'Não atribuído') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">Nenhuma turma cadastrada.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>