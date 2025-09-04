<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\login.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
};

require_once __DIR__ . '/../../negocio/ProfessorServico.php';
require_once __DIR__ . '/../../negocio/AdministradorServico.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $siape = $_POST['siape'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (empty($siape) || empty($senha)) {
        $_SESSION['login_error'] = "SIAPE e senha são obrigatórios.";
        header("Location: login.php");
        exit;
    }

    $adminServico = new AdministradorServico();
    $adminAuth = $adminServico->autenticar($siape, $senha);
    
    if ($adminAuth) {
        $_SESSION['usuario_logado'] = $siape;
        $_SESSION['nome_usuario_logado'] = $adminAuth['nome'];
        $_SESSION['tipo_usuario'] = 'admin';
        $_SESSION['cargo_usuario_logado'] = $adminAuth['cargo'];
        $_SESSION['login_success'] = "Login como administrador realizado com sucesso!";
        header("Location: principal_adm.php");
        exit;
    }

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
    <title>Login - SIGA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #386641;
            --secondary-color: #6A994E;
            --accent-color: #A7C957;
            --background-light: #F2E8CF;
            --text-color: #333;
            --white: #FFFFFF;
            --shadow-color: rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background-color: var(--background-light);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: var(--text-color);
        }

        .container {
            background-color: var(--white);
            border-radius: 20px; /* Aumentado */
            box-shadow: 0 10px 30px var(--shadow-color);
            display: flex;
            width: 90%;
            max-width: 1000px;
            overflow: hidden;
        }

        .left-side {
            background-color: var(--primary-color);
            color: var(--white);
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            flex: 1;
            text-align: center;
        }

        .logo img {
            max-width: 120px;
            margin-bottom: 20px;
        }

        .left-side h1 {
            font-size: 2em;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .left-side p {
            font-size: 1em;
            line-height: 1.6;
            opacity: 0.9;
        }
        
        .tech-logo img {
            max-width: 150px;
            margin-top: 30px;
            opacity: 0.8;
        }

        .right-side {
            padding: 50px;
            flex: 1.2;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-title {
            color: var(--primary-color);
            font-size: 2.2em;
            margin-bottom: 10px;
            font-weight: 600;
            text-align: center;
        }

        .login-subtitle {
            text-align: center;
            color: #777;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 0.9em;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ccc;
            border-radius: 25px; /* Arredondado */
            font-size: 1em;
            box-sizing: border-box;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(106, 153, 78, 0.2);
        }

        .login-button {
            background: linear-gradient(90deg, var(--secondary-color), var(--primary-color));
            color: var(--white);
            padding: 14px 15px;
            border: none;
            border-radius: 25px; /* Arredondado */
            cursor: pointer;
            font-size: 1.1em;
            font-weight: 600;
            width: 100%;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 10px;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }
        
        .signup-link {
            margin-top: 25px;
            text-align: center;
            font-size: 0.95em;
        }

        .signup-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }
        
        .message-box {
            text-align: center;
            margin-bottom: 20px;
            font-weight: 500;
            padding: 12px;
            border-radius: 25px; /* Arredondado */
            border: 1px solid transparent;
        }
        .error-message {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .success-message {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-side">
            <div class="logo">
                <img src="logos ifgoiano.png" alt="Instituto Federal Goiano">
            </div>
            <h1>Bem-vindo ao SIGA</h1>
            <p>Um software desenvolvido para otimizar a gestão e a comunicação na sua instituição.</p>
             <div class="tech-logo">
                <img src="logoAGAETECH.png-removebg-preview.png" alt="AGAE Tech">
            </div>
        </div>
        <div class="right-side">
            <h2 class="login-title">Acessar Sistema</h2>
            <p class="login-subtitle">Entre com suas credenciais</p>

            <?php
            if (isset($_SESSION['cadastro_success'])) {
                echo '<div class="message-box success-message">' . htmlspecialchars($_SESSION['cadastro_success']) . '</div>';
                unset($_SESSION['cadastro_success']);
            }
            if (isset($_SESSION['login_error'])) {
                echo '<div class="message-box error-message">' . htmlspecialchars($_SESSION['login_error']) . '</div>';
                unset($_SESSION['login_error']);
            }
            ?>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="siape">SIAPE</label>
                    <input type="text" id="siape" name="siape" required>
                </div>
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" required>
                </div>
                <button type="submit" class="login-button">Entrar</button>
            </form>

            <div class="signup-link">
                Não tem uma conta? <a href="cadastro.php">Cadastre-se</a>
            </div>
        </div>
    </div>
</body>
</html>