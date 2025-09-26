<?php
// C:\xampp\htdocs\Projeto_siga\telas\auth\helpers.php

function redirect_with_error($message, $page) {
    $_SESSION['op_error'] = $message;
    header("Location: $page");
    exit();
}

function redirect_with_success($message, $page) {
    $_SESSION['op_success'] = $message;
    header("Location: $page");
    exit();
}

function display_session_alerts() {
    if (isset($_SESSION['op_error'])) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['op_error']) . '</div>';
        unset($_SESSION['op_error']);
    }
    
    if (isset($_SESSION['op_success'])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['op_success']) . '</div>';
        unset($_SESSION['op_success']);
    }
}
?>