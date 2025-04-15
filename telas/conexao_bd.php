<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "projeto_banco"; 

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
?>
