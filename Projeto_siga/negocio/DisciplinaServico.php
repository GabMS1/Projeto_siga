<?php
// C:\xampp\htdocs\Projeto_siga\negocio\DisciplinaServico.php

require_once __DIR__ . '/../DAO/DisciplinaDAO.php';

class DisciplinaServico {
    public $id_disciplina;
    public $nome_disciplina;
    public $ch;
    public $siape_prof;

    public function set($prop, $value) {
        $this->$prop = $value;
    }

    public function cadastrar() {
        $disciplinaDAO = new DisciplinaDAO();
        return $disciplinaDAO->cadastrar($this->nome_disciplina, $this->ch, $this->siape_prof);
    }
    
    public function listarDisciplinas() {
        $disciplinaDAO = new DisciplinaDAO();
        return $disciplinaDAO->listarTodos();
    }
    
    // NOVO MÉTODO
    public function listarDisciplinasPorProfessor($siape_prof) {
        $disciplinaDAO = new DisciplinaDAO();
        return $disciplinaDAO->listarPorProfessor($siape_prof);
    }
    
    public function buscarDisciplina($id) {
        $disciplinaDAO = new DisciplinaDAO();
        return $disciplinaDAO->buscarPorId($id);
    }
    
    public function atualizarDisciplina($id, $nome, $ch) {
        $disciplinaDAO = new DisciplinaDAO();
        return $disciplinaDAO->atualizar($id, $nome, $ch);
    }

    public function excluirDisciplina($id) {
        $disciplinaDAO = new DisciplinaDAO();
        return $disciplinaDAO->excluir($id);
    }
}