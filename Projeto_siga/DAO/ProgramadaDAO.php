<?php
// C:\xampp\htdocs\Projeto_siga\DAO\ProgramadaDAO.php

// Inclui o arquivo de conexão com o banco de dados.
require_once __DIR__ . '/Conexao.php';

/**
 * A classe ProgramadaDAO é responsável por todas as operações de banco de dados
 * relacionadas à tabela 'programada'.
 */
class ProgramadaDAO {
    private $conn;

    /**
     * Construtor da classe que estabelece a conexão com o banco de dados.
     */
    public function __construct() {
        $conexao = new Conexao();
        $this->conn = $conexao->get_connection();
    }

    /**
     * Cadastra um novo registro de reposição agendada no banco de dados.
     *
     * @param string $dia A data da reposição (formato 'YYYY-MM-DD').
     * @param string $horario O horário da reposição (formato 'HH:MM:SS').
     * @param string $autor_gov O nome ou identificação da autoridade governamental (opcional).
     * @param int $id_turma O ID da turma.
     * @param int $id_disciplina O ID da disciplina.
     * @param int|null $id_ass_subs O ID do registro de professor substituto, ou null se não houver.
     * @param int $id_ass_ausente O ID do registro de professor ausente.
     * @return bool Retorna TRUE se o cadastro for bem-sucedido, ou FALSE em caso de falha.
     */
    public function cadastrar($dia, $horario, $autor_gov, $id_turma, $id_disciplina, $id_ass_subs, $id_ass_ausente) {
        $sql = "INSERT INTO programada (dia, horario, autor_gov, id_turma, id_disciplina, id_ass_subs, id_ass_ausente) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            error_log("ProgramadaDAO->cadastrar: Erro ao preparar query - " . $this->conn->error);
            return false;
        }

        if ($id_ass_subs === null) {
            $stmt->bind_param("sssiisi", $dia, $horario, $autor_gov, $id_turma, $id_disciplina, $id_ass_subs, $id_ass_ausente);
        } else {
            $stmt->bind_param("sssiiii", $dia, $horario, $autor_gov, $id_turma, $id_disciplina, $id_ass_subs, $id_ass_ausente);
        }
        
        $result = $stmt->execute();
        
        $stmt->close();
        return $result;
    }

    /**
     * Busca todas as reposições agendadas para um professor, seja como ausente ou substituto.
     *
     * @param string $siape_prof O SIAPE do professor a ser buscado.
     * @return array Retorna um array de arrays associativos com os dados das reposições.
     */
    public function buscarReposicoesPorProfessor($siape_prof) {
        $reposicoes = [];
        $sql = "
            SELECT 
                p.dia, 
                p.horario, 
                t.curso,
                t.serie,
                d.nome_disciplina,
                pa.siape_prof AS siape_ausente,
                ps.siape_prof AS siape_substituto
            FROM programada p
            JOIN prof_ausente pa ON p.id_ass_ausente = pa.id_ass_ausente
            LEFT JOIN prof_subs ps ON p.id_ass_subs = ps.id_ass_subs
            JOIN turma t ON p.id_turma = t.id_turma
            JOIN disciplina d ON p.id_disciplina = d.id_disciplina
            WHERE pa.siape_prof = ? OR ps.siape_prof = ?
            ORDER BY p.dia ASC, p.horario ASC
        ";

        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            error_log("ProgramadaDAO->buscarReposicoesPorProfessor: Erro ao preparar query - " . $this->conn->error);
            return $reposicoes;
        }

        $stmt->bind_param("ss", $siape_prof, $siape_prof);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        while ($linha = $resultado->fetch_assoc()) {
            $reposicoes[] = $linha;
        }
        
        $stmt->close();
        return $reposicoes;
    }
    
    /**
     * Busca todas as solicitações de reposição pendentes de aprovação administrativa.
     * Assume que uma solicitação é pendente se não há um registro de relatório associado.
     *
     * @return array Retorna um array de arrays associativos com as solicitações pendentes.
     */
    public function buscarTodasAusenciasPendentes() {
        $ausencias_pendentes = [];
        $sql = "
            SELECT 
                p.id_progra, 
                p.dia, 
                p.horario, 
                t.curso,
                t.serie,
                d.nome_disciplina,
                pa.siape_prof AS siape_ausente,
                ps.siape_prof AS siape_substituto
            FROM programada p
            JOIN prof_ausente pa ON p.id_ass_ausente = pa.id_ass_ausente
            LEFT JOIN prof_subs ps ON p.id_ass_subs = ps.id_ass_subs
            JOIN turma t ON p.id_turma = t.id_turma
            JOIN disciplina d ON p.id_disciplina = d.id_disciplina
            LEFT JOIN relatorio r ON p.id_progra = r.id_progra
            WHERE r.id_progra IS NULL
            ORDER BY p.dia ASC, p.horario ASC
        ";
        
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            error_log("ProgramadaDAO->buscarTodasAusenciasPendentes: Erro ao preparar query - " . $this->conn->error);
            return $ausencias_pendentes;
        }
        
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        while ($linha = $resultado->fetch_assoc()) {
            $ausencias_pendentes[] = $linha;
        }
        
        $stmt->close();
        return $ausencias_pendentes;
    }

    /**
     * Busca todas as reposições agendadas, independente do professor ou status de aprovação.
     * @return array Retorna um array de arrays associativos com os dados das reposições.
     */
    public function listarTodasReposicoes() {
        $reposicoes = [];
        $sql = "
            SELECT 
                p.dia, 
                p.horario, 
                t.curso,
                t.serie,
                d.nome_disciplina,
                pa.siape_prof AS siape_ausente,
                ps.siape_prof AS siape_substituto
            FROM programada p
            JOIN prof_ausente pa ON p.id_ass_ausente = pa.id_ass_ausente
            LEFT JOIN prof_subs ps ON p.id_ass_subs = ps.id_ass_subs
            JOIN turma t ON p.id_turma = t.id_turma
            JOIN disciplina d ON p.id_disciplina = d.id_disciplina
            ORDER BY p.dia ASC, p.horario ASC
        ";

        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            error_log("ProgramadaDAO->listarTodasReposicoes: Erro ao preparar query - " . $this->conn->error);
            return $reposicoes;
        }
        
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        while ($linha = $resultado->fetch_assoc()) {
            $reposicoes[] = $linha;
        }
        
        $stmt->close();
        return $reposicoes;
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