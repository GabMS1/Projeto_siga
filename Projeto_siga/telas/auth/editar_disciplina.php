<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\editar_disciplina.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../negocio/DisciplinaServico.php';

if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'admin') {
    $_SESSION['login_error'] = "Acesso negado.";
    header("Location: login.php");
    exit();
}

$disciplinaServico = new DisciplinaServico();
$disciplina = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nome = $_POST['nome_disciplina'];
    $ch = $_POST['ch'];
    $aulas_semanais = $_POST['aulas_semanais']; // Captura o novo campo

    if (empty($id) || empty($nome) || empty($ch) || empty($aulas_semanais)) {
        $_SESSION['op_error'] = "Todos os campos são obrigatórios.";
    } elseif ($disciplinaServico->atualizarDisciplina($id, $nome, $ch, $aulas_semanais)) { // Adiciona o novo parâmetro
        $_SESSION['op_success'] = "Disciplina atualizada com sucesso!";
        header("Location: disciplinas.php");
        exit();
    } else {
        $_SESSION['op_error'] = "Erro ao atualizar a disciplina.";
    }
    header("Location: editar_disciplina.php?id=" . $id);
    exit();

} elseif (isset($_GET['id'])) {
    $id = $_GET['id'];
    $disciplina = $disciplinaServico->buscarDisciplina($id);
    if (!$disciplina) {
        $_SESSION['op_error'] = "Disciplina não encontrada.";
        header("Location: disciplinas.php");
        exit();
    }
} else {
    header("Location: disciplinas.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Disciplina - SIGA</title>
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
        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 12px 15px;
            border-radius: 50px;
            border: 1px solid var(--border-color);
            box-sizing: border-box;
            font-size: 1em;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        input:focus {
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
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
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
        <li><a href="disciplinas.php" class="active"><i class="fas fa-book"></i> <span>Disciplinas</span></a></li>
        <li><a href="gerenciar_turmas.php"><i class="fas fa-users"></i> <span>Turmas</span></a></li>
        <li><a href="gerenciar_ausencias.php"><i class="fas fa-calendar-check"></i> <span>Ausências</span></a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Sair</span></a></li>
    </ul>
</div>

<div class="main-content">
    <div class="container">
        <h1>Editar Disciplina</h1>
        
        <?php 
            if(isset($_SESSION['op_success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['op_success']) ?></div>
                <?php unset($_SESSION['op_success']); 
            endif; 
            if(isset($_SESSION['op_error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['op_error']) ?></div>
                <?php unset($_SESSION['op_error']); 
            endif; 
        ?>

        <?php if ($disciplina): ?>
            <form action="editar_disciplina.php" method="POST">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($disciplina['id_disciplina']); ?>">
                
                <div class="form-group">
                    <label for="nome_disciplina">Nome da Disciplina</label>
                    <input type="text" id="nome_disciplina" name="nome_disciplina" value="<?php echo htmlspecialchars($disciplina['nome_disciplina']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="ch">Carga Horária (horas)</label>
                    <input type="number" id="ch" name="ch" value="<?php echo htmlspecialchars($disciplina['ch']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="aulas_semanais">Aulas por Semana</label>
                    <input type="number" id="aulas_semanais" name="aulas_semanais" value="<?php echo htmlspecialchars($disciplina['aulas_semanais']); ?>" required>
                </div>
                
                <div class="form-actions">
                    <a href="disciplinas.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success">Salvar Alterações</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>