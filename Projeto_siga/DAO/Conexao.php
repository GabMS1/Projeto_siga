<?php
// C:\xampp\htdocs\Projeto_siga\DAO\Conexao.php

class Conexao {
    private $host = 'localhost:3306';
    private $user = 'root';
    private $pass = '';
    private $db = 'projeto_siga';
    private $mysqli_con;

    function __construct() {
        // Conexão será estabelecida apenas quando get_connection() for chamado
    }

    function get_connection() {
        // Verifica se já existe uma conexão aberta
        if ($this->mysqli_con === null || $this->mysqli_con->connect_errno) {
            $this->mysqli_con = new mysqli($this->host, $this->user, $this->pass, $this->db);
            if ($this->mysqli_con->connect_errno) {
                // Apenas loga o erro de conexão. Mensagens para o usuário são tratadas nas classes de negócio/front-end.
                error_log("Falha ao conectar ao banco de dados: (" . $this->mysqli_con->connect_errno . ") " . $this->mysqli_con->connect_error);
                return false;
            }
            // Define o conjunto de caracteres para evitar problemas de codificação
            $this->mysqli_con->set_charset("utf8mb4");
        }
        return $this->mysqli_con;
    }

    function close() {
        // Fecha a conexão se ela estiver aberta e sem erros
        if ($this->mysqli_con && !$this->mysqli_con->connect_errno) {
            $this->mysqli_con->close();
            $this->mysqli_con = null; // Limpa a referência da conexão
        }
    }
}
?>