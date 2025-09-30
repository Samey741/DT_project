<?php
header('Content-Type: application/json');

//TODO connection pooling? We open/close connection all the time on repeat...

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
$conn = @new mysqli(    //@ <-- this suppresses warnings so it doesnt flood the console if connection failed
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
//TODO do we limit the query? Or do we load the entire table into memory? Pagination?
$result = $conn->query("
    SELECT * 
    FROM replication_queue 
    ORDER BY created_at ASC 
");

$processed = [];
$failed = [];

//TODO unify failed logic, there are 5 failed branches which do the same

while ($row = $result->fetch_assoc()) {
    $id = (int)$row['id'];
    $nodeId = $row['node_id'];
    $data = json_decode($row['data'], true);

    // Check node config
    // TODO remove once all configs are valid, its useless
    if (!isset($allNodes[$nodeId])) {
        $conn->query("UPDATE replication_queue SET status='failed' WHERE id=$id");
        $failed[] = $id;
        continue;
    }

    $node = $allNodes[$nodeId];

    // Connect to remote node safely
    try {
        $remoteConn = new mysqli();
        //TODO im waiting 2 seconds, if i dont connect i throw an error. Not ideal, connection pooling should fix this case
        $remoteConn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 2);
        @$remoteConn->real_connect( //@ <-- this suppresses warnings so it doesnt flood the console if connection failed
            $node['host'],
            $node['user'],
            $node['pass'],
            $node['name']
        );
    } catch (mysqli_sql_exception $e) {
        $conn->query("UPDATE replication_queue SET status='failed' WHERE id=$id");
        $failed[] = $id;
        continue;
    }

    //TODO idk if this does something tbh
    if ($remoteConn->connect_error) {
        $conn->query("UPDATE replication_queue SET status='failed' WHERE id=$id");
        $failed[] = $id;
        continue;
    }

    // Insert into remote table
    $stmt = $remoteConn->prepare("
        INSERT INTO ntovar (id, pc, nazov, vyrobca, popis, kusov, cena, kod, node_origin)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if ($stmt) {
        $stmt->bind_param(
            "sssssiiss",
            $data['id'],
            $data['pc'],
            $data['nazov'],
            $data['vyrobca'],
            $data['popis'],
            $data['kusov'],
            $data['cena'],
            $data['kod'],
            $localSignature
        );

        if ($stmt->execute()) {
            $conn->query("DELETE FROM replication_queue WHERE id=$id");
            $processed[] = $id;
        } else {
            $conn->query("UPDATE replication_queue SET status='failed' WHERE id=$id");
            $failed[] = $id;
        }

        $stmt->close();
    } else {
        $conn->query("UPDATE replication_queue SET status='failed' WHERE id=$id");
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
