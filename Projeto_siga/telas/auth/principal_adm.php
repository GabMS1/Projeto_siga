<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\principal_adm.php

// ATENÇÃO CRÍTICA: DEVE SER A PRIMEIRA COISA NO ARQUIVO, SEM ESPAÇOS OU LINHAS ACIMA.
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Inicia a sessão PHP se ainda não estiver iniciada.
};

// --- PROTEÇÃO DE ROTA ---
// Verifica se o usuário está logado E se o tipo de usuário é 'admin'.
// Se não estiver logado ou não for um administrador, redireciona para a página de login.
if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'admin') {
    $_SESSION['login_error'] = "Acesso negado. Faça login como administrador.";
    header("Location: login.php"); // Redireciona para login.php.
    exit; // Encerra o script.
}

// Pega o nome do administrador da sessão para exibir na página.
$nome_usuario = $_SESSION['nome_usuario_logado'] ?? 'Administrador';

// Inclui a classe de Conexão para acessar o banco de dados e buscar dados específicos do admin.
require_once __DIR__ . '/../../DAO/Conexao.php'; 

// Inicializa contadores para o dashboard do administrador.
$totalProfessores = 0;
$totalAdministradores = 0;
// Você pode adicionar mais variáveis aqui para outras estatísticas relevantes para o admin.

// Cria uma instância da classe de conexão com o banco de dados.
$conexao = new Conexao();
$conn = $conexao->get_connection();

