<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\professores.php

// ATENÇÃO CRÍTICA: DEVE SER A PRIMEIRA COISA NO ARQUIVO, SEM ESPAÇOS OU LINHAS ACIMA.
// Isso garante que não haja nenhum caractere antes de iniciar a sessão, o que pode causar erros.
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Inicia a sessão PHP se ainda não estiver iniciada.
}

// Inclui o serviço de Professor para listar os professores.
// O caminho '__DIR__ . '/../../negocio/ProfessorServico.php'' está correto
// assumindo que 'professores.php' está em 'telas/auth/' e 'ProfessorServico.php' em 'negocio/'.
require_once __DIR__ . '/../../negocio/ProfessorServico.php';

// --- PROTEÇÃO DE ROTA ---
// Verifica se o usuário está logado E se o tipo de usuário é 'admin'.
// Apenas administradores têm permissão para acessar esta página.
if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'admin') {
    $_SESSION['login_error'] = "Acesso negado. Faça login como administrador para ver os professores.";
    header("Location: login.php"); // Redireciona para a página de login.
    exit(); // Encerra o script para prevenir qualquer processamento adicional.
}

$professores = []; // Inicializa um array vazio para armazenar os professores.
$mensagem = ""; // Para mensagens de feedback.

try {
    $professorServico = new ProfessorServico();
    // Tenta listar todos os professores.
    $professores = $professorServico->listarProfessores();

    if (empty($professores)) {
        $mensagem = "Nenhum professor cadastrado no sistema.";
    }
} catch (Exception $e) {
    // Captura qualquer exceção que possa ocorrer durante a execução.
    $mensagem = "Erro ao carregar os professores: " . $e->getMessage();
    error_log("Erro em professores.php: " . $e->getMessage()); // Registra o erro no log do servidor.
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Gerenciar Professores</title>
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
        .add-professor-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #386641;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .add-professor-button:hover {
            background-color: #4d774e;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Gerenciar Professores</h1>

    <?php if (!empty($mensagem)): ?>
        <p class="message <?php echo strpos($mensagem, 'Erro') !== false ? 'error-message' : 'success-message'; ?>">
            <?php echo htmlspecialchars($mensagem); ?>
        </p>
    <?php endif; ?>

    <?php if (!empty($professores)): ?>
        <table>
            <thead>
                <tr>
                    <th>SIAPE</th>
                    <th>Nome</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($professores as $professor): ?>
                <tr>
                    <td><?php echo htmlspecialchars($professor['siape_prof']); ?></td>
                    <td><?php echo htmlspecialchars($professor['nome']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Nenhum professor encontrado.</p>
    <?php endif; ?>

    <a href="cadastro.php" class="add-professor-button">Cadastrar Novo Professor</a>
    <a href="principal_adm.php" class="back-link">← Voltar ao Dashboard Admin</a>
</div>

</body>
</html>
