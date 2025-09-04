<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\disciplinas.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../negocio/DisciplinaServico.php';

if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'admin') {
    $_SESSION['login_error'] = "Acesso negado. Faça login como administrador.";
    header("Location: login.php");
    exit();
}

$disciplinaServico = new DisciplinaServico();
$disciplinas = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao']) && $_POST['acao'] === 'excluir' && isset($_POST['id_excluir'])) {
        $id_excluir = trim($_POST['id_excluir']);
        if ($disciplinaServico->excluirDisciplina($id_excluir)) {
            $_SESSION['op_success'] = "Disciplina excluída com sucesso!";
        } else {
            $_SESSION['op_error'] = "Erro ao excluir a disciplina. Verifique se ela não está vinculada a turmas.";
        }
        header("Location: disciplinas.php");
        exit();
    }
}

try {
    $disciplinas = $disciplinaServico->listarDisciplinas();
} catch (Exception $e) {
    $_SESSION['op_error'] = "Erro ao carregar a lista de disciplinas.";
    error_log("Erro em disciplinas.php: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Disciplinas - SIGA</title>
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
        th, td { text-align: left; padding: 15px; border-bottom: 1px solid var(--border-color); }
        th { background-color: #f8f9fa; color: #555; font-weight: 600; text-transform: uppercase; font-size: 0.9em; }
        tr:hover { background-color: #f8f9fa; }
        
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 10px; font-weight: 500; border: 1px solid transparent; }
        .alert-success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        
        .actions-cell { display: flex; gap: 10px; }
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
        .btn-edit { background-color: var(--info-color); }
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
        <li><a href="disciplinas.php" class="active"><i class="fas fa-book"></i> <span>Disciplinas</span></a></li>
        <li><a href="gerenciar_turmas.php"><i class="fas fa-users"></i> <span>Turmas</span></a></li>
        <li><a href="gerenciar_ausencias.php"><i class="fas fa-calendar-check"></i> <span>Ausências</span></a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Sair</span></a></li>
    </ul>
</div>

<div class="main-content">
    <div class="header">
        <h1>Gerenciar Disciplinas</h1>
        <a href="cadastrar_disciplina.php" class="btn-add"><i class="fas fa-plus"></i> Nova Disciplina</a>
    </div>

    <?php 
        if(isset($_SESSION['op_success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['op_success']) ?></div>
            <?php unset($_SESSION['op_success']); 
        endif; 
        if(isset($_SESSION['op_error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['op_error']) ?></div>
            <?php unset($_SESSION['op_error']); 
        endif; 
    ?>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome da Disciplina</th>
                    <th>Carga Horária</th>
                    <th>Aulas/Semana</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($disciplinas)): ?>
                    <?php foreach ($disciplinas as $disciplina): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($disciplina['id_disciplina']); ?></td>
                            <td><?php echo htmlspecialchars($disciplina['nome_disciplina']); ?></td>
                            <td><?php echo htmlspecialchars($disciplina['ch']); ?>h</td>
                            <td><?php echo htmlspecialchars($disciplina['aulas_semanais']); ?></td>
                            <td class="actions-cell">
                                <a href="editar_disciplina.php?id=<?php echo htmlspecialchars($disciplina['id_disciplina']); ?>" class="btn-action btn-edit">Editar</a>
                                <form action="disciplinas.php" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir a disciplina \'<?php echo htmlspecialchars(addslashes($disciplina['nome_disciplina'])); ?>\'?');">
                                    <input type="hidden" name="acao" value="excluir">
                                    <input type="hidden" name="id_excluir" value="<?php echo htmlspecialchars($disciplina['id_disciplina']); ?>">
                                    <button type="submit" class="btn-action btn-delete">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding: 20px;">Nenhuma disciplina cadastrada.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>