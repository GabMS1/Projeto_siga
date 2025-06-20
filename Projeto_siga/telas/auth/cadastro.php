﻿<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\cadastro.php

// ATENÇÃO CRÍTICA: DEVE SER A PRIMEIRA COISA NO ARQUIVO, SEM ESPAÇOS OU LINHAS ACIMA.
// Isso evita o erro "headers already sent" ao tentar redirecionar.
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Inicia a sessão PHP se ainda não estiver iniciada.
};
// Linhas de depuração (úteis durante o desenvolvimento, comente ou remova em produção)
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// Inclui a classe de serviço para professores.
require_once __DIR__ . '/../../negocio/ProfessorServico.php';

// Verifica se o método da requisição HTTP é POST (ou seja, o formulário foi submetido).
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Cria uma nova instância da classe ProfessorServico para lidar com a lógica de cadastro.
    $professorServico = new ProfessorServico(); 

    // Obtém os dados enviados pelo formulário. O operador '??' define um valor padrão de string vazia
    // se a variável POST não estiver definida, evitando avisos.
    $siape = $_POST['siape_prof'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $senha = $_POST['senha'] ?? '';

    // Define as propriedades no objeto ProfessorServico com os dados do formulário.
    $professorServico->set("siape_prof", $siape);
    $professorServico->set("nome", $nome);
    $professorServico->set("senha", $senha); // A senha será hashed dentro do método cadastrar do ProfessorServico.

    // Validação básica: verifica se algum dos campos obrigatórios está vazio.
    if (empty($siape) || empty($nome) || empty($senha)) {
        $_SESSION['cadastro_error'] = "Todos os campos são obrigatórios."; // Armazena a mensagem de erro na sessão.
        header("Location: cadastro.php"); // Redireciona o usuário de volta com a mensagem de erro.
        exit; // Termina a execução do script para evitar envio de HTML indesejado.
    }

    // Tenta cadastrar o professor chamando o método 'cadastrar' do ProfessorServico.
    if ($professorServico->cadastrar()) {
        $_SESSION['cadastro_success'] = "Professor cadastrado com sucesso! Faça seu login."; // Mensagem de sucesso.
        header("Location: login.php"); // Redireciona para a página de login após o sucesso.
        exit;
    } else {
        // Se o cadastro falhar e nenhuma mensagem de erro específica foi definida na sessão pelo DAO,
        // define uma mensagem genérica.
        if (!isset($_SESSION['cadastro_error'])) {
            $_SESSION['cadastro_error'] = "Erro ao cadastrar professor. Tente novamente.";
        }
        header("Location: cadastro.php"); // Redireciona de volta com a mensagem de erro.
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
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

        /* --- Alterações aqui para centralizar e melhorar o visual da left-side --- */
        .left-side {
            background-color: #386641;
            color: #f0f7f4;
            padding: 40px;
            display: flex;
            flex-direction: column; /* Organiza os itens em coluna */
            justify-content: center; /* Centraliza verticalmente */
            align-items: center; /* Centraliza horizontalmente */
            flex: 1;
            text-align: center; /* Garante que o texto também seja centralizado */
        }

        .left-side .logo {
            margin-bottom: 20px;
            display: flex; /* Adicionado para centralizar a imagem dentro do logo */
            justify-content: center;
            align-items: center;
            width: 100%; /* Ocupa a largura total para ajudar no centramento */
        }

        .left-side .logo img {
            max-width: 200px; /* Mantido o tamanho maior para a logo principal */
            height: auto;
            display: block; /* Remove o espaço extra que inline-block pode criar */
        }

        .left-side img:not(.logo img) { /* Afeta a logoAGAETECH.png-removebg-preview.png */
            max-width: 120px; /* Tamanho ajustado para a logoAGAETECH */
            height: auto;
            margin-top: 15px;
            display: block; /* Garante que a imagem se comporte como bloco para centralização */
            margin-left: auto; /* Centraliza a imagem */
            margin-right: auto; /* Centraliza a imagem */
        }
        /* --- Fim das alterações na left-side --- */

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
            margin-bottom: 10px; /* Espaçamento abaixo do botão "Cadastrar" */
        }

        .login-button:hover {
            background-color: #268074;
        }

        /* --- Alterações aqui para o botão "Cadastrar administrador" --- */
        .admin-button {
            background-color: #2a9d8f; /* Mesma cor do login-button */
            color: #fff;
            padding: 12px 15px; /* Mesma padding do login-button */
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%; /* Mesma largura do login-button */
            margin-top: 5px; /* Ajuste a margem superior se necessário */
            transition: background-color 0.3s ease;
            display: block; /* Garante que o link se comporte como um botão de bloco */
            text-align: center; /* Centraliza o texto do link */
            text-decoration: none; /* Remove o sublinhado padrão do link */
        }

        .admin-button:hover {
            background-color: #268074; /* Mesma cor de hover do login-button */
        }
        /* --- Fim das alterações do botão admin --- */

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
            <img src="logoAGAETECH.png-removebg-preview.png" alt="Ilustração" >
            <h2>Transformando a Gestão Acadêmica com Tecnologia.</h2>
        </div>

        <div class="right-side">
            <h2 class="login-title">Cadastro de Professor</h2>

            <?php
            // Exibe mensagens de erro de cadastro, se houver.
            if (isset($_SESSION['cadastro_error'])) {
                echo '<p class="error-message">' . $_SESSION['cadastro_error'] . '</p>';
                unset($_SESSION['cadastro_error']); // Limpa a mensagem após exibir.
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

                <!-- Botão "Cadastrar administrador" adicionado para navegação -->
                <a href="cadastro_adm.php" class="admin-button">
                    Cadastrar administrador
                </a>

                <div style="margin-top: 15px; text-align: center;">
                    <a href="login.php" style="display: inline-block; background-color: #6a994e; color: #fff; padding: 10px 15px; border-radius: 4px; text-decoration: none;">Ir para Login</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
