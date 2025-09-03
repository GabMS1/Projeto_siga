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
        :root { --primary-color: #386641; --secondary-color: #2a9d8f; --text-color: #264653; --bg-light: #f8f9fa; }
        body { margin: 0; font-family: 'Poppins', sans-serif; display: flex; background-color: var(--bg-light); }
        .sidebar { width: 250px; background-color: var(--primary-color); color: white; position: fixed; height: 100%; }
        .sidebar h2 { text-align: center; padding: 20px 0; margin: 0; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar a { color: white; text-decoration: none; display: flex; align-items: center; padding: 12px 25px; }
        .sidebar a i { margin-right: 10px; }
        .sidebar a:hover, .sidebar a.active { background-color: rgba(255, 255, 255, 0.2); }
        .main-content { margin-left: 250px; padding: 30px; flex: 1; }
        .container { background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; }
        .btn-link { background-color: var(--secondary-color); color: white; padding: 8px 15px; border-radius: 5px; border: none; cursor: pointer; text-decoration: none; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; }
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
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($disciplinasDisponiveis)): ?>
                    <?php foreach ($disciplinasDisponiveis as $disciplina): ?>
                        <tr>
                            <td><?= htmlspecialchars($disciplina['nome_disciplina']) ?></td>
                            <td><?= htmlspecialchars($disciplina['ch']) ?>h</td>
                            <td>
                                <form action="vincular_disciplina.php" method="POST">
                                    <input type="hidden" name="id_disciplina" value="<?= $disciplina['id_disciplina'] ?>">
                                    <button type="submit" class="btn-link">Vincular a mim</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align:center;">Não há disciplinas disponíveis no momento.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>