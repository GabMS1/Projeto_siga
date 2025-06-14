<?php
// C:\xampp\htdocs\Projeto_siga\negocio\ProfessorServico.php

// Inclui a classe DAO, pois a camada de serviço a utiliza para interagir com o banco
require_once __DIR__ . '/../DAO/ProfessorDAO.php';

class Professor { // Nome da classe ProfessorServico para refletir seu papel como serviço
    public $siape_prof;
    public $nome;
    public $senha;

    // Método setter genérico
    public function set($prop, $value) {
        $this->$prop = $value;
    }

    /**
     * Tenta cadastrar um novo professor no sistema.
     * @return bool True se o cadastro foi bem-sucedido, false caso contrário.
     */
    public function cadastrar() {
        $professorDAO = new ProfessorDAO();
        // Passa os dados para o objeto DAO
        $professorDAO->set("siape_prof", $this->siape_prof);
        $professorDAO->set("nome", $this->nome);
        $professorDAO->set("senha", $this->senha); // A senha será hashada dentro do DAO

        // O ProfessorDAO já trata a verificação de SIAPE existente e define a mensagem de erro
        return $professorDAO->cadastrar();
    }

    /**
     * Tenta autenticar um professor no sistema.
     * @param string $siape_prof O SIAPE do professor a ser autenticado.
     * @param string $senha_digitada A senha fornecida pelo usuário.
     * @return bool True se a autenticação foi bem-sucedida, false caso contrário.
     */
    public function autenticar($siape_prof, $senha_digitada) {
        $professorDAO = new ProfessorDAO();
        // Busca a senha hash armazenada para o SIAPE fornecido
        $senhaHashArmazenada = $professorDAO->buscarSenhaHashPorSiape($siape_prof);

        // Verifica se uma senha hash foi encontrada e se ela corresponde à senha digitada
        // password_verify() é a função segura para comparar uma senha pura com um hash
        if ($senhaHashArmazenada && password_verify($senha_digitada, $senhaHashArmazenada)) {
            return true; // Autenticação bem-sucedida
        }
        return false; // Falha na autenticação (SIAPE não encontrado ou senha incorreta)
    }
}
?>