<?php
// C:\xampp\htdocs\Projeto_siga\DAO\ProfAusenteDAO.php

// Inclui o arquivo de conexão com o banco de dados.
require_once __DIR__ . '/Conexao.php';

/**
 * A classe ProfAusenteDAO é responsável por todas as operações de banco de dados
 * relacionadas à tabela 'prof_ausente'.
 */
class ProfAusenteDAO {
    private $conn;

    /**
     * Construtor da classe que estabelece a conexão com o banco de dados.
     */
    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }

    /**
     * Cadastra um novo registro de professor ausente no banco de dados.
     *
     * @param string $ass_ausente A assinatura do professor ausente.
     * @param string $siape_prof O SIAPE do professor ausente.
     * @return int|bool Retorna o ID do registro inserido se for bem-sucedido, ou FALSE em caso de falha.
     */
    public function cadastrar($ass_ausente, $siape_prof) {
        $sql = "INSERT INTO prof_ausente (ass_ausente, siape_prof) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            error_log("ProfAusenteDAO->cadastrar: Erro ao preparar query - " . $this->conn->error);
            return false;
        }

        $stmt->bind_param("ss", $ass_ausente, $siape_prof);
        $result = $stmt->execute();
        
        if ($result) {
            $last_id = $this->conn->insert_id;
            $stmt->close();
            return $last_id;
        } else {
            error_log("ProfAusenteDAO->cadastrar: Erro ao executar query - " . $stmt->error);
            $stmt->close();
            return false;
        }
    }
    
    /**
     * Busca o ID e a assinatura de um professor ausente pelo seu SIAPE.
     *
     * @param string $siape_prof O SIAPE do professor a ser buscado.
     * @return array|false Retorna um array associativo com os dados, ou FALSE se não for encontrado.
     */
    public function buscarPorSiape($siape_prof) {
        $sql = "SELECT id_ass_ausente, ass_ausente FROM prof_ausente WHERE siape_prof = ?";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            error_log("ProfAusenteDAO->buscarPorSiape: Erro ao preparar query - " . $this->conn->error);
            return false;
        }

        $stmt->bind_param("s", $siape_prof);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data;
    }
}
?>