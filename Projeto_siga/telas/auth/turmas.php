<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\turmas.php

// ATENÇÃO CRÍTICA: DEVE SER A PRIMEIRA COISA NO ARQUIVO, SEM ESPAÇOS OU LINHAS ACIMA.
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Inicia a sessão PHP se ainda não estiver iniciada.
}

// Inclui o serviço de Turma para listar as turmas.
require_once __DIR__ . '/../../negocio/TurmaServico.php';

// --- PROTEÇÃO DE ROTA ---
// Verifica se o usuário está logado E se o tipo de usuário é 'professor'.
if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'professor') {
    $_SESSION['login_error'] = "Acesso negado. Faça login como professor para ver suas turmas.";
    header("Location: login.php"); // Redireciona para a página de login.
    exit(); // Encerra o script.
}

// Pega o SIAPE do professor logado da sessão para buscar suas turmas.
$siape_professor_logado = $_SESSION['usuario_logado'];

$turmas = []; // Inicializa um array vazio para armazenar as turmas.
$mensagem = ""; // Para mensagens de feedback

try {
    $turmaServico = new TurmaServico();
    // Tenta listar as turmas do professor logado.
    $turmas = $turmaServico->buscarTurmasPorProfessor((int)$siape_professor_logado);

    if (empty($turmas)) {
        $mensagem = "Nenhuma turma cadastrada para este professor.";
    }
} catch (Exception $e) {
    // Captura qualquer exceção que possa ocorrer.
    $mensagem = "Erro ao carregar as turmas: " . $e->getMessage();
    error_log("Erro em turmas.php: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Minhas Turmas</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background-color: #f4f7f8;
            display: flex;
            justify-content: center;
            align-items: flex-start; /* Alinha ao topo para permitir scroll */
            min-height: 100vh;
            padding: 20px;
            box-sizing: border-box;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
            box-sizing: border-box;
            text-align: center;
        }
        h1 {
            color: #386641;
            margin-bottom: 25px;
            font-size: 2.2em;
        }
        .message {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 4px;
            font-weight: bold;
        }
        .error-message {
            color: red;
            background-color: #ffe0e0;
            border: 1px solid red;
        }
        .success-message {
            color: green;
            background-color: #e0ffe0;
            border: 1px solid green;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #386641;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .back-link {
            display: inline-block;
            margin-top: 30px;
            padding: 10px 20px;
            background-color: #2a9d8f;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .back-link:hover {
            background-color: #268074;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Minhas Turmas</h1>

    <?php if (!empty($mensagem)): ?>
        <p class="message <?php echo strpos($mensagem, 'Erro') !== false ? 'error-message' : 'success-message'; ?>">
            <?php echo htmlspecialchars($mensagem); ?>
        </p>
    <?php endif; ?>

    <?php if (!empty($turmas)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID da Turma</th>
                    <th>Curso</th>
                    <th>Série</th>
                    <th>Disciplina</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($turmas as $turma): ?>
                <tr>
                    <td><?php echo htmlspecialchars($turma['id_turma']); ?></td>
                    <td><?php echo htmlspecialchars($turma['curso']); ?></td>
                    <td><?php echo htmlspecialchars($turma['serie']); ?></td>
                    <td><?php echo htmlspecialchars($turma['nome_disciplina']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Nenhuma turma encontrada.</p>
    <?php endif; ?>

    <a href="principal.php" class="back-link">← Voltar ao Dashboard</a>
</div>

</body>
</html>