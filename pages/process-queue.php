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

// --- Fetch queue ordered by node_id, created_at ---
$result = $conn->query("
    SELECT *
    FROM replication_queue
    ORDER BY node_id ASC, created_at ASC
");

$processed = [];
$failed = [];

$currentNodeId = null;
$remoteConn = null;
$nodeOffline = false;

while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $nodeId = $row['node_id'];
    $data = json_decode($row['data'], true);

    if (!$data) {
        $conn->query("UPDATE replication_queue SET status='failed' WHERE id='{$conn->real_escape_string($id)}'");
        $failed[] = $id;
        continue;
    }

    $type = $data['type'] ?? 'insert';

    if (!isset($allNodes[$nodeId])) {
        $conn->query("UPDATE replication_queue SET status='failed' WHERE id='{$conn->real_escape_string($id)}'");
        $failed[] = $id;
        continue;
    }

    // --- New node detected ---
    if ($currentNodeId !== $nodeId) {
        if ($remoteConn) {
            $remoteConn->close();
            $remoteConn = null;
        }

        $currentNodeId = $nodeId;
        $nodeOffline = false;
        $node = $allNodes[$nodeId];

        try {
            $remoteConn = new mysqli();
            $remoteConn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 2);
            @$remoteConn->real_connect(
                $node['host'],
                $node['user'],
                $node['pass'],
                $node['name']
            );

            if ($remoteConn->connect_error) {
                throw new Exception($remoteConn->connect_error);
            }
        } catch (Exception $e) {
            // Node unreachable — mark all as failed + record IDs
            $nodeOffline = true;
            $remoteConn = null;

            $escapedNodeId = $conn->real_escape_string($nodeId);

            // Collect all IDs before marking as failed
            $resIds = $conn->query("SELECT id FROM replication_queue WHERE node_id='{$escapedNodeId}'");
            while ($r = $resIds->fetch_assoc()) {
                $failed[] = $r['id'];
            }

            // Bulk mark as failed
            $conn->query("UPDATE replication_queue SET status='failed' WHERE node_id='{$escapedNodeId}'");
            continue;
        }
    }

    if ($nodeOffline) {
        continue;
    }

    // --- Execute replication ---
    $success = false;

    if ($type === 'update') {
        $stmt = $remoteConn->prepare("
            UPDATE ntovar 
            SET pc=?, nazov=?, vyrobca=?, popis=?, kusov=?, cena=?, kod=? 
            WHERE id=?
        ");
        if ($stmt) {
            $stmt->bind_param(
                "ssssiiis",
                $data['pc'], $data['nazov'], $data['vyrobca'], $data['popis'],
                $data['kusov'], $data['cena'], $data['kod'], $data['id']
            );
            $success = $stmt->execute();
            $stmt->close();
        }
    } elseif ($type === 'delete') {
        $stmt = $remoteConn->prepare("DELETE FROM ntovar WHERE id=?");
        if ($stmt) {
            $stmt->bind_param("s", $data['id']);
            $success = $stmt->execute();
            $stmt->close();
        }
    } else {
        $stmt = $remoteConn->prepare("
            INSERT INTO ntovar (id, pc, nazov, vyrobca, popis, kusov, cena, kod, node_origin)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        if ($stmt) {
            $stmt->bind_param(
                "sssssiisi",
                $data['id'], $data['pc'], $data['nazov'], $data['vyrobca'],
                $data['popis'], $data['kusov'], $data['cena'], $data['kod'],
                $data['node_origin']
            );
            $success = $stmt->execute();
            $stmt->close();
        }
    }

    if ($success) {
        $conn->query("DELETE FROM replication_queue WHERE id='{$conn->real_escape_string($id)}'");
        $processed[] = $id;
    } else {
        $conn->query("UPDATE replication_queue SET status='failed' WHERE id='{$conn->real_escape_string($id)}'");
        $failed[] = $id;
    }
}

// --- Cleanup ---
if ($remoteConn) {
    $remoteConn->close();
}
$conn->close();

// --- JSON output ---
echo json_encode([
    'processed' => $processed,
    'failed' => $failed,
    'errors' => count($failed),
]);

?>