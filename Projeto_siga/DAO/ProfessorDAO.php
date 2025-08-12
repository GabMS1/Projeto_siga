<?php
// C:\xampp\htdocs\Projeto_siga\DAO\ProfessorDAO.php

require_once __DIR__ . '/Conexao.php';

class ProfessorDAO {
    private $conn;

    public function __construct() {
        $conexao = new Conexao();
        $this->conn = $conexao->get_connection();
    }

    public function cadastrar($siape, $nome, $senha_hash) {
        $sql = "INSERT INTO professor (siape_prof, nome, senha) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("ProfessorDAO->cadastrar: Erro ao preparar query - " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("sss", $siape, $nome, $senha_hash);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function buscarProfessorParaLogin($siape) {
        $sql = "SELECT nome, senha FROM professor WHERE siape_prof = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("ProfessorDAO->buscarProfessorParaLogin: Erro ao preparar query - " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("s", $siape);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data;
    }
    
    public function listarTodos() {
        $professores = [];
        $sql = "SELECT siape_prof, nome FROM professor ORDER BY nome ASC";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            error_log("ProfessorDAO->listarTodos: Erro ao preparar query - " . $this->conn->error);
            throw new Exception("Erro ao preparar a busca de professores: " . $this->conn->error);
        }

        $stmt->execute();
        $resultado = $stmt->get_result();
        while ($linha = $resultado->fetch_assoc()) {
            $professores[] = $linha;
        }
        $stmt->close();
        return $professores;
    }

    public function excluir($siape) {
        $sql = "DELETE FROM professor WHERE siape_prof = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("ProfessorDAO->excluir: Erro ao preparar query - " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("s", $siape);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function buscarPorSiape($siape) {
        $sql = "SELECT siape_prof, nome FROM professor WHERE siape_prof = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("ProfessorDAO->buscarPorSiape: Erro ao preparar query - " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("s", $siape);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $professor = $resultado->fetch_assoc();
        $stmt->close();
        return $professor;
    }

    public function atualizar($siape, $nome, $senha_hash = null) {
        if ($senha_hash) {
            $sql = "UPDATE professor SET nome = ?, senha = ? WHERE siape_prof = ?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                error_log("ProfessorDAO->atualizar (com senha): Erro ao preparar query - " . $this->conn->error);
                return false;
            }
            $stmt->bind_param("sss", $nome, $senha_hash, $siape);
        } else {
            $sql = "UPDATE professor SET nome = ? WHERE siape_prof = ?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                error_log("ProfessorDAO->atualizar (sem senha): Erro ao preparar query - " . $this->conn->error);
                return false;
            }
            $stmt->bind_param("ss", $nome, $siape);
        }
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}