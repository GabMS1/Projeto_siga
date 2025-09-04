<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\atribuir_professor_turma.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteção de Rota
if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../../negocio/TurmaServico.php';
require_once __DIR__ . '/../../negocio/ProfessorServico.php';
require_once __DIR__ . '/../../negocio/DisciplinaServico.php';

$mensagem = '';
$sucesso = false;
$turma = null;
$professores = [];

$turmaServico = new TurmaServico();
$professorServico = new ProfessorServico();
$disciplinaServico = new DisciplinaServico();

// Carregar dados da turma e lista de professores
if (isset($_GET['id_turma'])) {
    $id_turma = filter_input(INPUT_GET, 'id_turma', FILTER_VALIDATE_INT);
    if ($id_turma) {
        $turma = $turmaServico->buscarTurmaParaEdicao($id_turma);
        $professores = $professorServico->listarProfessores();
    }
    if (!$turma) {
        $mensagem = "Turma não encontrada.";
    }
}

// Processar o formulário de atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_disciplina = filter_input(INPUT_POST, 'id_disciplina', FILTER_VALIDATE_INT);
    $siape_prof = $_POST['siape_prof'] ?? '';

    if ($id_disciplina) {
        if ($disciplinaServico->atribuirProfessor($id_disciplina, $siape_prof)) {
             $_SESSION['op_success'] = "Professor atribuído à turma com sucesso!";
             header("Location: gerenciar_turmas.php");
             exit();
        } else {
            $mensagem = "Erro ao atribuir professor.";
            $sucesso = false;
        }
    } else {
        $mensagem = "ID da disciplina é inválido.";
    }
     // Recarregar dados em caso de falha
    $id_turma = filter_input(INPUT_POST, 'id_turma', FILTER_VALIDATE_INT);
    $turma = $turmaServico->buscarTurmaParaEdicao($id_turma);
    $professores = $professorServico->listarProfessores();
}

if (!isset($_GET['id_turma']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
     header("Location: gerenciar_turmas.php");
     exit();
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Admin: Atribuir Professor</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary-color: #386641; --secondary-color: #2a9d8f; --text-color: #264653; --bg-light: #f8f9fa; }
        body { margin: 0; font-family: 'Poppins', sans-serif; display: flex; background-color: var(--bg-light); }
        .main-content { padding: 30px; flex: 1; display: flex; justify-content: center; align-items: center; }
        .container { background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); max-width: 600px; width: 100%; }
        h1 { color: var(--text-color); margin-bottom: 10px; }
        .turma-info { margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid #eee; }
        .turma-info p { margin: 5px 0; color: #555; }
        .turma-info span { font-weight: 600; color: var(--primary-color); }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; }
        select { width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ccc; box-sizing: border-box; }
        .btn { padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; color: white; text-decoration: none; display: inline-block; font-weight: 500; }
        .btn-success { background-color: var(--secondary-color); }
        .btn-secondary { background-color: #6c757d; margin-left: 10px; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-danger { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<div class="main-content">
    <div class="container">
        <h1>Atribuir Professor à Turma</h1>
        
        <?php if ($mensagem): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>

        <?php if ($turma): ?>
            <div class="turma-info">
                <p><strong>Turma ID:</strong> <span><?= htmlspecialchars($turma['id_turma']) ?></span></p>
                <p><strong>Curso:</strong> <span><?= htmlspecialchars($turma['curso']) ?> - <?= htmlspecialchars($turma['serie']) ?>º Ano</span></p>
                <p><strong>Disciplina:</strong> <span><?= htmlspecialchars($turma['nome_disciplina']) ?></span></p>
            </div>
            
            <form action="atribuir_professor_turma.php" method="POST">
                <input type="hidden" name="id_turma" value="<?= htmlspecialchars($turma['id_turma']); ?>">
                <input type="hidden" name="id_disciplina" value="<?= htmlspecialchars($turma['id_disciplina']); ?>">

                <div class="form-group">
                    <label for="siape_prof">Selecione o Professor:</label>
                    <select id="siape_prof" name="siape_prof" required>
                        <option value="">Desvincular Professor</option>
                        <?php foreach ($professores as $professor): ?>
                            <option value="<?= htmlspecialchars($professor['siape_prof']); ?>" 
                                <?= ($turma['siape_prof'] == $professor['siape_prof']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($professor['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-success">Salvar Atribuição</button>
                <a href="gerenciar_turmas.php" class="btn btn-secondary">Cancelar</a>
            </form>
        <?php else: ?>
             <a href="gerenciar_turmas.php" class="btn btn-secondary">Voltar</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>