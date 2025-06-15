<?php
// C:\xampp\htdocs\Projeto_siga\negocio\ProfessorServico.php

require_once __DIR__ . '/../DAO/ProfessorDAO.php';

class Professor {
    public $siape_prof;
    public $nome;
    public $senha;

    public function set($prop, $value) {
        $this->$prop = $value;
    }

    public function cadastrar() {
        $professorDAO = new ProfessorDAO();
        $professorDAO->set("siape_prof", $this->siape_prof);
        $professorDAO->set("nome", $this->nome);
        $professorDAO->set("senha", $this->senha);

        return $professorDAO->cadastrar();
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
}
?>