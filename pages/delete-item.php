<?php
include __DIR__ . '/../resources/config/localConfig.php';

$nodes = [
    ['name'=>'1', 'db'=>$db_config1 ?? []],
    ['name'=>'2', 'db'=>$db_config2 ?? []],
    ['name'=>'3', 'db'=>$db_config3 ?? []]
];

$id = $_GET['k'] ?? '';
if (!$id) die("Chýba ID záznamu.");

// Connect to local DB
$conn = new mysqli($localConfig['host'], $localConfig['user'], $localConfig['pass'], $localConfig['name']);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Kontrola node_origin lokálne
$stmt = $conn->prepare("SELECT node_origin FROM ntovar WHERE id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if (!$row) {
    die("Záznam neexistuje.");
}
if ($row['node_origin'] != $localSignature) {
    die("Môžete mazať len vlastné záznamy (node_origin = {$localSignature}).");
}
$stmt->close();

// Lokálne DELETE
$deleteStmt = $conn->prepare("DELETE FROM ntovar WHERE id = ?");
$deleteStmt->bind_param("s", $id);
$deleteStmt->execute();
$deleteStmt->close();

$repl_id = date('YmdHis') . '_' . $localSignature;

// Vlož do queue pre každý uzol s 'type' => 'delete' v data
$queueStmt = $conn->prepare("
    INSERT INTO replication_queue (id, repl_id, node_id, data) VALUES (?, ?, ?, ?)
");

foreach ($nodes as $node) {
    $id_combined = $repl_id . "_" . $node['name'];
    $data = json_encode([
        'type' => 'delete',
        'id' => $id
    ]);
    $queueStmt->bind_param("ssss", $id_combined, $repl_id, $node['name'], $data);
    $queueStmt->execute();
}

$queueStmt->close();
$conn->close();

// Feedback a presmerovanie
echo "<p>Záznam zmazaný lokálne a pridaný do replikácie.</p>";
header('Location: index.php?menu=list-items');
exit;
?>