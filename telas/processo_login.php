<?php
include("conexao_bd.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $siape = $_POST['siape'];
    $senha = $_POST['senha'];

    $sql = "SELECT senha FROM professor WHERE siape_prof = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $siape);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($senha_hash);
        $stmt->fetch();
        if (password_verify($senha, $senha_hash)) {
            echo "Login realizado com sucesso!";
        } else {
            echo "Senha incorreta!";
        }
    } else {
        echo "SIAPE não encontrado!";
    }

    $stmt->close();
    $conn->close();
}
?>
