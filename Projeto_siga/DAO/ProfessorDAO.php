<?php
// Caminho corrigido para Conexao
require_once __DIR__ . '/Conexao.php';

class ProfessorDAO {
    public $siape_prof;
    public $nome;
    public $senha; // Usada para receber a senha pura antes do hash

    public function set($prop, $value) {
        $this->$prop = $value;
    }

    // MÉTODO DE CADASTRO
    public function cadastrar() {
        $conexao = new Conexao();
        $conn = $conexao->get_connection(); // Obtém a conexão

        if (!$conn) {
            // A Conexao já trata o erro, então apenas retornamos false aqui
            return false;
        }

        // Hashing da senha para segurança (mínimo necessário para armazenar senha)
        $senhaHash = password_hash($this->senha, PASSWORD_DEFAULT);

        // Uso de Prepared Statements para segurança (mínimo necessário)
        $SQL = "INSERT INTO professor (siape_prof, nome, senha) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($SQL);

        if (!$stmt) {
            // Logar o erro para depuração
            error_log("Erro ao preparar a query de cadastro: " . $conn->error);
            $_SESSION['cadastro_error'] = "Erro interno ao preparar o cadastro."; // Mensagem para o usuário
            $conexao->close();
            return false;
        }

        $stmt->bind_param("sss", $this->siape_prof, $this->nome, $senhaHash);
        $success = $stmt->execute();

        if (!$success) {
            error_log("Erro ao executar a query de cadastro: " . $stmt->error);
            // Verifica se o erro é de chave duplicada (SIAPE já existe)
            if ($stmt->errno == 1062) { // Código de erro para chave duplicada no MySQL
                $_SESSION['cadastro_error'] = "SIAPE já cadastrado.";
            } else {
                $_SESSION['cadastro_error'] = "Erro no banco de dados durante o cadastro.";
            }
        }

        $stmt->close();
        $conexao->close();
        return $success;
    }

    // ADIÇÃO MÍNIMA PARA FUNCIONAR O LOGIN: Método para buscar a senha hash
    public function buscarSenhaHashPorSiape($siape_prof) {
        $conexao = new Conexao();
        $conn = $conexao->get_connection();
        if (!$conn) {
            return false;
        }

        $SQL = "SELECT senha FROM professor WHERE siape_prof = ?";
        $stmt = $conn->prepare($SQL);

        if (!$stmt) {
            error_log("Erro ao preparar busca de senha hash: " . $conn->error);
            $conexao->close();
            return false;
        }

        $stmt->bind_param("s", $siape_prof);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $stmt->close();
        $conexao->close();
        return $row['senha'] ?? false; // Retorna a senha hash ou false se não encontrado
    }

    // ADIÇÃO MÍNIMA PARA FUNCIONAR O CADASTRO COM VERIFICAÇÃO DE UNICIDADE
    public function buscarPorSiape($siape_prof) {
        $conexao = new Conexao();
        $conn = $conexao->get_connection();
        if (!$conn) {
            return false;
        }

        $SQL = "SELECT siape_prof FROM professor WHERE siape_prof = ?";
        $stmt = $conn->prepare($SQL);

        if (!$stmt) {
            error_log("Erro ao preparar busca por SIAPE no ProfessorDAO: " . $conn->error);
            $conexao->close();
            return false;
        }

        $stmt->bind_param("s", $siape_prof);
        $stmt->execute();
        $result = $stmt->get_result();
        $num_rows = $result->num_rows;

        $stmt->close();
        $conexao->close();
        return $num_rows > 0; // Retorna true se encontrou, false caso contrário
    }
}
?>