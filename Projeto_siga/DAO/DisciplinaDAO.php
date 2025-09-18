<?php
// C:\xampp\htdocs\Projeto_siga\DAO\DisciplinaDAO.php

require_once __DIR__ . '/Conexao.php';

class DisciplinaDAO {
    private $conn;

    public function __construct() {
        $conexao = new Conexao();
        $this->conn = $conexao->get_connection();
    }

    public function cadastrar($nome_disciplina, $ch, $siape_prof) {
        $sql = "INSERT INTO disciplina (nome_disciplina, ch, siape_prof) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("DisciplinaDAO->cadastrar: Erro ao preparar query - " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("sis", $nome_disciplina, $ch, $siape_prof);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function listarTodos() {
        $disciplinas = [];
        $sql = "SELECT id_disciplina, nome_disciplina, ch, aulas_semanais FROM disciplina ORDER BY nome_disciplina ASC";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            error_log("DisciplinaDAO->listarTodos: Erro ao preparar query - " . $this->conn->error);
            throw new Exception("Erro ao preparar a busca de disciplinas: " . $this->conn->error);
        }

        $stmt->execute();
        $resultado = $stmt->get_result();
        while ($linha = $resultado->fetch_assoc()) {
            $disciplinas[] = $linha;
        }
        $stmt->close();
        return $disciplinas;
    }
    
    public function listarPorProfessor($siape_prof) {
        $disciplinas = [];
        $sql = "SELECT id_disciplina, nome_disciplina, ch, aulas_semanais FROM disciplina WHERE siape_prof = ? ORDER BY nome_disciplina ASC";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            error_log("DisciplinaDAO->listarPorProfessor: Erro ao preparar query - " . $this->conn->error);
            throw new Exception("Erro ao preparar a busca de disciplinas por professor: " . $this->conn->error);
        }

        $stmt->bind_param("s", $siape_prof);
        $stmt->execute();
        $resultado = $stmt->get_result();
        while ($linha = $resultado->fetch_assoc()) {
            $disciplinas[] = $linha;
        }
        $stmt->close();
        return $disciplinas;
    }
    
    /**
     * NOVA FUNÇÃO: Lista todas as disciplinas que ainda não foram atribuídas a um professor.
     */
    public function listarNaoAtribuidas() {
        $disciplinas = [];
        $sql = "SELECT id_disciplina, nome_disciplina, ch, aulas_semanais FROM disciplina WHERE siape_prof IS NULL ORDER BY nome_disciplina ASC";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            error_log("DisciplinaDAO->listarNaoAtribuidas: Erro ao preparar query - " . $this->conn->error);
            throw new Exception("Erro ao preparar a busca de disciplinas não atribuídas: " . $this->conn->error);
        }

        $stmt->execute();
        $resultado = $stmt->get_result();
        while ($linha = $resultado->fetch_assoc()) {
            $disciplinas[] = $linha;
        }
        $stmt->close();
        return $disciplinas;
    }

    public function buscarPorId($id) {
        $sql = "SELECT id_disciplina, nome_disciplina, ch, aulas_semanais FROM disciplina WHERE id_disciplina = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("DisciplinaDAO->buscarPorId: Erro ao preparar query - " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $disciplina = $resultado->fetch_assoc();
        $stmt->close();
        return $disciplina;
    }
    
    /**
     * NOVA FUNÇÃO: Atribui um professor a uma disciplina.
     */
    public function atribuirProfessor($id_disciplina, $siape_prof) {
        $sql = "UPDATE disciplina SET siape_prof = ? WHERE id_disciplina = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("DisciplinaDAO->atribuirProfessor: Erro ao preparar query - " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("si", $siape_prof, $id_disciplina);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }


    public function atualizar($id, $nome_disciplina, $ch) {
        $sql = "UPDATE disciplina SET nome_disciplina = ?, ch = ? WHERE id_disciplina = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("DisciplinaDAO->atualizar: Erro ao preparar query - " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("sii", $nome_disciplina, $ch, $id); // ch é INT
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    
    public function excluir($id) {
        if (!$this->conn) {
            return false;
        }

        $this->conn->begin_transaction();
        try {
            $sql_programada = "DELETE FROM programada WHERE id_disciplina = ?";
            $stmt_programada = $this->conn->prepare($sql_programada);
            if (!$stmt_programada) throw new Exception("Erro: " . $this->conn->error);
            $stmt_programada->bind_param("i", $id);
            $stmt_programada->execute();
            $stmt_programada->close();

            $sql_turma = "DELETE FROM turma WHERE id_disciplina = ?";
            $stmt_turma = $this->conn->prepare($sql_turma);
            if (!$stmt_turma) throw new Exception("Erro: " . $this->conn->error);
            $stmt_turma->bind_param("i", $id);
            $stmt_turma->execute();
            $stmt_turma->close();

            $sql_disciplina = "DELETE FROM disciplina WHERE id_disciplina = ?";
            $stmt_disciplina = $this->conn->prepare($sql_disciplina);
            if (!$stmt_disciplina) throw new Exception("Erro: " . $this->conn->error);
            $stmt_disciplina->bind_param("i", $id);
            $stmt_disciplina->execute();
            $stmt_disciplina->close();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("DisciplinaDAO->excluir: Falha na transação - " . $e->getMessage());
            return false;
        }
    }
}
?>