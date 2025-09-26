<?php
// C:\xampp\htdocs\Projeto_siga\DAO\Conexao.php

class Conexao {
    private static $instance = null;
    private $mysqli_con;

    private $host = 'db'; 
    private $user = 'user_siga'; 
    private $pass = 'siga_password'; 
    private $db = 'projeto_siga';
    
    private function __construct() {
        $this->mysqli_con = new mysqli($this->host, $this->user, $this->pass, $this->db);

        if ($this->mysqli_con->connect_errno) {
            error_log("Falha ao conectar ao banco de dados: (" . $this->mysqli_con->connect_errno . ") " . $this->mysqli_con->connect_error);
            // Em um ambiente de produção, você pode querer lançar uma exceção aqui.
            // throw new Exception("Falha na conexão com o banco de dados.");
        } else {
            $this->mysqli_con->set_charset("utf8mb4");
        }
    }

    /**
     * Obtém a instância única da conexão com o banco de dados (Singleton).
     * @return mysqli|null O objeto mysqli de conexão ou null em caso de falha.
     */
    public static function get_connection() {
        if (self::$instance === null) {
            self::$instance = new Conexao();
        }

        if (self::$instance->mysqli_con->connect_errno) {
            return null;
        }

        return self::$instance->mysqli_con;
    }

    /**
     * O método close pode ser removido ou mantido para fechar a conexão globalmente no final do script, se necessário.
     * Com Singleton, geralmente a conexão persiste durante a requisição.
     */
    // public static function close() { ... }

    private function __clone() {}
    public function __wakeup() {}
}