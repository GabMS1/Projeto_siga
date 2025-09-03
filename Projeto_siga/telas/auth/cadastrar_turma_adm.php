<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\cadastrar_turma_adm.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteção de Rota: Apenas administradores
if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'admin') {
    $_SESSION['login_error'] = "Acesso negado.";
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../../negocio/TurmaServico.php';
require_once __DIR__ . '/../../negocio/DisciplinaServico.php';

$disciplinas = [];
$mensagem = "";

try {
    // Busca TODAS as disciplinas para o admin poder escolher
    $disciplinaServico = new DisciplinaServico();
    $disciplinas = $disciplinaServico->listarDisciplinas();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id_turma = $_POST['id_turma'] ?? '';
        $curso = $_POST['curso'] ?? '';
        $serie = $_POST['serie'] ?? '';
        $id_disciplina = $_POST['id_disciplina'] ?? '';

        if (empty($id_turma) || empty($curso) || empty($serie) || empty($id_disciplina)) {
            $_SESSION['cadastro_turma_error'] = "Todos os campos são obrigatórios.";
        } elseif (!is_numeric($id_turma) || (int)$id_turma <= 0) {
            $_SESSION['cadastro_turma_error'] = "O ID da Turma deve ser um número inteiro positivo.";
        } else {
            $turmaServico = new TurmaServico();
            $turmaServico->set("id_turma", (int)$id_turma);
            $turmaServico->set("curso", $curso);
            $turmaServico->set("serie", $serie);
            $turmaServico->set("id_disciplina", (int)$id_disciplina);

            if ($turmaServico->cadastrar()) {
                $_SESSION['op_success'] = "Turma cadastrada com sucesso! ID: " . htmlspecialchars($id_turma);
                header("Location: gerenciar_turmas.php"); 
                exit();
            } else {
                if (!isset($_SESSION['cadastro_turma_error'])) {
                    $_SESSION['cadastro_turma_error'] = "Erro ao cadastrar turma. Verifique se o ID já existe.";
                }
            }
        }
        header("Location: cadastrar_turma_adm.php"); 
        exit();
    }
} catch (Exception $e) {
    $mensagem = "Erro ao carregar dados: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Admin: Cadastrar Nova Turma</title>
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
        .container { background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); max-width: 600px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; }
        input, select { width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ccc; box-sizing: border-box; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; color: white; text-decoration: none; display: inline-block; }
        .btn-success { background-color: var(--secondary-color); }
        .btn-secondary { background-color: #6c757d; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-danger { background-color: #f8d7da; color: #721c24; }
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
        <h1>Cadastrar Nova Turma</h1>
        <?php
        if (isset($_SESSION['cadastro_turma_error'])) {
            echo '<p class="alert alert-danger">' . htmlspecialchars($_SESSION['cadastro_turma_error']) . '</p>';
            unset($_SESSION['cadastro_turma_error']);
        }
        if (!empty($mensagem)) {
            echo '<p class="alert alert-danger">' . htmlspecialchars($mensagem) . '</p>';
        }
        ?>
        <form action="" method="POST">
            <div class="form-group">
                <label for="id_turma">ID da Turma (Ex: 301, 202):</label>
                <input type="number" id="id_turma" name="id_turma" min="1" required>
            </div>
            <div class="form-group">
                <label for="curso">Curso:</label>
                <select id="curso" name="curso" required>
                    <option value="">Selecione o Curso</option>
                    <option value="Agropecuária">Agropecuária</option>
                    <option value="Alimentos">Alimentos</option>
                    <option value="Informática">Informática</option>
                </select>
            </div>
            <div class="form-group">
                <label for="serie">Série:</label>
                <select id="serie" name="serie" required>
                    <option value="">Selecione a Série</option>
                    <option value="1">1º Ano</option>
                    <option value="2">2º Ano</option>
                    <option value="3">3º Ano</option>
                </select>
            </div>
            <div class="form-group">
                <label for="id_disciplina">Disciplina:</label>
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
            <button type="submit" class="btn btn-success">Cadastrar Turma</button>
            <a href="gerenciar_turmas.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
</body>
</html>