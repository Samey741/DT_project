<?php
include("../resources/config/localConfig.php"); // sets $localNodeId and $localConfig

$allNodes = [];

// List all node configs
$configFiles = [
    __DIR__ . '/../resources/config/config1.php',
    __DIR__ . '/../resources/config/config2.php',
    __DIR__ . '/../resources/config/config3.php'
];

// Load configs
foreach ($configFiles as $file) {
    include($file); // defines $node_id and $db_config
    $allNodes[$node_id] = $db_config; // use node_id as key
}

// Connect to local DB
$conn = new mysqli(
    $localConfig['host'],
    $localConfig['user'],
    $localConfig['pass'],
    $localConfig['name']
);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Fetch pending queue items
$result = $conn->query("SELECT * FROM replication_queue WHERE status='pending' ORDER BY created_at ASC LIMIT 10");

while ($row = $result->fetch_assoc()) {
    $nodeId = $row['node_id'];
    $data = json_decode($row['data'], true);

    // Node config exists?
    if (!isset($allNodes[$nodeId])) {
        $conn->query("UPDATE replication_queue SET status='failed', attempts=attempts+1 WHERE id={$row['id']}");
        continue;
    }

    $node = $allNodes[$nodeId];

    // Connect to remote node
    $remoteConn = @new mysqli($node['host'], $node['user'], $node['pass'], $node['name']);
    if ($remoteConn->connect_error) {
        $conn->query("UPDATE replication_queue SET attempts=attempts+1 WHERE id={$row['id']}");
        continue;
    }

    // Prepare insert
    $stmt = $remoteConn->prepare(
        "INSERT INTO ntovar (id, pc, nazov, vyrobca, popis, kusov, cena, kod)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );

    if ($stmt) {
        $stmt->bind_param(
            "sssssiis",
            $data['id'],
            $data['pc'],
            $data['nazov'],
            $data['vyrobca'],
            $data['popis'],
            $data['kusov'],
            $data['cena'],
            $data['kod']
        );

        if ($stmt->execute()) {
            $conn->query("DELETE FROM replication_queue WHERE id={$row['id']}");
        } else {
            $conn->query("UPDATE replication_queue SET attempts=attempts+1 WHERE id={$row['id']}");
        }

        $stmt->close();
    } else {
        $conn->query("UPDATE replication_queue SET attempts=attempts+1 WHERE id={$row['id']}");
    }

    $remoteConn->close();
}

$conn->close();
?>
