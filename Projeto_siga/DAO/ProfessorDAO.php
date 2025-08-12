<?php
// C:\xampp\htdocs\Projeto_siga\DAO\ProfessorDAO.php

require_once __DIR__ . '/Conexao.php'; // Inclui a classe de conexão com o banco de dados.

/**
 * A classe ProfessorDAO é responsável por todas as operações de acesso a dados
 * relacionadas aos professores na tabela 'professor'.
 */
class ProfessorDAO {
    // Propriedades para armazenar os dados do professor.
    public $siape_prof;
    public $nome;
    public $senha; // A senha será armazenada como hash no banco.

    /**
     * Define um valor para uma propriedade específica do DAO.
     * @param string $prop Nome da propriedade (ex: "siape_prof", "nome", "senha").
     * @param mixed $value O valor a ser atribuído à propriedade.
     */
    public function set($prop, $value) {
        $this->$prop = $value;
    }

    /**
     * Cadastra um novo professor no banco de dados.
     * A senha já deve estar hashed antes de ser passada para este método.
     * @return bool True se o cadastro for bem-sucedido, false caso contrário.
     */
    public function cadastrar() {
        $conexao = new Conexao();
        $conn = $conexao->get_connection();

        if (!$conn) {
            error_log("ProfessorDAO - Falha na conexão com o banco de dados.");
            $_SESSION['cadastro_error'] = "Erro interno do servidor. Tente novamente mais tarde.";
            return false;
        }

        $SQL = "INSERT INTO professor (siape_prof, nome, senha) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($SQL);

        if (!$stmt) {
            error_log("ProfessorDAO - Erro ao preparar a query de cadastro: " . $conn->error);
            $_SESSION['cadastro_error'] = "Erro interno ao preparar o cadastro.";
            $conexao->close();
            return false;
        }

        // Binda os parâmetros. 'sss' indica que todos são strings.
        // O SIAPE_PROF é INT no DB, mas muitas vezes tratado como string em inputs/outputs.
        // Se seu SIAPE for estritamente numérico e você quiser bindá-lo como INT, mude para "sis" e converta $this->siape_prof para (int).
        $stmt->bind_param("sss", $this->siape_prof, $this->nome, $this->senha);
        $success = $stmt->execute();

        if (!$success) {
            error_log("ProfessorDAO - Erro ao executar a query de cadastro: " . $stmt->error);
            if ($stmt->errno == 1062) { // Código de erro para entrada duplicada (SIAPE já cadastrado)
                $_SESSION['cadastro_error'] = "SIAPE já cadastrado.";
            } else {
                $_SESSION['cadastro_error'] = "Erro no banco de dados durante o cadastro.";
            }
        }

        $stmt->close();
        $conexao->close();
        return $success;
    }

    /**
     * Busca um professor pelo SIAPE para verificar sua existência.
     * @param string $siape_prof O SIAPE do professor a ser buscado.
     * @return bool True se o professor for encontrado, false caso contrário.
     */
    public function buscarPorSiape($siape_prof) {
        $conexao = new Conexao();
        $conn = $conexao->get_connection();
        if (!$conn) {
            error_log("ProfessorDAO - Falha na conexão ao buscar por SIAPE.");
            return false;
        }

        $SQL = "SELECT siape_prof FROM professor WHERE siape_prof = ?";
        $stmt = $conn->prepare($SQL);

        if (!$stmt) {
            error_log("ProfessorDAO - Erro ao preparar busca por SIAPE: " . $conn->error);
            $conexao->close();
            return false;
        }

        $stmt->bind_param("s", $siape_prof); // SIAPE como string.
        $stmt->execute();
        $result = $stmt->get_result();
        $num_rows = $result->num_rows;

        $stmt->close();
        $conexao->close();
        return $num_rows > 0;
    }

    /**
     * Busca informações de login de um professor (senha e nome) pelo SIAPE.
     * @param string $siape_prof O SIAPE do professor.
     * @return array|false Um array associativo com 'senha' e 'nome' do professor,
     * ou false se o professor não for encontrado ou ocorrer um erro.
     */
    public function buscarProfessorParaLogin($siape_prof) {
        $conexao = new Conexao();
        $conn = $conexao->get_connection();
        if (!$conn) {
            error_log("ProfessorDAO - Falha na conexão ao buscar dados para login.");
            return false;
        }

        $SQL = "SELECT senha, nome FROM professor WHERE siape_prof = ?";
        $stmt = $conn->prepare($SQL);

        if (!$stmt) {
            error_log("ProfessorDAO - Erro ao preparar busca de dados para login: " . $conn->error);
            $conexao->close();
            return false;
        }

        $stmt->bind_param("s", $siape_prof); // SIAPE como string.
        $stmt->execute();
        $result = $stmt->get_result();
        $professorData = $result->fetch_assoc(); // Retorna uma linha como array associativo ou null.

        $stmt->close();
        $conexao->close();
        return $professorData; // Retorna os dados do professor ou null/false.
    }

    /**
     * Busca todos os professores cadastrados no banco de dados.
     * @return array|false Um array de arrays associativos com os dados de todos os professores (siape_prof, nome),
     * ou um array vazio se nenhum professor for encontrado, ou false em caso de erro na conexão/query.
     */
    public function buscarTodosProfessores() {
        $conexao = new Conexao();
        $conn = $conexao->get_connection();
        if (!$conn) {
            error_log("ProfessorDAO - Falha na conexão ao buscar todos os professores.");
            $_SESSION['listar_professores_error'] = "Erro ao conectar ao banco de dados para listar professores.";
            return false;
        }

        // Query para selecionar SIAPE e nome de todos os professores.
        $SQL = "SELECT siape_prof, nome FROM professor ORDER BY nome ASC";
        $stmt = $conn->prepare($SQL);

        if (!$stmt) {
            error_log("ProfessorDAO - Erro ao preparar busca de todos os professores: " . $conn->error);
            $_SESSION['listar_professores_error'] = "Erro interno ao preparar a busca de professores.";
            $conexao->close();
            return false;
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $professores = [];
        while ($row = $result->fetch_assoc()) {
            $professores[] = $row; // Adiciona cada linha (professor) ao array.
        }

        $stmt->close();
        $conexao->close();
        return $professores; // Retorna o array de professores (pode estar vazio).
    }
}
?>
