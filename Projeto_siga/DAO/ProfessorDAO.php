<?php
// C:\xampp\htdocs\Projeto_siga-1\Projeto_siga\DAO\ProfessorDAO.php

// Inclui o arquivo de conexão com o banco de dados.
require_once __DIR__ . '/Conexao.php';

// A classe ProfessorDAO é responsável por todas as operações de banco de dados
// relacionadas aos professores (entidade 'professor').
class ProfessorDAO {
    // Propriedades para armazenar os dados do professor que serão usados nas operações.
    public $siape_prof; // SIAPE do professor, que é a chave primária
    public $nome;       // Nome do professor
    public $senha;      // Hash da senha do professor

    /**
     * Define um valor para uma propriedade específica do DAO.
     * @param string $prop Nome da propriedade (ex: "siape_prof", "nome", "senha").
     * @param mixed $value O valor a ser atribuído.
     */
    public function set($prop, $value) {
        $this->$prop = $value;
    }

    /**
     * Cadastra um novo professor na tabela 'professor'.
     * Assume que o hash da senha já foi gerado na camada de serviço (ProfessorServico.php).
     * @return bool Retorna TRUE se o cadastro for bem-sucedido, FALSE em caso de erro (ex: SIAPE duplicado).
     */
    public function cadastrar() {
        $conexao = new Conexao(); // Cria uma nova instância da classe de conexão.
        $conn = $conexao->get_connection(); // Obtém o objeto de conexão MySQLi.

        if (!$conn) {
            error_log("ProfessorDAO - Falha na conexão com o banco de dados.");
            $_SESSION['cadastro_error'] = "Erro interno do servidor. Tente novamente mais tarde.";
            return false;
        }

        // Prepara a consulta SQL para inserir um novo professor.
        $SQL = "INSERT INTO professor (siape_prof, nome, senha) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($SQL); // Prepara a query para prevenir SQL Injection.

        if (!$stmt) {
            error_log("ProfessorDAO - Erro ao preparar a query de cadastro: " . $conn->error);
            $_SESSION['cadastro_error'] = "Erro interno ao preparar o cadastro.";
            $conexao->close();
            return false;
        }

        // Binda os parâmetros à query preparada. 'sss' indica que todos são strings.
        $stmt->bind_param("sss", $this->siape_prof, $this->nome, $this->senha);
        $success = $stmt->execute(); // Executa a query.

        if (!$success) {
            error_log("ProfessorDAO - Erro ao executar a query de cadastro: " . $stmt->error);
            // Verifica se o erro é de chave duplicada (SIAPE já cadastrado).
            if ($stmt->errno == 1062) { // Código de erro para entrada duplicada no MySQL.
                $_SESSION['cadastro_error'] = "SIAPE já cadastrado.";
            } else {
                $_SESSION['cadastro_error'] = "Erro no banco de dados durante o cadastro.";
            }
        }

        $stmt->close(); // Fecha o statement.
        $conexao->close(); // Fecha a conexão com o banco de dados.
        return $success; // Retorna TRUE ou FALSE dependendo do sucesso da operação.
    }

    /**
     * Busca um professor no banco de dados pelo SIAPE para verificar sua existência.
     * @param string $siape_prof O SIAPE do professor a ser buscado.
     * @return bool Retorna TRUE se o professor for encontrado, FALSE caso contrário.
     */
    public function buscarPorSiape($siape_prof) {
        $conexao = new Conexao();
        $conn = $conexao->get_connection();
        if (!$conn) {
            return false;
        }

        $SQL = "SELECT siape_prof FROM professor WHERE siape_prof = ?";
        $stmt = $conn->prepare($SQL);

        if (!$stmt) {
            error_log("ProfessorDAO - Erro ao preparar busca por SIAPE: " . $conn->error);
            $conexao->close();
            return false;
        }

        $stmt->bind_param("s", $siape_prof);
        $stmt->execute();
        $result = $stmt->get_result();
        $num_rows = $result->num_rows; // Conta o número de linhas retornadas.

        $stmt->close();
        $conexao->close();
        return $num_rows > 0; // Retorna TRUE se houver pelo menos uma linha (professor encontrado).
    }

    /**
     * Busca os dados de um professor no banco de dados para fins de login.
     * Retorna o hash da senha e o nome do professor.
     * @param string $siape_prof O SIAPE do professor a ser buscado.
     * @return array|false Retorna um array associativo contendo as colunas 'senha' e 'nome'
     * se o professor for encontrado, ou FALSE caso contrário.
     */
    public function buscarProfessorParaLogin($siape_prof) {
        $conexao = new Conexao();
        $conn = $conexao->get_connection();
        if (!$conn) {
            return false;
        }

        $SQL = "SELECT senha, nome FROM professor WHERE siape_prof = ?";
        $stmt = $conn->prepare($SQL);

        if (!$stmt) {
            error_log("ProfessorDAO - Erro ao preparar busca de dados para login: " . $conn->error);
            $conexao->close();
            return false;
        }

        $stmt->bind_param("s", $siape_prof);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc(); // Extrai a linha como um array associativo.

        $stmt->close();
        $conexao->close();
        return $row ?? false; // Retorna a linha encontrada (ou null), que será false.
    }
}
?>
