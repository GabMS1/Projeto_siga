<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\minhas_reposicoes.php

// ATENÇÃO CRÍTICA: DEVE SER A PRIMEIRA COISA NO ARQUIVO.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclui o serviço de Ausência para listar as reposições.
require_once __DIR__ . '/../../negocio/AusenciaServico.php';

// --- PROTEÇÃO DE ROTA ---
// Verifica se o usuário está logado E se o tipo de usuário é 'professor'.
if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'professor') {
    $_SESSION['login_error'] = "Acesso negado. Faça login como professor.";
    header("Location: login.php");
    exit();
}

// Pega o SIAPE do professor logado da sessão.
$siape_professor_logado = $_SESSION['usuario_logado'];

$reposicoes = []; // Inicializa um array vazio para armazenar as reposições.
$mensagem = ""; // Para mensagens de feedback

try {
    $ausenciaServico = new AusenciaServico();
    // Tenta listar as reposições do professor logado.
    $reposicoes = $ausenciaServico->listarReposicoesPorProfessor((int)$siape_professor_logado);

    if (empty($reposicoes)) {
        $mensagem = "Nenhuma reposição agendada para você.";
    }
} catch (Exception $e) {
    // Captura qualquer exceção que possa ocorrer.
    $mensagem = "Erro ao carregar as reposições: " . $e->getMessage();
    error_log("Erro em minhas_reposicoes.php: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Minhas Reposições</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background-color: #f4f7f8;
            display: flex;
            justify-content: center;
            align-items: flex-start;
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
            max-width: 900px;
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
        .add-reposicao-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #386641;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .add-reposicao-button:hover {
            background-color: #4d774e;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Minhas Reposições</h1>

    <?php if (!empty($mensagem)): ?>
        <p class="message <?php echo strpos($mensagem, 'Erro') !== false ? 'error-message' : 'success-message'; ?>">
            <?php echo htmlspecialchars($mensagem); ?>
        </p>
    <?php endif; ?>

    <?php if (!empty($reposicoes)): ?>
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Horário</th>
                    <th>Turma</th>
                    <th>Disciplina</th>
                    <th>Professor Ausente</th>
                    <th>Professor Substituto</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reposicoes as $reposicao): ?>
                <tr>
                    <td><?php echo htmlspecialchars($reposicao['dia']); ?></td>
                    <td><?php echo htmlspecialchars(substr($reposicao['horario'], 0, 5)); ?></td>
                    <td><?php echo htmlspecialchars($reposicao['curso'] . " - " . $reposicao['serie']); ?></td>
                    <td><?php echo htmlspecialchars($reposicao['nome_disciplina']); ?></td>
                    <td><?php echo htmlspecialchars($reposicao['siape_ausente']); ?></td>
                    <td><?php echo htmlspecialchars($reposicao['siape_substituto']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Nenhuma reposição encontrada. Use o botão abaixo para agendar uma nova.</p>
    <?php endif; ?>

    <a href="agendar_reposicao.php" class="add-reposicao-button">Agendar Nova Reposição</a>
    <a href="principal.php" class="back-link">← Voltar ao Dashboard</a>
</div>

</body>
</html>