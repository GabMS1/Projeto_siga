<?php
// C:\xampp\htdocs\Projeto_siga\DAO\TurmaDAO.php

require_once __DIR__ . '/Conexao.php';

class TurmaDAO {
    private $conn;

    public function __construct($db_connection) {
        $this->conn = $db_connection;
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

    public $id_turma;
    public $curso;
    public $serie;
    public $id_disciplina;

    public function set($prop, $value) {
        $this->$prop = $value;
    }

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
                $_SESSION['cadastro_turma_error'] = "Esta disciplina já está cadastrada para esta turma.";
            } elseif ($stmt->errno == 1452) {
                 $_SESSION['cadastro_turma_error'] = "Disciplina associada não encontrada.";
            } else {
                $_SESSION['cadastro_turma_error'] = "Erro no banco de dados durante o cadastro da turma.";
            }
        }

        $stmt->close();
        return $success;
    }

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
     * @param string $siape_prof O SIAPE do professor logado.
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

    public function buscarTurmaCompletaPorId($id_turma, $id_disciplina) {
        $sql = "SELECT t.id_turma, t.curso, t.serie, t.id_disciplina, d.nome_disciplina, d.siape_prof, p.nome as nome_professor
                FROM turma t
                JOIN disciplina d ON t.id_disciplina = d.id_disciplina
                LEFT JOIN professor p ON d.siape_prof = p.siape_prof
                WHERE t.id_turma = ? AND t.id_disciplina = ?";
        
        if (!$this->conn) return false;

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("ii", $id_turma, $id_disciplina);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $turma = $resultado->fetch_assoc();
        $stmt->close();
        return $turma;
    }
    
    /**
     * CORREÇÃO PRINCIPAL AQUI: Garante que todos os professores/disciplinas da turma sejam retornados.
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
        
        // Garante que todos os resultados sejam coletados
        while ($linha = $resultado->fetch_assoc()) {
            $detalhes[] = $linha;
        }
        
        $stmt->close();
        return $detalhes;
    }
}
?>