﻿<?php
// C:\xampp\htdocs\Projeto_siga\negocio\TurmaServico.php

require_once __DIR__ . '/../DAO/TurmaDAO.php';
require_once __DIR__ . '/../DAO/Conexao.php';

class TurmaServico {
    private $turmaDAO;

    public function __construct() {
        $db_connection = Conexao::get_connection();
        $this->turmaDAO = new TurmaDAO($db_connection);
    }

    public function listarTodasAsTurmas() {
        return $this->turmaDAO->buscarTodas();
    }

    public function cadastrarTurma($id_turma, $curso, $serie) {
        return $this->turmaDAO->cadastrarTurma($id_turma, $curso, $serie);
    }

    public function associarDisciplina($id_turma, $id_disciplina) {
        $this->turmaDAO->set('id_turma', $id_turma);
        $this->turmaDAO->set('id_disciplina', $id_disciplina);
        return $this->turmaDAO->associarDisciplina();
    }

    // CORREÇÃO CRÍTICA: Método que faltava, chamando o DAO correto
    public function listarDetalhesDaTurma($id_turma) {
        return $this->turmaDAO->buscarDisciplinasEProfessoresPorTurmaId($id_turma);
    }

    public function buscarTurmasPorProfessor($siape_prof) {
        return $this->turmaDAO->buscarTurmasPorProfessor($siape_prof);
    }
}
?>