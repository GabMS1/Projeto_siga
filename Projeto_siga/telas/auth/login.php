<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\login.php

// ATENÇÃO CRÍTICA: DEVE SER A PRIMEIRA COISA NO ARQUIVO, SEM ESPAÇOS OU LINHAS ACIMA.
ini_set('display_errors', 1); // Ativa a exibição de erros (útil para depuração)
ini_set('display_startup_errors', 1); // Ativa a exibição de erros na inicialização
error_reporting(E_ALL); // Reporta todos os tipos de erros PHP

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Inicia a sessão PHP se ainda não estiver iniciada.
};

// Inclui as classes de serviço para Professores e Administradores.
require_once __DIR__ . '/../../negocio/ProfessorServico.php';
require_once __DIR__ . '/../../negocio/AdministradorServico.php';

// Verifica se a requisição HTTP é um POST (ou seja, o formulário de login foi submetido).
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $siape = $_POST['siape'] ?? ''; // Obtém o SIAPE digitado no formulário.
    $senha = $_POST['senha'] ?? ''; // Obtém a senha digitada no formulário.

    if (empty($siape) || empty($senha)) {
        $_SESSION['login_error'] = "SIAPE e senha são obrigatórios.";
        header("Location: login.php");
        exit;
    }

    // Tenta autenticar como ADMINISTRADOR primeiro
    $adminServico = new AdministradorServico();
    $adminAuth = $adminServico->autenticar($siape, $senha);
    
    if ($adminAuth) {
        $_SESSION['usuario_logado'] = $siape;
        $_SESSION['nome_usuario_logado'] = $adminAuth['nome'];
        $_SESSION['tipo_usuario'] = 'admin';
        $_SESSION['cargo_usuario_logado'] = $adminAuth['cargo']; // Armazena o cargo
        $_SESSION['login_success'] = "Login como administrador realizado com sucesso!";
        header("Location: principal_adm.php");
        exit;
    }

    // Se não for admin, tenta autenticar como PROFESSOR
    $professorServico = new ProfessorServico();
    $profAuth = $professorServico->autenticar($siape, $senha);

    if ($profAuth) {
        $_SESSION['usuario_logado'] = $siape;
        $_SESSION['nome_usuario_logado'] = $profAuth['nome'];
        $_SESSION['tipo_usuario'] = 'professor';
        $_SESSION['login_success'] = "Login realizado com sucesso!";
        header("Location: principal.php");
        exit;
    }

    // Se nenhuma autenticação for bem-sucedida
    $_SESSION['login_error'] = "SIAPE ou senha incorretos.";
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SUAP IF Goiano</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            background-color: #386641;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background-color: #f0f7f4;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            display: flex;
            width: 80%;
            max-width: 960px;
            overflow: hidden;
        }

        .left-side {
            background-color: #386641;
            color: #f0f7f4;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            flex: 1;
        }

        .logo img {
            max-width: 150px;
            height: auto;
            margin-bottom: 20px;
        }

        .left-side h1 {
            font-size: 2.5em;
            margin-bottom: 15px;
        }

        .left-side p {
            font-size: 1.1em;
            line-height: 1.6;
        }

        .right-side {
            padding: 40px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-title {
            color: #386641;
            font-size: 2em;
            margin-bottom: 25px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: bold;
        }

        input[type="text"],
        input[type="password"] {
            width: calc(100% - 22px);
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        
        .login-button {
            background-color: #2a9d8f;
            color: #fff;
            padding: 12px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        .login-button:hover {
            background-color: #268074;
        }

        /* --- NOVO ESTILO PARA O LINK DE CADASTRO --- */
        .signup-link {
            margin-top: 20px;
            text-align: center;
            font-size: 0.9em;
        }

        .signup-link a {
            color: #2a9d8f;
            text-decoration: none;
            font-weight: bold;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }
        /* --- FIM DO NOVO ESTILO --- */

        .error-message, .success-message {
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
            padding: 10px;
            border-radius: 4px;
        }
        .error-message {
            color: #721c24;
            background-color: #f8d7da;
        }
        .success-message {
            color: #155724;
            background-color: #d4edda;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-side">
            <div class="logo">
                <img src="logos ifgoiano.png" alt="Instituto Federal Goiano">
            </div>
            <h1>Um software desenvolvido para instituições.</h1>
            <img src="logoAGAETECH.png-removebg-preview.png" alt="Ilustração" style="max-width: 40%; height: auto; margin-top: 15px;">
        </div>
        <div class="right-side">
            <h2 class="login-title">Login</h2>
            <p style="text-align: center; color: #555; margin-bottom: 20px;">Acesse o SIGA:</p>

            <?php
            if (isset($_SESSION['cadastro_success'])) {
                echo '<p class="success-message">' . htmlspecialchars($_SESSION['cadastro_success']) . '</p>';
                unset($_SESSION['cadastro_success']);
            }
            if (isset($_SESSION['login_error'])) {
                echo '<p class="error-message">' . htmlspecialchars($_SESSION['login_error']) . '</p>';
                unset($_SESSION['login_error']);
            }
            ?>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="siape">SIAPE:</label>
                    <input type="text" id="siape" name="siape" required>
                </div>
                <div class="form-group">
                    <label for="senha">Senha:</label>
                    <input type="password" id="senha" name="senha" required>
                </div>
                <button type="submit" class="login-button">Acessar</button>
            </form>

            <div class="signup-link">
                Não tem uma conta? <a href="cadastro.php">Cadastre-se</a>
            </div>
            </div>
    </div>
</body>
</html>