<?php
// C:\xampp\htdocs\Projeto_siga-1\Projeto_siga\DAO\AdministradorDAO.php

// Inclui o arquivo de conexão com o banco de dados.
require_once __DIR__ . '/Conexao.php';

// A classe AdministradorDAO é responsável por todas as operações de banco de dados
// para a entidade 'admin' (administradores).
class AdministradorDAO {
    // Propriedades para armazenar os dados do administrador que serão usados nas operações.
    // 'siape_login' é a coluna que você ADICIONOU à sua tabela 'admin' para o SIAPE do usuário.
    public $siape_login; 
    public $nome;
    public $senha_adm; // Propriedade para armazenar o hash da senha (nome da coluna no seu DB)
    public $cargo;

    /**
     * Define um valor para uma propriedade específica do DAO.
     * Usado para popular o objeto DAO antes de realizar operações de banco de dados.
     * @param string $prop Nome da propriedade (ex: "siape_login", "nome", "senha_adm", "cargo").
     * @param mixed $value O valor a ser atribuído.
     */
    public function set($prop, $value) {
        $this->$prop = $value;
    }

    /**
     * Cadastra um novo administrador no banco de dados 'admin'.
     * @return bool Retorna TRUE se o cadastro for bem-sucedido, FALSE caso contrário.
     */
    public function cadastrar() {
        $conexao = new Conexao(); // Cria uma nova instância da classe de conexão.
        $conn = $conexao->get_connection(); // Obtém o objeto de conexão MySQLi.

        if (!$conn) {
            // Se a conexão falhar, registra um erro e retorna FALSE.
            error_log("AdministradorDAO - Falha na conexão com o banco de dados.");
            $_SESSION['cadastro_error'] = "Erro interno do servidor. Tente novamente mais tarde.";
            return false;
        }

        // Prepara a consulta SQL para inserir um novo registro na tabela 'admin'.
        // id_adm é AUTO_INCREMENT, então não o incluímos na inserção.
        // Incluímos 'siape_login' que você adicionou.
        $SQL = "INSERT INTO admin (siape_login, nome, senha_adm, cargo) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($SQL); // Prepara a query para prevenir SQL Injection.

        if (!$stmt) {
            // Se a preparação da query falhar, registra um erro e define uma mensagem.
            error_log("AdministradorDAO - Erro ao preparar a query de cadastro: " . $conn->error);
            $_SESSION['cadastro_error'] = "Erro interno ao preparar o cadastro do administrador.";
            $conexao->close();
            return false;
        }

        // Binda os parâmetros à query preparada. 'ssss' indica que todos são strings.
        $stmt->bind_param("ssss", $this->siape_login, $this->nome, $this->senha_adm, $this->cargo);
        $success = $stmt->execute(); // Executa a query.

        if (!$success) {
            // Se a execução da query falhar, registra o erro.
            error_log("AdministradorDAO - Erro ao executar a query de cadastro: " . $stmt->error);
            // Verifica se o erro é devido a uma entrada duplicada (SIAPE já existente).
            if ($stmt->errno == 1062) { // Código de erro para entrada duplicada no MySQL.
                $_SESSION['cadastro_error'] = "SIAPE já cadastrado para administrador.";
            } else {
                $_SESSION['cadastro_error'] = "Erro no banco de dados durante o cadastro do administrador.";
            }
        }

        $stmt->close(); // Fecha o statement.
        $conexao->close(); // Fecha a conexão com o banco de dados.
        return $success; // Retorna TRUE ou FALSE dependendo do sucesso da operação.
    }

    /**
     * Busca os dados de um administrador no banco de dados para fins de login.
     * Utiliza o 'siape_login' para encontrar o registro.
     * @param string $siape_login O SIAPE do administrador a ser buscado.
     * @return array|false Retorna um array associativo contendo as colunas 'senha_adm', 'nome', 'cargo'
     * se o administrador for encontrado, ou FALSE caso contrário.
     */
    public function buscarAdminParaLogin($siape_login) {
        $conexao = new Conexao();
        $conn = $conexao->get_connection();
        if (!$conn) {
            return false;
        }

        // Prepara a consulta SQL para selecionar os dados necessários para o login.
        // Busca pelo 'siape_login' (a nova coluna que você adicionou).
        $SQL = "SELECT senha_adm, nome, cargo FROM admin WHERE siape_login = ?";
        $stmt = $conn->prepare($SQL);

        if (!$stmt) {
            error_log("AdministradorDAO - Erro ao preparar busca de dados para login: " . $conn->error);
            $conexao->close();
            return false;
        }

        // Binda o SIAPE de login como parâmetro de string.
        $stmt->bind_param("s", $siape_login);
        $stmt->execute();
        $result = $stmt->get_result(); // Obtém o resultado da consulta.
        $row = $result->fetch_assoc(); // Extrai a linha como um array associativo.

        $stmt->close();
        $conexao->close();
        return $row ?? false; // Retorna a linha encontrada (ou null se não houver), ou false se for null.
    }
}
?>
