<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\cadastro.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
};

require_once __DIR__ . '/../../negocio/ProfessorServico.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $professorServico = new ProfessorServico(); 

    $siape = $_POST['siape_prof'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $senha = $_POST['senha'] ?? '';

    $professorServico->set("siape_prof", $siape);
    $professorServico->set("nome", $nome);
    $professorServico->set("senha", $senha);

    if (empty($siape) || empty($nome) || empty($senha)) {
        $_SESSION['cadastro_error'] = "Todos os campos são obrigatórios.";
        header("Location: cadastro.php");
        exit;
    }

    if ($professorServico->cadastrar()) {
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Professor - SIGA</title>
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
            border-radius: 20px;
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

        .right-side {
            padding: 50px;
            flex: 1.2;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-title {
            color: var(--primary-color);
            font-size: 2.2em;
            margin-bottom: 30px;
            font-weight: 600;
            text-align: center;
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
            border-radius: 25px;
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

        .submit-button {
            background: linear-gradient(90deg, var(--secondary-color), var(--primary-color));
            color: var(--white);
            padding: 14px 15px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: 600;
            width: 100%;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 10px;
        }

        .submit-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .login-link {
            margin-top: 25px;
            text-align: center;
            font-size: 0.95em;
        }

        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .message-box {
            text-align: center;
            margin-bottom: 20px;
            font-weight: 500;
            padding: 12px;
            border-radius: 25px;
            border: 1px solid transparent;
        }
        .error-message {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
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
            <p>Faça seu cadastro para começar a gerenciar suas atividades acadêmicas.</p>
        </div>

        <div class="right-side">
            <h2 class="form-title">Cadastro de Professor</h2>

            <?php
            if (isset($_SESSION['cadastro_error'])) {
                echo '<p class="message-box error-message">' . htmlspecialchars($_SESSION['cadastro_error']) . '</p>';
                unset($_SESSION['cadastro_error']);
            }
            ?>

            <form action="cadastro.php" method="POST">
                <div class="form-group">
                    <label for="siape_prof">SIAPE</label>
                    <input type="text" id="siape_prof" name="siape_prof" required>
                </div>

                <div class="form-group">
                    <label for="nome">Nome Completo</label>
                    <input type="text" id="nome" name="nome" required>
                </div>

                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" required>
                </div>

                <button type="submit" class="submit-button" name="enviar">Cadastrar</button>
            </form>
            
            <div class="login-link">
                Já possui uma conta? <a href="login.php">Faça login</a>
            </div>
        </div>
    </div>
</body>
</html>