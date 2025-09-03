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
    <style>
        body {
            font-family: 'Poppins', sans-serif;
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
            align-items: center;
            flex: 1;
            text-align: center;
        }

        .left-side .logo img {
            max-width: 150px;
            height: auto;
            margin-bottom: 20px;
        }

        .left-side h1 {
            font-size: 2em;
            margin-bottom: 15px;
        }

        .right-side {
            padding: 40px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-title {
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
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }

        .submit-button {
            background-color: #2a9d8f;
            color: #fff;
            padding: 12px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background-color 0.3s ease;
            margin-bottom: 15px;
        }

        .submit-button:hover {
            background-color: #268074;
        }
        
        .login-link {
            text-align: center;
            font-size: 0.9em;
        }

        .login-link a {
            color: #2a9d8f;
            text-decoration: none;
            font-weight: bold;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .error-message {
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
            padding: 10px;
            border-radius: 4px;
            color: #721c24;
            background-color: #f8d7da;
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
                echo '<p class="error-message">' . htmlspecialchars($_SESSION['cadastro_error']) . '</p>';
                unset($_SESSION['cadastro_error']);
            }
            ?>

            <form action="cadastro.php" method="POST">
                <div class="form-group">
                    <label for="siape_prof">SIAPE:</label>
                    <input type="text" id="siape_prof" name="siape_prof" required>
                </div>

                <div class="form-group">
                    <label for="nome">Nome Completo:</label>
                    <input type="text" id="nome" name="nome" required>
                </div>

                <div class="form-group">
                    <label for="senha">Senha:</label>
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