<?php
// C:\xampp\htdocs\Projeto_siga-1\Projeto_siga\telas\auth\Principal.php

// ATENÇÃO CRÍTICA: DEVE SER A PRIMEIRA COISA NO ARQUIVO, SEM ESPAÇOS OU LINHAS ACIMA.
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Inicia a sessão PHP se ainda não estiver iniciada.
}

// Inclui a classe de Conexão para acessar o banco de dados.
require_once __DIR__ . '/../../DAO/Conexao.php'; 
// Inclui o ProfessorServico para contar e listar professores.
require_once __DIR__ . '/../../negocio/ProfessorServico.php';
// Inclui o AdministradorServico para contar e listar administradores (opcional, mas bom ter).
require_once __DIR__ . '/../../negocio/AdministradorServico.php';

// --- PROTEÇÃO DE ROTA ---
// Verifica se o usuário está logado E se o tipo de usuário é 'professor'.
// Se não estiver logado ou não for um professor, redireciona para a página de login com uma mensagem de erro.
if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'professor') {
    $_SESSION['login_error'] = "Acesso negado. Faça login como professor.";
    header("Location: login.php"); // Redireciona para login.php.
    exit(); // Encerra o script para evitar que a página seja carregada sem autenticação.
}

// Pega o SIAPE e o nome do professor da sessão (definidos no login.php após a autenticação bem-sucedida).
$siape = $_SESSION['usuario_logado'];
$nome = $_SESSION['nome_usuario_logado'];

// Inicializa os contadores para exibição no dashboard.
$totalDisciplinas = 0;
$totalRelatorios = 0;
$totalReposPendentes = 0;
$totalTurmas = 0;

// Cria uma instância da classe de conexão com o banco de dados.
$conexao = new Conexao();
$conn = $conexao->get_connection(); // Obtém o objeto de conexão.

