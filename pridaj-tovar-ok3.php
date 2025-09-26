<?php
include("config.php"); // Lokálne nastavenia
include("config2.php"); // 2. uzol

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
$local_stmt->bind_param("sissiisi", $id, $pc, $nazov, $vyrobca, $popis, $kusov, $cena, $kod);

// Vykonanie príkazu v lokálnej databáze
if (!$local_stmt->execute()) {
    echo "Error: " . $local_stmt->error;
}

// Zatvorenie príkazu a pripojenia k lokálnej databáze
$local_stmt->close();
$local_conn->close();

// Pokus o pripojenie k vzdialenej databáze
$remote_conn = new mysqli($remote_servername, $remote_username, $remote_password, $remote_dbname);

if ($remote_conn->connect_error) {
    // Ak sa nepodarí pripojiť, uložíme údaje do textového súboru
    $data = $id . "," . $pc . "," . $nazov . "," . $vyrobca . "," . $popis . "," . $kusov . "," . $cena . "," . $kod . PHP_EOL;
    file_put_contents('failed_transactions.txt', $data, FILE_APPEND);
    echo "Connection failed to remote DB. Data written to text file.";
} else {
    // Ak je pripojenie úspešné, zapíšeme údaje do vzdialenej databázy
    $remote_sql = "INSERT INTO ntovar (id, pc, nazov, vyrobca, popis, kusov, cena, kod) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $remote_stmt = $remote_conn->prepare($remote_sql);
    $remote_stmt->bind_param("sissiisi", $id, $pc, $nazov, $vyrobca, $popis, $kusov, $cena, $kod);

    // Vykonanie príkazu v vzdialenej databáze
    if (!$remote_stmt->execute()) {
        echo "Error: " . $remote_stmt->error;
    } else {
        echo "Record successfully written to both local and remote DBs.";
    }

    // Zatvorenie príkazu a pripojenia k vzdialenej databáze
    $remote_stmt->close();
    $remote_conn->close();
}
?>
