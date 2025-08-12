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
$mensagem = '';
$sucesso = false;
$disciplina = null;

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $disciplina = $disciplinaServico->buscarDisciplina($id);
    if (!$disciplina) {
        $mensagem = "Disciplina não encontrada.";
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nome = $_POST['nome_disciplina'];
    $ch = $_POST['ch'];

    if (empty($id) || empty($nome) || empty($ch)) {
        $mensagem = "Todos os campos são obrigatórios.";
    } elseif ($disciplinaServico->atualizarDisciplina($id, $nome, $ch)) {
        $mensagem = "Disciplina atualizada com sucesso!";
        $sucesso = true;
        $disciplina = $disciplinaServico->buscarDisciplina($id);
    } else {
        $mensagem = "Erro ao atualizar a disciplina.";
        $sucesso = false;
        $disciplina = ['id_disciplina' => $id, 'nome_disciplina' => $nome, 'ch' => $ch];
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
    <title>Editar Disciplina</title>
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
        .form-container input[type="time"] {
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
        <li><a href="professores.php">🧑‍🏫 Gerenciar Professores</a></li>
        <li><a href="administradores.php">⚙️ Gerenciar Admins</a></li>
        <li><a href="disciplinas.php" class="active">📚 Gerenciar Disciplinas</a></li>
        <li><a href="logout.php">🚪 Sair</a></li>
    </ul>
</div>

<div class="main">
    <h1>Editar Disciplina</h1>
    <div class="form-container">
        <?php if ($mensagem): ?>
            <div class="alert <?php echo $sucesso ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <?php if ($disciplina): ?>
            <form action="editar_disciplina.php" method="POST">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($disciplina['id_disciplina']); ?>">
                
                <label for="nome_disciplina">Nome da Disciplina:</label>
                <input type="text" id="nome_disciplina" name="nome_disciplina" value="<?php echo htmlspecialchars($disciplina['nome_disciplina']); ?>" required>

                <label for="ch">Carga Horária (HH:MM:SS):</label>
                <input type="time" id="ch" name="ch" value="<?php echo htmlspecialchars($disciplina['ch']); ?>" step="1" required>

                <button type="submit" class="btn btn-success">Salvar Alterações</button>
                <a href="disciplinas.php" class="btn btn-info">Cancelar</a>
            </form>
        <?php else: ?>
            <p>Disciplina não encontrada ou erro de acesso.</p>
            <a href="disciplinas.php" class="btn btn-info">Voltar</a>
        <?php endif; ?>
    </div>
</div>

</body>
</html>