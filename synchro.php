<?php
// Synchro.php
include("config1.php"); // Importuje nastavenia pre vzdialené pripojenie

// Vytvorenie pripojenia k vzdialenej databáze
@$remote_conn = new mysqli($remote_servername, $remote_username, $remote_password, $remote_dbname);

// Kontrola pripojenia k vzdialenej databáze
if ($remote_conn->connect_error) {
    die("Connection failed to remote DB: " . $remote_conn->connect_error);
}

// Načítanie príkazov zo súboru
@$filename = 'failed_transactions.txt';
if (file_exists($filename) && is_readable($filename)) {
    $queries = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    if ($queries) {
        foreach ($queries as $query) {
            // Odstráni koncový bodkočiarku pre konzistenciu
            $query = rtrim($query, ";");
            // Vykonanie príkazu
            if ($remote_conn->query($query) === TRUE) {
                echo "Query executed successfully: " . $query . "\n";
            } else {
                echo "Error executing query: " . $remote_conn->error . "\n";
            }
        }
        // Vymazanie obsahu súboru po úspešnej synchronizácii
        file_put_contents($filename, '');
    }
} else {
    echo "The file failed_transactions.txt does not exist or is not readable.\n";
}

// Zatvorenie pripojenia
$remote_conn->close();
?>
