<?php
include 'koneksi.php';

$q = mysqli_query($koneksi, "
    SELECT UNIX_TIMESTAMP(MAX(updated_at)) AS last_update
    FROM berita
");

$r = mysqli_fetch_assoc($q);
echo $r['last_update'] ?? time();
