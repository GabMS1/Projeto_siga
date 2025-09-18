<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\cadastrar_turma.php

// ATENÇÃO CRÍTICA: DEVE SER A PRIMEIRA COISA NO ARQUIVO, SEM ESPAÇOS OU LINHAS ACIMA.
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Inicia a sessão PHP se ainda não estiver iniciada.
}

// Inclui o serviço de Turma para lidar com a lógica de cadastro e listagem de disciplinas.
require_once __DIR__ . '/../../negocio/TurmaServico.php';

// --- PROTEÇÃO DE ROTA ---
// Apenas administradores podem acessar esta página para cadastrar turmas.
if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'admin') {
    $_SESSION['login_error'] = "Acesso negado. Apenas administradores podem cadastrar turmas.";
    header("Location: login.php"); // Redireciona para a página de login.
    exit(); // Encerra o script.
}

// Pega o SIAPE do administrador logado da sessão.
$siape_admin_logado = $_SESSION['usuario_logado'];

$disciplinas_disponiveis = []; // Para armazenar as disciplinas para o dropdown
$mensagem = ""; // Para mensagens de feedback

try {
    $turmaServico = new TurmaServico();
    // Lista todas as disciplinas para o admin escolher
    $disciplinas_disponiveis = $turmaServico->listarDisciplinasParaSelecao(null); // Passar null ou um método que lista todas

    // Se o formulário foi submetido via POST.
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Coleta os dados do formulário.
        $id_turma = $_POST['id_turma'] ?? '';
        $curso = $_POST['curso'] ?? '';
        $serie = $_POST['serie'] ?? '';
        $id_disciplina = $_POST['id_disciplina'] ?? '';

        // --- VALIDAÇÕES DOS DADOS DO FORMULÁRIO ---
        if (empty($id_turma) || empty($curso) || empty($serie) || empty($id_disciplina)) {
            $_SESSION['cadastro_turma_error'] = "Todos os campos são obrigatórios.";
        } elseif (!is_numeric($id_turma) || (int)$id_turma <= 0) {
            $_SESSION['cadastro_turma_error'] = "O ID da Turma deve ser um número inteiro positivo.";
        } elseif (!is_numeric($id_disciplina) || (int)$id_disciplina <= 0) {
            $_SESSION['cadastro_turma_error'] = "A Disciplina selecionada é inválida.";
        } else {
            // Define as propriedades no objeto de serviço.
            $turmaServico->set("id_turma", (int)$id_turma);
            $turmaServico->set("curso", $curso);
            $turmaServico->set("serie", $serie);
            $turmaServico->set("id_disciplina", (int)$id_disciplina);

            // Tenta cadastrar a turma.
            if ($turmaServico->cadastrar()) {
                $_SESSION['cadastro_turma_success'] = "Turma cadastrada com sucesso! ID: " . htmlspecialchars($id_turma);
                header("Location: gerenciar_turmas.php"); 
                exit();
            } else {
                // A mensagem de erro específica já deve ter sido definida no Serviço/DAO.
                if (!isset($_SESSION['cadastro_turma_error'])) {
                    $_SESSION['cadastro_turma_error'] = "Erro ao cadastrar turma. Tente novamente.";
                }
            }
        }
        // Redireciona em caso de erro na validação ou no cadastro, para exibir a mensagem.
        header("Location: cadastrar_turma.php"); 
        exit();
    }

} catch (Exception $e) {
    $mensagem = "Erro ao carregar dados: " . $e->getMessage();
    error_log("Erro em cadastrar_turma.php: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Admin: Cadastrar Nova Turma</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background-color: #f4f7f8;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            box-sizing: border-box;
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
        input[type="number"],
        select {
            width: 100%;
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
    <h1>Cadastrar Nova Turma</h1>

    <?php
    if (isset($_SESSION['cadastro_turma_success'])) {
        echo '<p class="success-message">' . $_SESSION['cadastro_turma_success'] . '</p>';
        unset($_SESSION['cadastro_turma_success']);
    }
    if (isset($_SESSION['cadastro_turma_error'])) {
        echo '<p class="error-message">' . $_SESSION['cadastro_turma_error'] . '</p>';
        unset($_SESSION['cadastro_turma_error']);
    }
    if (!empty($mensagem)) {
        echo '<p class="error-message">' . htmlspecialchars($mensagem) . '</p>';
    }
    ?>

    <form action="" method="POST">
        <div class="form-group">
            <label for="id_turma">ID da Turma (Ex: 301, 202):</label>
            <input type="number" id="id_turma" name="id_turma" min="1" required>
        </div>

        <div class="form-group">
            <label for="curso">Curso:</label>
            <select id="curso" name="curso" required>
                <option value="">Selecione o Curso</option>
                <option value="Agropecuária">Agropecuária</option>
                <option value="Alimentos">Alimentos</option>
                <option value="Informática">Informática</option>
            </select>
        </div>

        <div class="form-group">
            <label for="serie">Série:</label>
            <select id="serie" name="serie" required>
                <option value="">Selecione a Série</option>
                <option value="1">1º Ano</option>
                <option value="2">2º Ano</option>
                <option value="3">3º Ano</option>
            </select>
        </div>

        <div class="form-group">
            <label for="id_disciplina">Disciplina:</label>
            <select id="id_disciplina" name="id_disciplina" required>
                <option value="">Selecione uma Disciplina</option>
                <?php if (!empty($disciplinas_disponiveis)): ?>
                    <?php foreach ($disciplinas_disponiveis as $disciplina): ?>
                        <option value="<?php echo htmlspecialchars($disciplina['id_disciplina']); ?>">
                            <?php echo htmlspecialchars($disciplina['nome_disciplina']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="" disabled>Nenhuma disciplina encontrada.</option>
                <?php endif; ?>
            </select>
        </div>

        <button type="submit" class="submit-button">Cadastrar Turma</button>
    </form>

    <a href="gerenciar_turmas.php" class="back-link">← Voltar ao Gerenciamento de Turmas</a>
</div>

</body>
</html>