<?php
// C:\xampp\htdocs\Projeto_siga\negocio\AusenciaServico.php

// Inclui as classes DAO necessárias para interagir com o banco de dados.
require_once __DIR__ . '/../DAO/ProfAusenteDAO.php';
require_once __DIR__ . '/../DAO/ProfSubsDAO.php';
require_once __DIR__ . '/../DAO/ProgramadaDAO.php';
require_once __DIR__ . '/../DAO/DisciplinaDAO.php';
require_once __DIR__ . '/../DAO/TurmaDAO.php';
require_once __DIR__ . '/../DAO/Conexao.php';

/**
 * A classe AusenciaServico lida com a lógica de negócio relacionada ao registro
 * de faltas e reposições de professores, seguindo as regras da instituição.
 * Ela orquestra as operações, chamando as classes DAO para persistir os dados.
 */
class AusenciaServico {
    
    /**
     * Registra uma ausência e sua respectiva reposição no sistema.
     * Esta função coordena o processo de inserção nas tabelas 'prof_ausente',
     * 'prof_subs' e 'programada'.
     *
     * @param array $dados Um array associativo com todos os dados do formulário de reposição.
     * Esperado: 'dia', 'horario', 'id_turma', 'id_disciplina',
     * 'siape_ausente', 'assinatura_ausente', 'siape_substituto', 'assinatura_substituto'.
     * @return bool True se o registro for bem-sucedido, false caso contrário.
     */
    public function registrarAusenciaEReposicao($dados) {
        
        // Inicia a transação no banco de dados para garantir a consistência
        // Se qualquer uma das operações falhar, todas serão desfeitas.
        $conexao = new Conexao();
        $conn = $conexao->get_connection();
        $conn->begin_transaction();

        try {
            // 1. Cadastra o professor ausente na tabela 'prof_ausente'
            $profAusenteDAO = new ProfAusenteDAO();
            $id_ausente = $profAusenteDAO->cadastrar($dados['assinatura_ausente'], $dados['siape_ausente']);

            if (!$id_ausente) {
                throw new Exception("Erro ao cadastrar professor ausente.");
            }

            // 2. Cadastra o professor substituto na tabela 'prof_subs'
            $profSubsDAO = new ProfSubsDAO();
            $id_substituto = $profSubsDAO->cadastrar($dados['assinatura_substituto'], $dados['siape_substituto']);
            
            if (!$id_substituto) {
                throw new Exception("Erro ao cadastrar professor substituto.");
            }

            // 3. Agenda a reposição na tabela 'programada', usando os IDs recém-criados.
            $programadaDAO = new ProgramadaDAO();
            $sucesso = $programadaDAO->cadastrar(
                $dados['dia'],
                $dados['horario'],
                $dados['autor_gov'] ?? 'N/A',
                $dados['id_turma'],
                $dados['id_disciplina'],
                $id_substituto,
                $id_ausente
            );

            if (!$sucesso) {
                throw new Exception("Erro ao agendar a reposição.");
            }

            // Se tudo deu certo, confirma a transação (salva no banco).
            $conn->commit();
            return true;

        } catch (Exception $e) {
            // Em caso de qualquer erro, desfaz todas as operações da transação.
            $conn->rollback();
            error_log("AusenciaServico: Falha na transação de reposição - " . $e->getMessage());
            $_SESSION['reposicao_error'] = "Ocorreu um erro ao registrar a reposição. " . $e->getMessage();
            return false;
        } finally {
            $conexao->close();
        }
    }
    
    /**
     * Registra uma falta programada sem um substituto.
     * @param array $dados Um array associativo com os dados da falta.
     * @return bool True se o registro for bem-sucedido, false caso contrário.
     */
    public function programarFalta($dados) {
        $conexao = new Conexao();
        $conn = $conexao->get_connection();
        $conn->begin_transaction();

        try {
            $profAusenteDAO = new ProfAusenteDAO();
            $id_ausente = $profAusenteDAO->cadastrar($dados['assinatura_ausente'], $dados['siape_ausente']);

            if (!$id_ausente) {
                throw new Exception("Erro ao cadastrar professor ausente.");
            }

            $programadaDAO = new ProgramadaDAO();
            // id_ass_subs é setado como NULL para indicar uma falta sem reposição
            $sucesso = $programadaDAO->cadastrar(
                $dados['dia'],
                $dados['horario'],
                'N/A',
                $dados['id_turma'],
                $dados['id_disciplina'],
                null,
                $id_ausente
            );

            if (!$sucesso) {
                throw new Exception("Erro ao programar a falta.");
            }

            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollback();
            error_log("AusenciaServico: Falha na transação de programação de falta - " . $e->getMessage());
            return false;
        } finally {
            $conexao->close();
        }
    }

    /**
     * Lista todas as reposições agendadas para um professor específico,
     * tanto como ausente quanto como substituto.
     *
     * @param int $siape_prof O SIAPE do professor logado.
     * @return array Um array de reposições.
     */
    public function listarReposicoesPorProfessor($siape_prof) {
        $programadaDAO = new ProgramadaDAO();
        return $programadaDAO->buscarReposicoesPorProfessor((string)$siape_prof);
    }
    
    /**
     * Lista todas as solicitações de ausência pendentes de aprovação administrativa.
     *
     * @return array Um array de ausências pendentes.
     */
    public function listarTodasAusenciasPendentes() {
        $programadaDAO = new ProgramadaDAO();
        return $programadaDAO->buscarTodasAusenciasPendentes();
    }
    
    /**
     * Lista todas as reposições agendadas no sistema.
     * @return array Um array de reposições.
     */
    public function listarTodasReposicoes() {
        $programadaDAO = new ProgramadaDAO();
        return $programadaDAO->listarTodasReposicoes();
    }
}
?>