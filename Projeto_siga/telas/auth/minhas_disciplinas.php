<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\minhas_disciplinas.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../negocio/DisciplinaServico.php';

if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'professor') {
    $_SESSION['login_error'] = "Acesso negado. Faça login como professor.";
    header("Location: login.php");
    exit();
}

$siape = $_SESSION['usuario_logado'];
$disciplinaServico = new DisciplinaServico();
$disciplinas = [];
$mensagem = "";
$sucesso = false;

// Carrega a lista de disciplinas para exibição
try {
    $disciplinas = $disciplinaServico->listarDisciplinasPorProfessor($siape);
} catch (Exception $e) {
    $mensagem = "Erro ao carregar a lista de disciplinas.";
    $sucesso = false;
    error_log("Erro em minhas_disciplinas.php: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Disciplinas</title>
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
        body { margin: 0; font-family: 'Poppins', sans-serif; display: flex; background-color: var(--bg-light); }
        .sidebar { width: 250px; background-color: var(--primary-color); color: white; position: fixed; height: 100%; box-shadow: var(--shadow); display: flex; flex-direction: column; align-items: center; }
        .sidebar h2 { font-size: 1.5em; font-weight: 700; margin-bottom: 30px; margin-top: 20px;}
        .sidebar ul { list-style: none; padding: 0; margin: 0; width: 100%; }
        .sidebar li { width: 100%; margin-bottom: 5px; }
        .sidebar a { color: white; text-decoration: none; font-weight: 500; display: flex; align-items: center; padding: 12px 25px; transition: all 0.3s ease; }
        .sidebar a i { margin-right: 15px; font-size: 1.1em; width: 20px; text-align: center; }
        .sidebar a:hover, .sidebar a.active { background-color: rgba(255, 255, 255, 0.2); border-left: 4px solid var(--accent-color); padding-left: 21px; }
        
        .main-content { margin-left: 250px; padding: 30px; flex: 1; width: calc(100% - 250px); }
        .container { background-color: var(--card-bg); padding: 30px; border-radius: 12px; box-shadow: var(--shadow); }
        
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .header h1 { color: var(--text-color); margin: 0; font-size: 1.8em; font-weight: 600;}
        
        .btn { padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: 500; cursor: pointer; border: none; transition: background-color 0.3s ease; color: white; }
        .btn-add { background-color: var(--secondary-color); }
        .btn-add:hover { background-color: #218e81; }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { text-align: left; padding: 12px 15px; border-bottom: 1px solid var(--border-color); }
        th { background-color: #f2f2f2; color: #555; font-weight: 600; }
        tr:hover { background-color: #f9f9f9; }
        
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; font-weight: 500; }
        .alert-success { background-color: #d4edda; color: #155724; }
        .alert-danger { background-color: #f8d7da; color: #721c24; }
        
        .actions-cell { display: flex; gap: 10px; }
        .btn-edit { background-color: #007bff; }
        .btn-edit:hover { background-color: #0069d9; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>SIGA</h2>
    <ul>
        <li><a href="principal.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
        <li><a href="minhas_disciplinas.php" class="active"><i class="fas fa-book"></i> <span>Disciplinas</span></a></li>
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
    <div class="container">
        <div class="header">
            <h1>Minhas Disciplinas</h1>
            <a href="vincular_disciplina.php" class="btn btn-add"><i class="fas fa-link"></i> Vincular Nova Disciplina</a>
        </div>

        <?php 
        if (isset($_SESSION['op_success'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['op_success']) . '</div>';
            unset($_SESSION['op_success']);
        }
        if ($mensagem) {
            echo '<div class="alert ' . ($sucesso ? 'alert-success' : 'alert-danger') . '">' . htmlspecialchars($mensagem) . '</div>';
        }
        ?>

        <h2>Disciplinas Vinculadas (Total: <?php echo count($disciplinas); ?>)</h2>
        
        <?php if (!empty($disciplinas)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome da Disciplina</th>
                        <th>Carga Horária</th>
                        <th>Aulas Semanais</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($disciplinas as $disciplina): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($disciplina['id_disciplina']); ?></td>
                            <td><?php echo htmlspecialchars($disciplina['nome_disciplina']); ?></td>
                            <td><?php echo htmlspecialchars($disciplina['ch']); ?>h</td>
                            <td><?php echo htmlspecialchars($disciplina['aulas_semanais']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nenhuma disciplina vinculada a você. Clique no botão acima para vincular uma.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>