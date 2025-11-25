﻿<?php
// C:\xampp\htdocs\Projeto_siga-2\Projeto_siga\telas\auth\gerenciar_turmas.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// CORREÇÃO: A tela deve usar o Serviço.
require_once __DIR__ . '/../../negocio/TurmaServico.php'; // Já estava aqui
require_once __DIR__ . '/helpers.php'; // Adicionado para alertas

$turmaServico = new TurmaServico();
$turmas_raw = $turmaServico->listarTodasAsTurmas();

// Organiza os dados brutos em um array estruturado por turma
$turmas_agrupadas = [];
foreach ($turmas_raw as $item) {
    $id_turma = $item['id_turma'];
    if (!isset($turmas_agrupadas[$id_turma])) {
        $turmas_agrupadas[$id_turma] = [
            'id_turma' => $id_turma,
            'curso' => $item['curso'],
            'serie' => $item['serie'],
            'disciplinas' => []
        ];
    }
    // Adiciona a disciplina e o professor (se houver) à turma
    $turmas_agrupadas[$id_turma]['disciplinas'][] = [
        'id_disciplina' => $item['id_disciplina'],
        'nome_disciplina' => $item['nome_disciplina'],
        'nome_professor' => $item['nome_professor'] // Pode ser null
    ];
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        .header-actions { display: flex; gap: 15px; }
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
        tr:hover { background-color: #f8f9fa; }
        
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 10px; font-weight: 500; border: 1px solid transparent; }
        .alert-success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        
        .actions-cell { display: flex; flex-wrap: wrap; gap: 10px; }
        .btn-action {
            padding: 8px 15px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 0.9em;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: opacity 0.3s, transform 0.2s;
        }
        .btn-action:hover { opacity: 0.8; transform: translateY(-2px); }
        .btn-view { background-color: var(--secondary-color); }
        .btn-assign { background-color: var(--info-color); }
        .btn-delete { background-color: var(--danger-color); }
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
        <div class="header-actions">
            <a href="cadastrar_turma_nova.php" class="btn-add"><i class="fas fa-plus"></i> Nova Turma</a>
            <a href="cadastrar_turma_adm.php" class="btn-add"><i class="fas fa-link"></i> Associar Disciplina</a>
        </div>
    </div>

    <?php display_session_alerts(); ?>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID Turma</th>
                    <th>Curso</th>
                    <th>Série</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($turmas_agrupadas)): ?>
                    <?php foreach ($turmas_agrupadas as $id_turma => $turma): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($turma['id_turma']); ?></td>
                            <td><?php echo htmlspecialchars($turma['curso']); ?></td>
                            <td><?php echo htmlspecialchars($turma['serie']); ?>º Ano</td>
                            <td class="actions-cell">
                                <a href="ver_professores_turma.php?id_turma=<?= $id_turma ?>" class="btn-action btn-view">Ver Professores</a>
                                <a href="atribuir_professor_turma.php?id_turma=<?= $id_turma ?>" class="btn-action btn-assign">Atribuir Professor</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center; padding: 20px;">Nenhuma turma cadastrada.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>