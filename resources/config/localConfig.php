<?php
//customize on each device
$localSignature = "node1";

$configFiles = [
    __DIR__ . '/config1.php',
    __DIR__ . '/config2.php',
    __DIR__ . '/config3.php'
];

$allNodes = [];   // id => config
$localConfig = null;

foreach ($configFiles as $file) {
    include($file); // sets $node_id, $node_id, $db_config

    $allNodes[$node_id] = [
        'signature' => $node_id,
        'db' => $db_config
    ];

    if ($node_id === $localSignature) {
        $localConfig = $db_config;
    }
}

if (!$localConfig) die("Cannot determine local config.");
?>
