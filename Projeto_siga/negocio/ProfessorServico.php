<?php
// C:\xampp\htdocs\Projeto_siga\negocio\ProfessorServico.php

require_once __DIR__ . '/../DAO/ProfessorDAO.php';
require_once __DIR__ . '/../DAO/Conexao.php';

class ProfessorServico {
    private $professorDAO;
    private $conn;

    public function __construct() {
        $this->conn = Conexao::get_connection();
        if ($this->conn) {
            $this->professorDAO = new ProfessorDAO($this->conn);
        }
    }

    public function cadastrar($siape_prof, $nome, $senha) {
        if (!$this->conn) { return false; }

        try {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT); 
            return $this->professorDAO->cadastrar($siape_prof, $nome, $senha_hash);
        } catch (Exception $e) {
            error_log("ProfessorServico->cadastrar: " . $e->getMessage());
            return false;
        }
    }

    public function autenticar($siape_prof, $senha_digitada) {
        if (!$this->conn) { return false; }

        try {
            $professorData = $this->professorDAO->buscarProfessorParaLogin($siape_prof);

            if ($professorData && password_verify($senha_digitada, $professorData['senha'])) {
                return [
                    'siape' => $siape_prof,
                    'nome' => $professorData['nome']
                ];
            }
            return false;
        } catch (Exception $e) {
            error_log("ProfessorServico->autenticar: " . $e->getMessage());
            return false;
        }
    }

    public function listarProfessores() {
        if (!$this->conn) { return []; }

        try {
            return $this->professorDAO->listarTodos();
        } catch (Exception $e) {
            error_log("ProfessorServico->listarProfessores: " . $e->getMessage());
            return [];
        }
    }
    
    public function excluirProfessor($siape_prof) {
        if (!$this->conn) { return false; }

        try {
            return $this->professorDAO->excluir($siape_prof);
        } catch (Exception $e) {
            error_log("ProfessorServico->excluirProfessor: " . $e->getMessage());
            return false;
        }
    }

    public function buscarProfessor($siape_prof) {
        if (!$this->conn) { return false; }

        try {
            return $this->professorDAO->buscarPorSiape($siape_prof);
        } catch (Exception $e) {
            error_log("ProfessorServico->buscarProfessor: " . $e->getMessage());
            return false;
        }
    }
    
    public function atualizarProfessor($siape_prof, $nome, $nova_senha = null) {
        if (!$this->conn) { return false; }

        try {
            $senha_hash = null;
            if ($nova_senha) {
                $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            }
            return $this->professorDAO->atualizar($siape_prof, $nome, $senha_hash);
        } catch (Exception $e) {
            error_log("ProfessorServico->atualizarProfessor: " . $e->getMessage());
            return false;
        }
    }
}