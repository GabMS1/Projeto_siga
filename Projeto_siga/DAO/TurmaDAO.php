<?php
// C:\xampp\htdocs\Projeto_siga-1\Projeto_siga\DAO\TurmaDAO.php

// Inclui o arquivo de conexão com o banco de dados.
require_once __DIR__ . '/Conexao.php';

/**
 * A classe TurmaDAO é responsável por todas as operações de banco de dados
 * relacionadas às turmas (tabela 'turma').
 */
class TurmaDAO {
    private $conn;

    /**
     * Construtor da classe que estabelece a conexão com o banco de dados.
     */
    public function __construct() {
        $conexao = new Conexao();
        $this->conn = $conexao->get_connection();
    }

    public function buscarTodas() {
        $turmas = [];
        $sql = "SELECT t.id_turma, t.curso, t.serie, d.id_disciplina, d.nome_disciplina, p.nome as nome_professor
                FROM turma t
                JOIN disciplina d ON t.id_disciplina = d.id_disciplina
                LEFT JOIN professor p ON d.siape_prof = p.siape_prof
                ORDER BY t.id_turma, d.nome_disciplina ASC";
        
        if (!$this->conn) {
            error_log("TurmaDAO - Falha na conexão com o banco de dados.");
            return [];
        }

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];

        $stmt->execute();
        $resultado = $stmt->get_result();
        while ($linha = $resultado->fetch_assoc()) {
            $turmas[] = $linha;
        }
        $stmt->close();
        return $turmas;
    }

    // Propriedades para armazenar os dados da turma.
    public $id_turma;
    public $curso;
    public $serie;
    public $id_disciplina;

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
     * @return bool True se o cadastro for bem-sucedido, false caso contrário.
     */
    public function cadastrar() {
        if (!$this->conn) {
            $_SESSION['cadastro_turma_error'] = "Erro interno do servidor. Tente novamente mais tarde.";
            return false;
        }

        $SQL = "INSERT INTO turma (id_turma, curso, serie, id_disciplina) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($SQL);

        if (!$stmt) {
            $_SESSION['cadastro_turma_error'] = "Erro interno ao preparar o cadastro da turma.";
            return false;
        }

        $stmt->bind_param("issi", $this->id_turma, $this->curso, $this->serie, $this->id_disciplina);
        $success = $stmt->execute();

        if (!$success) {
            if ($stmt->errno == 1062) {
                $_SESSION['cadastro_turma_error'] = "O ID da turma já existe para outra disciplina. Cadastre a disciplina nesta turma existente.";
            } elseif ($stmt->errno == 1452) {
                 $_SESSION['cadastro_turma_error'] = "Disciplina associada não encontrada.";
            } else {
                $_SESSION['cadastro_turma_error'] = "Erro no banco de dados durante o cadastro da turma.";
            }
        }

        $stmt->close();
        return $success;
    }

    /**
     * Verifica se um ID de turma já existe no banco de dados.
     * @param int $id_turma O ID da turma a ser verificado.
     * @return bool True se o ID da turma já existe, false caso contrário.
     */
    public function buscarPorId($id_turma) {
        if (!$this->conn) return false;

        $SQL = "SELECT id_turma FROM turma WHERE id_turma = ?";
        $stmt = $this->conn->prepare($SQL);
        if (!$stmt) return false;

        $stmt->bind_param("i", $id_turma);
        $stmt->execute();
        $result = $stmt->get_result();
        $num_rows = $result->num_rows;

        $stmt->close();
        return $num_rows > 0;
    }

    /**
     * Busca todas as turmas associadas a um professor específico.
     * @param string $siape_prof O SIAPE do professor.
     * @return array|false Um array de turmas ou false em caso de erro.
     */
    public function buscarTurmasPorProfessor($siape_prof) {
        if (!$this->conn) return false;

        $SQL = "SELECT t.id_turma, t.curso, t.serie, d.nome_disciplina
                FROM turma t
                JOIN disciplina d ON t.id_disciplina = d.id_disciplina
                WHERE d.siape_prof = ?";
        $stmt = $this->conn->prepare($SQL);
        if (!$stmt) return false;

        $stmt->bind_param("s", $siape_prof);
        $stmt->execute();
        $result = $stmt->get_result();

        $turmas = [];
        while ($row = $result->fetch_assoc()) {
            $turmas[] = $row;
        }

        $stmt->close();
        return $turmas;
    }

    /**
     * Busca os dados completos de uma turma pelo seu ID.
     * @param int $id_turma O ID da turma a ser buscada.
     * @return array|false Retorna os dados da turma ou false se não for encontrada.
     */
    public function buscarTurmaCompletaPorId($id_turma) {
        $sql = "SELECT t.id_turma, t.curso, t.serie, t.id_disciplina, d.nome_disciplina, d.siape_prof, p.nome as nome_professor
                FROM turma t
                JOIN disciplina d ON t.id_disciplina = d.id_disciplina
                LEFT JOIN professor p ON d.siape_prof = p.siape_prof
                WHERE t.id_turma = ?";
        
        if (!$this->conn) return false;

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("i", $id_turma);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $turma = $resultado->fetch_assoc();
        $stmt->close();
        return $turma;
    }
    
    /**
     * Busca todas as disciplinas e professores para um ID de turma específico.
     * @param int $id_turma O ID da turma.
     * @return array Retorna um array de resultados.
     */
    public function buscarDisciplinasEProfessoresPorTurmaId($id_turma) {
        $detalhes = [];
        $sql = "SELECT t.curso, t.serie, d.nome_disciplina, p.nome as nome_professor, p.siape_prof
                FROM turma t
                JOIN disciplina d ON t.id_disciplina = d.id_disciplina
                LEFT JOIN professor p ON d.siape_prof = p.siape_prof
                WHERE t.id_turma = ?
                ORDER BY d.nome_disciplina ASC";

        if (!$this->conn) return [];
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param("i", $id_turma);
        $stmt->execute();
        $resultado = $stmt->get_result();
        while ($linha = $resultado->fetch_assoc()) {
            $detalhes[] = $linha;
        }
        $stmt->close();
        return $detalhes;
    }

    /**
     * Destrutor para fechar a conexão.
     */
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>