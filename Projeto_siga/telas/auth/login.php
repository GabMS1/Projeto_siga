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
// Isso permite que a lógica de autenticação de ambos os tipos de usuário seja acessível.
require_once __DIR__ . '/../../negocio/ProfessorServico.php';
require_once __DIR__ . '/../../negocio/AdministradorServico.php';

// Verifica se a requisição HTTP é um POST (ou seja, o formulário de login foi submetido).
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $siape = $_POST['siape'] ?? ''; // Obtém o SIAPE digitado no formulário.
    $senha = $_POST['senha'] ?? ''; // Obtém a senha digitada no formulário.

    // Validação básica: verifica se SIAPE e senha foram preenchidos.
    if (empty($siape) || empty($senha)) {
        $_SESSION['login_error'] = "SIAPE e senha são obrigatórios."; // Mensagem de erro.
        header("Location: login.php"); // Redireciona de volta para a página de login.
        exit; // Encerra o script.
    }

    // --- Tenta autenticar como ADMINISTRADOR primeiro ---
    $adminServico = new AdministradorServico(); // Cria uma instância do serviço de Administrador.
    $adminAuth = $adminServico->autenticar($siape, $senha); // Tenta autenticar o usuário como admin.
    
    if ($adminAuth) {
        // Se a autenticação como administrador for bem-sucedida:
        $_SESSION['usuario_logado'] = $siape;                   // Armazena o SIAPE na sessão.
        $_SESSION['nome_usuario_logado'] = $adminAuth['nome'];  // Armazena o nome do admin na sessão.
        $_SESSION['tipo_usuario'] = 'admin';                    // Define o tipo de usuário na sessão. CRUCIAL para controle de acesso.
        $_SESSION['login_success'] = "Login como administrador realizado com sucesso!"; // Mensagem de sucesso.
        header("Location: principal_adm.php"); // Redireciona para o painel do administrador.
        exit; // Encerra o script.
    }

    // --- Se não for admin, tenta autenticar como PROFESSOR ---
    $professorServico = new ProfessorServico(); // Cria uma instância do serviço de Professor.
    $profAuth = $professorServico->autenticar($siape, $senha); // Tenta autenticar o usuário como professor.

    if ($profAuth) {
        // Se a autenticação como professor for bem-sucedida:
        $_SESSION['usuario_logado'] = $siape;                   // Armazena o SIAPE na sessão.
        $_SESSION['nome_usuario_logado'] = $profAuth['nome'];   // Armazena o nome do professor na sessão.
        $_SESSION['tipo_usuario'] = 'professor';                // Define o tipo de usuário na sessão. CRUCIAL para controle de acesso.
        $_SESSION['login_success'] = "Login realizado com sucesso!"; // Mensagem de sucesso.
        header("Location: principal.php"); // Redireciona para o painel do professor.
        exit; // Encerra o script.
    }

    // --- Se nenhuma autenticação for bem-sucedida ---
    $_SESSION['login_error'] = "SIAPE ou senha incorretos."; // Mensagem de erro genérica.
    header("Location: login.php"); // Redireciona de volta para a página de login.
    exit; // Encerra o script.
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SUAP IF Goiano</title>
    <style>
        /* Estilos CSS (mantidos conforme seu arquivo original) */
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

        .logo {
            margin-bottom: 20px;
        }

        .logo img {
            max-width: 150px;
            height: auto;
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

        .password-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            user-select: none;
            content: "👁️";
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

        .forgot-password {
            margin-top: 20px;
            text-align: center;
        }

        .forgot-password a {
            color: #555;
            text-decoration: none;
            font-size: 0.9em;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        .social-login {
            margin-top: 25px;
            text-align: center;
        }

        .social-login button {
            background-color: #fff;
            color: #555;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 10px 15px;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
        }

        .social-login button img {
            max-height: 24px;
            margin-right: 10px;
        }

        hr {
            border: 0;
            border-top: 1px solid #ccc;
            margin: 20px 0;
        }

        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .success-message {
            color: green;
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
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
            <p style="text-align: center; color: #555; margin-bottom: 20px;">Acesse ao SUAP IFGOIANO:</p>

            <?php
            // Exibe mensagem de sucesso de cadastro (se houver vindo do cadastro.php ou cadastro_adm.php).
            if (isset($_SESSION['cadastro_success'])) {
                echo '<p class="success-message">' . $_SESSION['cadastro_success'] . '</p>';
                unset($_SESSION['cadastro_success']); // Limpa a mensagem após exibir.
            }
            // Exibe mensagem de erro de login (se houver).
            if (isset($_SESSION['login_error'])) {
                echo '<p class="error-message">' . $_SESSION['login_error'] . '</p>';
                unset($_SESSION['login_error']); // Limpa a mensagem após exibir.
            }
            ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="siape">SIAPE:</label>
                    <input type="text" id="siape_prof" name="siape" required>
                </div>
                <div class="form-group password-container">
                    <label for="senha">Senha:</label>
                    <input type="password" id="senha" name="senha" required>
                    <span class="password-toggle" onclick="togglePasswordVisibility()">👁️</span>
                </div>
                <button type="submit" class="login-button">Acessar</button>
            </form>

            <div class="forgot-password">
                <a href="#">Esqueceu ou deseja alterar sua senha?</a>
            </div>
            <hr>
            <div class="social-login">
                <button>
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/53/Google_%22G%22_Logo.svg/2048px-Google_%22G%22_Logo.svg.png" alt="Google Logo">
                    Entrar com g<span style="color: #4285F4;">o</span><span style="color: #EA4335;">o</span><span style="color: #FBBC05;">g</span><span style="color: #4285F4;">l</span><span style="color: #34A853;">e</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        // Função JavaScript para alternar a visibilidade da senha.
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById("senha");
            const toggleIcon = document.querySelector(".password-toggle");
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                toggleIcon.textContent = "👁️‍🗨️"; // Altera o ícone para "olho aberto com risco"
            } else {
                passwordInput.type = "password";
                toggleIcon.textContent = "👁️"; // Altera o ícone para "olho fechado"
            }
        }
    </script>
</body>
</html>
