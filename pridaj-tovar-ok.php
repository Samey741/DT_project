<?php
include("config.php");

$pc = $_POST["pc"];
$nazov = $_POST["nazov"];
$vyrobca = $_POST["vyrobca"];
$popis = $_POST["popis"];
$kusov = $_POST["kusov"];
$cena = $_POST["cena"];
$kod = $_POST["kod"];



// Vytvorenie pripojenia
$conn = new mysqli($servername, $username, $password, $dbname);

// Kontrola pripojenia
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Získanie časovej známky
$timestamp = time();

// Generovanie ID pomocou aktuálnej časovej známky
$id = date('YmdHis', $timestamp);

// Premenné s hodnotami, ktoré chcete vložiť (tu si ich musíte nastaviť alebo ich získať z formulára)


// Príprava SQL príkazu na vloženie
$sql = "INSERT INTO ntovar (id, pc, nazov, vyrobca, popis, kusov, cena, kod) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

// Pripravenie príkazu
$stmt = $conn->prepare($sql);

// Kontrola, či je príkaz pripravený
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

// Viazanie parametrov k pripravenému príkazu
$stmt->bind_param("sissiisi", $id, $pc, $nazov, $vyrobca, $popis, $kusov, $cena, $kod);

// Vykonanie príkazu
if ($stmt->execute()) {
    echo "Záznam bol na uzle 1 zapísaný do db";
} else {
    echo "Error: " . $stmt->error;
}

// Zatvorenie príkazu
$stmt->close();

// Zatvorenie pripojenia
$conn->close();
?>