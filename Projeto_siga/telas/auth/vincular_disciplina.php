<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\vincular_disciplina.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Proteção de Rota
if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'professor') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../../negocio/DisciplinaServico.php';

$disciplinaServico = new DisciplinaServico();
$siape_professor = $_SESSION['usuario_logado'];

// Processar a vinculação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_disciplina'])) {
    $id_disciplina = filter_input(INPUT_POST, 'id_disciplina', FILTER_VALIDATE_INT);
    if ($id_disciplina && $disciplinaServico->atribuirProfessor($id_disciplina, $siape_professor)) {
        $_SESSION['op_success'] = "Disciplina vinculada a você com sucesso!";
    } else {
        $_SESSION['op_error'] = "Erro ao vincular a disciplina.";
    }
    header("Location: minhas_disciplinas.php");
    exit();
}

// Listar disciplinas não atribuídas
$disciplinasDisponiveis = $disciplinaServico->listarDisciplinasNaoAtribuidas();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Vincular Disciplinas</title>
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
        
        .btn-link { background-color: var(--secondary-color); color: white; padding: 8px 15px; border-radius: 5px; border: none; cursor: pointer; text-decoration: none; font-weight: 500; transition: background-color 0.3s ease; }
        .btn-link:hover { background-color: #218e81; }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { text-align: left; padding: 12px 15px; border-bottom: 1px solid var(--border-color); }
        th { background-color: #f2f2f2; color: #555; font-weight: 600; }
        tr:hover { background-color: #f9f9f9; }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>SIGA</h2>
    <ul>
        <li><a href="principal.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
        <li><a href="minhas_disciplinas.php" class="active"><i class="fas fa-book"></i> <span>Disciplinas</span></a></li>
        <li><a href="turmas.php"><i class="fas fa-users"></i> <span>Turmas</span></a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Sair</span></a></li>
    </ul>
</div>

<div class="main-content">
    <div class="container">
        <div class="header">
            <h1>Disciplinas Disponíveis para Vínculo</h1>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Disciplina</th>
                    <th>Carga Horária</th>
                    <th>Aulas Semanais</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($disciplinasDisponiveis)): ?>
                    <?php foreach ($disciplinasDisponiveis as $disciplina): ?>
                        <tr>
                            <td><?= htmlspecialchars($disciplina['nome_disciplina']) ?></td>
                            <td><?= htmlspecialchars($disciplina['ch']) ?>h</td>
                            <td><?= htmlspecialchars($disciplina['aulas_semanais']) ?></td>
                            <td>
                                <form action="vincular_disciplina.php" method="POST">
                                    <input type="hidden" name="id_disciplina" value="<?= $disciplina['id_disciplina'] ?>">
                                    <button type="submit" class="btn-link"><i class="fas fa-plus"></i> Vincular a mim</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center; padding: 20px;">Não há disciplinas disponíveis no momento.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>