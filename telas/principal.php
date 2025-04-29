<?php
    session_start();
    if (!isset($_SESSION['usuario'])) {
        header("Location: login.php");
        exit();
    }
    $nomeUsuario = $_SESSION['usuario'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tela Principal</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 0; /* Reset default margin */
            display: flex; /* Enable flexbox for layout */
        }

        .sidebar {
            background-color: #f0f7f4; /* Light green sidebar */
            width: 250px;
            height: 100vh;
            position: fixed;
            left: -250px; /* Initially hidden */
            top: 0;
            transition: left 0.3s ease;
            padding-top: 60px; /* Adjust for the header */
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar.open {
            left: 0;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar ul li a {
            display: block;
            padding: 15px 20px;
            text-decoration: none;
            color: #386641; /* Dark green text */
            transition: background-color 0.3s ease;
        }

        .sidebar ul li a:hover {
            background-color: #e0ece7; /* Lighter green on hover */
        }

        .content {
            flex-grow: 1; /* Content takes remaining width */
            padding: 20px;
            transition: margin-left 0.3s ease;
            margin-left: 0; /* Adjust margin when sidebar is open */
        }

        .content.sidebar-open {
            margin-left: 250px;
        }

        .menu-button {
            background-color: #2a9d8f; /* Teal button */
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            position: fixed; /* Fixed position for the button */
            top: 10px;
            left: 10px;
            z-index: 10; /* Ensure it's above the sidebar */
        }

        .menu-button:hover {
            background-color: #268074;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #386641;
        }

        p {
            margin-bottom: 15px;
            color: #555;
        }
    </style>
</head>
<body>
    <button class="menu-button" onclick="toggleSidebar()">☰ Menu</button>

    <div class="sidebar">
        <ul>
            <li><a href="#">Falta Programada</a></li>
            <li><a href="#">Falta não programada</a></li>
            </ul>
    </div>

    <div class="content">
        <h1>Bem-vindo(a), <?php echo htmlspecialchars($nomeUsuario); ?>!</h1>
        <p>Esta é a tela principal do seu programa com um menu lateral.</p>
        <p>Clique no botão "☰ Menu" no canto superior esquerdo para abrir a barra lateral.</p>
        </div>

    <script>
        const sidebar = document.querySelector('.sidebar');
        const content = document.querySelector('.content');
        const body = document.body;

        function toggleSidebar() {
            sidebar.classList.toggle('open');
            content.classList.toggle('sidebar-open');
            // Optional: Toggle a class on the body to adjust other elements if needed
            // body.classList.toggle('sidebar-open');
        }
    </script>
</body>
</html>