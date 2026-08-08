<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

session_start();
include 'koneksi.php';

date_default_timezone_set('Asia/Jayapura');

$hour = (int)date('H');
$today = date('Y-m-d');

if ($hour >= 3 && $hour < 15) {
    $shift = 'Malam';
} else {
    $shift = 'Pagi';
    if ($hour < 3) {
        $today = date('Y-m-d', strtotime('-1 day'));
    }
}

// ambil plan
$plans = [];
$qPlan = mysqli_query($koneksi, "SELECT DISTINCT plan FROM berita");
while ($p = mysqli_fetch_assoc($qPlan)) {
    $plans[] = $p['plan'];
}

foreach ($plans as $plan) {

    echo '<div class="plan-column">';
    echo '<h2>Plan: '.htmlspecialchars($plan).'<br>Shift '.$shift.'</h2>';
    echo '<div class="scroll-content">';

    $planSafe = mysqli_real_escape_string($koneksi, $plan);

    $whereDate  = "DATE(b.tgl_posting) = '$today'";
    $whereShift = "AND b.judul LIKE '%Shift $shift%'";
    $orderBy    = "ORDER BY b.tgl_posting ASC";

    if (stripos($plan, 'Tailing Facility Planning') !== false) {
        $whereDate  = "1=1";
        $whereShift = "";
        $orderBy    = "ORDER BY b.tgl_posting DESC LIMIT 1";
    }

    $q = mysqli_query($koneksi, "
        SELECT b.*, k.nama_kategori, u.username
        FROM berita b
        LEFT JOIN kategori k ON b.id_kategori = k.id
        LEFT JOIN user u ON b.id_user = u.id
        WHERE b.plan = '$planSafe'
          AND $whereDate
          $whereShift
        $orderBy
    ");

    if (mysqli_num_rows($q) == 0) {
        echo '<p style="text-align:center;">Tidak ada laporan hari ini.</p>';
    }

    while ($l = mysqli_fetch_assoc($q)) {

        echo '<div class="card">';
        echo '<h4>'.$l['judul'].'</h4>';
        echo '<p><b>Kategori:</b> '.$l['nama_kategori'].'</p>';
        echo '<p><b>User:</b> '.$l['username'].'</p>';
        echo '<p><b>Tanggal:</b> '.$l['tgl_posting'].'</p>';
        echo '<hr>';
        echo '<div>'.$l['isi_berita'].'</div>';

        // gambar utama
        if ($l['gambar']) {
            echo '<img src="/admin/foto_berita/'.$l['gambar'].'" style="max-width:100%;border-radius:10px;">';
        }

        // ===== FOTO TAMBAHAN =====
        $fotos = [];
        $qFoto = mysqli_query(
            $koneksi,
            "SELECT foto FROM berita_foto WHERE id_berita=".(int)$l['id_berita']
        );
        while ($f = mysqli_fetch_assoc($qFoto)) {
            $fotos[] = $f['foto'];
        }

        if (!empty($fotos)) {
            echo '<div class="foto-tambahan">';
            foreach ($fotos as $f) {
                echo '<img src="/admin/foto_berita/'.$f.'" style="width:120px;height:120px;object-fit:cover;border-radius:8px;">';
            }
            echo '</div>';
        }

        echo '</div>'; // card
    }

    echo '</div>'; // scroll-content
    echo '</div>'; // plan-column
}
