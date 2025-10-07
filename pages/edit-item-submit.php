<?php
include("../resources/config/localConfig.php"); // $localConfig, $localSignature
include("../resources/config/config1.php");
include("../resources/config/config2.php");
include("../resources/config/config3.php");

// Nodes array (rovnaké ako v add-item-submit)
$nodes = [
    ['name'=>'1', 'db'=>$db_config1 ?? []],
    ['name'=>'2', 'db'=>$db_config2 ?? []],
    ['name'=>'3', 'db'=>$db_config3 ?? []]
];

// Collect POST data
$id = $_POST['id'] ?? ''; // Originálne ID záznamu
$pc = $_POST['pc'] ?? '';
$nazov = $_POST['nazov'] ?? '';
$vyrobca = $_POST['vyrobca'] ?? '';
$popis = $_POST['popis'] ?? '';
$kusov = intval($_POST['kusov'] ?? 0);
$cena = floatval($_POST['cena'] ?? 0);
$kod = $_POST['kod'] ?? '';
$node_origin = $localSignature; // Zachovať pôvodný, ale pre istotu

if (!$id) die("Chýba ID záznamu.");

// Connect to local DB
$local_conn = new mysqli($localConfig['host'], $localConfig['user'], $localConfig['pass'], $localConfig['name']);
if ($local_conn->connect_error) die("Connection failed: " . $local_conn->connect_error);

// Kontrola node_origin lokálne
$stmt = $local_conn->prepare("SELECT node_origin FROM ntovar WHERE id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if ($row['node_origin'] != $localSignature) {
    die("Môžete upravovať len vlastné záznamy.");
}
$stmt->close();

// Lokálny UPDATE
$updateStmt = $local_conn->prepare("
    UPDATE ntovar SET pc = ?, nazov = ?, vyrobca = ?, popis = ?, kusov = ?, cena = ?, kod = ? WHERE id = ?
");
$updateStmt->bind_param("ssssiiis", $pc, $nazov, $vyrobca, $popis, $kusov, $cena, $kod, $id);
$updateStmt->execute();
$updateStmt->close();

// Vytvor replikačné ID (podobne ako add)
$repl_id = date('YmdHis') . '_' . $localSignature; // Unikátne pre replikáciu

// Vlož do queue pre každý uzol s 'type' => 'update' v data
$queueStmt = $local_conn->prepare("
    INSERT INTO replication_queue (id, repl_id, node_id, data) VALUES (?, ?, ?, ?)
");

foreach ($nodes as $node) {
    $id_combined = $repl_id . "_" . $node['name'];
    $data = json_encode([
        'type' => 'update', // Nový kľúč pre typ operácie
        'id' => $id, // Originálne ID pre WHERE
        'pc' => $pc,
        'nazov' => $nazov,
        'vyrobca' => $vyrobca,
        'popis' => $popis,
        'kusov' => $kusov,
        'cena' => $cena,
        'kod' => $kod,
        'node_origin' => $node_origin // Zachovať
    ]);
    $queueStmt->bind_param("ssss", $id_combined, $repl_id, $node['name'], $data);
    $queueStmt->execute();
}

$queueStmt->close();
$local_conn->close();

// Feedback
echo "<p>Zmeny uložené lokálne a pridané do replikácie.</p>";
echo "<p><a href='../index.php?menu=list-items'>Zobraziť zoznam tovarov</a></p>";
?>