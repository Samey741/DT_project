<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distribuovaná databáza</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/table.css">
</head>
<body>
<div class="container">
    <header>
        <h1>Distribuovaná databáza</h1>
    </header>
    <aside class="sidebar">
        <?php include("menu.php"); ?>
    </aside>
    <main class="content">
        <?php
        // Determine which page to show
        $allowed_pages = ['home','add-item','list-items','search', 'edit-item', 'delete-item'];
        $menu = isset($_GET['menu']) ? $_GET['menu'] : 'home';
        $page = in_array($menu, $allowed_pages) ? $menu : 'home';
        include "pages/{$page}.php";
        ?>
    </main>
</div>
</body>
</html>
