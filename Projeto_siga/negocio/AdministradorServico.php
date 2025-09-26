<?php
// C:\xampp\htdocs\Projeto_siga-1\Projeto_siga\negocio\AdministradorServico.php

// Inclui o arquivo AdministradorDAO, que contém a lógica de interação com o banco de dados para administradores.
require_once __DIR__ . '/../DAO/AdministradorDAO.php';
require_once __DIR__ . '/../DAO/Conexao.php';

// A classe AdministradorServico lida com as regras de negócio para administradores,
// como criar novos administradores e autenticá-los.
class AdministradorServico {
    private $administradorDAO;
    private $conn;

    public function __construct() {
        $this->conn = Conexao::get_connection();
        if ($this->conn) {
            $this->administradorDAO = new AdministradorDAO($this->conn);
        }
    }

    // O método set pode ser removido se os dados forem passados diretamente para os métodos de serviço,
    // o que é uma prática mais limpa.

    /**
     * Define um valor para uma propriedade específica da classe.
     * Permite atribuir dados do formulário às propriedades da classe.
     * @param string $prop Nome da propriedade a ser definida.
     * @param mixed $value O valor a ser atribuído.
     */
    // public function set($prop, $value) { ... }

    /**
     * Gerencia o processo de criação de um novo administrador.
     * A senha é hashed antes de ser enviada para o DAO.
     * @param string $siape_adm O SIAPE que o usuário digitou no formulário de cadastro.
     * @param string $senha A senha em texto puro digitada.
     * @param string $nome O nome completo do administrador.
     * @param string $cargo O cargo do administrador.
     * @return bool Retorna TRUE se o administrador foi criado com sucesso, FALSE caso contrário.
     */
    public function criarAdministrador($siape_adm, $senha, $nome, $cargo) {
        if (!$this->conn) {
            $_SESSION['cadastro_error'] = "Erro de conexão com o banco de dados.";
            return false;
        }

        try {
            $this->administradorDAO->set("siape_login", $siape_adm);
            $this->administradorDAO->set("nome", $nome);
            $this->administradorDAO->set("senha_adm", password_hash($senha, PASSWORD_DEFAULT));
            $this->administradorDAO->set("cargo", $cargo);

            return $this->administradorDAO->cadastrar();
        } catch (Exception $e) {
            error_log("AdministradorServico->criarAdministrador: " . $e->getMessage());
            $_SESSION['cadastro_error'] = "Ocorreu um erro interno ao criar o administrador.";
            return false;
        }
    }

    /**
     * Realiza a autenticação de um administrador.
     * Compara a senha digitada com o hash da senha armazenado no banco.
     * @param string $siape_adm O SIAPE fornecido pelo usuário na tentativa de login.
     * @param string $senha_digitada A senha em texto puro digitada.
     * @return array|false Retorna um array com 'siape', 'nome' e 'cargo' do administrador se a autenticação for bem-sucedida,
     * ou FALSE se as credenciais forem inválidas.
     */
    public function autenticar($siape_adm, $senha_digitada) {
        if (!$this->conn) {
            error_log("AdministradorServico->autenticar: Falha na conexão.");
            return false;
        }

        try {
            $adminData = $this->administradorDAO->buscarAdminParaLogin($siape_adm);

            if ($adminData && password_verify($senha_digitada, $adminData['senha_adm'])) {
                return [
                    'siape' => $siape_adm,
                    'nome' => $adminData['nome'],
                    'cargo' => $adminData['cargo']
                ];
            }
            return false;
        } catch (Exception $e) {
            error_log("AdministradorServico->autenticar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lista todos os administradores.
     * @return array Um array com todos os administradores ou um array vazio em caso de erro.
     */
    public function listarAdministradores() {
        if (!$this->conn) {
            return [];
        }
        return $this->administradorDAO->listarTodos();
    }
}
?>