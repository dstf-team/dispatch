<?php
include 'koneksi.php';

$q = mysqli_query($koneksi,"SELECT * FROM media_log ORDER BY id ASC");

$data = [];

while($row = mysqli_fetch_assoc($q)){
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
?>