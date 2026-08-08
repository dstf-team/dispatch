<?php
include "koneksi.php";
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


$iduser = $_SESSION['iduser'];

// =====================
// TANDAI SUDAH DIBACA
// =====================
mysqli_query($koneksi,"
UPDATE komentar k 
INNER JOIN berita b ON k.id_berita = b.id_berita
SET k.status = 1
WHERE b.id_user = '$iduser'
");

// =====================
// AMBIL DATA NOTIF
// =====================
$sql = "
SELECT k.id_komentar,
       k.isi_komentar,
       k.date_created,
       b.judul
FROM komentar k
INNER JOIN berita b ON k.id_berita = b.id_berita
WHERE b.id_user = '$iduser'
ORDER BY k.id_komentar DESC
";

$q = mysqli_query($koneksi, $sql);

// CEK ERROR QUERY
if(!$q){
    die('Query Error: '.mysqli_error($koneksi));
}
?>

<h3>📩 Notifikasi Komentar</h3>
<hr>

<?php while($r = mysqli_fetch_assoc($q)){ ?>

<div style="margin-bottom:10px;border-bottom:1px solid #ccc;padding:10px;">
    <b><?php echo $r['judul']; ?></b><br>
    <?php echo $r['isi_komentar']; ?><br>
    <small><?php echo $r['date_created']; ?></small>
</div>

<?php } ?>
