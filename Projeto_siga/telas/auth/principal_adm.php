<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\principal_adm.php

// ATENÇÃO CRÍTICA: DEVE SER A PRIMEIRA COISA NO ARQUIVO, SEM ESPAÇOS OU LINHAS ACIMA.
// Inicia a sessão PHP se ainda não estiver iniciada.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclui a classe de Conexão para acessar o banco de dados.
require_once __DIR__ . '/../../DAO/Conexao.php';
// Inclui o ProfessorServico para contar e listar professores.
require_once __DIR__ . '/../../negocio/ProfessorServico.php';
// Inclui o AdministradorServico para contar e listar administradores (opcional, mas bom ter).
require_once __DIR__ . '/../../negocio/AdministradorServico.php';

// --- PROTEÇÃO DE ROTA ---
// Verifica se o usuário está logado E se o tipo de usuário é 'admin'.
// Se não estiver logado ou não for um administrador, redireciona para a página de login com uma mensagem de erro.
if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'admin') {
    $_SESSION['login_error'] = "Acesso negado. Faça login como administrador.";
    header("Location: login.php"); // Redireciona para login.php.
    exit(); // Encerra o script para evitar que a página seja carregada sem autenticação.
}

// Pega o SIAPE e o nome do administrador da sessão.
$siape_adm = $_SESSION['usuario_logado'];
$nome_adm = $_SESSION['nome_usuario_logado'];
$cargo_adm = $_SESSION['cargo_usuario_logado'] ?? 'Administrador'; // Pega o cargo, se disponível.

// Inicializa contadores.
$totalProfessores = 0;
$totalAdministradores = 0;
// Você pode adicionar mais contadores aqui (ex: total de disciplinas, turmas, relatórios pendentes, etc.)

$conexao = new Conexao();
$conn = $conexao->get_connection();

if ($conn) {
    // Contar total de professores.
    $stmtProf = $conn->prepare("SELECT COUNT(*) AS total FROM professor");
    if ($stmtProf) {
        $stmtProf->execute();
        $resProf = $stmtProf->get_result();
        if ($row = $resProf->fetch_assoc()) {
            $totalProfessores = $row['total'];
        }
        $stmtProf->close();
    } else {
        error_log("Erro ao preparar query de contagem de professores: " . $conn->error);
    }

    // Contar total de administradores.
    $stmtAdm = $conn->prepare("SELECT COUNT(*) AS total FROM admin");
    if ($stmtAdm) {
        $stmtAdm->execute();
        $resAdm = $stmtAdm->get_result();
        if ($row = $resAdm->fetch_assoc()) {
            $totalAdministradores = $row['total'];
        }
        $stmtAdm->close();
    } else {
        error_log("Erro ao preparar query de contagem de administradores: " . $conn->error);
    }

    // Adicione outras consultas de contagem aqui, se desejar.

    $conexao->close();
} else {
    error_log("principal_adm.php: Conexão com o banco de dados falhou.");
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Administrador</title>
    <style>
        body { margin: 0; font-family: 'Segoe UI', sans-serif; display: flex; background-color: #f4f7f8; }
        .sidebar { width: 220px; height: 100vh; background-color: #386641; color: white; padding-top: 30px; position: fixed; box-shadow: 2px 0 5px rgba(0,0,0,0.1); }
        .sidebar h2 { text-align: center; margin-bottom: 20px; font-size: 22px; color: white; }
        .sidebar ul { list-style: none; padding: 0; margin: 0; }
        .sidebar li { padding: 8px 20px; margin-bottom: 5px; }
        .sidebar a { color: white; text-decoration: none; font-weight: bold; display: block; padding: 8px 12px; border-radius: 4px; transition: background-color 0.3s; }
        .sidebar a:hover { background-color: #4d774e; }
        .sidebar a:active { background-color: #2a5133; }
        
        .main { margin-left: 220px; padding: 30px; flex: 1; width: calc(100% - 220px); }
        .main h1 { color: #2a9d8f; margin-bottom: 30px; }
        .cards { display: flex; gap: 20px; margin-top: 20px; flex-wrap: wrap; }
        .card { background-color: white; padding: 20px; border-radius: 8px; flex: 1; min-width: 180px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.05); text-align: center; }
        .card h3 { margin: 0; color: #555; font-size: 16px; margin-bottom: 10px; }
        .card p { font-size: 28px; font-weight: bold; color: #386641; margin-top: 0; }
        .card-buttons { display: flex; gap: 10px; margin-top: 15px; justify-content: center; flex-wrap: wrap; }
        .btn-view, .btn-add { padding: 8px 15px; border-radius: 4px; font-size: 14px; text-decoration: none; text-align: center; flex: 1; min-width: 100px; }
        .btn-view { background-color: #2a9d8f; color: white; }
        .btn-add { background-color: #386641; color: white; }
        .btn-view:hover { background-color: #268074; }
        .btn-add:hover { background-color: #4d774e; }
        .section { margin-top: 40px; background-color: white; padding: 25px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
        .section h2 { margin-bottom: 20px; color: #555; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .list-item { background-color: #f9f9f9; padding: 10px 15px; border-radius: 5px; margin-bottom: 8px; border-left: 4px solid #2a9d8f; color: #333; }
        .list-item:last-child { margin-bottom: 0; }
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
        <li><a href="calendario.php">🗓️ Calendário de Reposições</a></li>
        <li><a href="logout.php">🚪 Sair</a></li>
    </ul>
</div>

<div class="main">
    <h1>Bem-vindo(a), <?php echo htmlspecialchars($nome_adm); ?> (<?php echo htmlspecialchars($cargo_adm); ?>)!</h1>

    <div class="cards">
        <div class="card">
            <h3>Professores Cadastrados</h3>
            <p><?php echo $totalProfessores; ?></p>
            <div class="card-buttons">
                <a href="professores.php" class="btn-view">Ver Professores</a>
                <a href="cadastro.php" class="btn-add">+ Cadastrar Professor</a>
            </div>
        </div>
        <div class="card">
            <h3>Administradores Cadastrados</h3>
            <p><?php echo $totalAdministradores; ?></p>
            <div class="card-buttons">
                <a href="administradores.php" class="btn-view">Ver Administradores</a>
                <a href="cadastro_adm.php" class="btn-add">+ Cadastrar Admin</a>
            </div>
        </div>
        </div>

    <div class="section">
        <h2>Atividades Recentes do Sistema</h2>
        <div class='list-item'>🔄 Nenhuma atividade recente para exibir.</div>
        <div class='list-item'>💡 Comece a gerenciar professores e turmas!</div>
    </div>

</div>

</body>
</html>