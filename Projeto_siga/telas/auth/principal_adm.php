<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\principal_adm.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../DAO/Conexao.php';

if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'admin') {
    $_SESSION['login_error'] = "Acesso negado. Faça login como administrador.";
    header("Location: login.php");
    exit();
}

$nome_adm = $_SESSION['nome_usuario_logado'];
$cargo_adm = $_SESSION['cargo_usuario_logado'] ?? 'Administrador';

$totalProfessores = 0;
$totalAdministradores = 0;
$totalDisciplinas = 0;
$totalTurmas = 0; // Variável adicionada
$reposicoesPendentes = 0;

$conexao = new Conexao();
$conn = $conexao->get_connection();

if ($conn) {
    // Contar total de professores.
    $stmtProf = $conn->prepare("SELECT COUNT(*) AS total FROM professor");
    if($stmtProf) { $stmtProf->execute(); $resProf = $stmtProf->get_result(); if($row = $resProf->fetch_assoc()) { $totalProfessores = $row['total']; } $stmtProf->close(); }

    // Contar total de administradores.
    $stmtAdm = $conn->prepare("SELECT COUNT(*) AS total FROM admin");
    if($stmtAdm) { $stmtAdm->execute(); $resAdm = $stmtAdm->get_result(); if($row = $resAdm->fetch_assoc()) { $totalAdministradores = $row['total']; } $stmtAdm->close(); }

    // Contar total de disciplinas.
    $stmtDisc = $conn->prepare("SELECT COUNT(*) AS total FROM disciplina");
    if($stmtDisc) { $stmtDisc->execute(); $resDisc = $stmtDisc->get_result(); if($row = $resDisc->fetch_assoc()) { $totalDisciplinas = $row['total']; } $stmtDisc->close(); }
    
    // Contar total de turmas.
    $stmtTurmas = $conn->prepare("SELECT COUNT(*) AS total FROM turma");
    if($stmtTurmas) { $stmtTurmas->execute(); $resTurmas = $stmtTurmas->get_result(); if($row = $resTurmas->fetch_assoc()) { $totalTurmas = $row['total']; } $stmtTurmas->close(); }

    // Contar reposições pendentes (sem relatório associado).
    $stmtPend = $conn->prepare("SELECT COUNT(*) AS total FROM programada p LEFT JOIN relatorio r ON p.id_progra = r.id_progra WHERE r.id_progra IS NULL");
    if($stmtPend) { $stmtPend->execute(); $resPend = $stmtPend->get_result(); if($row = $resPend->fetch_assoc()) { $reposicoesPendentes = $row['total']; } $stmtPend->close(); }

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
    <title>Painel do Administrador - SIGA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #386641;
            --secondary-color: #2a9d8f;
            --accent-color: #f4a261;
            --text-color: #264653;
            --bg-light: #f8f9fa;
            --card-bg: #ffffff;
            --shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
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

        .sidebar {
            width: 250px;
            background-color: var(--primary-color);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100%;
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .sidebar h2 {
            font-size: 1.5em;
            font-weight: 700;
            margin-bottom: 30px;
            color: white;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
            width: 100%;
        }
        .sidebar li {
            width: 100%;
            margin-bottom: 5px;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            padding: 12px 25px;
            transition: all 0.3s ease;
        }
        .sidebar a i {
            margin-right: 15px;
            font-size: 1.1em;
            width: 20px;
            text-align: center;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: rgba(255, 255, 255, 0.2);
            border-left: 4px solid var(--accent-color);
            padding-left: 21px;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 30px;
            flex: 1;
            width: calc(100% - 250px);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: var(--text-color);
            font-size: 1.8em;
            font-weight: 600;
            margin: 0;
        }
        .header p {
            margin: 0;
            color: #888;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
        }
        .card {
            background-color: var(--card-bg);
            padding: 25px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
        }
        .card i {
            font-size: 2.5em;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        .card h3 {
            margin: 0;
            color: #888;
            font-size: 0.9em;
            font-weight: 500;
            text-transform: uppercase;
        }
        .card p {
            font-size: 2.2em;
            font-weight: 700;
            color: var(--text-color);
            margin: 5px 0 15px 0;
        }
        .btn-view { 
            padding: 10px 20px; 
            border-radius: 5px; 
            text-decoration: none; 
            font-weight: 500;
            cursor: pointer; 
            border: none; 
            background-color: var(--secondary-color); 
            color: white; 
            display: inline-block;
            transition: background-color 0.3s ease;
        }
        .btn-view:hover { 
            background-color: #218e81; 
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Admin SIGA</h2>
    <ul>
        <li><a href="principal_adm.php" class="active"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
        <li><a href="professores.php"><i class="fas fa-chalkboard-teacher"></i> <span>Professores</span></a></li>
        <li><a href="administradores.php"><i class="fas fa-user-shield"></i> <span>Admins</span></a></li>
        <li><a href="disciplinas.php"><i class="fas fa-book"></i> <span>Disciplinas</span></a></li>
        <li><a href="gerenciar_turmas.php"><i class="fas fa-users"></i> <span>Turmas</span></a></li>
        <li><a href="gerenciar_ausencias.php"><i class="fas fa-calendar-check"></i> <span>Ausências</span></a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Sair</span></a></li>
    </ul>
</div>

<div class="main-content">
    <div class="header">
        <div>
            <h1>Painel Administrativo</h1>
            <p>Bem-vindo(a), <?php echo htmlspecialchars($nome_adm); ?>!</p>
        </div>
    </div>

    <div class="cards">
        <div class="card">
            <i class="fas fa-users"></i>
            <h3>Professores</h3>
            <p><?php echo $totalProfessores; ?></p>
            <a href="professores.php" class="btn-view">Gerenciar</a>
        </div>
        <div class="card">
            <i class="fas fa-user-tie"></i>
            <h3>Administradores</h3>
            <p><?php echo $totalAdministradores; ?></p>
            <a href="administradores.php" class="btn-view">Gerenciar</a>
        </div>
        <div class="card">
            <i class="fas fa-book-reader"></i>
            <h3>Disciplinas</h3>
            <p><?php echo $totalDisciplinas; ?></p>
            <a href="disciplinas.php" class="btn-view">Gerenciar</a>
        </div>
        <div class="card">
            <i class="fas fa-school"></i>
            <h3>Turmas</h3>
            <p><?php echo $totalTurmas; ?></p>
            <a href="gerenciar_turmas.php" class="btn-view">Gerenciar</a>
        </div>
        <div class="card">
            <i class="fas fa-calendar-alt"></i>
            <h3>Reposições Pendentes</h3>
            <p><?php echo $reposicoesPendentes; ?></p>
            <a href="gerenciar_ausencias.php" class="btn-view">Gerenciar</a>
        </div>
    </div>
</div>

</body>
</html>