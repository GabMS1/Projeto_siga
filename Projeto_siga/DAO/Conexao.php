<?php
// conexao.php CORRIGIDO (FINAL)
$host = 'db';  // ✅ Nome do serviço no Docker Compose
$usuario = 'siga_user';
// CORREÇÃO CRÍTICA: A senha deve ser SenhaSegura123! para bater com o Docker Compose
$senha = 'S!g@!2$'; 
$banco = 'projeto_siga';

$conexao = new mysqli($host, $usuario, $senha, $banco);

if ($conexao->connect_error) {
    die("Erro de conexão: " . $conexao->connect_error);
}
?>