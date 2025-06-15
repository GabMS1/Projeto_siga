<?php
// C:\xampp\htdocs\Projeto_siga\DAO\ProfessorDAO.php

require_once __DIR__ . '/Conexao.php';

class ProfessorDAO {
    public $siape_prof;
    public $nome;
    public $senha;

    public function set($prop, $value) {
        $this->$prop = $value;
    }

    public function cadastrar() {
        $conexao = new Conexao();
        $conn = $conexao->get_connection();

        if (!$conn) {
            return false;
        }

        $senhaHash = password_hash($this->senha, PASSWORD_DEFAULT);

        $SQL = "INSERT INTO professor (siape_prof, nome, senha) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($SQL);

        if (!$stmt) {
            error_log("ProfessorDAO - Erro ao preparar a query de cadastro: " . $conn->error);
            $_SESSION['cadastro_error'] = "Erro interno ao preparar o cadastro.";
            $conexao->close();
            return false;
        }

        $stmt->bind_param("sss", $this->siape_prof, $this->nome, $senhaHash);
        $success = $stmt->execute();

        if (!$success) {
            error_log("ProfessorDAO - Erro ao executar a query de cadastro: " . $stmt->error);
            if ($stmt->errno == 1062) {
                $_SESSION['cadastro_error'] = "SIAPE já cadastrado.";
            } else {
                $_SESSION['cadastro_error'] = "Erro no banco de dados durante o cadastro.";
            }
        }

        $stmt->close();
        $conexao->close();
        return $success;
    }

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
        $num_rows = $result->num_rows;

        $stmt->close();
        $conexao->close();
        return $num_rows > 0;
    }

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
        $row = $result->fetch_assoc();

        $stmt->close();
        $conexao->close();
        return $row ?? false;
    }
}
?>