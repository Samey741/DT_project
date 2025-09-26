<?php
include("config2.php"); // Zahrnutie konfiguračného súboru pre vzdialené pripojenie

function isServerAlive($remote_servername) {
    $connection = @fsockopen($remote_servername, 80, $errno, $errstr, 5);
    if ($connection) {
        fclose($connection);
        return true;
    }
    return false;
}

$serverAlive = isServerAlive($remote_servername);
if ($serverAlive) {
    echo "Uzol2 je aktívny. ";
    $filename = 'failed_transactions.txt';
    if (file_exists($filename) && filesize($filename) > 0) {
        include("synchro.php");
    } else {
        echo "Súbor 'failed_transactions.txt' je prázdny.";
    }
} else {
    echo "Uzol2 je neaktívny.";
}
?>