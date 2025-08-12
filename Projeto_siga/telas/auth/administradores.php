<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\administradores.php

// ATENÇÃO CRÍTICA: DEVE SER A PRIMEIRA COISA NO ARQUIVO, SEM ESPAÇOS OU LINHAS ACIMA.
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Inicia a sessão PHP se ainda não estiver iniciada.
}

// Inclui o serviço de Administrador para listar os administradores.
require_once __DIR__ . '/../../negocio/AdministradorServico.php';

// --- PROTEÇÃO DE ROTA ---
// Verifica se o usuário está logado E se o tipo de usuário é 'admin'.
// Apenas administradores têm permissão para acessar esta página.
if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'admin') {
    $_SESSION['login_error'] = "Acesso negado. Faça login como administrador para ver os administradores.";
    header("Location: login.php"); // Redireciona para a página de login.
    exit(); // Encerra o script.
}

$administradores = []; // Inicializa um array vazio para armazenar os administradores.
$mensagem = ""; // Para mensagens de feedback.

try {
    $administradorServico = new AdministradorServico();
    // Tenta listar todos os administradores.
    $administradores = $administradorServico->listarAdministradores();

    if (empty($administradores)) {
        $mensagem = "Nenhum administrador cadastrado no sistema.";
    }
} catch (Exception $e) {
    // Captura qualquer exceção que possa ocorrer.
    $mensagem = "Erro ao carregar os administradores: " . $e->getMessage();
    error_log("Erro em administradores.php: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Gerenciar Administradores</title>
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
        .add-admin-button { /* Novo estilo para o botão "Cadastrar Novo Administrador" */
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #386641;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .add-admin-button:hover {
            background-color: #4d774e;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Gerenciar Administradores</h1>

    <?php if (!empty($mensagem)): ?>
        <p class="message <?php echo strpos($mensagem, 'Erro') !== false ? 'error-message' : 'success-message'; ?>">
            <?php echo htmlspecialchars($mensagem); ?>
        </p>
    <?php endif; ?>

    <?php if (!empty($administradores)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>SIAPE</th>
                    <th>Nome</th>
                    <th>Cargo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($administradores as $admin): ?>
                <tr>
                    <td><?php echo htmlspecialchars($admin['id_adm']); ?></td>
                    <td><?php echo htmlspecialchars($admin['siape_adm']); ?></td>
                    <td><?php echo htmlspecialchars($admin['nome']); ?></td>
                    <td><?php echo htmlspecialchars($admin['cargo']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Nenhum administrador encontrado.</p>
    <?php endif; ?>

    <a href="cadastro_adm.php" class="add-admin-button">Cadastrar Novo Administrador</a>
    <a href="principal_adm.php" class="back-link">← Voltar ao Dashboard Admin</a>
</div>

</body>
</html>
