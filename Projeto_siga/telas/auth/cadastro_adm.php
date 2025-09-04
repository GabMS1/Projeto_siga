<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\cadastro_adm.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
};

require_once __DIR__ . '/../../negocio/AdministradorServico.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $siape = $_POST['siape'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $cargo = $_POST['cargo'] ?? '';

    try {
        if (empty($siape) || empty($senha) || empty($confirmar_senha) || empty($nome) || empty($cargo)) {
            throw new Exception("Todos os campos são obrigatórios.");
        }
        if (!is_numeric($siape)) {
            throw new Exception("O SIAPE deve conter apenas números.");
        }
        if ($senha !== $confirmar_senha) {
            throw new Exception("As senhas não coincidem.");
        }
        if (strlen($senha) < 8) {
            throw new Exception("A senha deve ter no mínimo 8 caracteres.");
        }

        $administradorServico = new AdministradorServico();
        $resultado = $administradorServico->criarAdministrador($siape, $senha, $nome, $cargo);

        if ($resultado) {
            $_SESSION['cadastro_success'] = "Administrador cadastrado com sucesso!";
            header("Location: login.php");
            exit;
        } else {
             if (!isset($_SESSION['cadastro_error'])) {
                 throw new Exception("Erro ao cadastrar administrador. Tente novamente.");
             }
        }
    } catch (Exception $e) {
        $_SESSION['cadastro_error'] = $e->getMessage();
        header("Location: cadastro_adm.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Administrador - SIGA</title>
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
            margin-bottom: 15px;
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
        input[type="password"],
        select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ccc;
            border-radius: 25px;
            font-size: 1em;
            box-sizing: border-box;
            transition: border-color 0.3s, box-shadow 0.3s;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-color: #fff;
        }
        
        select {
            background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23007CB2%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22/%3E%3C/svg%3E');
            background-repeat: no-repeat;
            background-position: right 15px top 50%;
            background-size: .65em auto;
            padding-right: 30px;
        }

        input:focus,
        select:focus {
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
                <img src="logos ifgoiano.png" alt="IF Goiano">
            </div>
            <h1>Acesso Administrativo</h1>
            <p>Faça seu cadastro para gerenciar o sistema acadêmico.</p>
        </div>
        
        <div class="right-side">
            <h2 class="form-title">Cadastro de Administrador</h2>
            
            <?php 
            if (isset($_SESSION['cadastro_error'])): ?>
                <div class="message-box error-message"><?= htmlspecialchars($_SESSION['cadastro_error']) ?></div>
                <?php unset($_SESSION['cadastro_error']); ?>
            <?php endif; ?>

            <form action="cadastro_adm.php" method="POST">
                <div class="form-group">
                    <label for="siape">SIAPE</label>
                    <input type="text" id="siape" name="siape" required>
                </div>
                
                <div class="form-group">
                    <label for="nome">Nome Completo</label>
                    <input type="text" id="nome" name="nome" required>
                </div>
                
                <div class="form-group">
                    <label for="cargo">Cargo</label>
                    <select id="cargo" name="cargo" required>
                        <option value="">Selecione...</option>
                        <option value="Coordenador">Coordenador</option>
                        <option value="Diretor">Diretor</option>
                        <option value="Secretário">Secretário</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="senha">Senha (mínimo 8 caracteres)</label>
                    <input type="password" id="senha" name="senha" minlength="8" required>
                </div>
                
                <div class="form-group">
                    <label for="confirmar_senha">Confirmar Senha</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" minlength="8" required>
                </div>
                
                <button type="submit" class="submit-button">Cadastrar</button>
            </form>
            
            <div class="login-link">
                <a href="login.php">Já tem conta? Faça login</a>
            </div>
        </div>
    </div>
</body>
</html>