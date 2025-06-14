<?php
// C:\xampp\htdocs\Projeto_trabalho\telas\professor\principal.php

// Inicia a sessão PHP
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclui a classe de Conexão para acessar o banco de dados
require_once __DIR__ . '/../../DAO/Conexao.php'; // Caminho corrigido para Conexao.php

// Protege acesso: se o usuário não estiver logado, redireciona para a página de login
if (!isset($_SESSION['usuario_logado'])) {
    header("Location: ../auth/login.php"); // Caminho corrigido para o login.php
    exit();
}

// Pega o SIAPE e o nome da sessão (definidos no login.php)
$siape = $_SESSION['usuario_logado'];
$nome = $_SESSION['nome_usuario_logado'];

// Inicializa os contadores
$totalDisciplinas = 0;
$totalRelatorios = 0;
$totalReposPendentes = 0;
$totalTurmas = 0;

// Cria uma instância da classe de conexão
$conexao = new Conexao();
$conn = $conexao->get_connection();

if ($conn) { // Somente tenta buscar os dados se a conexão for bem-sucedida
    // Buscar dados reais
    // Disciplinas
    $stmt1 = $conn->prepare("SELECT COUNT(*) AS total FROM disciplina WHERE siape_prof = ?");
    if ($stmt1) {
        $stmt1->bind_param("s", $siape);
        $stmt1->execute();
        $res1 = $stmt1->get_result();
        if ($row = $res1->fetch_assoc()) {
            $totalDisciplinas = $row['total'];
        }
        $stmt1->close();
    } else {
        error_log("Erro ao preparar query de Disciplinas: " . $conn->error);
    }


    // Relatórios
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

    // Reposições pendentes
    // Ajuste aqui se a estrutura da sua tabela de reposições for diferente,
    // garantindo que siape_prof esteja diretamente na tabela 'prof_ausente'
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


    // Turmas distintas (associadas a disciplinas do professor)
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


    // Últimos Relatórios
    $ultimosRelatorios = [];
    $stmt_relatorios = $conn->prepare("SELECT aulas_substituidas, aulas_cedidas FROM relatorio WHERE siape_prof = ? ORDER BY id_relatorio DESC LIMIT 3");
    if ($stmt_relatorios) {
        $stmt_relatorios->bind_param("s", $siape);
        $stmt_relatorios->execute();
        $res_relatorios = $stmt_relatorios->get_result();
        while ($row = $res_relatorios->fetch_assoc()) {
            $ultimosRelatorios[] = $row;
        }
        $stmt_relatorios->close();
    } else {
        error_log("Erro ao preparar query de Últimos Relatórios: " . $conn->error);
    }


    // Reposições Agendadas
    $reposicoesAgendadas = [];
    $stmt_reposicoes = $conn->prepare("SELECT dia, horario FROM programada WHERE id_ass_ausente IN 
                                    (SELECT id_ass_ausente FROM prof_ausente WHERE siape_prof = ?) ORDER BY dia ASC LIMIT 3");
    if ($stmt_reposicoes) {
        $stmt_reposicoes->bind_param("s", $siape);
        $stmt_reposicoes->execute();
        $res_reposicoes = $stmt_reposicoes->get_result();
        while ($row = $res_reposicoes->fetch_assoc()) {
            $reposicoesAgendadas[] = $row;
        }
        $stmt_reposicoes->close();
    } else {
        error_log("Erro ao preparar query de Reposições Agendadas: " . $conn->error);
    }

    $conexao->close(); // Fecha a conexão após todas as operações
} else {
    // Se a conexão falhou, os contadores permanecerão em 0 e as listas vazias
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
        body { margin: 0; font-family: 'Segoe UI', sans-serif; display: flex; background-color: #f4f7f8; }
        .sidebar { width: 220px; height: 100vh; background-color: #386641; color: white; padding-top: 30px; position: fixed; }
        .sidebar h2 { text-align: center; margin-bottom: 20px; font-size: 22px; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar ul li { padding: 15px 20px; }
        .sidebar ul li a { color: white; text-decoration: none; font-weight: bold; display: block; }
        .sidebar ul li a:hover { background-color: #4d774e; border-radius: 4px; }
        .main { margin-left: 220px; padding: 30px; flex: 1; }
        .main h1 { color: #2a9d8f; }
        .cards { display: flex; gap: 20px; margin-top: 20px; flex-wrap: wrap; }
        .card { background-color: white; padding: 20px; border-radius: 8px; flex: 1; min-width: 180px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.05); }
        .card h3 { margin: 0; color: #555; font-size: 16px; }
        .card p { font-size: 24px; font-weight: bold; color: #386641; }
        .section { margin-top: 40px; }
        .section h2 { margin-bottom: 10px; color: #555; }
        .list-item { background-color: white; padding: 10px 15px; border-radius: 5px; margin-bottom: 10px; box-shadow: 0 0 5px rgba(0,0,0,0.05); }

        .sidebar {
            width: 220px;
            height: 100vh;
            background-color: #386641;
            color: white;
            padding-top: 30px;
            position: fixed;
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
            padding: 8px 20px; /* Reduzi um pouco o padding para ficar mais compacto */
            margin-bottom: 5px;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            display: block;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .sidebar a:hover {
            background-color: #4d774e; /* Verde mais claro para hover */
        }

        .sidebar a:active {
            background-color: #2a5133; /* Verde mais escuro para clique */
        }
        .card-buttons {
            display: flex;
            gap: 8px;
            margin-top: 10px;
            flex-wrap: wrap;
        }

        .btn-view, .btn-add {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            text-decoration: none;
            text-align: center;
            flex: 1;
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
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Professor</h2>
    <ul>
        <li><a href="principal.php">📋 Dashboard</a></li>
        <li><a href="disciplinas.php">📚 Disciplinas</a></li>
        <li><a href="reposicoes.php">🔁 Reposições</a></li>
        <li><a href="relatorios.php">📄 Relatórios</a></li>
        <li><a href="medias.php">📈 Médias</a></li>
        <li><a href="ajuda.php">❓ Ajuda</a></li>
        <li><a href="../auth/logout.php">🚪 Sair</a></li> </ul>
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
                <a href="reposicoes.php" class="btn-view">Ver Reposições</a>
                <a href="agendar_reposicao.php" class="btn-add">+ Agendar</a>
            </div>
        </div>
        <div class="card">
            <h3>Relatórios Aprovados</h3>
            <p><?php echo $totalRelatorios; ?></p>
            <div class="card-buttons">
                <a href="relatorios.php" class="btn-view">Ver Relatórios</a>
                <a href="cadastrar_relatorio.php" class="btn-add">+ Novo Relatório</a>
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