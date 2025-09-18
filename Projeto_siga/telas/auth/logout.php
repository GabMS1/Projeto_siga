<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\logout.php

// Inicia a sessão para poder manipulá-la.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destrói todas as variáveis de sessão.
$_SESSION = array();

// Se a sessão usa cookies, o cookie de sessão também deve ser destruído.
// Nota: Isso irá invalidar o cookie de sessão e forçar uma nova sessão.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destrói a sessão.
session_destroy();

// Redireciona o usuário para a página de cadastro.
header("Location: cadastro.php");
exit;
?>