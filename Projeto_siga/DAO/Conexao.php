<?php
// C:\xampp\htdocs\Projeto_siga\DAO\Conexao.php

class Conexao {
    private $host = 'localhost:3306';
    private $user = 'root';
    private $pass = '';
    private $db = 'projeto_siga';
    private $mysqli_con;

    function __construct() {
    }

    function get_connection() {
        if ($this->mysqli_con === null || $this->mysqli_con->connect_errno) {
            $this->mysqli_con = new mysqli($this->host, $this->user, $this->pass, $this->db);
            if ($this->mysqli_con->connect_errno) {
                error_log("Falha ao conectar ao banco de dados: (" . $this->mysqli_con->connect_errno . ") " . $this->mysqli_con->connect_error);
                return false;
            }
            $this->mysqli_con->set_charset("utf8mb4");
        }
        return $this->mysqli_con;
    }

    function close() {
        if ($this->mysqli_con && !$this->mysqli_con->connect_errno) {
            $this->mysqli_con->close();
            $this->mysqli_con = null;
        }
    }
}
?>