<?php
include 'koneksi.php';
// Mengambil ID terbesar dari tabel media_log
$q = mysqli_query($koneksi, "SELECT MAX(id) as max_id FROM media_log");
$data = mysqli_fetch_assoc($q);
echo $data['max_id'] ? $data['max_id'] : 0;
?>