<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\editar_professor.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../negocio/ProfessorServico.php';

if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'admin') {
    $_SESSION['login_error'] = "Acesso negado.";
    header("Location: login.php");
    exit();
}

$professorServico = new ProfessorServico();
$mensagem = '';
$sucesso = false;
$professor = null;

if (isset($_GET['siape'])) {
    $siape = $_GET['siape'];
    $professor = $professorServico->buscarProfessor($siape);
    if (!$professor) {
        $mensagem = "Professor não encontrado.";
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siape = $_POST['siape'];
    $nome = $_POST['nome'];
    $nova_senha = $_POST['nova_senha'] ?? null;

    if ($professorServico->atualizarProfessor($siape, $nome, $nova_senha)) {
        $mensagem = "Professor atualizado com sucesso!";
        $sucesso = true;
        $professor = $professorServico->buscarProfessor($siape);
    } else {
        $mensagem = "Erro ao atualizar professor.";
        $sucesso = false;
        $professor = ['siape_prof' => $siape, 'nome' => $nome];
    }
} else {
    header("Location: professores.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Professor</title>
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
        .main h1 { color: #2a9d8f; margin-bottom: 30px; }

        .btn { padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; cursor: pointer; border: none; }
        .btn-success { background-color: #386641; color: white; }
        .btn-success:hover { background-color: #2a5133; }
        .btn-info { background-color: #5bc0de; color: white; }
        .btn-info:hover { background-color: #31b0d5; }

        .form-container { background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
        .form-container label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        .form-container input[type="text"], 
        .form-container input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; font-weight: bold; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Administrador</h2>
    <ul>
        <li><a href="principal_adm.php">📊 Dashboard</a></li>
        <li><a href="professores.php" class="active">🧑‍🏫 Gerenciar Professores</a></li>
        <li><a href="administradores.php">⚙️ Gerenciar Admins</a></li>
        <li><a href="logout.php">🚪 Sair</a></li>
    </ul>
</div>

<div class="main">
    <h1>Editar Professor</h1>
    <div class="form-container">
        <?php if ($mensagem): ?>
            <div class="alert <?php echo $sucesso ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <?php if ($professor): ?>
            <form action="editar_professor.php" method="POST">
                <input type="hidden" name="siape" value="<?php echo htmlspecialchars($professor['siape_prof']); ?>">
                
                <label for="nome">Nome Completo:</label>
                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($professor['nome']); ?>" required>

                <label for="nova_senha">Nova Senha (deixe em branco para não alterar):</label>
                <input type="password" id="nova_senha" name="nova_senha">

                <button type="submit" class="btn btn-success">Salvar Alterações</button>
                <a href="professores.php" class="btn btn-info">Cancelar</a>
            </form>
        <?php else: ?>
            <p>Professor não encontrado ou erro de acesso.</p>
            <a href="professores.php" class="btn btn-info">Voltar</a>
        <?php endif; ?>
    </div>
</div>

</body>
</html>