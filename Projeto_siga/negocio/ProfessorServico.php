<?php
// C:\xampp\htdocs\Projeto_siga-1\Projeto_siga\negocio\ProfessorServico.php

// Inclui o arquivo do ProfessorDAO, que é responsável por interagir com o banco de dados para professores.
require_once __DIR__ . '/../DAO/ProfessorDAO.php';

// A classe ProfessorServico lida com a lógica de negócio relacionada aos professores,
// como cadastrar novos professores e autenticá-los.
class ProfessorServico {
    public $siape_prof;
    public $nome;
    public $senha;

    /**
     * Define um valor para uma propriedade da classe.
     * @param string $prop Nome da propriedade.
     * @param mixed $value Valor a ser definido.
     */
    public function set($prop, $value) {
        $this->$prop = $value;
    }

    /**
     * Realiza o cadastro de um novo professor.
     * A senha é hashed antes de ser enviada para o DAO para segurança.
     * @return bool True se o cadastro for bem-sucedido, false caso contrário.
     */
    public function cadastrar() {
        $professorDAO = new ProfessorDAO(); // Instancia o objeto de acesso a dados para professores.
        $professorDAO->set("siape_prof", $this->siape_prof);
        $professorDAO->set("nome", $this->nome);
        // CRUCIAL: A senha é hashed (codificada) antes de ser enviada para o banco de dados.
        // Isso impede que a senha seja armazenada em texto puro, aumentando a segurança.
        $professorDAO->set("senha", password_hash($this->senha, PASSWORD_DEFAULT)); 

        return $professorDAO->cadastrar(); // Chama o método cadastrar no DAO.
    }

    /**
     * Autentica um professor com base no SIAPE e senha digitada.
     * Compara a senha digitada com o hash da senha armazenado no banco.
     * @param string $siape_prof SIAPE do professor.
     * @param string $senha_digitada Senha informada pelo usuário.
     * @return array|false Um array com siape e nome do professor se a autenticação for bem-sucedida, false caso contrário.
     */
    public function autenticar($siape_prof, $senha_digitada) {
        $professorDAO = new ProfessorDAO(); // Instancia o objeto de acesso a dados.
        // Busca os dados do professor (incluindo o hash da senha) no banco.
        $professorData = $professorDAO->buscarProfessorParaLogin($siape_prof);

        // Verifica se encontrou o professor E se a senha digitada corresponde ao hash armazenado.
        if ($professorData && password_verify($senha_digitada, $professorData['senha'])) {
            return [
                'siape' => $siape_prof,
                'nome' => $professorData['nome']
            ];
        }
        return false; // Retorna false se a autenticação falhar.
    }
}
?>
