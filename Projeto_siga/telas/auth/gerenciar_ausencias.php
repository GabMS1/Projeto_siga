<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\gerenciar_ausencias.php

// ATENÇÃO CRÍTICA: DEVE SER A PRIMEIRA COISA NO ARQUIVO.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclui o serviço de Ausência para listar as reposições.
require_once __DIR__ . '/../../negocio/AusenciaServico.php';

// --- PROTEÇÃO DE ROTA ---
// Verifica se o usuário está logado E se o tipo de usuário é 'admin'.
if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'admin') {
    $_SESSION['login_error'] = "Acesso negado. Faça login como administrador.";
    header("Location: login.php");
    exit();
}

$ausencias = []; // Inicializa um array vazio para armazenar as ausências e reposições.
$mensagem = ""; // Para mensagens de feedback

try {
    $ausenciaServico = new AusenciaServico();
    
    // Agora o método listarTodasAusenciasPendentes() existe e pode ser chamado.
    $ausencias = $ausenciaServico->listarTodasAusenciasPendentes();

    if (empty($ausencias)) {
        $mensagem = "Nenhuma ausência ou reposição pendente para aprovação.";
    }
} catch (Exception $e) {
    $mensagem = "Erro ao carregar a lista de ausências: " . $e->getMessage();
    error_log("Erro em gerenciar_ausencias.php: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Ausências</title>
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

        .btn-view { padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 14px; color: white; background-color: #007bff; }
        .btn-view:hover { background-color: #0069d9; }
        .btn-success { background-color: #386641; color: white; }
        .btn-success:hover { background-color: #2a5133; }
        .btn-danger { background-color: #dc3545; color: white; }
        .btn-danger:hover { background-color: #c82333; }
        
        .table-container { background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.05); margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; color: #555; }
        tr:hover { background-color: #f9f9f9; }
        
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; font-weight: bold; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .actions-cell { display: flex; gap: 5px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Administrador</h2>
    <ul>
        <li><a href="principal_adm.php">📊 Dashboard</a></li>
        <li><a href="professores.php">🧑‍🏫 Gerenciar Professores</a></li>
        <li><a href="administradores.php">⚙️ Gerenciar Admins</a></li>
        <li><a href="gerenciar_ausencias.php" class="active">🔁 Gerenciar Ausências</a></li>
        <li><a href="disciplinas.php">📚 Gerenciar Disciplinas</a></li>
        <li><a href="logout.php">🚪 Sair</a></li>
    </ul>
</div>

<div class="main">
    <h1>Gerenciar Ausências e Reposições</h1>

    <?php if ($mensagem): ?>
        <div class="alert alert-info">
            <?php echo htmlspecialchars($mensagem); ?>
        </div>
    <?php endif; ?>

    <div class="table-container">
        <h2>Solicitações Pendentes (Total: <?php echo count($ausencias); ?>)</h2>
        <?php if (!empty($ausencias)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID da Solicitação</th>
                        <th>Professor Ausente</th>
                        <th>Professor Substituto</th>
                        <th>Data</th>
                        <th>Horário</th>
                        <th>Disciplina</th>
                        <th>Turma</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ausencias as $ausencia): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ausencia['id_progra']); ?></td>
                            <td><?php echo htmlspecialchars($ausencia['siape_ausente']); ?></td>
                            <td><?php echo htmlspecialchars($ausencia['siape_substituto']); ?></td>
                            <td><?php echo htmlspecialchars($ausencia['dia']); ?></td>
                            <td><?php echo htmlspecialchars(substr($ausencia['horario'], 0, 5)); ?></td>
                            <td><?php echo htmlspecialchars($ausencia['nome_disciplina']); ?></td>
                            <td><?php echo htmlspecialchars($ausencia['curso'] . " - " . $ausencia['serie']); ?></td>
                            <td class="actions-cell">
                                <a href="#" class="btn-success">Aprovar</a>
                                <a href="#" class="btn-danger">Rejeitar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nenhuma solicitação de ausência ou reposição pendente.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>