<?php
// C:\xampp\htdocs\Projeto_siga-1\Projeto_siga\negocio\TurmaServico.php

// Inclui o TurmaDAO para operações de banco de dados.
require_once __DIR__ . '/../DAO/TurmaDAO.php';
// Inclui o DisciplinaServico para poder listar as disciplinas disponíveis.
// Nota: O caminho '__DIR__ . '/DisciplinaServico.php'' está correto porque
// DisciplinaServico.php e TurmaServico.php estão na mesma pasta 'negocio'.
require_once __DIR__ . '/DisciplinaServico.php';

/**
 * A classe TurmaServico lida com a lógica de negócio relacionada às turmas,
 * como validação de dados, unicidade do ID e orquestração do cadastro e listagem.
 */
class TurmaServico {
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
     * Inclui validações de unicidade do ID da turma e existência das chaves estrangeiras.
     * @return bool True se o cadastro for bem-sucedido, false caso contrário.
     */
    public function cadastrar() {
        $turmaDAO = new TurmaDAO();

        // Validação de unicidade do ID da turma, já que não é AUTO_INCREMENT.
        if ($turmaDAO->buscarPorId((int)$this->id_turma)) {
            $_SESSION['cadastro_turma_error'] = "O ID da turma '" . htmlspecialchars($this->id_turma) . "' já existe. Por favor, escolha outro.";
            return false;
        }

        // Define as propriedades no objeto DAO com os dados recebidos.
        $turmaDAO->set("id_turma", (int)$this->id_turma); // Converte para INT
        $turmaDAO->set("curso", $this->curso);
        $turmaDAO->set("serie", $this->serie);
        $turmaDAO->set("id_disciplina", (int)$this->id_disciplina); // Converte para INT
        $turmaDAO->set("siape_prof", (int)$this->siape_prof);       // Converte para INT

        // Chama o método 'cadastrar' do TurmaDAO para salvar a turma no banco.
        return $turmaDAO->cadastrar();
    }

    /**
     * Busca a lista de disciplinas para popular o dropdown no formulário de turma.
     * Reutiliza o DisciplinaServico para isso.
     * @param int $siape_prof O SIAPE do professor logado.
     * @return array Um array de disciplinas.
     */
    public function listarDisciplinasParaSelecao($siape_prof) {
        $disciplinaServico = new DisciplinaServico();
        return $disciplinaServico->listarDisciplinas($siape_prof);
    }

    /**
     * Lista todas as turmas associadas a um professor específico.
     * Este método chama o DAO para buscar os dados.
     * @param int $siape_prof O SIAPE do professor para buscar as turmas.
     * @return array Um array de turmas ou um array vazio se nenhuma for encontrada.
     */
    public function listarTurmas($siape_prof) {
        $turmaDAO = new TurmaDAO();
        // Chama o método no DAO para buscar as turmas pelo SIAPE do professor.
        $turmas = $turmaDAO->buscarTurmasPorProfessor($siape_prof);
        
        // Garante que sempre retornemos um array, mesmo que o DAO retorne false.
        return $turmas !== false ? $turmas : [];
    }
}
?>
