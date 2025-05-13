<?php
require_once 'conexao_bd.php';
session_start();

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $siape = $conn->real_escape_string($_POST['siape_prof']);
    $senha = $_POST['senha'];

    $sql = "SELECT siape_prof, senha, nome FROM professor WHERE siape_prof = '$siape'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows === 1) {
        $usuario = $result->fetch_assoc();


        if (password_verify($senha, $usuario['senha'])) {
            $_SESSION['siape_prof'] = $usuario['siape'];
            $_SESSION['nome'] = $usuario['nome'];

            header("Location: ./principal.php");
            exit();
        } else {
            echo "aqui else";
            $_SESSION['msg'] = "Senha incorreta.";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['msg'] = "SIAPE não encontrado.";
        header("Location: login.php");
        exit();
    }
}

?>