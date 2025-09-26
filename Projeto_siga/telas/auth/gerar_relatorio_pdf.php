<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\gerar_relatorio_pdf.php

// Inicia o buffer de saída para capturar qualquer output prematuro (como BOMs).
ob_start();

// ATENÇÃO CRÍTICA: DEVE SER A PRIMEIRA COISA NO ARQUIVO, SEM ESPAÇOS OU LINHAS ACIMA.
if (session_status() === PHP_SESSION_NONE) {
session_start();
}

// Inclui a biblioteca FDPF a partir do caminho que você definiu
require_once __DIR__ . '/../../fpdf/fpdf.php';

// Inclui o serviço de Ausência para listar as reposições.
require_once __DIR__ . '/../../negocio/AusenciaServico.php';
require_once __DIR__ . '/../../negocio/ProfessorServico.php';

// --- PROTEÇÃO DE ROTA ---
if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'professor') {
$_SESSION['login_error'] = "Acesso negado. Faça login como professor.";
header("Location: login.php");
exit();
}

$siape_professor_logado = $_SESSION['usuario_logado'];
$nome_professor_logado = $_SESSION['nome_usuario_logado'];

// 1. Obter os dados do banco de dados
try {
$ausenciaServico = new AusenciaServico();
$reposicoes = $ausenciaServico->listarReposicoesPorProfessor((int)$siape_professor_logado);
} catch (Exception $e) {
echo "Erro ao carregar as reposições: " . $e->getMessage();
exit;
}

// 2. Criar uma nova instância do PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Adicionar imagem do logo
$logoPath = __DIR__ . '/logos ifgoiano.png';
if (file_exists($logoPath)) {
$pdf->Image($logoPath, 85, 10, 40);
}

// Pular linha e adicionar título
$pdf->Cell(0, 30, '', 0, 1); // Célula vazia para espaçamento
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Relatório de Reposição de Aula'), 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 5, 'Gerado em: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
$pdf->Ln(10);

// Informações do professor
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Professor: ' . $nome_professor_logado), 0, 1);
$pdf->Cell(0, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'SIAPE: ' . $siape_professor_logado), 0, 1);
$pdf->Ln(5);

// 3. Adicionar a tabela com os dados das reposições
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(240, 240, 240); // Cor de fundo para o cabeçalho
$pdf->Cell(25, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Data'), 1, 0, 'C', true);
$pdf->Cell(20, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Horário'), 1, 0, 'C', true);
$pdf->Cell(25, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Turma'), 1, 0, 'C', true);
$pdf->Cell(60, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Disciplina'), 1, 0, 'C', true);
$pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Prof. Ausente'), 1, 0, 'C', true);
$pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Prof. Substituto'), 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 10);
if (!empty($reposicoes)) {
foreach ($reposicoes as $reposicao) {
$pdf->Cell(25, 8, date('d/m/Y', strtotime($reposicao['dia'])), 1, 0);
$pdf->Cell(20, 8, substr($reposicao['horario'], 0, 5), 1, 0);
$pdf->Cell(25, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $reposicao['curso'] . " - " . $reposicao['serie']), 1, 0);
$pdf->Cell(60, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $reposicao['nome_disciplina']), 1, 0);
$pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $reposicao['siape_ausente']), 1, 0);
$pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $reposicao['siape_substituto']), 1, 1);
}
} else {
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Nenhuma reposição encontrada.'), 1, 1, 'C');
}

// Limpa o buffer de saída de qualquer conteúdo indesejado (BOMs, etc.)
ob_end_clean();

// 4. Forçar o download do arquivo
$pdf->Output('D', 'relatorio_reposicoes.pdf');
?>