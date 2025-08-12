<?php
// C:\xampp\htdocs\Projeto_siga-1\Projeto_siga\negocio\DisciplinaServico.php

// Inclui o DisciplinaDAO, responsável por operar diretamente no banco de dados.
// O caminho "__DIR__ . '/../DAO/DisciplinaDAO.php'" está correto se este arquivo
// DisciplinaServico.php estiver na pasta 'negocio' e DisciplinaDAO.php na pasta 'DAO'.
require_once __DIR__ . '/../DAO/DisciplinaDAO.php';

/**
 * A classe DisciplinaServico lida com a lógica de negócio relacionada às disciplinas,
 * como validação de dados e orquestração do cadastro e listagem.
 */
class DisciplinaServico {
    // Propriedades para armazenar os dados da disciplina no nível de serviço.
    public $nome_disciplina;
    public $ch; // Carga Horária (espera uma string no formato 'HH:MM:SS')
    public $siape_prof; // SIAPE do professor que está cadastrando (espera um inteiro)

    /**
     * Define um valor para uma propriedade da classe.
     * @param string $prop Nome da propriedade (ex: "nome_disciplina", "ch", "siape_prof").
     * @param mixed $value O valor a ser atribuído.
     */
    public function set($prop, $value) {
        $this->$prop = $value;
    }

    /**
     * Realiza o cadastro de uma nova disciplina.
     * Inclui validações antes de persistir os dados.
     * @return bool True se o cadastro for bem-sucedido, false caso contrário.
     */
    public function cadastrar() {
        // --- Validações de Negócio (ex: verificar carga horária, etc.) ---
        // Você pode adicionar lógicas de validação mais complexas aqui.
        // Por exemplo, se a carga horária for um número fora de um intervalo aceitável.

        // Instancia o objeto de acesso a dados para disciplinas.
        $disciplinaDAO = new DisciplinaDAO();
        
        // Define as propriedades no objeto DAO com os dados que vieram do formulário
        // e já foram setados neste objeto DisciplinaServico.
        $disciplinaDAO->set("nome_disciplina", $this->nome_disciplina);
        $disciplinaDAO->set("ch", $this->ch);
        $disciplinaDAO->set("siape_prof", $this->siape_prof);

        // Chama o método 'cadastrar' do DisciplinaDAO para salvar a disciplina no banco.
        return $disciplinaDAO->cadastrar();
    }

    /**
     * Lista todas as disciplinas associadas a um professor específico.
     * Este método chama o DAO para buscar os dados.
     * @param int $siape_prof O SIAPE do professor para buscar as disciplinas.
     * @return array Um array de disciplinas ou um array vazio se nenhuma for encontrada.
     * Em caso de falha na conexão ou na query, o DAO pode retornar false,
     * mas aqui retornamos um array vazio para simplificar o tratamento na tela.
     */
    public function listarDisciplinas($siape_prof) {
        $disciplinaDAO = new DisciplinaDAO();
        // Chama o método no DAO para buscar as disciplinas pelo SIAPE do professor.
        $disciplinas = $disciplinaDAO->buscarDisciplinasPorProfessor($siape_prof);
        
        // Garante que sempre retornemos um array, mesmo que o DAO retorne false.
        return $disciplinas !== false ? $disciplinas : [];
    }
}
?>
