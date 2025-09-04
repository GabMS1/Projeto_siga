<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\atribuir_professor_turma.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../../negocio/TurmaServico.php';
require_once __DIR__ . '/../../negocio/ProfessorServico.php';
require_once __DIR__ . '/../../negocio/DisciplinaServico.php';

$mensagem = '';
$turma = null;
$professores = [];

$turmaServico = new TurmaServico();
$professorServico = new ProfessorServico();
$disciplinaServico = new DisciplinaServico();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_disciplina = filter_input(INPUT_POST, 'id_disciplina', FILTER_VALIDATE_INT);
    $siape_prof = $_POST['siape_prof'] ?? '';

    if ($id_disciplina) {
        // Se o siape for vazio, trata como NULL para desvincular
        $siape_prof_final = !empty($siape_prof) ? $siape_prof : null;
        if ($disciplinaServico->atribuirProfessor($id_disciplina, $siape_prof_final)) {
             $_SESSION['op_success'] = "Professor atribuído à disciplina com sucesso!";
             header("Location: gerenciar_turmas.php");
             exit();
        } else {
            $_SESSION['op_error'] = "Erro ao atribuir professor.";
        }
    } else {
        $_SESSION['op_error'] = "ID da disciplina é inválido.";
    }
    header("Location: atribuir_professor_turma.php?id_turma=" . $_POST['id_turma'] . "&id_disciplina=" . $_POST['id_disciplina']);
    exit();
}

if (isset($_GET['id_turma']) && isset($_GET['id_disciplina'])) {
    $id_turma = filter_input(INPUT_GET, 'id_turma', FILTER_VALIDATE_INT);
    $id_disciplina = filter_input(INPUT_GET, 'id_disciplina', FILTER_VALIDATE_INT);
    
    if ($id_turma && $id_disciplina) {
        $turma = $turmaServico->buscarTurmaParaEdicao($id_turma, $id_disciplina); // Método precisa ser ajustado para receber id_disciplina
        $professores = $professorServico->listarProfessores();
    }
    if (!$turma) {
        $_SESSION['op_error'] = "Turma ou disciplina não encontrada.";
        header("Location: gerenciar_turmas.php");
        exit();
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
    <title>Atribuir Professor - SIGA</title>
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
        
        .main-content { margin-left: 260px; padding: 30px; flex: 1; display: flex; justify-content: center; align-items: center; }
        .container { background-color: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px var(--shadow-color); max-width: 600px; width: 100%; }
        
        h1 { color: var(--text-color); margin-bottom: 10px; text-align: center; font-weight: 600; }
        .turma-info { margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid var(--border-color); text-align: center; }
        .turma-info p { margin: 5px 0; color: #555; font-size: 1.1em; }
        .turma-info span { font-weight: 600; color: var(--primary-color); }
        
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; color: #555; }
        select {
            width: 100%;
            padding: 12px 15px;
            border-radius: 50px;
            border: 1px solid var(--border-color);
            box-sizing: border-box;
            font-size: 1em;
            transition: border-color 0.3s, box-shadow 0.3s;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-color: #fff;
            background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23007CB2%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22/%3E%3C/svg%3E');
            background-repeat: no-repeat;
            background-position: right 15px top 50%;
            background-size: .65em auto;
            padding-right: 30px;
        }
        select:focus {
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
        <h1>Atribuir Professor</h1>
        
        <?php if (isset($_SESSION['op_error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['op_error']); ?></div>
            <?php unset($_SESSION['op_error']); ?>
        <?php endif; ?>

        <?php if ($turma): ?>
            <div class="turma-info">
                <p><strong>Turma:</strong> <span><?= htmlspecialchars($turma['id_turma']) ?></span></p>
                <p><strong>Curso:</strong> <span><?= htmlspecialchars($turma['curso']) ?> - <?= htmlspecialchars($turma['serie']) ?>º Ano</span></p>
                <p><strong>Disciplina:</strong> <span><?= htmlspecialchars($turma['nome_disciplina']) ?></span></p>
            </div>
            
            <form action="atribuir_professor_turma.php" method="POST">
                <input type="hidden" name="id_turma" value="<?= htmlspecialchars($turma['id_turma']); ?>">
                <input type="hidden" name="id_disciplina" value="<?= htmlspecialchars($turma['id_disciplina']); ?>">

                <div class="form-group">
                    <label for="siape_prof">Selecione o Professor</label>
                    <select id="siape_prof" name="siape_prof">
                        <option value="">-- Desvincular Professor --</option>
                        <?php foreach ($professores as $professor): ?>
                            <option value="<?= htmlspecialchars($professor['siape_prof']); ?>" 
                                <?= ($turma['siape_prof'] == $professor['siape_prof']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($professor['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-actions">
                     <a href="gerenciar_turmas.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success">Salvar Atribuição</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>