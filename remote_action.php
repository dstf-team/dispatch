<?php
$cmd = $_GET['action'] ?? '';

$data = [
    'cmd' => $cmd,
    'time' => time() // 🔥 bikin selalu beda
];

file_put_contents("remote.txt", json_encode($data));

echo "ok";
?>