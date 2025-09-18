<?php
// C:\xampp\htdocs\Projeto_siga\DAO\Conexao.php

class Conexao {
private $host = 'db'; 
private $user = 'user_siga'; 
private $pass = 'siga_password'; 
private $db = 'projeto_siga';
    
    // Declaração da propriedade para evitar o aviso do PHP
    private $mysqli_con;

    /**
     * Obtém e retorna uma conexão ativa com o banco de dados.
     * Se a conexão ainda não foi estabelecida ou se a conexão existente encontrou um erro,
     * uma nova tentativa de conexão é feita.
     * @return mysqli|false O objeto mysqli de conexão bem-sucedida, ou FALSE em caso de falha.
     */
    function get_connection() {
        // Verifica se a conexão não existe (null) ou se há um erro na conexão existente.
        if ($this->mysqli_con === null || $this->mysqli_con->connect_errno) {
            // Tenta estabelecer uma nova conexão MySQLi.
            $this->mysqli_con = new mysqli($this->host, $this->user, $this->pass, $this->db);
            // Verifica se a tentativa de conexão resultou em um erro.
            if ($this->mysqli_con->connect_errno) {
                // Registra o erro detalhado no log de erros do PHP (útil para depuração).
                error_log("Falha ao conectar ao banco de dados: (" . $this->mysqli_con->connect_errno . ") " . $this->mysqli_con->connect_error);
                return false; // Retorna FALSE para indicar que a conexão falhou.
            }
            // Define o conjunto de caracteres para a conexão para UTF-8 (importante para caracteres especiais).
            $this->mysqli_con->set_charset("utf8mb4");
        }
        return $this->mysqli_con; // Retorna o objeto de conexão MySQLi ativo.
    }

    /**
     * Fecha a conexão ativa com o banco de dados.
     * Isso libera os recursos do banco de dados.
     */
    function close() {
        // Verifica se a conexão existe e se não há um erro ativo antes de tentar fechar.
        if ($this->mysqli_con && !$this->mysqli_con->connect_errno) {
            $this->mysqli_con->close(); // Fecha a conexão.
            $this->mysqli_con = null; // Zera o objeto de conexão para que possa ser recriado se necessário.
        }
    }
}