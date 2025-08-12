<?php
// C:\xampp\htdocs\Projeto_siga-1\Projeto_siga\DAO\DisciplinaDAO.php

// Inclui o arquivo de conexão com o banco de dados.
// Este caminho '__DIR__ . '/Conexao.php'' está correto se este arquivo
// (DisciplinaDAO.php) estiver na mesma pasta (DAO) que Conexao.php.
require_once __DIR__ . '/Conexao.php';

/**
 * A classe DisciplinaDAO é responsável por todas as operações de banco de dados
 * relacionadas às disciplinas (tabela 'disciplina').
 */
class DisciplinaDAO {
    // Propriedades para armazenar os dados da disciplina.
    public $nome_disciplina;
    public $ch; // Carga Horária, tratada como STRING (TIME)
    public $siape_prof; // SIAPE do professor, tratado como INT

    /**
     * Define um valor para uma propriedade específica do DAO.
     * @param string $prop Nome da propriedade a ser definida (ex: "nome_disciplina", "ch", "siape_prof").
     * @param mixed $value O valor a ser atribuído à propriedade.
     */
    public function set($prop, $value) {
        $this->$prop = $value;
    }

    /**
     * Cadastra uma nova disciplina no banco de dados.
     * @return bool True se o cadastro for bem-sucedido, false caso contrário.
     */
    public function cadastrar() {
        $conexao = new Conexao(); // Cria uma nova instância da classe de conexão.
        $conn = $conexao->get_connection(); // Obtém o objeto de conexão MySQLi.

        if (!$conn) {
            error_log("DisciplinaDAO - Falha na conexão com o banco de dados.");
            $_SESSION['cadastro_disciplina_error'] = "Erro interno do servidor. Tente novamente mais tarde.";
            return false;
        }

        // Prepara a consulta SQL para inserir uma nova disciplina.
        // id_disciplina é AUTO_INCREMENT, então não o incluímos na inserção.
        $SQL = "INSERT INTO disciplina (nome_disciplina, ch, siape_prof) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($SQL); // Prepara a query para prevenir SQL Injection.

        if (!$stmt) {
            error_log("DisciplinaDAO - Erro ao preparar a query de cadastro: " . $conn->error);
            $_SESSION['cadastro_disciplina_error'] = "Erro interno ao preparar o cadastro da disciplina.";
            $conexao->close();
            return false;
        }

        // ATENÇÃO: bind_param é "ssi"
        // 's' para nome_disciplina (string)
        // 's' para ch (string, pois é do tipo TIME no DB)
        // 'i' para siape_prof (int, pois é do tipo INT no DB)
        $stmt->bind_param("ssi", $this->nome_disciplina, $this->ch, $this->siape_prof);
        $success = $stmt->execute(); // Executa a query.

        if (!$success) {
            error_log("DisciplinaDAO - Erro ao executar a query de cadastro: " . $stmt->error);
            // Verifica se o erro é devido a uma entrada duplicada ou chave estrangeira inválida.
            if ($stmt->errno == 1062) { // Código de erro para entrada duplicada no MySQL.
                $_SESSION['cadastro_disciplina_error'] = "Já existe uma disciplina com este nome ou o SIAPE do professor é inválido.";
            } else {
                $_SESSION['cadastro_disciplina_error'] = "Erro no banco de dados durante o cadastro da disciplina.";
            }
        }

        $stmt->close(); // Fecha o statement.
        $conexao->close(); // Fecha a conexão com o banco de dados.
        return $success; // Retorna true ou false.
    }

    /**
     * Busca todas as disciplinas associadas a um professor específico.
     * @param int $siape_prof O SIAPE do professor.
     * @return array|false Um array de arrays associativos com os dados das disciplinas,
     * ou um array vazio se nenhuma disciplina for encontrada, ou false em caso de erro na conexão.
     */
    public function buscarDisciplinasPorProfessor($siape_prof) {
        $conexao = new Conexao();
        $conn = $conexao->get_connection();
        if (!$conn) {
            error_log("DisciplinaDAO - Falha na conexão ao buscar disciplinas para professor.");
            // Definir mensagem de erro na sessão para exibição na tela.
            $_SESSION['listar_disciplinas_error'] = "Erro ao conectar ao banco de dados para listar disciplinas.";
            return false;
        }

        // Query para selecionar todas as disciplinas de um dado professor.
        $SQL = "SELECT id_disciplina, nome_disciplina, ch FROM disciplina WHERE siape_prof = ?";
        $stmt = $conn->prepare($SQL);

        if (!$stmt) {
            error_log("DisciplinaDAO - Erro ao preparar busca de disciplinas por professor: " . $conn->error);
            $_SESSION['listar_disciplinas_error'] = "Erro interno ao preparar a busca de disciplinas.";
            $conexao->close();
            return false;
        }

        $stmt->bind_param("i", $siape_prof); // Binda o SIAPE do professor como inteiro.
        $stmt->execute();
        $result = $stmt->get_result(); // Obtém o resultado da consulta.

        $disciplinas = [];
        while ($row = $result->fetch_assoc()) {
            $disciplinas[] = $row; // Adiciona cada linha (disciplina) ao array.
        }

        $stmt->close();
        $conexao->close();
        return $disciplinas; // Retorna o array de disciplinas (pode estar vazio se nenhuma for encontrada).
    }
}
?>
