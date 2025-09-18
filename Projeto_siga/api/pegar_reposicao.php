<?php
// C:\xampp\htdocs\Projeto_siga\api\pegar_reposicao.php

header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../negocio/AusenciaServico.php';

// Verifica se a requisição é um POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit();
}

// Verifica se o usuário está logado como professor
if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'professor') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit();
}

// Recebe os dados do POST
$id_progra = $_POST['id_progra'] ?? null;
$siape_substituto = $_POST['siape_substituto'] ?? null;
$nome_substituto = $_POST['nome_substituto'] ?? null;

// Validação básica
if (!$id_progra || !$siape_substituto || !$nome_substituto) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados incompletos.']);
    exit();
}

try {
    $ausenciaServico = new AusenciaServico();
    
    // Chama o serviço para pegar a falta
    if ($ausenciaServico->pegarFalta($id_progra, $siape_substituto, $nome_substituto)) {
        echo json_encode(['success' => true, 'message' => 'Reposição pega com sucesso!']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao processar a solicitação.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor: ' . $e->getMessage()]);
}