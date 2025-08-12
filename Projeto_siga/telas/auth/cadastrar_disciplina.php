<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\cadastrar_disciplina.php

// ATENÇÃO CRÍTICA: DEVE SER A PRIMEIRA COISA NO ARQUIVO, SEM ESPAÇOS OU LINHAS ACIMA.
// Isso garante que não haja nenhum caractere antes de iniciar a sessão, o que pode causar erros.
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Inicia a sessão PHP se ainda não estiver iniciada.
}

// Inclui o serviço de Disciplina para lidar com a lógica de cadastro.
// Este caminho assume que o arquivo DisciplinaServico.php está em 'negocio/'
// e este arquivo (cadastrar_disciplina.php) está em 'telas/auth/'.
require_once __DIR__ . '/../../negocio/DisciplinaServico.php';

// --- PROTEÇÃO DE ROTA ---
// Verifica se o usuário está logado E se o tipo de usuário é 'professor'.
// Apenas professores têm permissão para acessar esta página e cadastrar disciplinas.
if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'professor') {
    $_SESSION['login_error'] = "Acesso negado. Faça login como professor para cadastrar disciplinas.";
    header("Location: login.php"); // Redireciona o usuário para a página de login.
    exit(); // Encerra o script para prevenir qualquer processamento adicional.
}

// Pega o SIAPE do professor que está logado na sessão.
// Este SIAPE será usado para associar a nova disciplina ao professor correto.
$siape_professor_logado = $_SESSION['usuario_logado'];

// Verifica se o formulário de cadastro foi submetido usando o método POST.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Coleta os dados do formulário, usando o operador de coalescência nula (??)
    // para garantir que as variáveis sejam definidas e evitar avisos.
    $nome_disciplina = $_POST['nome_disciplina'] ?? '';
    $carga_horaria_input = $_POST['carga_horaria'] ?? ''; // O valor inserido pelo usuário (em horas).

    // Instancia a classe DisciplinaServico, que contém a lógica de negócio para disciplinas.
    $disciplinaServico = new DisciplinaServico();

    // --- VALIDAÇÕES DOS DADOS DO FORMULÁRIO ---
    // Verifica se os campos obrigatórios estão vazios.
    if (empty($nome_disciplina) || empty($carga_horaria_input)) {
        $_SESSION['cadastro_disciplina_error'] = "Todos os campos são obrigatórios.";
    } 
    // Verifica se a carga horária é numérica e um número inteiro positivo.
    elseif (!is_numeric($carga_horaria_input) || (int)$carga_horaria_input <= 0) {
        $_SESSION['cadastro_disciplina_error'] = "A carga horária deve ser um número inteiro positivo.";
    } 
    else {
        // --- PREPARAÇÃO DOS DADOS PARA O BANCO DE DADOS ---
        // Formata a carga horária inserida (que é um número de horas)
        // para o formato de string 'HH:MM:SS' esperado pela coluna TIME no seu banco de dados.
        // Por exemplo, '2' se torna '02:00:00'.
        $carga_horaria_formatada = sprintf("%02d:00:00", (int)$carga_horaria_input);

        // Define as propriedades no objeto de serviço com os dados coletados.
        $disciplinaServico->set("nome_disciplina", $nome_disciplina);
        $disciplinaServico->set("ch", $carga_horaria_formatada); // Define a carga horária formatada.
        // Converte o SIAPE do professor para inteiro antes de setar,
        // garantindo o tipo de dado correto para o banco.
        $disciplinaServico->set("siape_prof", (int)$siape_professor_logado); 

        // --- TENTA CADASTRAR A DISCIPLINA ---
        if ($disciplinaServico->cadastrar()) {
            // Se o cadastro for bem-sucedido, armazena uma mensagem de sucesso na sessão.
            $_SESSION['cadastro_disciplina_success'] = "Disciplina '" . htmlspecialchars($nome_disciplina) . "' cadastrada com sucesso!";
            // Redireciona para a própria página para limpar o formulário e evitar reenvio acidental.
            header("Location: cadastrar_disciplina.php"); 
            exit(); // Encerra o script.
        } else {
            // Se o cadastro falhar e nenhuma mensagem de erro específica foi definida
            // pelo DAO (ex: nome de disciplina duplicado), define uma mensagem genérica.
            if (!isset($_SESSION['cadastro_disciplina_error'])) {
                $_SESSION['cadastro_disciplina_error'] = "Erro ao cadastrar disciplina. Tente novamente.";
            }
        }
    }
    // Em caso de erro na validação ou no cadastro, redireciona para a própria página
    // para exibir a mensagem de erro armazenada na sessão.
    header("Location: cadastrar_disciplina.php"); 
    exit(); // Encerra o script.
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Cadastrar Nova Disciplina</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background-color: #f4f7f8;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            box-sizing: border-box;
            text-align: center;
        }
        h1 {
            color: #386641;
            margin-bottom: 25px;
            font-size: 2em;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: bold;
        }
        input[type="text"],
        input[type="number"] {
            width: calc(100% - 22px);
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .submit-button {
            background-color: #2a9d8f;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            width: 100%;
            transition: background-color 0.3s ease;
            margin-top: 20px;
        }
        .submit-button:hover {
            background-color: #268074;
        }
        .back-link {
            display: block;
            margin-top: 20px;
            color: #386641;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }
        .back-link:hover {
            color: #2a5133;
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
    <h1>Cadastrar Nova Disciplina</h1>

    <?php
    // Exibe mensagem de sucesso, se houver.
    if (isset($_SESSION['cadastro_disciplina_success'])) {
        echo '<p class="success-message">' . $_SESSION['cadastro_disciplina_success'] . '</p>';
        unset($_SESSION['cadastro_disciplina_success']); // Limpa a mensagem após exibir.
    }
    // Exibe mensagem de erro, se houver.
    if (isset($_SESSION['cadastro_disciplina_error'])) {
        echo '<p class="error-message">' . $_SESSION['cadastro_disciplina_error'] . '</p>';
        unset($_SESSION['cadastro_disciplina_error']); // Limpa a mensagem após exibir.
    }
    ?>

    <form action="" method="POST">
        <div class="form-group">
            <label for="nome_disciplina">Nome da Disciplina:</label>
            <input type="text" id="nome_disciplina" name="nome_disciplina" required>
        </div>

        <div class="form-group">
            <label for="carga_horaria">Carga Horária (horas):</label>
            <input type="number" id="carga_horaria" name="carga_horaria" min="1" required>
        </div>

        <button type="submit" class="submit-button">Cadastrar Disciplina</button>
    </form>

    <a href="principal.php" class="back-link">← Voltar ao Dashboard</a>
</div>

</body>
</html>
