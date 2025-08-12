<?php
// C:\xampp\htdocs\Projeto_siga\negocio\ProfessorServico.php

require_once __DIR__ . '/../DAO/ProfessorDAO.php';

class ProfessorServico {
    public $siape_prof;
    public $nome;
    public $senha;

    public function set($prop, $value) {
        $this->$prop = $value;
    }

    public function cadastrar() {
        $professorDAO = new ProfessorDAO();
        $senha_hash = password_hash($this->senha, PASSWORD_DEFAULT); 
        return $professorDAO->cadastrar($this->siape_prof, $this->nome, $senha_hash);
    }

    public function autenticar($siape_prof, $senha_digitada) {
        $professorDAO = new ProfessorDAO();
        $professorData = $professorDAO->buscarProfessorParaLogin($siape_prof);

        if ($professorData && password_verify($senha_digitada, $professorData['senha'])) {
            return [
                'siape' => $siape_prof,
                'nome' => $professorData['nome']
            ];
        }
        return false;
    }

    public function listarProfessores() {
        $professorDAO = new ProfessorDAO();
        return $professorDAO->listarTodos();
    }
    
    public function excluirProfessor($siape_prof) {
        $professorDAO = new ProfessorDAO();
        return $professorDAO->excluir($siape_prof);
    }

    public function buscarProfessor($siape_prof) {
        $professorDAO = new ProfessorDAO();
        return $professorDAO->buscarPorSiape($siape_prof);
    }
    
    public function atualizarProfessor($siape_prof, $nome, $nova_senha = null) {
        $professorDAO = new ProfessorDAO();
        $senha_hash = null;
        if ($nova_senha) {
            $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        }
        return $professorDAO->atualizar($siape_prof, $nome, $senha_hash);
    }
}