<?php
include("conexao_bd.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['enviar'])) {
    $siape = $conn->real_escape_string($_POST['siape']);
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    $check = $conn->query("SELECT * FROM professor WHERE siape_prof = '$siape'");
    if ($check->num_rows > 0) {
        echo "SIAPE já cadastrado.";
    } else {
        $insert_professor = $conn->query("INSERT INTO professor (siape_prof, senha) VALUES ('$siape', '$senha')");
        if ($insert_professor) {
            header("Location: login.php");
            exit;
        } else {
            echo "Erro ao cadastrar: " . $conn->error;
        }
    }
} else {
    echo "Formulário não enviado corretamente.";
}
?>
