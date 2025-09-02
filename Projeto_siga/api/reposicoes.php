<?php
// C:\xampp\htdocs\Projeto_siga\api\reposicoes.php

header('Content-Type: application/json');

// Inclui o serviço de Ausência para listar as reposições.
require_once __DIR__ . '/../negocio/AusenciaServico.php';

try {
    $ausenciaServico = new AusenciaServico();
    $reposicoes = $ausenciaServico->listarTodasReposicoes();
    
    echo json_encode(['success' => true, 'data' => $reposicoes]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao carregar reposições. ' . $e->getMessage()]);
}
?>