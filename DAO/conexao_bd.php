<?php
$host = "localhost";
$user = "root";
$pass = "";
$banco = "projeto";

$conexao=mysqli_connect($host, $user, $pass, $banco) or die (mysql_error());

    mysqli_select_db($banco) or die (mysql_error());
?>