if ($conn) {
    // --- CONSULTAS AO BANCO DE DADOS PARA POPULAR O DASHBOARD DO PROFESSOR ---
    // Todas as consultas usam Prepared Statements para segurança contra SQL Injection.

    // 1. Contar o total de disciplinas do professor logado.
    $stmt1 = $conn->prepare("SELECT COUNT(*) AS total FROM disciplina WHERE siape_prof = ?");
    if ($stmt1) {
        $stmt1->bind_param("s", $siape); // Binda o SIAPE do professor à query.
        $stmt1->execute();
        $res1 = $stmt1->get_result();
        if ($row = $res1->fetch_assoc()) {
            $totalDisciplinas = $row['total'];
        }
        $stmt1->close();
    } else {
        error_log("Erro ao preparar query de Disciplinas: " . $conn->error);
    }

    // 2. Contar o total de relatórios do professor logado.
    $stmt2 = $conn->prepare("SELECT COUNT(*) AS total FROM relatorio WHERE siape_prof = ?");
    if ($stmt2) {
        $stmt2->bind_param("s", $siape);
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        if ($row = $res2->fetch_assoc()) {
            $totalRelatorios = $row['total'];
        }
        $stmt2->close();
    } else {
        error_log("Erro ao preparar query de Relatórios: " . $conn->error);
    }

    // 3. Contar o total de reposições pendentes associadas ao professor.
    $stmt3 = $conn->prepare("SELECT COUNT(*) AS total FROM programada WHERE id_ass_ausente IN 
                            (SELECT id_ass_ausente FROM prof_ausente WHERE siape_prof = ?)");
    if ($stmt3) {
        $stmt3->bind_param("s", $siape);
        $stmt3->execute();
        $res3 = $stmt3->get_result();
        if ($row = $res3->fetch_assoc()) {
            $totalReposPendentes = $row['total'];
        }
        $stmt3->close();
    } else {
        error_log("Erro ao preparar query de Reposições Pendentes: " . $conn->error);
    }

    // 4. Contar o total de turmas distintas associadas às disciplinas do professor.
    $stmt4 = $conn->prepare("SELECT COUNT(DISTINCT t.id_turma) AS total 
                            FROM turma t JOIN disciplina d ON t.id_disciplina = d.id_disciplina 
                            WHERE d.siape_prof = ?");
    if ($stmt4) {
        $stmt4->bind_param("s", $siape);
        $stmt4->execute();
        $res4 = $stmt4->get_result();
        if ($row = $res4->fetch_assoc()) {
            $totalTurmas = $row['total'];
        }
        $stmt4->close();
    } else {
    error_log("Erro ao preparar query de Turmas: " . $conn->error);
    }

    // 5. Buscar os 3 últimos relatórios do professor.
    $ultimosRelatorios = []; // Inicializa um array vazio.
    $stmt_relatorios = $conn->prepare("SELECT aulas_substituidas, aulas_cedidas FROM relatorio WHERE siape_prof = ? ORDER BY id_relatorio DESC LIMIT 3");
    if ($stmt_relatorios) {
        $stmt_relatorios->bind_param("s", $siape);
        $stmt_relatorios->execute();
        $res_relatorios = $stmt_relatorios->get_result();
        while ($row = $res_relatorios->fetch_assoc()) {
            $ultimosRelatorios[] = $row; // Adiciona cada linha ao array.
        }
        $stmt_relatorios->close();
    } else {
        error_log("Erro ao preparar query de Últimos Relatórios: " . $conn->error);
    }

    // 6. Buscar as 3 próximas reposições agendadas para o professor.
    $reposicoesAgendadas = []; // Inicializa um array vazio.
    $stmt_reposicoes = $conn->prepare("SELECT dia, horario FROM programada WHERE id_ass_ausente IN 
                                    (SELECT id_ass_ausente FROM prof_ausente WHERE siape_prof = ?) ORDER BY dia ASC LIMIT 3");
    if ($stmt_reposicoes) {
        $stmt_reposicoes->bind_param("s", $siape);
        $stmt_reposicoes->execute();
        $res_reposicoes = $stmt_reposicoes->get_result();
        while ($row = $res_reposicoes->fetch_assoc()) {
            $reposicoesAgendadas[] = $row; // Adiciona cada linha ao array.
        }
        $stmt_reposicoes->close();
    } else {
        error_log("Erro ao preparar query de Reposições Agendadas: " . $conn->error);
    }

    $conexao->close(); // Fecha a conexão com o banco de dados.
} else {
    // Se a conexão com o banco de dados falhar, registra um erro.
    error_log("Principal.php: Conexão com o banco de dados falhou.");
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Painel do Professor</title>
    <style>
        /* Estilos CSS (mantidos conforme seu arquivo original) */
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
    <h2>Professor</h2>
    <ul>
        <li><a href="principal.php">📋 Dashboard</a></li>
        <li><a href="disciplinas.php">📚 Disciplinas</a></li>
        
        <li><a href="minhas_reposicoes.php">🔁 Minhas Reposições</a></li>
        <li><a href="agendar_reposicao.php">🗓️ Agendar Reposição</a></li>
        <li><a href="calendario.php">🗓️ Calendário de Reposições</a></li>

        <li><a href="relatorios.php">📄 Relatórios</a></li>
        <li><a href="medias.php">📈 Médias</a></li>
        <li><a href="ajuda.php">❓ Ajuda</a></li>
        <li><a href="logout.php">🚪 Sair</a></li>
    </ul>
</div>

<div class="main">
    <h1>Bem-vindo(a), <?php echo htmlspecialchars($nome); ?>!</h1>

    <div class="cards">
        <div class="card">
            <h3>Disciplinas</h3>
            <p><?php echo $totalDisciplinas; ?></p>
            <div class="card-buttons">
                <a href="disciplinas.php" class="btn-view">Ver Disciplinas</a>
                <a href="cadastrar_disciplina.php" class="btn-add">+ Nova Disciplina</a>
            </div>
        </div>
        <div class="card">
            <h3>Turmas</h3>
            <p><?php echo $totalTurmas; ?></p>
            <div class="card-buttons">
                <a href="turmas.php" class="btn-view">Ver Turmas</a>
                <a href="cadastrar_turma.php" class="btn-add">+ Nova Turma</a>
            </div>
        </div>
        <div class="card">
            <h3>Reposições Pendentes</h3>
            <p><?php echo $totalReposPendentes; ?></p>
            <div class="card-buttons">
                <a href="minhas_reposicoes.php" class="btn-view">Ver Reposições</a>
                <a href="agendar_reposicao.php" class="btn-add">+ Agendar</a>
            </div>
        </div>
        <div class="card">
            <h3>Relatório de Reposições</h3>
            <p>Gerar PDF</p>
            <div class="card-buttons">
                <a href="gerar_relatorio_pdf.php" class="btn-view" target="_blank">Gerar Relatório</a>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Últimos Relatórios</h2>
        <?php
        if (!empty($ultimosRelatorios)) {
            foreach ($ultimosRelatorios as $row) {
                echo "<div class='list-item'>✅ Aulas Substituídas: " . htmlspecialchars($row['aulas_substituidas']) . " | Aulas Cedidas: " . htmlspecialchars($row['aulas_cedidas']) . "</div>";
            }
        } else {
            echo "<div class='list-item'>Nenhum relatório recente.</div>";
        }
        ?>
    </div>

    <div class="section">
        <h2>Reposições Agendadas</h2>
        <?php
        if (!empty($reposicoesAgendadas)) {
            foreach ($reposicoesAgendadas as $row) {
                echo "<div class='list-item'>📅 " . date('d/m', strtotime($row['dia'])) . " às " . substr($row['horario'], 0, 5) . "</div>";
            }
        } else {
            echo "<div class='list-item'>Nenhuma reposição agendada.</div>";
        }
        ?>
    </div>
</div>

</body>
</html>