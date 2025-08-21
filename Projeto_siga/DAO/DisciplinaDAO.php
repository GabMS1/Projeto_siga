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
        $stmt->bind_param("ssi", $nome_disciplina, $ch, $siape_prof);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function listarTodos() {
        $disciplinas = [];
        $sql = "SELECT id_disciplina, nome_disciplina, ch FROM disciplina ORDER BY nome_disciplina ASC";
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
    
    // NOVO MÉTODO
    public function listarPorProfessor($siape_prof) {
        $disciplinas = [];
        $sql = "SELECT id_disciplina, nome_disciplina, ch FROM disciplina WHERE siape_prof = ? ORDER BY nome_disciplina ASC";
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

    public function buscarPorId($id) {
        $sql = "SELECT id_disciplina, nome_disciplina, ch FROM disciplina WHERE id_disciplina = ?";
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

    public function atualizar($id, $nome_disciplina, $ch) {
        $sql = "UPDATE disciplina SET nome_disciplina = ?, ch = ? WHERE id_disciplina = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("DisciplinaDAO->atualizar: Erro ao preparar query - " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("ssi", $nome_disciplina, $ch, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function excluir($id) {
        $sql = "DELETE FROM disciplina WHERE id_disciplina = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("DisciplinaDAO->excluir: Erro ao preparar query - " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}