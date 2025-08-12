<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\professores.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../negocio/ProfessorServico.php';

if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'admin') {
    $_SESSION['login_error'] = "Acesso negado. Faça login como administrador.";
    header("Location: login.php");
    exit();
}

$nome_adm = $_SESSION['nome_usuario_logado'] ?? 'Admin';

$professorServico = new ProfessorServico();
$professores = [];
$mensagem = "";
$sucesso = false;

// Lógica para lidar com a ação de exclusão via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao']) && $_POST['acao'] === 'excluir' && isset($_POST['siape_excluir'])) {
        $siape_excluir = trim($_POST['siape_excluir']);
        if ($professorServico->excluirProfessor($siape_excluir)) {
            $mensagem = "Professor excluído com sucesso!";
            $sucesso = true;
        } else {
            $mensagem = "Erro ao excluir o professor.";
            $sucesso = false;
        }
    }
}

// Carrega a lista de professores para exibição
try {
    $professores = $professorServico->listarProfessores();
} catch (Exception $e) {
    $mensagem = "Erro ao carregar a lista de professores.";
    $sucesso = false;
    error_log("Erro em professores.php: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Professores</title>
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
        .btn-danger { background-color: #d9534f; color: white; }
        .btn-danger:hover { background-color: #c9302c; }
        .btn-info { background-color: #5bc0de; color: white; }
        .btn-info:hover { background-color: #31b0d5; }
        
        .table-container { background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.05); margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; color: #555; }
        tr:hover { background-color: #f9f9f9; }
        
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; font-weight: bold; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .actions-cell { display: flex; gap: 5px; }
        .btn-edit, .btn-delete { padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 14px; color: white; }
        .btn-edit { background-color: #007bff; }
        .btn-edit:hover { background-color: #0069d9; }
        .btn-delete { background-color: #dc3545; }
        .btn-delete:hover { background-color: #c82333; }
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
    <h1>Gerenciar Professores</h1>

    <?php if ($mensagem): ?>
        <div class="alert <?php echo $sucesso ? 'alert-success' : 'alert-danger'; ?>">
            <?php echo htmlspecialchars($mensagem); ?>
        </div>
    <?php endif; ?>

    <div class="table-container">
        <h2>Lista de Professores (Total: <?php echo count($professores); ?>)</h2>
        <?php if (!empty($professores)): ?>
            <table>
                <thead>
                    <tr>
                        <th>SIAPE</th>
                        <th>Nome</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($professores as $professor): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($professor['siape_prof']); ?></td>
                            <td><?php echo htmlspecialchars($professor['nome']); ?></td>
                            <td class="actions-cell">
                                <a href="editar_professor.php?siape=<?php echo htmlspecialchars($professor['siape_prof']); ?>" class="btn-edit">Editar</a>
                                <form action="professores.php" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir o professor <?php echo htmlspecialchars($professor['nome']); ?>?');">
                                    <input type="hidden" name="acao" value="excluir">
                                    <input type="hidden" name="siape_excluir" value="<?php echo htmlspecialchars($professor['siape_prof']); ?>">
                                    <button type="submit" class="btn-delete">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nenhum professor encontrado.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>