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

    if (!isset($data)) {
        // Invalid JSON data
        $localStmt = $conn->prepare("UPDATE replication_queue SET status='failed' WHERE id=?");
        $localStmt->bind_param("s", $id);
        $localStmt->execute();
        $localStmt->close();
        $failed[] = $id;
        continue;
    }

    $type = $data['type'] ?? 'insert';

    // Check node config
    if (!isset($allNodes[$nodeId])) {
        $localStmt = $conn->prepare("UPDATE replication_queue SET status='failed' WHERE id=?");
        $localStmt->bind_param("s", $id);
        $localStmt->execute();
        $localStmt->close();
        $failed[] = $id;
        continue;
    }

    $node = $allNodes[$nodeId];

    // Connect to remote node safely
    $remoteConn = null;
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
        // Connection exception
        $localStmt = $conn->prepare("UPDATE replication_queue SET status='failed' WHERE id=?");
        $localStmt->bind_param("s", $id);
        $localStmt->execute();
        $localStmt->close();
        $failed[] = $id;
        continue;
    }

    if ($remoteConn->connect_error) {
        $localStmt = $conn->prepare("UPDATE replication_queue SET status='failed' WHERE id=?");
        $localStmt->bind_param("s", $id);
        $localStmt->execute();
        $localStmt->close();
        $failed[] = $id;
        if ($remoteConn) $remoteConn->close();
        continue;
    }

    // Prepare and execute based on type (on REMOTE connection)
    $success = false;
    if ($type === 'update') {
        // UPDATE na remote
        $remoteStmt = $remoteConn->prepare("
            UPDATE ntovar SET pc = ?, nazov = ?, vyrobca = ?, popis = ?, kusov = ?, cena = ?, kod = ? WHERE id = ?
        ");
        if ($remoteStmt) {
            $pc_val = $data['pc'] ?? null;
            $nazov_val = $data['nazov'] ?? null;
            $vyrobca_val = $data['vyrobca'] ?? null;
            $popis_val = $data['popis'] ?? null;
            $kusov_val = $data['kusov'] ?? null;
            $cena_val = $data['cena'] ?? null;
            $kod_val = $data['kod'] ?? null;
            $update_id_val = $data['id'] ?? null; // Originálne ID pre WHERE

            $remoteStmt->bind_param("ssssiiis", $pc_val, $nazov_val, $vyrobca_val, $popis_val, $kusov_val, $cena_val, $kod_val, $update_id_val);

            $success = $remoteStmt->execute();
            $remoteStmt->close();
        }
    }
    elseif ($type === 'delete') {
        // DELETE na remote
        $remoteStmt = $remoteConn->prepare("DELETE FROM ntovar WHERE id = ?");
        if ($remoteStmt) {
            $delete_id_val = $data['id'] ?? null;
            $remoteStmt->bind_param("s", $delete_id_val);
            $success = $remoteStmt->execute();
            $remoteStmt->close();
        }
    }
    else {
        // Default: INSERT na remote
        $remoteStmt = $remoteConn->prepare("
            INSERT INTO ntovar (id, pc, nazov, vyrobca, popis, kusov, cena, kod, node_origin)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if ($remoteStmt) {
            // Inicializácia premenných z $data (použi originálne id z data, nie queue id)
            $insert_id_val = $data['id'] ?? $id; // Prefer data['id'] pre konzistentnosť
            $pc_val = $data['pc'] ?? null;
            $nazov_val = $data['nazov'] ?? null;
            $vyrobca_val = $data['vyrobca'] ?? null;
            $popis_val = $data['popis'] ?? null;
            $kusov_val = $data['kusov'] ?? null;
            $cena_val = $data['cena'] ?? null;
            $kod_val = $data['kod'] ?? null;
            $origin_val = $data['node_origin'] ?? null;

            // Opravený bind_param: s(id), s(pc), s(nazov), s(vyrobca), s(popis), i(kusov), i(cena), s(kod), i(node_origin)
            $remoteStmt->bind_param(
                "sssssiisi",
                $insert_id_val,
                $pc_val,
                $nazov_val,
                $vyrobca_val,
                $popis_val,
                $kusov_val,
                $cena_val,
                $kod_val,
                $origin_val
            );

            $success = $remoteStmt->execute();
            $remoteStmt->close();
        }
    }

    // Handle success/failure on local queue
    if ($success) {
        $localStmt = $conn->prepare("DELETE FROM replication_queue WHERE id=?");
        $localStmt->bind_param("s", $id);
        $localStmt->execute();
        $localStmt->close();
        $processed[] = $id;
    } else {
        $localStmt = $conn->prepare("UPDATE replication_queue SET status='failed' WHERE id=?");
        $localStmt->bind_param("s", $id);
        $localStmt->execute();
        $localStmt->close();
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
?>