<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\gerenciar_turmas.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_logado']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// CORREÇÃO: A tela deve usar o Serviço.
require_once __DIR__ . '/../../negocio/TurmaServico.php';

$turmaServico = new TurmaServico();
$turmas_raw = $turmaServico->listarTodasAsTurmas();

// Restante do código permanece igual...
?>