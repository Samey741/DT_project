<?php
include __DIR__ . '/../resources/config/localConfig.php';

// Získaj ID z GET
$id = $_GET['e'] ?? ''; // z linky v list-items.php

if (!$id) {
    die("Chýba ID záznamu.");
}

// Pripoj sa k lokálnej DB
$conn = new mysqli($localConfig['host'], $localConfig['user'], $localConfig['pass'], $localConfig['name']);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Načítaj dáta a skontroluj node_origin
$stmt = $conn->prepare("SELECT * FROM ntovar WHERE id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    die("Záznam neexistuje.");
}

if ($row['node_origin'] != $localSignature) { // $localSignature z localConfig.php (tvoj lokálny node ID)
    die("Môžete upravovať len záznamy vytvorené na tomto uzle (node_origin = {$localSignature}).");
}

$stmt->close();
$conn->close();
?>

<h2>Editácia tovaru (ID: <?php echo htmlspecialchars($id); ?>)</h2>
<form action="pages/edit-item-submit.php" method="POST">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>" />
    <label>Mesto: <input name="pc" type="text" value="<?php echo htmlspecialchars($row['pc']); ?>" /></label><br/><br/>
    <label>Názov: <input name="nazov" type="text" value="<?php echo htmlspecialchars($row['nazov']); ?>" /></label><br/><br/>
    <label>Výrobca: <input name="vyrobca" type="text" value="<?php echo htmlspecialchars($row['vyrobca']); ?>"/></label><br/><br/>
    <label>Popis: <input name="popis" type="text" value="<?php echo htmlspecialchars($row['popis']); ?>"/></label><br/><br/>
    <label>Kusov: <input name="kusov" type="number" value="<?php echo htmlspecialchars($row['kusov']); ?>"/></label><br/><br/>
    <label>Cena: <input name="cena" type="number" step="0.01" value="<?php echo htmlspecialchars($row['cena']); ?>"/></label><br/><br/>
    <label>Kód: <input name="kod" type="text" value="<?php echo htmlspecialchars($row['kod']); ?>"/></label><br/><br/>
    <input type="submit" name="submit" value="Uložiť zmeny"/>
    <input type="reset" value="Resetovať"/>
</form>