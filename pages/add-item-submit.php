<?php
include("../resources/config/localConfig.php"); // $localConfig is set
include("../resources/config/config1.php");
include("../resources/config/config2.php");
include("../resources/config/config3.php");

// --- Nodes array ---
$nodes = [
    ['name'=>'node1', 'db'=>$db_config1 ?? []],
    ['name'=>'node2', 'db'=>$db_config2 ?? []],
    ['name'=>'node3', 'db'=>$db_config3 ?? []]
];

// --- Collect POST data ---
$pc      = $_POST['pc'] ?? '';
$nazov   = $_POST['nazov'] ?? '';
$vyrobca = $_POST['vyrobca'] ?? '';
$popis   = $_POST['popis'] ?? '';
$kusov   = intval($_POST['kusov'] ?? 0);
$cena    = floatval($_POST['cena'] ?? 0);
$kod     = $_POST['kod'] ?? '';
$id      = date('YmdHis');

// --- Connect to local DB ---
$conn = new mysqli($localConfig['host'], $localConfig['user'], $localConfig['pass'], $localConfig['name']);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// --- Insert replication queue entries for all nodes ---
$queueStmt = $conn->prepare(
    "INSERT INTO replication_queue (node_id, data) VALUES (?, ?)"
);

foreach ($nodes as $node) {
    $nodeDataJson = json_encode(compact('id','pc','nazov','vyrobca','popis','kusov','cena','kod'));
    $queueStmt->bind_param("ss", $node['name'], $nodeDataJson);
    $queueStmt->execute();
}

$queueStmt->close();
$conn->close();

// --- Feedback to user ---
echo "<p>Tovar pridaný do replikácie.</p>";
echo "<p><a href='../index.php?menu=list-items'>Zobraziť zoznam tovarov</a></p>";
?>
