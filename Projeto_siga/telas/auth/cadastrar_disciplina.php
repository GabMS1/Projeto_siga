<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\cadastrar_disciplina.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../negocio/DisciplinaServico.php';

if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'professor') {
    $_SESSION['login_error'] = "Acesso negado. Faça login como professor para cadastrar disciplinas.";
    header("Location: login.php");
    exit();
}

$siape_professor_logado = $_SESSION['usuario_logado'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome_disciplina = $_POST['nome_disciplina'] ?? '';
    $carga_horaria_input = $_POST['carga_horaria'] ?? '';

    $disciplinaServico = new DisciplinaServico();

    if (empty($nome_disciplina) || empty($carga_horaria_input)) {
        $_SESSION['cadastro_disciplina_error'] = "Todos os campos são obrigatórios.";
    } 
    elseif (!is_numeric($carga_horaria_input) || (int)$carga_horaria_input <= 0) {
        $_SESSION['cadastro_disciplina_error'] = "A carga horária deve ser um número inteiro positivo.";
    } 
    else {
        $disciplinaServico->set("nome_disciplina", $nome_disciplina);
        $disciplinaServico->set("ch", (int)$carga_horaria_input); // Envia como inteiro
        $disciplinaServico->set("siape_prof", $siape_professor_logado); 

        if ($disciplinaServico->cadastrar()) {
            $_SESSION['cadastro_disciplina_success'] = "Disciplina '" . htmlspecialchars($nome_disciplina) . "' cadastrada com sucesso!";
            header("Location: minhas_disciplinas.php"); 
            exit();
        } else {
            if (!isset($_SESSION['cadastro_disciplina_error'])) {
                $_SESSION['cadastro_disciplina_error'] = "Erro ao cadastrar disciplina. Tente novamente.";
            }
        }
    }
    header("Location: cadastrar_disciplina.php"); 
    exit();
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
    if (isset($_SESSION['cadastro_disciplina_success'])) {
        echo '<p class="success-message">' . $_SESSION['cadastro_disciplina_success'] . '</p>';
        unset($_SESSION['cadastro_disciplina_success']);
    }
    if (isset($_SESSION['cadastro_disciplina_error'])) {
        echo '<p class="error-message">' . $_SESSION['cadastro_disciplina_error'] . '</p>';
        unset($_SESSION['cadastro_disciplina_error']);
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

    <a href="minhas_disciplinas.php" class="back-link">← Voltar às Minhas Disciplinas</a>
</div>

</body>
</html>