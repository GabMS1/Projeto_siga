<?php
// C:\xampp\htdocs\Projeto_siga-1\Projeto_siga\negocio\AdministradorServico.php

// Inclui o arquivo AdministradorDAO, que contém a lógica de interação com o banco de dados para administradores.
require_once __DIR__ . '/../DAO/AdministradorDAO.php';

// A classe AdministradorServico lida com as regras de negócio para administradores,
// como criar novos administradores e autenticá-los.
class AdministradorServico {
    public $siape_adm; // Propriedade para armazenar o SIAPE do administrador (este é o campo do formulário)
    public $nome;       // Propriedade para armazenar o nome do administrador
    public $senha;      // Propriedade para armazenar a senha do administrador (texto puro antes do hash)
    public $cargo;      // Propriedade para armazenar o cargo do administrador

    /**
     * Define um valor para uma propriedade específica da classe.
     * Permite atribuir dados do formulário às propriedades da classe.
     * @param string $prop Nome da propriedade a ser definida.
     * @param mixed $value O valor a ser atribuído.
     */
    public function set($prop, $value) {
        $this->$prop = $value;
    }

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
        $administradorDAO = new AdministradorDAO(); // Cria uma instância do DAO.
        
        // Define as propriedades no objeto DAO com os dados recebidos do formulário.
        $administradorDAO->set("siape_login", $siape_adm); // NOVO: Mapeia o SIAPE do formulário para 'siape_login' no DAO.
                                                          // Lembre-se de adicionar 'siape_login' à sua tabela 'admin'!
        $administradorDAO->set("nome", $nome);
        
        // CRUCIAL: A senha é hashed antes de ser armazenada no banco de dados.
        $administradorDAO->set("senha_adm", password_hash($senha, PASSWORD_DEFAULT)); // Mapeia para 'senha_adm' no DAO
        $administradorDAO->set("cargo", $cargo);

        // Chama o método cadastrar do AdministradorDAO para salvar os dados.
        return $administradorDAO->cadastrar();
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
        $administradorDAO = new AdministradorDAO(); // Cria uma instância do DAO.
        
        // Busca os dados do administrador no banco de dados com base no SIAPE de login.
        $adminData = $administradorDAO->buscarAdminParaLogin($siape_adm);

        // Verifica se um administrador foi encontrado E se a senha digitada corresponde ao hash da senha armazenada.
        if ($adminData && password_verify($senha_digitada, $adminData['senha_adm'])) { // Usa 'senha_adm' da sua tabela
            // Se a autenticação for bem-sucedida, retorna um array com as informações do administrador.
            return [
                'siape' => $siape_adm, // Retorna o SIAPE que foi usado para login
                'nome' => $adminData['nome'],
                'cargo' => $adminData['cargo']
            ];
        }
        // Se o administrador não for encontrado ou a senha não corresponder, retorna FALSE.
        return false;
    }
}
?>
