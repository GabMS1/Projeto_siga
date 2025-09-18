<?php
// C:\xampp\htdocs\Projeto_siga\DAO\ProfSubsDAO.php

// Inclui o arquivo de conexão com o banco de dados.
require_once __DIR__ . '/Conexao.php';

/**
 * A classe ProfSubsDAO é responsável por todas as operações de banco de dados
 * relacionadas à tabela 'prof_subs'.
 */
class ProfSubsDAO {
    private $conn;

    /**
     * Construtor da classe que estabelece a conexão com o banco de dados.
     */
    public function __construct() {
        $conexao = new Conexao();
        $this->conn = $conexao->get_connection();
    }

    /**
     * Cadastra um novo registro de professor substituto no banco de dados.
     *
     * @param string $ass_subs A assinatura do professor substituto.
     * @param string $siape_prof O SIAPE do professor substituto.
     * @return int|bool Retorna o ID do registro inserido se for bem-sucedido, ou FALSE em caso de falha.
     */
    public function cadastrar($ass_subs, $siape_prof) {
        $sql = "INSERT INTO prof_subs (ass_subs, siape_prof) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            error_log("ProfSubsDAO->cadastrar: Erro ao preparar query - " . $this->conn->error);
            return false;
        }

        $stmt->bind_param("ss", $ass_subs, $siape_prof);
        $result = $stmt->execute();
        
        if ($result) {
            $last_id = $this->conn->insert_id;
            $stmt->close();
            return $last_id;
        } else {
            error_log("ProfSubsDAO->cadastrar: Erro ao executar query - " . $stmt->error);
            $stmt->close();
            return false;
        }
    }
    
    /**
     * Fecha a conexão com o banco de dados.
     */
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>