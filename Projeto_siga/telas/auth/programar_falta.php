<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\programar_falta.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../negocio/AusenciaServico.php';
require_once __DIR__ . '/../../negocio/ProfessorServico.php';
require_once __DIR__ . '/../../negocio/DisciplinaServico.php';
require_once __DIR__ . '/../../negocio/TurmaServico.php';

if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'professor') {
    $_SESSION['login_error'] = "Acesso negado. Faça login como professor.";
    header("Location: login.php");
    exit();
}

$siape_professor_logado = $_SESSION['usuario_logado'];
$nome_professor_logado = $_SESSION['nome_usuario_logado'];

$mensagem = "";
$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ausenciaServico = new AusenciaServico();
    
    $dados_falta = [
        'dia' => $_POST['dia'] ?? '',
        'horario' => $_POST['horario'] ?? '',
        'id_turma' => $_POST['id_turma'] ?? '',
        'id_disciplina' => $_POST['id_disciplina'] ?? '',
        'siape_ausente' => $siape_professor_logado,
        'assinatura_ausente' => $nome_professor_logado,
    ];

    if (
        empty($dados_falta['dia']) || 
        empty($dados_falta['horario']) || 
        empty($dados_falta['id_turma']) || 
        empty($dados_falta['id_disciplina'])
    ) {
        $mensagem = "Todos os campos são obrigatórios.";
    } else {
        if ($ausenciaServico->programarFalta($dados_falta)) {
            $mensagem = "Falta programada com sucesso!";
            $sucesso = true;
        } else {
            $mensagem = "Erro ao programar a falta. Tente novamente.";
        }
    }
}

$disciplinaServico = new DisciplinaServico();
$disciplinas = $disciplinaServico->listarDisciplinas();

$turmaServico = new TurmaServico();
$turmas = $turmaServico->listarTurmas($siape_professor_logado);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programar Falta</title>
    <style>
        body { margin: 0; font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f4f7f8; }
        .container { background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 20px rgba(0,0,0,0.1); width: 100%; max-width: 600px; box-sizing: border-box; }
        h1 { color: #386641; text-align: center; margin-bottom: 25px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="date"], input[type="time"], select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .submit-button { background-color: #dc3545; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 1em; width: 100%; transition: background-color 0.3s ease; }
        .submit-button:hover { background-color: #c82333; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #386641; text-decoration: none; font-weight: bold; }
        .error-message, .success-message { text-align: center; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-weight: bold; }
        .error-message { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .success-message { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>

<div class="container">
    <h1>Programar Falta</h1>
    
    <?php if ($mensagem): ?>
        <p class="message <?php echo $sucesso ? 'success-message' : 'error-message'; ?>">
            <?php echo htmlspecialchars($mensagem); ?>
        </p>
    <?php endif; ?>

    <form action="" method="POST">
        <input type="hidden" name="siape_ausente" value="<?php echo htmlspecialchars($siape_professor_logado); ?>">
        <input type="hidden" name="assinatura_ausente" value="<?php echo htmlspecialchars($nome_professor_logado); ?>">

        <div class="form-group">
            <label for="dia">Data da Falta:</label>
            <input type="date" id="dia" name="dia" required>
        </div>
        
        <div class="form-group">
            <label for="horario">Horário da Falta:</label>
            <input type="time" id="horario" name="horario" required>
        </div>

        <div class="form-group">
            <label for="id_turma">Turma:</label>
            <select id="id_turma" name="id_turma" required>
                <option value="">Selecione a Turma</option>
                <?php foreach ($turmas as $turma): ?>
                    <option value="<?php echo htmlspecialchars($turma['id_turma']); ?>">
                        <?php echo htmlspecialchars($turma['curso']) . " - " . htmlspecialchars($turma['serie']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="id_disciplina">Disciplina:</label>
            <select id="id_disciplina" name="id_disciplina" required>
                <option value="">Selecione a Disciplina</option>
                <?php foreach ($disciplinas as $disciplina): ?>
                    <option value="<?php echo htmlspecialchars($disciplina['id_disciplina']); ?>">
                        <?php echo htmlspecialchars($disciplina['nome_disciplina']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="submit-button">Programar Falta</button>
    </form>

    <a href="principal.php" class="back-link">← Voltar ao Dashboard</a>
</div>

</body>
</html>