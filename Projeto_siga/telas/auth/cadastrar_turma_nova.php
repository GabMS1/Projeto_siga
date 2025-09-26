<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// CORREÇÃO: A tela deve usar o Serviço, não o DAO diretamente.
require_once __DIR__ . '/../../negocio/TurmaServico.php';
require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_turma = $_POST['id_turma'] ?? '';
    $curso = $_POST['curso'] ?? '';
    $serie = $_POST['serie'] ?? '';

    // Lista de valores permitidos pelo ENUM do banco de dados
    $cursos_permitidos = ['Agropecuária', 'Alimentos', 'Informática'];
    $series_permitidas = ['1', '2', '3'];

    if (empty($id_turma) || empty($curso) || empty($serie)) {
        redirect_with_error("Todos os campos são obrigatórios.", "cadastrar_turma_nova.php");
    } elseif (!is_numeric($id_turma) || (int)$id_turma <= 0) {
        redirect_with_error("O ID da Turma deve ser um número inteiro positivo.", "cadastrar_turma_nova.php");
    } elseif (!in_array($curso, $cursos_permitidos) || !in_array($serie, $series_permitidas)) {
        redirect_with_error("Por favor, selecione um curso e uma série válidos.", "cadastrar_turma_nova.php");
    } else {
        $turmaServico = new TurmaServico();
        
        if ($turmaServico->cadastrarTurma((int)$id_turma, $curso, $serie)) {
            redirect_with_success("Turma " . htmlspecialchars($id_turma) . " criada com sucesso!", "gerenciar_turmas.php");
        } else {
            $error_message = $_SESSION['op_error'] ?? "Erro desconhecido ao criar turma.";
            redirect_with_error($error_message, "cadastrar_turma_nova.php");
        }
    }
}

// Restante do HTML permanece igual...
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Criar Nova Turma - SIGA</title>
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
        .sidebar a:hover { background-color: rgba(255, 255, 255, 0.1); }
        .sidebar a.active { background-color: var(--secondary-color); }
        
        .main-content { margin-left: 260px; padding: 30px; flex: 1; width: calc(100% - 260px); }
        .container { background-color: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px var(--shadow-color); max-width: 700px; margin: auto; }
        
        h1 { color: var(--text-color); margin-bottom: 30px; text-align: center; font-weight: 600; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; color: #555; }
        input, select {
            width: 100%; padding: 12px 15px; border-radius: 50px; border: 1px solid var(--border-color); box-sizing: border-box; font-size: 1em;
        }
        input:focus, select:focus { outline: none; border-color: var(--secondary-color); box-shadow: 0 0 0 3px rgba(106, 153, 78, 0.2); }
        .form-actions { display: flex; gap: 15px; margin-top: 30px; }
        .btn { padding: 12px 30px; border-radius: 50px; cursor: pointer; text-decoration: none; font-weight: 600; border: none; }
        .btn-success { background-color: var(--primary-color); color: white; flex-grow: 1; }
        .btn-secondary { background-color: #ccc; color: #555; }

        .alert { padding: 15px; margin-bottom: 20px; border-radius: 10px; font-weight: 500; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-header"><h2>SIGA</h2></div>
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
        <h1>Criar Nova Turma</h1>
        <?php display_session_alerts(); ?>
        <form action="" method="POST">
            <div class="form-group">
                <label for="id_turma">ID da Turma (Ex: 301, 202)</label>
                <input type="number" id="id_turma" name="id_turma" min="1" required>
            </div>
            <div class="form-group">
                <label for="curso">Curso</label>
                <select id="curso" name="curso" required>
                    <option value="">Selecione o Curso</option>
                    <option value="Agropecuária">Agropecuária</option>
                    <option value="Alimentos">Alimentos</option>
                    <option value="Informática">Informática</option>
                </select>
            </div>
            <div class="form-group">
                <label for="serie">Série</label>
                <select id="serie" name="serie" required>
                    <option value="">Selecione a Série</option>
                    <option value="1">1º Ano</option>
                    <option value="2">2º Ano</option>
                    <option value="3">3º Ano</option>
                </select>
            </div>
            <div class="form-actions">
                <a href="gerenciar_turmas.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-success">Criar Turma</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>