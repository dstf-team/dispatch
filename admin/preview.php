<?php
include "koneksi.php";
$id = $_GET['id'];

$q = mysqli_query($koneksi, "SELECT * FROM berita WHERE id_berita='$id'");
$data = mysqli_fetch_assoc($q);

// foto multiple
$gm = mysqli_query($koneksi, "SELECT * FROM gambar_berita WHERE id_berita='$id'");
?>

<!DOCTYPE html>
<html>
<head>
<title>Preview Laporan</title>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 30px;
    line-height: 1.6;
    color: #000;
}

/* KOP SURAT */
.header-kop {
    text-align: center;
    border-bottom: 3px solid #000;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.header-kop img {
    width: 90px;
    float: left;
    margin-right: 20px;
}

.judul-kop {
    font-size: 20px;
    font-weight: bold;
}

.sub-kop {
    font-size: 14px;
    margin-top: -5px;
}

/* KONTEN */
.section-title {
    font-size: 18px;
    font-weight: bold;
    margin-top: 30px;
    border-left: 4px solid #333;
    padding-left: 10px;
}

img.preview-foto {
    width: 260px;
    margin: 10px;
    border: 1px solid #bbb;
    padding: 5px;
    border-radius: 3px;
}

.print-button {
    padding: 8px 16px;
    background: #0066cc;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

/* PRINT MODE */
@media print {
    .print-button { display: none; }
}
</style>

</head>
<body>

<button class="print-button" onclick="window.print()">PRINT LAPORAN</button>

<div class="header-kop">
    <img src="logo.png">
    <div>
        <div class="judul-kop">PT. HALMAHERA PERSADA LYGEND</div>
        <div class="sub-kop">Jl. Merdeka No. 123, Jakarta 10220 | Telp: (021) 555123</div>
        <div class="sub-kop">Email: info@perusahaan.com</div>
    </div>
    <div style="clear: both;"></div>
</div>

<h2 style="text-align:center; text-decoration:underline;">LAPORAN BERITA KEGIATAN</h2>

<div>
    <p><b>Judul:</b> <?= $data['judul']; ?></p>
    <p><b>Tanggal Posting:</b> <?= $data['tgl_posting']; ?></p>
</div>

<div class="section-title">Isi Laporan</div>
<p style="text-align: justify;"><?= nl2br($data['isi_berita']); ?></p>


<div class="section-title">Foto Utama</div>
<?php if($data['gambar'] != "") { ?>
    <img class="preview-foto" src="gambar/<?= $data['gambar']; ?>">
<?php } else { ?>
    <p>Tidak ada foto utama.</p>
<?php } ?>


<div class="section-title">Dokumentasi (Multiple Foto)</div>
<?php
$ada = false;
while($g = mysqli_fetch_assoc($gm)) {
    $ada = true;
    echo '<img class="preview-foto" src="gambar_multi/'.$g['nama_file'].'">';
}
if(!$ada) echo "<p>Tidak ada foto dokumentasi tambahan.</p>";
?>


<?php if ($data['status'] == 'approved') { ?>
    <p>
        <b>Status:</b> 
        <span style="color:green;font-weight:bold;">APPROVED</span><br>
        <b>Approved by:</b> <?= htmlspecialchars($data['approved_by']); ?><br>
        <b>Approved at:</b> <?= htmlspecialchars($data['approved_at']); ?>
    </p>
<?php } else { ?>
    <p>
        <b>Status:</b> 
        <span style="color:orange;font-weight:bold;">PENDING APPROVAL</span>
    </p>
<?php } ?>



</body>
</html>
