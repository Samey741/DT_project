<?php
include("config1.php"); // Lokálne nastavenia
include("config2.php"); // Nastavenia vzdialeného uzla

// Prijatie vstupných dát z POST požiadavky
$pc = $_POST["pc"];
$nazov = $_POST["nazov"];
$vyrobca = $_POST["vyrobca"];
$popis = $_POST["popis"];
$kusov = $_POST["kusov"];
$cena = $_POST["cena"];
$kod = $_POST["kod"];

// Vytvorenie pripojenia k lokálnej databáze
$local_conn = new mysqli($servername, $username, $password, $dbname);

// Kontrola pripojenia k lokálnej databáze
if ($local_conn->connect_error) {
    die("Connection failed to local DB: " . $local_conn->connect_error);
}

// Získanie časovej známky a generovanie ID
$id = date('YmdHis', time());

// SQL príkaz pre lokálnu databázu
$local_sql = "INSERT INTO ntovar (id, pc, nazov, vyrobca, popis, kusov, cena, kod) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$local_stmt = $local_conn->prepare($local_sql);

// Kontrola, či je statement správne pripravený
if ($local_stmt === false) {
    die("Local prepare failed: " . $local_conn->error);
}

$local_stmt->bind_param("sssssiis", $id, $pc, $nazov, $vyrobca, $popis, $kusov, $cena, $kod);

// Vykonanie príkazu v lokálnej databáze
if (!$local_stmt->execute()) {
    echo "Local execute failed: " . $local_stmt->error;
}

// Zatvorenie príkazu a pripojenia k lokálnej databáze
$local_stmt->close();
$local_conn->close();

// SQL príkaz pre vzdialenú databázu
$remote_sql = "INSERT INTO ntovar (id, pc, nazov, vyrobca, popis, kusov, cena, kod) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

try {
    // Pokus o pripojenie k vzdialenej databáze
    $remote_conn = new mysqli($remote_servername, $remote_username, $remote_password, $remote_dbname);

    // Kontrola pripojenia k vzdialenej databáze
    if ($remote_conn->connect_error) {
        throw new Exception("Connection failed to remote DB: " . $remote_conn->connect_error);
    }

    // Pripravenie SQL príkazu pre vzdialenú databázu
    $remote_stmt = $remote_conn->prepare($remote_sql);

    // Kontrola, či je statement správne pripravený
    if ($remote_stmt === false) {
        throw new Exception("Remote prepare failed: " . $remote_conn->error);
    }

    $remote_stmt->bind_param("sssssiis", $id, $pc, $nazov, $vyrobca, $popis, $kusov, $cena, $kod);

    // Vykonanie príkazu vzdialenej databáze
    if (!$remote_stmt->execute()) {
        throw new Exception("Remote execute failed: " . $remote_stmt->error);
    }

    // Zatvorenie príkazu a pripojenia k vzdialenej databáze
    $remote_stmt->close();
    $remote_conn->close();

    echo "Record successfully written to both local and remote DBs.";

} catch (Exception $e) {
    // Tu zapíšeme len SQL dotaz, ktorý sa nepodarilo vykonať
    $failed_sql = "INSERT INTO ntovar (id, pc, nazov, vyrobca, popis, kusov, cena, kod) VALUES ('$id', '$pc', '$nazov', '$vyrobca', '$popis', '$kusov', '$cena', '$kod');" . PHP_EOL;
    file_put_contents('failed_transactions.txt', $failed_sql, FILE_APPEND);
    // Upravené upozornenie pre používateľa
    echo "Nedá sa nahrať na Uzol2. ";
}

// Presmerovanie alebo ďalšie akcie
header('Location: index.php?menu=8');
exit;
?>
