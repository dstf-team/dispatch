<?php

include 'koneksi.php';

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$shift   = $_GET['shift'] ?? 'DAY';

$query = mysqli_query($koneksi,"
SELECT

a.*,

l.nama_lokasi,
l.latitude,
l.longitude

FROM aktivitas_unit a

LEFT JOIN lokasi l
ON a.lokasi_id = l.id

WHERE a.tanggal='$tanggal'
AND a.shift='$shift'
");

$data = [];

while($d = mysqli_fetch_assoc($query)){

    $data[] = $d;

}

header('Content-Type: application/json');

echo json_encode($data);