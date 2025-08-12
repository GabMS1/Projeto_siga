<?php
// C:\xampp\htdocs\Projeto_siga-1\Projeto_siga\DAO\TurmaDAO.php

// Inclui o arquivo de conexão com o banco de dados.
require_once __DIR__ . '/Conexao.php';

/**
 * A classe TurmaDAO é responsável por todas as operações de banco de dados
 * relacionadas às turmas (tabela 'turma').
 */
class TurmaDAO {
    // Propriedades para armazenar os dados da turma.
    public $id_turma;
    public $curso;
    public $serie;
    public $id_disciplina; // Chave estrangeira para a disciplina
    public $siape_prof;    // Chave estrangeira para o professor

    /**
     * Define um valor para uma propriedade específica do DAO.
     * @param string $prop Nome da propriedade a ser definida.
     * @param mixed $value O valor a ser atribuído à propriedade.
     */
    public function set($prop, $value) {
        $this->$prop = $value;
    }

    /**
     * Cadastra uma nova turma no banco de dados.
     * Assume que as validações de unicidade e existência das chaves estrangeiras
     * (id_disciplina, siape_prof) serão feitas na camada de serviço.
     * @return bool True se o cadastro for bem-sucedido, false caso contrário.
     */
    public function cadastrar() {
        $conexao = new Conexao();
        $conn = $conexao->get_connection();

        if (!$conn) {
            error_log("TurmaDAO - Falha na conexão com o banco de dados.");
            $_SESSION['cadastro_turma_error'] = "Erro interno do servidor. Tente novamente mais tarde.";
            return false;
        }

        // Prepara a consulta SQL para inserir uma nova turma.
        // id_turma é fornecido, não é AUTO_INCREMENT.
        $SQL = "INSERT INTO turma (id_turma, curso, serie, id_disciplina, siape_prof) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($SQL);

        if (!$stmt) {
            error_log("TurmaDAO - Erro ao preparar a query de cadastro: " . $conn->error);
            $_SESSION['cadastro_turma_error'] = "Erro interno ao preparar o cadastro da turma.";
            $conexao->close();
            return false;
        }

        // 'isssi' indica: int, string, string, int, int
        // id_turma (int), curso (string), serie (string), id_disciplina (int), siape_prof (int)
        $stmt->bind_param("issii", $this->id_turma, $this->curso, $this->serie, $this->id_disciplina, $this->siape_prof);
        $success = $stmt->execute();

        if (!$success) {
            error_log("TurmaDAO - Erro ao executar a query de cadastro: " . $stmt->error);
            if ($stmt->errno == 1062) { // Código de erro para entrada duplicada
                $_SESSION['cadastro_turma_error'] = "O ID da turma já existe. Por favor, escolha outro.";
            } elseif ($stmt->errno == 1452) { // Código de erro para chave estrangeira inválida
                 $_SESSION['cadastro_turma_error'] = "Disciplina ou Professor associado não encontrado. Verifique os dados.";
            } else {
                $_SESSION['cadastro_turma_error'] = "Erro no banco de dados durante o cadastro da turma.";
            }
        }

        $stmt->close();
        $conexao->close();
        return $success;
    }

    /**
     * Verifica se um ID de turma já existe no banco de dados.
     * @param int $id_turma O ID da turma a ser verificado.
     * @return bool True se o ID da turma já existe, false caso contrário.
     */
    public function buscarPorId($id_turma) {
        $conexao = new Conexao();
        $conn = $conexao->get_connection();
        if (!$conn) {
            error_log("TurmaDAO - Falha na conexão ao buscar turma por ID.");
            return false;
        }

        $SQL = "SELECT id_turma FROM turma WHERE id_turma = ?";
        $stmt = $conn->prepare($SQL);

        if (!$stmt) {
            error_log("TurmaDAO - Erro ao preparar busca por ID de turma: " . $conn->error);
            $conexao->close();
            return false;
        }

        $stmt->bind_param("i", $id_turma);
        $stmt->execute();
        $result = $stmt->get_result();
        $num_rows = $result->num_rows;

        $stmt->close();
        $conexao->close();
        return $num_rows > 0;
    }

    /**
     * Busca todas as turmas associadas a um professor específico,
     * incluindo o nome da disciplina a que pertencem.
     * @param int $siape_prof O SIAPE do professor.
     * @return array|false Um array de arrays associativos com os dados das turmas,
     * ou um array vazio se nenhuma turma for encontrada, ou false em caso de erro na conexão.
     */
    public function buscarTurmasPorProfessor($siape_prof) {
        $conexao = new Conexao();
        $conn = $conexao->get_connection();
        if (!$conn) {
            error_log("TurmaDAO - Falha na conexão ao buscar turmas para professor.");
            $_SESSION['listar_turmas_error'] = "Erro ao conectar ao banco de dados para listar turmas.";
            return false;
        }

        // Query para selecionar todas as turmas de um dado professor,
        // juntando com a tabela de disciplina para pegar o nome da disciplina.
        $SQL = "SELECT t.id_turma, t.curso, t.serie, d.nome_disciplina
                FROM turma t
                JOIN disciplina d ON t.id_disciplina = d.id_disciplina
                WHERE t.siape_prof = ?";
        $stmt = $conn->prepare($SQL);

        if (!$stmt) {
            error_log("TurmaDAO - Erro ao preparar busca de turmas por professor: " . $conn->error);
            $_SESSION['listar_turmas_error'] = "Erro interno ao preparar a busca de turmas.";
            $conexao->close();
            return false;
        }

        $stmt->bind_param("i", $siape_prof); // Binda o SIAPE do professor como inteiro.
        $stmt->execute();
        $result = $stmt->get_result();

        $turmas = [];
        while ($row = $result->fetch_assoc()) {
            $turmas[] = $row; // Adiciona cada linha (turma) ao array.
        }

        $stmt->close();
        $conexao->close();
        return $turmas; // Retorna o array de turmas (pode estar vazio).
    }
}
?>
