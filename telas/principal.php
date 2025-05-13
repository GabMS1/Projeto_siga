
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tela Principal</title>
    <style>
        /* (mantive seu estilo igual) */
        body { font-family: sans-serif; margin: 0; display: flex; }
        .sidebar { background-color: #f0f7f4; width: 250px; height: 100vh; position: fixed; left: -250px; top: 0; transition: left 0.3s ease; padding-top: 60px; box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1); }
        .sidebar.open { left: 0; }
        .sidebar ul { list-style: none; padding: 0; margin: 0; }
        .sidebar ul li a { display: block; padding: 15px 20px; text-decoration: none; color: #386641; transition: background-color 0.3s ease; }
        .sidebar ul li a:hover { background-color: #e0ece7; }
        .content { flex-grow: 1; padding: 20px; transition: margin-left 0.3s ease; margin-left: 0; display: flex; flex-direction: column; }
        .content.sidebar-open { margin-left: 250px; }
        .menu-button { background-color: #2a9d8f; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; font-size: 16px; position: fixed; top: 10px; left: 10px; z-index: 10; }
        .menu-button:hover { background-color: #268074; }
        .user-info { background-color: #e0ece7; padding: 10px 15px; border-radius: 5px; position: fixed; top: 10px; right: 10px; z-index: 10; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); font-size: 0.9em; color: #386641; text-align: right; }
        .user-info p { margin: 5px 0; }
        h1 { text-align: center; margin-bottom: 20px; color: #386641; margin-top: 60px; }
        p { margin-bottom: 15px; color: #555; }
        .notification { background-color: #4CAF50; color: white; padding: 15px; border-radius: 5px; position: fixed; bottom: 20px; right: 20px; z-index: 11; opacity: 0; transition: opacity 0.5s ease-in-out; }
        .notification.show { opacity: 1; }
    </style>
</head>
<body onload="showNotification()">
    <button class="menu-button" onclick="toggleSidebar()">☰ Menu</button>

    <div class="sidebar">
        <ul>
            <li><a href="#">Falta Programada</a></li>
            <li><a href="#">Falta não programada</a></li>
            <li><a href="#">Repor Aulas</a></li>
            <li><a href="#">Relatório de aulas</a></li>
            <li><a href="logout.php">Sair</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="user-info">
            <p>Nome: <?php echo htmlspecialchars($_SESSION['nome']); ?></p>
            <p>SIAPE: <?php echo htmlspecialchars($_SESSION['siape_prof']); ?></p>
        </div>
        <h1>Bem-vindo(a)!</h1>
        <p>Esta é a tela principal do seu programa com um menu lateral e informações do usuário.</p>
        <p>Clique no botão "☰ Menu" no canto superior esquerdo para abrir a barra lateral.</p>
    </div>

    <div id="notification" class="notification">
        Bem-vindo(a), <?php echo htmlspecialchars($_SESSION['nome']); ?>!
    </div>

    <script>
        const sidebar = document.querySelector('.sidebar');
        const content = document.querySelector('.content');
        const notification = document.getElementById('notification');

        function toggleSidebar() {
            sidebar.classList.toggle('open');
            content.classList.toggle('sidebar-open');
        }

        function showNotification() {
            notification.classList.add('show');
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }
    </script>
</body>
</html>
