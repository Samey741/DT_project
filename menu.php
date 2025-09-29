<?php
$menuItems = [
        'home' => 'Domov',
        'add-item' => 'Pridaj Tovar',
        'list-items' => 'Zoznam Tovarov',
        'search' => 'Vyhľadávanie'
];

$current = isset($_GET['menu']) ? $_GET['menu'] : 'home';

echo '<ul class="menu">';
foreach ($menuItems as $id => $name) {
    $active = ($id === $current) ? 'active' : '';
    echo "<li class='$active'><a href='?menu=$id'>$name</a></li>";
}
echo '</ul>';
?>
