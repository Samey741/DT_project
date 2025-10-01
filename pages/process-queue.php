<?php
header('Content-Type: application/json');

// --- Json structure for debugging on client (browser console)
$errors = [];
set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$errors) {
    $errors[] = [
        'type' => $errno,
        'message' => $errstr,
        'file' => $errfile,
        'line' => $errline
    ];
    return true; // prevent default handler
});

// --- Load local config ---
include("../resources/config/localConfig.php");

// --- Load node configs ---
$allNodes = [];
$configFiles = [
    __DIR__ . '/../resources/config/config1.php',
    __DIR__ . '/../resources/config/config2.php',
    __DIR__ . '/../resources/config/config3.php'
];

foreach ($configFiles as $file) {
    include($file); // defines $node_id and $db_config
    if (isset($node_id, $db_config)) {
        $allNodes[$node_id] = $db_config;
    }
}

// --- Connect local DB ---
$conn = @new mysqli(
    $localConfig['host'],
    $localConfig['user'],
    $localConfig['pass'],
    $localConfig['name']
);

if ($conn->connect_error) {
    echo json_encode([
        'processed' => [],
        'failed' => [],
        'errors' => array_merge($errors, [[
            'type' => 'connect_error',
            'message' => $conn->connect_error
        ]])
    ]);
    exit;
}

// --- Fetch the entire queue ---
$result = $conn->query("
    SELECT *
    FROM replication_queue
    ORDER BY created_at ASC
");

$processed = [];
$failed = [];

while ($row = $result->fetch_assoc()) {
    $id = $row['id']; // Kombinované id (repl_id + node_id)
    $nodeId = $row['node_id'];
    $data = json_decode($row['data'], true);

    // Check node config
    if (!isset($allNodes[$nodeId])) {
        $stmt = $conn->prepare("UPDATE replication_queue SET status='failed' WHERE id=?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $stmt->close();
        $failed[] = $id;
        continue;
    }

    $node = $allNodes[$nodeId];

    // Connect to remote node safely
    try {
        $remoteConn = new mysqli();
        $remoteConn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 2);
        @$remoteConn->real_connect(
            $node['host'],
            $node['user'],
            $node['pass'],
            $node['name']
        );
    } catch (mysqli_sql_exception $e) {
        $stmt = $conn->prepare("UPDATE replication_queue SET status='failed' WHERE id=?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $stmt->close();
        $failed[] = $id;
        continue;
    }

    if ($remoteConn->connect_error) {
        $stmt = $conn->prepare("UPDATE replication_queue SET status='failed' WHERE id=?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $stmt->close();
        $failed[] = $id;
        continue;
    }

    // Insert into remote table
    $stmt = $remoteConn->prepare("
        INSERT INTO ntovar (id, pc, nazov, vyrobca, popis, kusov, cena, kod, node_origin)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if ($stmt) {
        // Inicializácia premenných z $data
        $id_val = $id; // Použijeme kombinované id z replication_queue
        $pc_val = $data['pc'] ?? null;
        $nazov_val = $data['nazov'] ?? null;
        $vyrobca_val = $data['vyrobca'] ?? null;
        $popis_val = $data['popis'] ?? null;
        $kusov_val = $data['kusov'] ?? null;
        $cena_val = $data['cena'] ?? null;
        $kod_val = $data['kod'] ?? null;
        $origin_val = $data['node_origin'] ?? null;

        // Bind parametrov
        $stmt->bind_param(
            "sissssiis",
            $id_val,
            $pc_val,
            $nazov_val,
            $vyrobca_val,
            $popis_val,
            $kusov_val,
            $cena_val,
            $kod_val,
            $origin_val
        );

        if ($stmt->execute()) {
            $stmt = $conn->prepare("DELETE FROM replication_queue WHERE id=?");
            $stmt->bind_param("s", $id);
            $stmt->execute();
            $stmt->close();
            $processed[] = $id;
        } else {
            $stmt = $conn->prepare("UPDATE replication_queue SET status='failed' WHERE id=?");
            $stmt->bind_param("s", $id);
            $stmt->execute();
            $stmt->close();
            $failed[] = $id;
        }

        $stmt->close();
    } else {
        $stmt = $conn->prepare("UPDATE replication_queue SET status='failed' WHERE id=?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $stmt->close();
        $failed[] = $id;
    }

    $remoteConn->close();
}

$conn->close();

// --- Final JSON ---
echo json_encode([
    'processed' => $processed,
    'failed' => $failed,
    'errors' => $errors
]);