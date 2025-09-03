<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\Principal.php

// ATENÇÃO CRÍTICA: DEVE SER A PRIMEIRA COISA NO ARQUIVO, SEM ESPAÇOS OU LINHAS ACIMA.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../DAO/Conexao.php';
require_once __DIR__ . '/../../negocio/ProfessorServico.php';
require_once __DIR__ . '/../../negocio/DisciplinaServico.php';
require_once __DIR__ . '/../../negocio/TurmaServico.php';
require_once __DIR__ . '/../../negocio/AusenciaServico.php';

if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'professor') {
    $_SESSION['login_error'] = "Acesso negado. Faça login como professor.";
    header("Location: login.php");
    exit();
}

$siape = $_SESSION['usuario_logado'];
$nome = $_SESSION['nome_usuario_logado'];

$totalDisciplinas = 0;
$totalTurmas = 0;
$totalReposPendentes = 0;
$totalCargaHoraria = 0;
$reposicoesAgendadas = [];

$conexao = new Conexao();
$conn = $conexao->get_connection();

if ($conn) {
    // 1. Contar o total de disciplinas do professor logado e calcular a carga horária total.
    $stmt1 = $conn->prepare("SELECT COUNT(*) AS total, SUM(TIME_TO_SEC(ch)) AS total_segundos FROM disciplina WHERE siape_prof = ?");
    if ($stmt1) {
        $stmt1->bind_param("s", $siape);
        $stmt1->execute();
        $res1 = $stmt1->get_result();
        if ($row = $res1->fetch_assoc()) {
            $totalDisciplinas = $row['total'];
            $totalCargaHoraria = gmdate('H:i', $row['total_segundos']);
        }
        $stmt1->close();
    }

    // 2. Contar o total de turmas distintas associadas às disciplinas do professor.
    $stmt2 = $conn->prepare("SELECT COUNT(DISTINCT t.id_turma) AS total 
                            FROM turma t JOIN disciplina d ON t.id_disciplina = d.id_disciplina 
                            WHERE d.siape_prof = ?");
    if ($stmt2) {
        $stmt2->bind_param("s", $siape);
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        if ($row = $res2->fetch_assoc()) {
            $totalTurmas = $row['total'];
        }
        $stmt2->close();
    }
    
    // 3. Contar o total de reposições pendentes (faltas programadas sem substituto) para o professor logado.
    $stmt3 = $conn->prepare("SELECT COUNT(*) AS total
                            FROM programada p
                            JOIN prof_ausente pa ON p.id_ass_ausente = pa.id_ass_ausente
                            WHERE pa.siape_prof = ? AND p.id_ass_subs IS NULL");
    if ($stmt3) {
        $stmt3->bind_param("s", $siape);
        $stmt3->execute();
        $res3 = $stmt3->get_result();
        if ($row = $res3->fetch_assoc()) {
            $totalReposPendentes = $row['total'];
        }
        $stmt3->close();
    }

    // 4. Buscar as 3 próximas reposições agendadas para o professor.
    $stmt4 = $conn->prepare("SELECT dia, horario FROM programada pa
                                JOIN prof_ausente a ON pa.id_ass_ausente = a.id_ass_ausente
                                WHERE a.siape_prof = ?
                                ORDER BY dia ASC LIMIT 3");
    if ($stmt4) {
        $stmt4->bind_param("s", $siape);
        $stmt4->execute();
        $res4 = $stmt4->get_result();
        while ($row = $res4->fetch_assoc()) {
            $reposicoesAgendadas[] = $row;
        }
        $stmt4->close();
    }

    $conexao->close();
} else {
    error_log("Principal.php: Conexão com o banco de dados falhou.");
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Painel do Professor - SIGA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #386641;
            --accent-color: #FFC107;
            --text-color: #333;
            --text-light-color: #666;
            --bg-light: #f8f9fa;
            --bg-dark: #e9ecef;
            --card-bg: #ffffff;
            --shadow-light: 0 4px 15px rgba(0, 0, 0, 0.08);
            --border-color: #dee2e6;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            display: flex;
            background-color: var(--bg-light);
            color: var(--text-color);
            min-height: 100vh;
        }

        /* Sidebar Design */
        .sidebar {
            width: 250px;
            background-color: var(--secondary-color);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100%;
            box-shadow: var(--shadow-light);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .sidebar h2 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 40px;
            color: white;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
            width: 100%;
        }
        .sidebar li {
            width: 100%;
            margin-bottom: 8px;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            padding: 12px 30px;
            border-radius: 0 50px 50px 0;
            transition: all 0.3s ease;
        }
        .sidebar a i {
            margin-right: 15px;
            font-size: 18px;
        }
        .sidebar a:hover {
            background-color: var(--primary-color);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            transform: translateX(10px);
        }
        .sidebar a.active {
            background-color: var(--primary-color);
            font-weight: 600;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        /* Main Content Design */
        .main {
            margin-left: 250px;
            padding: 40px;
            flex: 1;
            width: calc(100% - 250px);
        }
        .main h1 {
            color: var(--text-color);
            margin-bottom: 35px;
            font-size: 32px;
            font-weight: 700;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 15px;
        }

        /* Cards Design */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        .card {
            background-color: var(--card-bg);
            padding: 25px;
            border-radius: 12px;
            box-shadow: var(--shadow-light);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
        }
        .card h3 {
            margin: 0;
            color: var(--text-light-color);
            font-size: 18px;
            margin-bottom: 10px;
            font-weight: 500;
        }
        .card p {
            font-size: 36px;
            font-weight: 700;
            color: var(--primary-color);
            margin-top: 0;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        .card p:hover {
            color: var(--secondary-color);
        }

        /* Section Design */
        .section {
            margin-top: 50px;
            background-color: var(--card-bg);
            padding: 30px;
            border-radius: 12px;
            box-shadow: var(--shadow-light);
        }
        .section h2 {
            margin-top: 0;
            margin-bottom: 25px;
            color: var(--text-color);
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 15px;
            font-size: 24px;
            font-weight: 600;
        }
        .list-item {
            background-color: var(--bg-light);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 5px solid var(--primary-color);
            color: var(--text-color);
            font-weight: 400;
            transition: transform 0.2s ease;
        }
        .list-item:hover {
            transform: translateX(5px);
        }
        .list-item:last-child {
            margin-bottom: 0;
        }

        /* Success Message Styling */
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            font-weight: 500;
            text-align: center;
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                padding-top: 20px;
            }
            .sidebar h2 {
                font-size: 18px;
                margin-bottom: 30px;
            }
            .sidebar a {
                padding: 12px 15px;
                justify-content: center;
                border-radius: 8px;
            }
            .sidebar a span {
                display: none;
            }
            .sidebar a i {
                margin-right: 0;
            }
            .main {
                margin-left: 80px;
                padding: 20px;
                width: calc(100% - 80px);
            }
            .main h1 {
                font-size: 26px;
            }
            .list-container {
                padding: 20px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                flex-direction: row;
                justify-content: center;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                padding: 15px 0;
            }
            .sidebar h2 {
                display: none;
            }
            .sidebar ul {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 5px;
            }
            .sidebar li {
                width: auto;
            }
            .sidebar a {
                padding: 8px 15px;
                border-radius: 8px;
            }
            .main {
                margin-left: 0;
                padding: 20px;
                width: 100%;
            }
            .cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Professor</h2>
    <ul>
        <li><a href="principal.php" class="active"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
        <li><a href="minhas_disciplinas.php"><i class="fas fa-book"></i> <span>Minhas Disciplinas</span></a></li>
        <li><a href="turmas.php"><i class="fas fa-users"></i> <span>Minhas Turmas</span></a></li>
        <li><a href="minhas_reposicoes.php"><i class="fas fa-redo-alt"></i> <span>Minhas Reposições</span></a></li>
        <li><a href="agendar_reposicao.php"><i class="fas fa-calendar-plus"></i> <span>Agendar Reposição</span></a></li>
        <li><a href="programar_falta.php"><i class="fas fa-calendar-times"></i> <span>Programar Falta</span></a></li>
        <li><a href="calendario.php"><i class="fas fa-calendar-alt"></i> <span>Calendário</span></a></li>
        <li><a href="gerar_relatorio_pdf.php" target="_blank"><i class="fas fa-file-pdf"></i> <span>Relatório</span></a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Sair</span></a></li>
    </ul>
</div>

<div class="main">
    <h1>Bem-vindo(a), <?php echo htmlspecialchars($nome); ?>!</h1>

    <?php if (isset($_SESSION['cadastro_disciplina_success'])): ?>
        <div class="success-message">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_SESSION['cadastro_disciplina_success']); ?> 🎉
        </div>
        <?php unset($_SESSION['cadastro_disciplina_success']); ?>
    <?php endif; ?>

    <div class="cards">
        <div class="card">
            <h3>Disciplinas Atribuídas</h3>
            <p><?php echo $totalDisciplinas; ?></p>
        </div>
        <div class="card">
            <h3>Carga Horária Total</h3>
            <p><?php echo $totalCargaHoraria; ?>h</p>
        </div>
        <div class="card">
            <h3>Turmas</h3>
            <p><?php echo $totalTurmas; ?></p>
        </div>
        <div class="card">
            <h3>Faltas a Repor</h3>
            <p><?php echo $totalReposPendentes; ?></p>
        </div>
    </div>
    
    <div class="section">
        <h2><i class="fas fa-calendar-check"></i> Próximas Reposições Agendadas</h2>
        <?php
        if (!empty($reposicoesAgendadas)) {
            foreach ($reposicoesAgendadas as $row) {
                echo "<div class='list-item'>📅 " . date('d/m/Y', strtotime($row['dia'])) . " às " . substr($row['horario'], 0, 5) . "</div>";
            }
        } else {
            echo "<div class='list-item'>Nenhuma reposição agendada para você.</div>";
        }
        ?>
    </div>
</div>
</body>
</html>