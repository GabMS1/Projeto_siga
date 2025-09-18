<?php
// C:\xampp\htdocs\Projeto_siga-1\Projeto_siga\negocio\TurmaServico.php

// Inclui o TurmaDAO para operações de banco de dados.
require_once __DIR__ . '/../DAO/TurmaDAO.php';
// Inclui o DisciplinaServico para poder listar as disciplinas disponíveis.
require_once __DIR__ . '/DisciplinaServico.php';

/**
 * A classe TurmaServico lida com a lógica de negócio relacionada às turmas,
 * como validação de dados, unicidade do ID e orquestração do cadastro e listagem.
 */
class TurmaServico {
    public function listarTodasAsTurmas() {
        $turmaDAO = new TurmaDAO();
        return $turmaDAO->buscarTodas();
    }
    // Propriedades para armazenar os dados da turma no nível de serviço.
    public $id_turma;
    public $curso;
    public $serie;
    public $id_disciplina;
    public $siape_prof;

    /**
     * Define um valor para uma propriedade da classe.
     * @param string $prop Nome da propriedade.
     * @param mixed $value O valor a ser atribuído.
     */
    public function set($prop, $value) {
        $this->$prop = $value;
    }

    /**
     * Realiza o cadastro de uma nova turma.
     * @return bool True se o cadastro for bem-sucedido, false caso contrário.
     */
    public function cadastrar() {
        $turmaDAO = new TurmaDAO();

        // Define as propriedades no objeto DAO com os dados recebidos.
        $turmaDAO->set("id_turma", (int)$this->id_turma); // Converte para INT
        $turmaDAO->set("curso", $this->curso);
        $turmaDAO->set("serie", $this->serie);
        $turmaDAO->set("id_disciplina", (int)$this->id_disciplina); // Converte para INT

        // Chama o método 'cadastrar' do TurmaDAO para salvar a turma no banco.
        return $turmaDAO->cadastrar();
    }

    /**
     * Busca a lista de disciplinas para popular o dropdown no formulário de turma.
     * @param string|null $siape_prof O SIAPE do professor logado (ou null para admin).
     * @return array Um array de disciplinas.
     */
    public function listarDisciplinasParaSelecao($siape_prof) {
        $disciplinaServico = new DisciplinaServico();
        if ($siape_prof) {
            return $disciplinaServico->listarDisciplinasPorProfessor($siape_prof);
        } else {
            return $disciplinaServico->listarDisciplinas();
        }
    }

    /**
     * Lista todas as turmas associadas a um professor específico.
     * @param string $siape_prof O SIAPE do professor para buscar as turmas.
     * @return array Um array de turmas ou um array vazio se nenhuma for encontrada.
     */
    public function listarTurmas($siape_prof) {
        $turmaDAO = new TurmaDAO();
        $turmas = $turmaDAO->buscarTurmasPorProfessor($siape_prof);
        return $turmas !== false ? $turmas : [];
    }
    
    /**
     * Busca os dados completos de uma turma para a tela de atribuição de professor.
     * @param int $id_turma O ID da turma a ser buscada.
     * @param int $id_disciplina O ID da disciplina específica dentro da turma.
     * @return array|false Retorna os dados da turma ou false se não for encontrada.
     */
    public function buscarTurmaParaEdicao($id_turma, $id_disciplina) {
        $turmaDAO = new TurmaDAO();
        return $turmaDAO->buscarTurmaCompletaPorId($id_turma, $id_disciplina);
    }

    /**
     * Lista todas as disciplinas e seus respectivos professores para um ID de turma.
     * @param int $id_turma O ID da turma.
     * @return array Retorna uma lista de disciplinas e professores.
     */
    public function listarProfessoresDaTurma($id_turma) {
        $turmaDAO = new TurmaDAO();
        return $turmaDAO->buscarDisciplinasEProfessoresPorTurmaId($id_turma);
    }
}