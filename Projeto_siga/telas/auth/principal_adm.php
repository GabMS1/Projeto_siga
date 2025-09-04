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
$totalTurmas = 0;
$reposicoesPendentes = 0;

$conexao = new Conexao();
$conn = $conexao->get_connection();

if ($conn) {
    $stmtProf = $conn->prepare("SELECT COUNT(*) AS total FROM professor");
    if($stmtProf) { $stmtProf->execute(); $resProf = $stmtProf->get_result(); if($row = $resProf->fetch_assoc()) { $totalProfessores = $row['total']; } $stmtProf->close(); }

    $stmtAdm = $conn->prepare("SELECT COUNT(*) AS total FROM admin");
    if($stmtAdm) { $stmtAdm->execute(); $resAdm = $stmtAdm->get_result(); if($row = $resAdm->fetch_assoc()) { $totalAdministradores = $row['total']; } $stmtAdm->close(); }

    $stmtDisc = $conn->prepare("SELECT COUNT(*) AS total FROM disciplina");
    if($stmtDisc) { $stmtDisc->execute(); $resDisc = $stmtDisc->get_result(); if($row = $resDisc->fetch_assoc()) { $totalDisciplinas = $row['total']; } $stmtDisc->close(); }
    
    $stmtTurmas = $conn->prepare("SELECT COUNT(DISTINCT id_turma) AS total FROM turma");
    if($stmtTurmas) { $stmtTurmas->execute(); $resTurmas = $stmtTurmas->get_result(); if($row = $resTurmas->fetch_assoc()) { $totalTurmas = $row['total']; } $stmtTurmas->close(); }

    $stmtPend = $conn->prepare("SELECT COUNT(*) AS total FROM programada p LEFT JOIN relatorio r ON p.id_progra = r.id_progra WHERE r.id_progra IS NULL AND p.id_ass_subs IS NOT NULL");
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
            --secondary-color: #6A994E;
            --accent-color: #A7C957;
            --background-light: #FBFBFB;
            --text-color: #333;
            --white: #FFFFFF;
            --shadow-color: rgba(0, 0, 0, 0.08);
            --border-color: #E0E0E0;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            display: flex;
            background-color: var(--background-light);
            color: var(--text-color);
        }

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
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header-text h1 { color: var(--text-color); font-size: 2em; font-weight: 600; margin: 0; }
        .header-text p { margin: 5px 0 0; color: #777; }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
        }
        .card {
            background-color: var(--white);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px var(--shadow-color);
            text-align: left;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 150px;
        }
        .card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12); }
        .card-header { display: flex; align-items: center; gap: 15px; margin-bottom: 15px; }
        .card-icon { font-size: 1.8em; color: var(--primary-color); background-color: rgba(56, 102, 65, 0.1); padding: 12px; border-radius: 50%; }
        .card-header h3 { margin: 0; color: #555; font-size: 1em; font-weight: 600; }
        .card-body p { font-size: 2.5em; font-weight: 700; color: var(--text-color); margin: 0; }
        .card-footer { margin-top: 20px; }
        .btn-view {
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
            border: none;
            background-color: var(--secondary-color);
            color: white;
            display: inline-block;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .btn-view:hover { background-color: var(--primary-color); transform: translateY(-2px); }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <h2>SIGA</h2>
    </div>
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
        <div class="header-text">
            <h1>Painel Administrativo</h1>
            <p>Bem-vindo(a), <?php echo htmlspecialchars($nome_adm); ?>!</p>
        </div>
    </div>

    <div class="cards">
        <div class="card">
            <div>
                <div class="card-header">
                    <i class="fas fa-chalkboard-teacher card-icon"></i>
                    <h3>Professores</h3>
                </div>
                <div class="card-body">
                    <p><?php echo $totalProfessores; ?></p>
                </div>
            </div>
            <div class="card-footer">
                <a href="professores.php" class="btn-view">Gerenciar</a>
            </div>
        </div>
        <div class="card">
            <div>
                <div class="card-header">
                    <i class="fas fa-user-shield card-icon"></i>
                    <h3>Administradores</h3>
                </div>
                <div class="card-body">
                    <p><?php echo $totalAdministradores; ?></p>
                </div>
            </div>
            <div class="card-footer">
                <a href="administradores.php" class="btn-view">Gerenciar</a>
            </div>
        </div>
        <div class="card">
            <div>
                <div class="card-header">
                    <i class="fas fa-book-reader card-icon"></i>
                    <h3>Disciplinas</h3>
                </div>
                <div class="card-body">
                    <p><?php echo $totalDisciplinas; ?></p>
                </div>
            </div>
            <div class="card-footer">
                <a href="disciplinas.php" class="btn-view">Gerenciar</a>
            </div>
        </div>
        <div class="card">
            <div>
                <div class="card-header">
                    <i class="fas fa-school card-icon"></i>
                    <h3>Turmas</h3>
                </div>
                <div class="card-body">
                    <p><?php echo $totalTurmas; ?></p>
                </div>
            </div>
            <div class="card-footer">
                <a href="gerenciar_turmas.php" class="btn-view">Gerenciar</a>
            </div>
        </div>
        <div class="card">
            <div>
                <div class="card-header">
                    <i class="fas fa-calendar-alt card-icon"></i>
                    <h3>Reposições Pendentes</h3>
                </div>
                <div class="card-body">
                    <p><?php echo $reposicoesPendentes; ?></p>
                </div>
            </div>
            <div class="card-footer">
                <a href="gerenciar_ausencias.php" class="btn-view">Gerenciar</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>