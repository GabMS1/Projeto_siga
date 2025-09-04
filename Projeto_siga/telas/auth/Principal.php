<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\Principal.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../DAO/Conexao.php';

if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'professor') {
    $_SESSION['login_error'] = "Acesso negado. Faça login como professor.";
    header("Location: login.php");
    exit();
}

$siape = $_SESSION['usuario_logado'];
$nome = $_SESSION['nome_usuario_logado'];

$totalDisciplinas = 0;
$totalTurmas = 0;
$reposicoesDisponiveis = 0;
$totalCargaHoraria = 0;
$horasRestantes = 0;
$reposicoesAgendadas = [];

$conexao = new Conexao();
$conn = $conexao->get_connection();

if ($conn) {
    $stmt1 = $conn->prepare("SELECT COUNT(*) AS total, SUM(ch) AS total_horas, SUM(aulas_semanais) AS total_aulas_semanais FROM disciplina WHERE siape_prof = ?");
    if ($stmt1) {
        $stmt1->bind_param("s", $siape);
        $stmt1->execute();
        $res1 = $stmt1->get_result();
        if ($row = $res1->fetch_assoc()) {
            $totalDisciplinas = $row['total'] ?? 0;
            $totalCargaHoraria = $row['total_horas'] ?? 0;
            $totalAulasSemanais = $row['total_aulas_semanais'] ?? 0;

            if ($totalAulasSemanais > 0) {
                $inicioSemestre = strtotime("first Monday of August " . date('Y'));
                $hoje = time();
                if ($hoje > $inicioSemestre) {
                    $semanasPassadas = floor(($hoje - $inicioSemestre) / (60 * 60 * 24 * 7));
                    $horasCumpridas = $semanasPassadas * $totalAulasSemanais;
                    $horasRestantes = $totalCargaHoraria - $horasCumpridas;
                    $horasRestantes = max(0, $horasRestantes);
                } else {
                    $horasRestantes = $totalCargaHoraria;
                }
            } else {
                $horasRestantes = $totalCargaHoraria;
            }
        }
        $stmt1->close();
    }

    $stmt2 = $conn->prepare("SELECT COUNT(DISTINCT t.id_turma) AS total FROM turma t JOIN disciplina d ON t.id_disciplina = d.id_disciplina WHERE d.siape_prof = ?");
    if($stmt2){ $stmt2->bind_param("s", $siape); $stmt2->execute(); $res2 = $stmt2->get_result(); if($row = $res2->fetch_assoc()){ $totalTurmas = $row['total']; } $stmt2->close(); }
    
    $stmt3 = $conn->prepare("SELECT COUNT(*) AS total FROM programada p JOIN prof_ausente pa ON p.id_ass_ausente = pa.id_ass_ausente WHERE p.id_ass_subs IS NULL");
    if($stmt3){ $stmt3->execute(); $res3 = $stmt3->get_result(); if($row = $res3->fetch_assoc()){ $reposicoesDisponiveis = $row['total']; } $stmt3->close(); }

    $stmt4 = $conn->prepare("SELECT p.dia, p.horario, d.nome_disciplina, t.curso, t.serie FROM programada p JOIN prof_ausente pa ON p.id_ass_ausente = pa.id_ass_ausente LEFT JOIN prof_subs ps ON p.id_ass_subs = ps.id_ass_subs JOIN disciplina d ON p.id_disciplina = d.id_disciplina JOIN turma t ON p.id_turma = t.id_turma WHERE (pa.siape_prof = ? OR ps.siape_prof = ?) AND p.dia >= CURDATE() ORDER BY p.dia ASC, p.horario ASC LIMIT 3");
    if($stmt4){ $stmt4->bind_param("ss", $siape, $siape); $stmt4->execute(); $res4 = $stmt4->get_result(); while($row = $res4->fetch_assoc()){ $reposicoesAgendadas[] = $row; } $stmt4->close(); }

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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #386641;
            --secondary-color: #6A994E;
            --accent-color: #A7C957;
            --background-light: #FBFBFB;
            --text-color: #333;
            --white: #FFFFFF;
            --shadow-color: rgba(0, 0, 0, 0.08);
            --border-color: #E0E0E0;
        }
        body { margin: 0; font-family: 'Poppins', sans-serif; display: flex; background-color: var(--background-light); color: var(--text-color); }
        .sidebar {
            width: 260px;
            background-color: var(--primary-color);
            color: var(--white);
            position: fixed;
            height: 100%;
            box-shadow: 2px 0 10px var(--shadow-color);
            display: flex;
            flex-direction: column;
            padding-top: 20px;
        }
        .sidebar-header {
            padding: 0 25px;
            text-align: center;
            margin-bottom: 30px;
        }
        .sidebar-header h2 {
            font-size: 1.8em;
            font-weight: 700;
            margin: 0;
            color: var(--white);
        }
        .sidebar ul { list-style: none; padding: 0; margin: 0; width: 100%; }
        .sidebar li { width: 100%; }
        .sidebar a {
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            padding: 15px 25px;
            transition: all 0.3s ease;
            position: relative;
            border-left: 4px solid transparent;
        }
        .sidebar a i { margin-right: 15px; font-size: 1.2em; width: 20px; text-align: center; }
        .sidebar a:hover { background-color: rgba(255, 255, 255, 0.1); border-left-color: var(--accent-color); }
        .sidebar a.active { background-color: var(--secondary-color); border-left-color: var(--accent-color); font-weight: 600; }
        
        .main-content { margin-left: 260px; padding: 30px; flex: 1; width: calc(100% - 260px); }
        .header { margin-bottom: 30px; }
        .header h1 { color: var(--text-color); font-size: 2em; font-weight: 600; margin: 0; }
        
        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; }
        .card {
            background-color: var(--white);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px var(--shadow-color);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12); }
        .card-icon { font-size: 2.2em; padding: 20px; border-radius: 50%; color: white; }
        .card-info h3 { margin: 0; color: #888; font-size: 0.9em; font-weight: 500; text-transform: uppercase; }
        .card-info p { font-size: 2em; font-weight: 700; color: var(--text-color); margin: 5px 0 0 0; }

        .icon-ch { background-color: #e76f51; }
        .icon-restantes { background-color: #457b9d; }
        .icon-disciplinas { background-color: #2a9d8f; }
        .icon-turmas { background-color: #f4a261; }
        .icon-disponiveis { background-color: #e63946; }

        .section { margin-top: 40px; background-color: var(--white); padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px var(--shadow-color); }
        .section h2 { margin-top: 0; margin-bottom: 20px; color: var(--text-color); border-bottom: 2px solid var(--border-color); padding-bottom: 10px; font-size: 1.4em; font-weight: 600; display: flex; align-items: center; }
        .section h2 i { margin-right: 10px; color: var(--primary-color); }
        .list-item { display: flex; align-items: center; gap: 15px; padding: 15px; border-radius: 10px; margin-bottom: 10px; border: 1px solid var(--border-color); background-color: #f9f9f9; }
        .list-item i { color: var(--secondary-color); font-size: 1.5em; }

        a.card-link { text-decoration: none; color: inherit; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <h2>SIGA</h2>
    </div>
    <ul>
        <li><a href="principal.php" class="active"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
        <li><a href="minhas_disciplinas.php"><i class="fas fa-book"></i> <span>Disciplinas</span></a></li>
        <li><a href="turmas.php"><i class="fas fa-users"></i> <span>Turmas</span></a></li>
        <li><a href="minhas_reposicoes.php"><i class="fas fa-history"></i> <span>Reposições</span></a></li>
        <li><a href="agendar_reposicao.php"><i class="fas fa-calendar-plus"></i> <span>Agendar Reposição</span></a></li>
        <li><a href="programar_falta.php"><i class="fas fa-calendar-times"></i> <span>Programar Falta</span></a></li>
        <li><a href="calendario.php"><i class="fas fa-calendar-alt"></i> <span>Calendário Geral</span></a></li>
        <li><a href="gerar_relatorio_pdf.php" target="_blank"><i class="fas fa-file-pdf"></i> <span>Gerar Relatório</span></a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Sair</span></a></li>
    </ul>
</div>

<div class="main-content">
    <div class="header">
        <h1>Painel do Professor</h1>
        <p>Bem-vindo(a), <?php echo htmlspecialchars(explode(' ', $nome)[0]); ?>!</p>
    </div>

    <div class="cards">
        <div class="card">
            <div class="card-icon icon-ch"><i class="fas fa-hourglass-start"></i></div>
            <div class="card-info">
                <h3>Carga Total</h3>
                <p><?php echo $totalCargaHoraria; ?>h</p>
            </div>
        </div>
        <div class="card">
            <div class="card-icon icon-restantes"><i class="fas fa-hourglass-half"></i></div>
            <div class="card-info">
                <h3>Horas Restantes</h3>
                <p><?php echo $horasRestantes; ?>h</p>
            </div>
        </div>
        <div class="card">
            <div class="card-icon icon-disciplinas"><i class="fas fa-book-open"></i></div>
            <div class="card-info">
                <h3>Disciplinas</h3>
                <p><?php echo $totalDisciplinas; ?></p>
            </div>
        </div>
        <div class="card">
            <div class="card-icon icon-turmas"><i class="fas fa-chalkboard-teacher"></i></div>
            <div class="card-info">
                <h3>Turmas</h3>
                <p><?php echo $totalTurmas; ?></p>
            </div>
        </div>
        
        <a href="calendario.php" class="card-link">
            <div class="card">
                <div class="card-icon icon-disponiveis"><i class="fas fa-hand-holding-hand"></i></div>
                <div class="card-info">
                    <h3>Reposições Disponíveis</h3>
                    <p><?php echo $reposicoesDisponiveis; ?></p>
                </div>
            </div>
        </a>

    </div>
    
    <div class="section">
        <h2><i class="fas fa-calendar-check"></i> Próximos Eventos</h2>
        <?php
        if (!empty($reposicoesAgendadas)) {
            foreach ($reposicoesAgendadas as $row) {
                echo "<div class='list-item'>
                        <i class='fas fa-calendar-day'></i>
                        <div><strong>" . date('d/m/Y', strtotime($row['dia'])) . " às " . substr($row['horario'], 0, 5) . "h</strong><br>
                        <small>" . htmlspecialchars($row['nome_disciplina']) . " - " . htmlspecialchars($row['curso'] . " " . $row['serie']) . "º Ano</small></div>
                      </div>";
            }
        } else {
            echo "<div class='list-item'><i class='fas fa-info-circle'></i> Nenhuma reposição agendada para você nos próximos dias.</div>";
        }
        ?>
    </div>
</div>
</body>
</html>