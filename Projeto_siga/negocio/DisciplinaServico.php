<?php
// C:\xampp\htdocs\Projeto_siga\negocio\DisciplinaServico.php

require_once __DIR__ . '/../DAO/DisciplinaDAO.php';
require_once __DIR__ . '/../DAO/Conexao.php';

class DisciplinaServico {
    private $disciplinaDAO;
    private $conn;

    public function __construct() {
        $this->conn = Conexao::get_connection();
        if ($this->conn) {
            $this->disciplinaDAO = new DisciplinaDAO($this->conn);
        }
    }

    public function cadastrar($nome_disciplina, $ch, $siape_prof, $aulas_semanais) {
        if (!$this->conn) { return false; }

        try {
            return $this->disciplinaDAO->cadastrar($nome_disciplina, $ch, $siape_prof, $aulas_semanais);
        } catch (Exception $e) {
            error_log("DisciplinaServico->cadastrar: " . $e->getMessage());
            return false;
        }
    }

    public function listarDisciplinas() {
        if (!$this->conn) { return []; }

        try {
            return $this->disciplinaDAO->listarTodos();
        } catch (Exception $e) {
            error_log("DisciplinaServico->listarDisciplinas: " . $e->getMessage());
            return [];
        }
    }
    
    public function listarDisciplinasPorProfessor($siape_prof) {
        if (!$this->conn) { return []; }

        try {
            return $this->disciplinaDAO->listarPorProfessor($siape_prof);
        } catch (Exception $e) {
            error_log("DisciplinaServico->listarDisciplinasPorProfessor: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * NOVA FUNÇÃO: Serviço para listar disciplinas não atribuídas.
     */
    public function listarDisciplinasNaoAtribuidas() {
        if (!$this->conn) { return []; }

        try {
            return $this->disciplinaDAO->listarNaoAtribuidas();
        } catch (Exception $e) {
            error_log("DisciplinaServico->listarDisciplinasNaoAtribuidas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * NOVA FUNÇÃO: Serviço para atribuir um professor a uma disciplina.
     */
    public function atribuirProfessor($id_disciplina, $siape_prof) {
        if (!$this->conn) { return false; }

        try {
            return $this->disciplinaDAO->atribuirProfessor($id_disciplina, $siape_prof);
        } catch (Exception $e) {
            error_log("DisciplinaServico->atribuirProfessor: " . $e->getMessage());
            return false;
        }
    }
    
    public function buscarDisciplina($id) {
        if (!$this->conn) { return false; }

        try {
            return $this->disciplinaDAO->buscarPorId($id);
        } catch (Exception $e) {
            error_log("DisciplinaServico->buscarDisciplina: " . $e->getMessage());
            return false;
        }
    }
    
    public function atualizarDisciplina($id, $nome, $ch, $aulas_semanais) {
        if (!$this->conn) { return false; }

        try {
            return $this->disciplinaDAO->atualizar($id, $nome, $ch, $aulas_semanais);
        } catch (Exception $e) {
            error_log("DisciplinaServico->atualizarDisciplina: " . $e->getMessage());
            return false;
        }
    }

    public function excluirDisciplina($id) {
        if (!$this->conn) { return false; }

        try {
            return $this->disciplinaDAO->excluir($id);
        } catch (Exception $e) {
            error_log("DisciplinaServico->excluirDisciplina: " . $e->getMessage());
            return false;
        }
    }
}
?>