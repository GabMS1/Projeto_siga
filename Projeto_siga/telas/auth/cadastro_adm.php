<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\cadastro_adm.php

// ATENÇÃO CRÍTICA: DEVE SER A PRIMEIRA COISA NO ARQUIVO, SEM ESPAÇOS OU LINHAS ACIMA.
ini_set('display_errors', 1); // Ativa a exibição de erros (bom para desenvolvimento)
ini_set('display_startup_errors', 1); // Ativa a exibição de erros durante a inicialização
error_reporting(E_ALL); // Reporta todos os tipos de erros

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Inicia a sessão PHP se ainda não estiver iniciada.
};

// Inclui a classe de serviço para Administradores.
require_once __DIR__ . '/../../negocio/AdministradorServico.php';

// Verifica se o método da requisição HTTP é POST (o formulário foi submetido).
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtém os dados enviados pelo formulário, usando o operador null coalescing (??) para evitar avisos.
    $siape = $_POST['siape'] ?? '';              // SIAPE do administrador.
    $senha = $_POST['senha'] ?? '';              // Senha em texto puro.
    $confirmar_senha = $_POST['confirmar_senha'] ?? ''; // Confirmação da senha.
    $nome = $_POST['nome'] ?? '';                // Nome completo do administrador.
    $cargo = $_POST['cargo'] ?? '';              // Cargo do administrador.

    try {
        // --- Validações de Entrada ---

        // 1. Verifica se todos os campos obrigatórios estão preenchidos.
        if (empty($siape) || empty($senha) || empty($confirmar_senha) || empty($nome) || empty($cargo)) {
            throw new Exception("Todos os campos são obrigatórios.");
        }

        // 2. Verifica se o SIAPE contém apenas números.
        if (!is_numeric($siape)) {
            throw new Exception("O SIAPE deve conter apenas números.");
        }

        // 3. Verifica se a senha e a confirmação de senha são idênticas.
        if ($senha !== $confirmar_senha) {
            throw new Exception("As senhas não coincidem.");
        }

        // 4. Verifica se a senha tem o comprimento mínimo exigido.
        if (strlen($senha) < 8) {
            throw new Exception("A senha deve ter no mínimo 8 caracteres.");
        }

        // --- Processamento do Cadastro ---

        // Cria uma nova instância da classe AdministradorServico para lidar com a lógica de cadastro.
        $administradorServico = new AdministradorServico();
        
        // Chama o método 'criarAdministrador' do serviço, passando todos os dados necessários.
        // A senha será hashed dentro do método criarAdministrador no serviço.
        $resultado = $administradorServico->criarAdministrador($siape, $senha, $nome, $cargo);

        // Verifica o resultado do cadastro.
        if ($resultado) {
            $_SESSION['cadastro_success'] = "Administrador cadastrado com sucesso!"; // Mensagem de sucesso.
            header("Location: login.php"); // Redireciona para a página de login após o sucesso.
            exit; // Termina a execução do script.
        } else {
             // Se o cadastro falhar e nenhuma mensagem de erro específica já foi definida pelo DAO
             // (ex: SIAPE duplicado), define uma mensagem genérica de erro.
             if (!isset($_SESSION['cadastro_error'])) {
                 throw new Exception("Erro ao cadastrar administrador. Tente novamente.");
             }
        }
    } catch (Exception $e) {
        // Captura qualquer exceção (erro) lançada durante o processo de validação ou cadastro.
        $_SESSION['cadastro_error'] = $e->getMessage(); // Armazena a mensagem de erro na sessão.
        header("Location: cadastro_adm.php"); // Redireciona de volta para a página de cadastro com o erro.
        exit; // Termina a execução do script.
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Administrador - SUAP IF Goiano</title>
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
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
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
        .right-side {
            padding: 40px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .login-button {
            background-color: #2a9d8f;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-side">
            <img src="logos ifgoiano.png" alt="IF Goiano" style="max-width:150px;">
            <h1>Cadastro de Administradores</h1>
            <p>Sistema de Gestão Acadêmica</p>
        </div>
        
        <div class="right-side">
            <h2>Cadastrar Administrador</h2>
            
            <?php 
            // Exibe mensagens de erro de cadastro, se houver.
            if (isset($_SESSION['cadastro_error'])): ?>
                <div class="error-message"><?= $_SESSION['cadastro_error'] ?></div>
                <?php unset($_SESSION['cadastro_error']); // Limpa a mensagem após exibir. ?>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>SIAPE*</label>
                    <input type="text" name="siape" required>
                </div>
                
                <div class="form-group">
                    <label>Nome Completo*</label>
                    <input type="text" name="nome" required>
                </div>
                
                <div class="form-group">
                    <label>Cargo*</label>
                    <select name="cargo" required>
                        <option value="">Selecione...</option>
                        <option value="Coordenador">Coordenador</option>
                        <option value="Diretor">Diretor</option>
                        <option value="Secretário">Secretário</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Senha* (mínimo 8 caracteres)</label>
                    <input type="password" name="senha" minlength="8" required>
                </div>
                
                <div class="form-group">
                    <label>Confirmar Senha*</label>
                    <input type="password" name="confirmar_senha" minlength="8" required>
                </div>
                
                <button type="submit" class="login-button">Cadastrar</button>
            </form>
            
            <div style="text-align:center; margin-top:20px;">
                <a href="login.php">Já tem conta? Faça login</a>
            </div>
        </div>
    </div>
</body>
</html>
