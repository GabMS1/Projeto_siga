<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\cadastro.php

// ATENÇÃO CRÍTICA: SEM ESPAÇOS OU LINHAS ACIMA.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
};
// Remova as linhas de ini_set daqui se não precisar mais depurar cadastro
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require_once __DIR__ . '/../../negocio/ProfessorServico.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $professor = new Professor();
    $siape = $_POST['siape_prof'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $senha = $_POST['senha'] ?? '';

    $professor->set("siape_prof", $siape);
    $professor->set("nome", $nome);
    $professor->set("senha", $senha);

    if (empty($siape) || empty($nome) || empty($senha)) {
        $_SESSION['cadastro_error'] = "Todos os campos são obrigatórios.";
        header("Location: cadastro.php");
        exit;
    }

    if ($professor->cadastrar()) {
        $_SESSION['cadastro_success'] = "Professor cadastrado com sucesso! Faça seu login.";
        header("Location: login.php");
        exit;
    } else {
        if (!isset($_SESSION['cadastro_error'])) {
            $_SESSION['cadastro_error'] = "Erro ao cadastrar professor. Tente novamente.";
        }
        header("Location: cadastro.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro</title>
    <style>
        /* ... seu CSS ... */
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

        /* Estilos para mensagens de feedback */
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
            <h1>Menos Burocracia, Mais Educação!</h1>
            <img src="logoAGAETECH.png-removebg-preview.png" alt="Ilustração" style="max-width: 40%; height: auto; margin-top: 15px;">
            <h2>Transformando a Gestão Acadêmica com Tecnologia.</h2>
        </div>

        <div class="right-side">
            <h2 class="login-title">Cadastro de Professor</h2>

            <?php
            if (isset($_SESSION['cadastro_error'])) {
                echo '<p class="error-message">' . $_SESSION['cadastro_error'] . '</p>';
                unset($_SESSION['cadastro_error']);
            }
            ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="siape_prof">SIAPE:</label>
                    <input type="text" id="siape_prof" name="siape_prof" required>
                </div>

                <div class="form-group">
                    <label for="nome">Nome:</label>
                    <input type="text" id="nome" name="nome" required>
                </div>

                <div class="form-group password-container">
                    <label for="senha">Senha:</label>
                    <input type="password" id="senha" name="senha" required>
                </div>

                <button type="submit" class="login-button" name="enviar">Cadastrar</button>

                <div style="margin-top: 15px; text-align: center;">
                    <a href="login.php" style="display: inline-block; background-color: #6a994e; color: #fff; padding: 10px 15px; border-radius: 4px; text-decoration: none;">Ir para Login</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>