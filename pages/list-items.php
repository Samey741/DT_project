<?php
include __DIR__ . '/../resources/config/localConfig.php';


// Connect to the database
$conn = mysqli_connect($localConfig['host'], $localConfig['user'], $localConfig['pass'], $localConfig['name']);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch all items
$sql = "SELECT id, pc, nazov, vyrobca, popis, kusov, cena, kod FROM ntovar";
$result = mysqli_query($conn, $sql);
?>

<h2>Zoznam Tovarov</h2>

<table class="item-table">
    <thead>
    <tr>
        <th>Kód</th>
        <th>Názov</th>
        <th>Výrobca</th>
        <th>Popis</th>
        <th>Kusov</th>
        <th>Cena</th>
        <th>Akcie</th>
    </tr>
    </thead>
    <tbody>
    <?php while($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['kod']); ?></td>
            <td><?php echo htmlspecialchars($row['nazov']); ?></td>
            <td><?php echo htmlspecialchars($row['vyrobca']); ?></td>
            <td><?php echo htmlspecialchars($row['popis']); ?></td>
            <td><?php echo htmlspecialchars($row['kusov']); ?></td>
            <td><?php echo htmlspecialchars($row['cena']); ?> €</td>
            <td>
                <a href="index.php?menu=edit-item&e=<?php echo $row['id']; ?>" class="edit-btn">Edituj</a>
                <a href="index.php?menu=delete-item&k=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('Naozaj chcete zmazať tento tovar?');">X</a>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

<?php
mysqli_close($conn);
?>
