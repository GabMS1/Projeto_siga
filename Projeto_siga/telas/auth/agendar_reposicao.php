<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\agendar_reposicao.php

// ATENÇÃO CRÍTICA: DEVE SER A PRIMEIRA COISA NO ARQUIVO.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclui os serviços necessários para a lógica e para popular os dropdowns.
require_once __DIR__ . '/../../negocio/AusenciaServico.php';
require_once __DIR__ . '/../../negocio/ProfessorServico.php';
require_once __DIR__ . '/../../negocio/DisciplinaServico.php';
require_once __DIR__ . '/../../negocio/TurmaServico.php';

// --- PROTEÇÃO DE ROTA ---
// Verifica se o usuário está logado e se é um professor.
if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'professor') {
    $_SESSION['login_error'] = "Acesso negado. Faça login como professor.";
    header("Location: login.php");
    exit();
}

// Pega os dados do professor logado da sessão.
$siape_professor_logado = $_SESSION['usuario_logado'];
$nome_professor_logado = $_SESSION['nome_usuario_logado'];

$mensagem = "";

// Lógica para lidar com o envio do formulário via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ausenciaServico = new AusenciaServico();
    
    // Coleta os dados do formulário
    $dados_reposicao = [
        'dia' => $_POST['dia'] ?? '',
        'horario' => $_POST['horario'] ?? '',
        'id_turma' => $_POST['id_turma'] ?? '',
        'id_disciplina' => $_POST['id_disciplina'] ?? '',
        'siape_ausente' => $siape_professor_logado,
        'assinatura_ausente' => $nome_professor_logado,
        'siape_substituto' => $_POST['siape_substituto'] ?? '',
        'assinatura_substituto' => $_POST['nome_substituto'] ?? '',
        'autor_gov' => 'N/A' // Ou um campo de formulário para ser preenchido
    ];

    // Validação básica dos dados
    if (
        empty($dados_reposicao['dia']) || 
        empty($dados_reposicao['horario']) || 
        empty($dados_reposicao['id_turma']) || 
        empty($dados_reposicao['id_disciplina']) ||
        empty($dados_reposicao['siape_substituto'])
    ) {
        $_SESSION['reposicao_error'] = "Todos os campos obrigatórios devem ser preenchidos.";
        header("Location: agendar_reposicao.php");
        exit();
    }
    
    // Chama o método do serviço para registrar a ausência e a reposição
    if ($ausenciaServico->registrarAusenciaEReposicao($dados_reposicao)) {
        $_SESSION['reposicao_success'] = "Reposição agendada com sucesso!";
        header("Location: agendar_reposicao.php");
        exit();
    } else {
        // O AusenciaServico já define a mensagem de erro na sessão.
        header("Location: agendar_reposicao.php");
        exit();
    }
}

// Prepara os dados para popular os dropdowns do formulário
$professorServico = new ProfessorServico();
$professores = $professorServico->listarProfessores();

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
    <title>Agendar Reposição</title>
    <style>
        body { margin: 0; font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f4f7f8; }
        .container { background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 20px rgba(0,0,0,0.1); width: 100%; max-width: 600px; box-sizing: border-box; }
        h1 { color: #386641; text-align: center; margin-bottom: 25px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="date"], input[type="time"], select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .submit-button { background-color: #2a9d8f; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 1em; width: 100%; transition: background-color 0.3s ease; }
        .submit-button:hover { background-color: #268074; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #386641; text-decoration: none; font-weight: bold; }
        .error-message, .success-message { text-align: center; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-weight: bold; }
        .error-message { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .success-message { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>

<div class="container">
    <h1>Agendar Reposição</h1>
    
    <?php
    if (isset($_SESSION['reposicao_success'])) {
        echo '<p class="success-message">' . $_SESSION['reposicao_success'] . '</p>';
        unset($_SESSION['reposicao_success']);
    }
    if (isset($_SESSION['reposicao_error'])) {
        echo '<p class="error-message">' . $_SESSION['reposicao_error'] . '</p>';
        unset($_SESSION['reposicao_error']);
    }
    ?>

    <form action="" method="POST">
        <input type="hidden" name="siape_ausente" value="<?php echo htmlspecialchars($siape_professor_logado); ?>">
        <input type="hidden" name="assinatura_ausente" value="<?php echo htmlspecialchars($nome_professor_logado); ?>">

        <div class="form-group">
            <label for="dia">Data da Reposição:</label>
            <input type="date" id="dia" name="dia" required>
        </div>
        
        <div class="form-group">
            <label for="horario">Horário da Reposição:</label>
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

        <div class="form-group">
            <label for="siape_substituto">Professor Substituto:</label>
            <select id="siape_substituto" name="siape_substituto" required>
                <option value="">Selecione o Professor</option>
                <?php foreach ($professores as $professor): 
                    // Evita que o professor se selecione como substituto
                    if ($professor['siape_prof'] !== $siape_professor_logado): ?>
                        <option value="<?php echo htmlspecialchars($professor['siape_prof']); ?>">
                            <?php echo htmlspecialchars($professor['nome']); ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>
        
        <input type="hidden" id="nome_substituto" name="nome_substituto">

        <button type="submit" class="submit-button">Agendar Reposição</button>
    </form>

    <a href="principal.php" class="back-link">← Voltar ao Dashboard</a>
</div>

<script>
    // Script para preencher o nome do professor substituto no campo oculto
    // Este campo é necessário para o método AusenciaServico::registrarAusenciaEReposicao
    document.getElementById('siape_substituto').addEventListener('change', function() {
        var nomeSelecionado = this.options[this.selectedIndex].text;
        document.getElementById('nome_substituto').value = nomeSelecionado;
    });
</script>

</body>
</html>