if ($conn) {
    // --- CONSULTAS AO BANCO DE DADOS PARA POPULAR O DASHBOARD DO ADMINISTRADOR ---

    // Exemplo: Contar o total de professores cadastrados.
    $stmt_professores = $conn->prepare("SELECT COUNT(*) AS total FROM professor");
    if ($stmt_professores) {
        $stmt_professores->execute();
        $res_professores = $stmt_professores->get_result();
        if ($row = $res_professores->fetch_assoc()) {
            $totalProfessores = $row['total'];
        }
        $stmt_professores->close();
    } else {
        error_log("Erro ao preparar query de total de professores: " . $conn->error);
    }

    // Exemplo: Contar o total de administradores cadastrados (usando a tabela 'admin').
    $stmt_admins = $conn->prepare("SELECT COUNT(*) AS total FROM admin"); // Usa sua tabela 'admin'
    if ($stmt_admins) {
        $stmt_admins->execute();
        $res_admins = $stmt_admins->get_result();
        if ($row = $res_admins->fetch_assoc()) {
            $totalAdministradores = $row['total'];
        }
        $stmt_admins->close();
    } else {
        error_log("Erro ao preparar query de total de administradores: " . $conn->error);
    }

    // Você pode adicionar mais consultas aqui para dados como:
    // - total de disciplinas, total de turmas, relatórios pendentes de aprovação, etc.

    $conexao->close(); // Fecha a conexão com o banco de dados.
} else {
    error_log("Principal_adm.php: Conexão com o banco de dados falhou.");
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Administrador - SUAP IF Goiano</title>
    <style>
        /* Estilos CSS (adaptados para um layout de painel com sidebar) */
        body {
            font-family: sans-serif;
            margin: 0;
            background-color: #f4f7f8; /* Cor de fundo mais clara para o painel */
            display: flex; /* Permite layout flexbox para sidebar e conteúdo */
            min-height: 100vh;
        }

        .sidebar {
            width: 220px;
            height: 100vh;
            background-color: #386641; /* Cor de fundo da sidebar */
            color: white; /* Cor do texto da sidebar */
            padding-top: 30px;
            position: fixed; /* Sidebar fixa na lateral */
            box-shadow: 2px 0 5px rgba(0,0,0,0.1); /* Sombra para destaque */
            font-family: 'Segoe UI', sans-serif;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 22px;
            color: white;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar li {
            padding: 8px 20px;
            margin-bottom: 5px;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            display: block; /* Faz o link ocupar toda a área clicável do item da lista */
            padding: 8px 12px;
            border-radius: 4px;
            transition: background-color 0.3s; /* Transição suave ao passar o mouse */
        }

        .sidebar a:hover {
            background-color: #4d774e; /* Cor mais escura ao passar o mouse */
        }

        .sidebar a:active {
            background-color: #2a5133; /* Cor ainda mais escura ao clicar */
        }

        .main {
            margin-left: 220px; /* Garante que o conteúdo principal comece após a sidebar */
            padding: 30px;
            flex: 1; /* Ocupa o restante do espaço disponível */
            width: calc(100% - 220px); /* Garante que o main não transborde */
        }

        .main h1 {
            color: #2a9d8f; /* Cor do título principal */
            margin-bottom: 30px;
        }

        .cards {
            display: flex;
            gap: 20px; /* Espaço entre os cards */
            margin-top: 20px;
            flex-wrap: wrap; /* Permite que os cards quebrem linha em telas menores */
        }

        .card {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            flex: 1; /* Permite que os cards se ajustem e ocupem espaço */
            min-width: 250px; /* Largura mínima para evitar que fiquem muito pequenos */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05); /* Sombra suave */
            text-align: center;
        }

        .card h3 {
            margin: 0;
            color: #555;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .card p {
            font-size: 28px;
            font-weight: bold;
            color: #386641;
            margin-top: 0;
        }

        .card-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            justify-content: center; /* Centraliza os botões */
            flex-wrap: wrap;
        }

        .btn-view, .btn-add {
            padding: 8px 15px;
            border-radius: 4px;
            font-size: 14px;
            text-decoration: none;
            text-align: center;
            flex: 1; /* Permite que os botões se ajustem */
            min-width: 100px;
        }

        .btn-view {
            background-color: #2a9d8f;
            color: white;
        }

        .btn-add {
            background-color: #386641;
            color: white;
        }

        .btn-view:hover {
            background-color: #268074;
        }

        .btn-add:hover {
            background-color: #4d774e;
        }
        .section {
            margin-top: 40px;
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .section h2 {
            margin-bottom: 20px;
            color: #555;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .list-item {
            background-color: #f9f9f9;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 8px;
            border-left: 4px solid #2a9d8f;
            color: #333;
        }
        .list-item:last-child {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Administrador</h2>
        <ul>
            <li><a href="principal_adm.php">📋 Dashboard</a></li>
            <!-- Links para as futuras páginas de gerenciamento do administrador -->
            <li><a href="gerenciar_professores.php">🧑‍🏫 Gerenciar Professores</a></li>
            <li><a href="gerenciar_disciplinas.php">📚 Gerenciar Disciplinas</a></li>
            <li><a href="gerenciar_turmas.php">👥 Gerenciar Turmas</a></li>
            <li><a href="aprovar_reposicoes.php">✅ Aprovar Reposições</a></li>
            <li><a href="gerenciar_relatorios.php">📄 Gerenciar Relatórios</a></li>
            <li><a href="ajuda_adm.php">❓ Ajuda</a></li>
            <li><a href="logout.php">🚪 Sair</a></li>
        </ul>
    </div>

    <div class="main">
        <h1>Bem-vindo(a), <?= htmlspecialchars($nome_usuario) ?>!</h1>

        <div class="cards">
            <div class="card">
                <h3>Total de Professores</h3>
                <p><?= $totalProfessores ?></p>
                <div class="card-buttons">
                    <a href="gerenciar_professores.php" class="btn-view">Ver Professores</a>
                    <a href="cadastro.php" class="btn-add">+ Novo Professor</a>
                </div>
            </div>
            <div class="card">
                <h3>Total de Administradores</h3>
                <p><?= $totalAdministradores ?></p>
                <div class="card-buttons">
                    <a href="gerenciar_administradores.php" class="btn-view">Ver Administradores</a>
                    <a href="cadastro_adm.php" class="btn-add">+ Novo Administrador</a>
                </div>
            </div>
            <!-- Você pode adicionar mais cards aqui para outras estatísticas importantes para o administrador -->
        </div>

        <div class="section">
            <h2>Ações Rápidas</h2>
            <div class="card-buttons">
                <a href="aprovar_reposicoes.php" class="btn-view">Aprovar Reposições Pendentes</a>
                <a href="gerenciar_relatorios.php" class="btn-view">Revisar Relatórios Enviados</a>
            </div>
        </div>

        <!-- Você pode adicionar mais seções aqui para:
             - Últimos logins de administradores/professores
             - Gráficos de dados do sistema (ex: total de aulas repostas vs. pendentes)
             - Notificações importantes do sistema
        -->
    </div>
</body>
</html>
