<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\gerenciar_turmas.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../../negocio/TurmaServico.php';

$turmaServico = new TurmaServico();
$turmas_raw = $turmaServico->listarTodasAsTurmas(); 

// Agrupar disciplinas por turma
$turmas_agrupadas = [];
foreach ($turmas_raw as $turma) {
    $id_turma = $turma['id_turma'];
    if (!isset($turmas_agrupadas[$id_turma])) {
        $turmas_agrupadas[$id_turma] = [
            'id_turma' => $id_turma,
            'curso' => $turma['curso'],
            'serie' => $turma['serie'],
            'disciplinas' => []
        ];
    }
    $turmas_agrupadas[$id_turma]['disciplinas'][] = [
        'id_disciplina' => $turma['id_disciplina'],
        'nome_disciplina' => $turma['nome_disciplina'],
        'nome_professor' => $turma['nome_professor']
    ];
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Turmas - SIGA</title>
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
            --danger-color: #e63946;
            --info-color: #457b9d;
        }
        body { margin: 0; font-family: 'Poppins', sans-serif; display: flex; background-color: var(--background-light); }
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
        .sidebar-header { padding: 0 25px; text-align: center; margin-bottom: 30px; }
        .sidebar-header h2 { font-size: 1.8em; font-weight: 700; margin: 0; color: var(--white); }
        .sidebar ul { list-style: none; padding: 0; margin: 0; width: 100%; }
        .sidebar a {
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            padding: 15px 25px;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        .sidebar a i { margin-right: 15px; font-size: 1.2em; width: 20px; text-align: center; }
        .sidebar a:hover { background-color: rgba(255, 255, 255, 0.1); border-left-color: var(--accent-color); }
        .sidebar a.active { background-color: var(--secondary-color); border-left-color: var(--accent-color); font-weight: 600; }
        
        .main-content { margin-left: 260px; padding: 30px; flex: 1; width: calc(100% - 260px); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .header h1 { color: var(--text-color); font-size: 2em; font-weight: 600; margin: 0; }
        .btn-add {
            padding: 10px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            border: none;
            background-color: var(--primary-color);
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .btn-add:hover { background-color: var(--secondary-color); transform: translateY(-2px); }

        .table-container { background-color: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px var(--shadow-color); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { text-align: left; padding: 15px; border-bottom: 1px solid var(--border-color); vertical-align: middle; }
        th { background-color: #f8f9fa; color: #555; font-weight: 600; text-transform: uppercase; font-size: 0.9em; }
        tr:not(.turma-header):hover { background-color: #f8f9fa; }
        
        .turma-header { background-color: var(--primary-color) !important; color: white; font-size: 1.1em; }
        .turma-header td { padding: 12px 15px; }
        .turma-header a { color: var(--accent-color); text-decoration: none; font-weight: 600; }
        .turma-header a:hover { text-decoration: underline; }
        .disciplina-row td { padding-left: 30px; }

        .alert { padding: 15px; margin-bottom: 20px; border-radius: 10px; font-weight: 500; border: 1px solid transparent; }
        .alert-success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }

        .btn-action {
            padding: 6px 12px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 0.8em;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: opacity 0.3s, transform 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-action:hover { opacity: 0.8; transform: translateY(-2px); }
        .btn-edit { background-color: var(--info-color); }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-header">
        <h2>SIGA</h2>
    </div>
    <ul>
        <li><a href="principal_adm.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
        <li><a href="professores.php"><i class="fas fa-chalkboard-teacher"></i> <span>Professores</span></a></li>
        <li><a href="administradores.php"><i class="fas fa-user-shield"></i> <span>Admins</span></a></li>
        <li><a href="disciplinas.php"><i class="fas fa-book"></i> <span>Disciplinas</span></a></li>
        <li><a href="gerenciar_turmas.php" class="active"><i class="fas fa-users"></i> <span>Turmas</span></a></li>
        <li><a href="gerenciar_ausencias.php"><i class="fas fa-calendar-check"></i> <span>Ausências</span></a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Sair</span></a></li>
    </ul>
</div>

<div class="main-content">
    <div class="header">
        <h1>Gerenciar Turmas</h1>
        <a href="cadastrar_turma_adm.php" class="btn-add"><i class="fas fa-plus"></i> Nova Turma/Disciplina</a>
    </div>

    <?php if(isset($_SESSION['op_success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['op_success']) ?></div>
        <?php unset($_SESSION['op_success']); endif; ?>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Disciplina</th>
                    <th>Professor</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($turmas_agrupadas)): ?>
                    <?php foreach ($turmas_agrupadas as $id_turma => $turma_info): ?>
                        <tr class="turma-header">
                            <td colspan="3">
                                <strong>Turma <?= htmlspecialchars($turma_info['id_turma']) ?></strong> - 
                                <?= htmlspecialchars($turma_info['curso']) . ' ' . htmlspecialchars($turma_info['serie']) . 'º Ano' ?>
                                <a href="ver_professores_turma.php?id_turma=<?= htmlspecialchars($id_turma) ?>" style="float: right;">
                                    <i class="fas fa-eye"></i> Ver Detalhes
                                </a>
                            </td>
                        </tr>
                        <?php foreach ($turma_info['disciplinas'] as $disciplina): ?>
                            <tr class="disciplina-row">
                                <td><?= htmlspecialchars($disciplina['nome_disciplina']) ?></td>
                                <td><?= htmlspecialchars($disciplina['nome_professor'] ?? '<em>Não atribuído</em>') ?></td>
                                <td>
                                    <a href="atribuir_professor_turma.php?id_turma=<?= htmlspecialchars($id_turma) ?>&id_disciplina=<?= $disciplina['id_disciplina'] ?>" class="btn-action btn-edit">
                                        <i class="fas fa-user-edit"></i> Atribuir/Alterar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align:center; padding: 20px;">Nenhuma turma cadastrada.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>