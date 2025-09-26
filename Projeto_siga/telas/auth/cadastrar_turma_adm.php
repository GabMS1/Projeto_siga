<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\cadastrar_turma_adm.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'admin') {
    $_SESSION['login_error'] = "Acesso negado.";
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../../negocio/TurmaServico.php';
require_once __DIR__ . '/../../negocio/DisciplinaServico.php';
require_once __DIR__ . '/../../DAO/TurmaDAO.php'; // Para listar turmas
require_once __DIR__ . '/helpers.php';

$disciplinas = [];
$mensagem = "";

try {
    $disciplinaServico = new DisciplinaServico();
    $disciplinas = $disciplinaServico->listarDisciplinas();
    
    $turmaServico = new TurmaServico();
    $turmasRaw = $turmaServico->listarTodasAsTurmas();
    $turmas = [];
    foreach ($turmasRaw as $t) {
        $turmas[$t['id_turma']] = $t['id_turma'] . ' - ' . $t['curso'] . ' ' . $t['serie'] . 'º Ano';
    }
    $turmas = array_unique($turmas);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id_turma = $_POST['id_turma'] ?? '';
        $id_disciplina = $_POST['id_disciplina'] ?? '';

        if (empty($id_turma) || empty($id_disciplina)) {
            redirect_with_error("Todos os campos são obrigatórios.", "cadastrar_turma_adm.php");
        } else {
            if ($turmaServico->associarDisciplina((int)$id_turma, (int)$id_disciplina)) {
                redirect_with_success("Disciplina associada à turma " . htmlspecialchars($id_turma) . " com sucesso!", "gerenciar_turmas.php");
            } else {
                // A mensagem de erro específica já foi definida no TurmaDAO e está em $_SESSION['op_error'].
                // Se a sessão não tiver a mensagem, usamos uma mensagem padrão.
                $error_message = $_SESSION['op_error'] ?? "Erro desconhecido ao associar disciplina.";
                redirect_with_error($error_message, "cadastrar_turma_adm.php");
            }
        }
    }

try {
    $disciplinaServico = new DisciplinaServico();
    $disciplinas = $disciplinaServico->listarDisciplinas();
    
    $turmaServico = new TurmaServico();
    $turmasRaw = $turmaServico->listarTodasAsTurmas();
    $turmas = [];
    foreach ($turmasRaw as $t) {
        $turmas[$t['id_turma']] = $t['id_turma'] . ' - ' . $t['curso'] . ' ' . $t['serie'] . 'º Ano';
    }
    $turmas = array_unique($turmas);

} catch (Exception $e) {
    $mensagem = "Erro ao carregar dados: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Associar Disciplina à Turma - SIGA</title>
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
        body { margin: 0; font-family: 'Poppins', sans-serif; display: flex; background-color: var(--background-light); }
        .sidebar { width: 260px; background-color: var(--primary-color); color: var(--white); position: fixed; height: 100%; box-shadow: 2px 0 10px var(--shadow-color); display: flex; flex-direction: column; padding-top: 20px; }
        .sidebar-header { padding: 0 25px; text-align: center; margin-bottom: 30px; }
        .sidebar-header h2 { font-size: 1.8em; font-weight: 700; margin: 0; color: var(--white); }
        .sidebar ul { list-style: none; padding: 0; margin: 0; width: 100%; }
        .sidebar a { color: var(--white); text-decoration: none; font-weight: 500; display: flex; align-items: center; padding: 15px 25px; transition: all 0.3s ease; border-left: 4px solid transparent; }
        .sidebar a i { margin-right: 15px; font-size: 1.2em; width: 20px; text-align: center; }
        .sidebar a:hover { background-color: rgba(255, 255, 255, 0.1); border-left-color: var(--accent-color); }
        .sidebar a.active { background-color: var(--secondary-color); border-left-color: var(--accent-color); font-weight: 600; }
        
        .main-content { margin-left: 260px; padding: 30px; flex: 1; width: calc(100% - 260px); }
        .container { background-color: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px var(--shadow-color); max-width: 700px; margin: auto; }
        
        h1 { color: var(--text-color); margin-bottom: 30px; text-align: center; font-weight: 600; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; color: #555; }
        input, select {
            width: 100%;
            padding: 12px 15px;
            border-radius: 50px;
            border: 1px solid var(--border-color);
            box-sizing: border-box;
            font-size: 1em;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        input:focus, select:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(106, 153, 78, 0.2);
        }
        .form-actions { display: flex; gap: 15px; margin-top: 30px; }
        .btn { padding: 12px 30px; border-radius: 50px; cursor: pointer; text-decoration: none; font-weight: 600; border: none; transition: background-color 0.3s, transform 0.2s; }
        .btn-success { background-color: var(--primary-color); color: white; flex-grow: 1; }
        .btn-success:hover { background-color: var(--secondary-color); transform: translateY(-2px); }
        .btn-secondary { background-color: #ccc; color: #555; }
        .btn-secondary:hover { background-color: #bbb; }

        .alert { padding: 15px; margin-bottom: 20px; border-radius: 10px; font-weight: 500; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-header">
        <h2>SIGA</h2>
    </div>
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
        <h1>Associar Disciplina à Turma</h1>
        <?php
        display_session_alerts(); // Exibe alertas de sucesso ou erro
        if (!empty($mensagem)) { echo '<p class="alert alert-danger">' . htmlspecialchars($mensagem) . '</p>'; }
        ?>
        <form action="" method="POST">
            <div class="form-group">
                <label for="id_turma">Turma</label>
                <select id="id_turma" name="id_turma" required>
                    <option value="">Selecione uma Turma</option>
                    <?php foreach ($turmas as $id => $nome): ?>
                        <option value="<?= htmlspecialchars($id); ?>"><?= htmlspecialchars($nome); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="id_disciplina">Disciplina</label>
                <select id="id_disciplina" name="id_disciplina" required>
                    <option value="">Selecione uma Disciplina</option>
                    <?php if (!empty($disciplinas)): ?>
                        <?php foreach ($disciplinas as $disciplina): ?>
                            <option value="<?= htmlspecialchars($disciplina['id_disciplina']); ?>">
                                <?= htmlspecialchars($disciplina['nome_disciplina']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>Nenhuma disciplina cadastrada.</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-actions">
                <a href="gerenciar_turmas.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-success">Associar Disciplina</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>