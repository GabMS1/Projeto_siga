<?php
// conexao.php CORRIGIDO
$host = 'db';  // ✅ Nome do serviço no Docker Compose
$usuario = 'root';
$senha = 'S!g@!2$';
$banco = 'siga';

$conexao = new mysqli($host, $usuario, $senha, $banco);

if ($conexao->connect_error) {
    die("Erro de conexão: " . $conexao->connect_error);
}
?>