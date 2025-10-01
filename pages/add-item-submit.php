<?php

//TODO use __DIR__ and not this path
include("../resources/config/localConfig.php"); // $localConfig is set
include("../resources/config/config1.php");
include("../resources/config/config2.php");
include("../resources/config/config3.php");

// --- Nodes array ---
$nodes = [
    ['name'=>'1', 'db'=>$db_config1 ?? []],
    ['name'=>'2', 'db'=>$db_config2 ?? []],
    ['name'=>'3', 'db'=>$db_config3 ?? []]
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
$node_origin = $localSignature;

// --- Connect to local DB ---
$conn = new mysqli($localConfig['host'], $localConfig['user'], $localConfig['pass'], $localConfig['name']);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$repl_id = $id; // rovnaké id ako v datach


$queueStmt = $conn->prepare(
    "INSERT INTO replication_queue (id, repl_id, node_id, data) VALUES (?, ?, ?, ?)"
);


foreach ($nodes as $node) {
    $id_combined = $id . "_" .  $node['name'];
    $nodeDataJson = json_encode(compact('id','pc','nazov','vyrobca','popis','kusov','cena','kod', 'node_origin'));
    $queueStmt->bind_param("ssss", $id_combined, $repl_id, $node['name'], $nodeDataJson);
    $queueStmt->execute();
}

$queueStmt->close();
$conn->close();

//TODO call the process-queue here? or let the background process handle it?

// --- Feedback to user ---
echo "<p>Tovar pridaný do replikácie.</p>";
echo "<p><a href='../index.php?menu=list-items'>Zobraziť zoznam tovarov</a></p>";
?>
