<?php
session_start();
include 'koneksi.php';

date_default_timezone_set('Asia/Jayapura'); // timezone Jayapura
$today_calendar = date('Y-m-d'); // 🔴 KHUSUS PLANNER

$hour = (int)date('H');
$today = date('Y-m-d');

// OVER SHIFT LOGIC + KOREKSI TANGGAL (TANPA UBAH STRUKTUR)
if ($hour >= 5 && $hour < 17) {
    // 05:00 – 16:59 → tampil Shift Malam (hari ini)
    $shift = 'Malam';
} else {
    // 17:00 – 23:59 & 00:00 – 04:59 → tampil Shift Pagi
    $shift = 'Pagi';

    // Jika dini hari, ambil laporan kemarin
    if ($hour < 5) {
        $today = date('Y-m-d', strtotime('-1 day'));
    }
}


// Ambil semua plan
$plans = array();
$qPlan = mysqli_query($koneksi, "SELECT DISTINCT plan FROM berita");
while ($p = mysqli_fetch_assoc($qPlan)) {
    $plans[] = $p['plan'];
}

// Ambil laporan per plan shift hari ini
$laporan_per_plan = array();
foreach ($plans as $plan) {
    $laporan_per_plan[$plan] = array();
    $planSafe = mysqli_real_escape_string($koneksi, $plan);

    // default (plan biasa)
    $whereDate  = "DATE(b.tgl_posting) = '$today'";
    $whereShift = "AND b.judul LIKE '%Shift $shift%'";

   // 🔴 KHUSUS PLAN TAILING FACILITY PLANNING
if (stripos($plan, 'tailing facility') !== false) {
    $whereDate  = "DATE(b.tgl_posting) = '$today_calendar'";
    $whereShift = ""; // planning tidak pakai shift
}


    $q = mysqli_query($koneksi, "
        SELECT b.*, k.nama_kategori, u.username
        FROM berita b
        LEFT JOIN kategori k ON b.id_kategori = k.id
        LEFT JOIN user u ON b.id_user = u.id
        WHERE b.plan='$planSafe'
          AND $whereDate
          $whereShift
        ORDER BY b.tgl_posting ASC
    ");


    while ($row = mysqli_fetch_assoc($q)) {
        $fotos = array();
        $qFoto = mysqli_query(
    $koneksi,
    "SELECT foto FROM berita_foto WHERE id_berita=" . (int)$row['id_berita']
);

        while ($f = mysqli_fetch_assoc($qFoto)) {
            $fotos[] = $f['foto'];
        }
        $row['fotos'] = $fotos;
        $laporan_per_plan[$plan][] = $row;
    }
}



?>



<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>LAPORAN BA OVER SHIFT <?php echo $shift; ?></title>
<style>
body {
    font-family: 'Arial', sans-serif;
    background: #f4f4f9;
    color: #000;
    margin: 0; padding: 0;
    overflow: hidden;
}

/* HEADER */
.header {
    width: 100%;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #002147;
    color: #fff;
    box-shadow: 0 3px 8px rgba(0,0,0,0.2);
}
.header img {
    height: 50px;
}
.header .time {
    font-size: 18px;
    font-weight: bold;
}

/* MAIN CONTAINER */
.container {
    height: calc(100vh - 70px);
    overflow: hidden;
    display: flex;
    gap: 15px;
    padding: 10px;
    box-sizing: border-box;
}

/* PLAN COLUMN */
.plan-column {
    flex:1;
    height:100%;
    overflow:hidden;
    position:relative;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.15);
    padding: 10px;
}
.scroll-content {
    position:absolute;
    width:100%;
}
.card {
    margin-bottom:20px;
    padding:15px;
    border-radius:8px;
    background: #fefefe;
    box-shadow: 0 1px 5px rgba(0,0,0,0.1);
}
.card img { 
    border-radius:6px; 
    margin-top:10px;
    max-width:100%;
}
.foto-tambahan {
    display:flex; flex-wrap:wrap; gap:10px; margin-top:10px;
}
.foto-tambahan img { width:100px; border-radius:5px; }

h2,h4 {
    margin:5px 0;
    text-align:center;
    color: #002147;
}


.plan-column {
    cursor: grab;
}

.plan-column:active {
    cursor: grabbing;
}

/* ===== FULLSCREEN IMAGE VIEW ===== */
.fullscreen-img {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.9);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.fullscreen-img img {
    max-width: 95%;
    max-height: 95%;
    border-radius: 8px;
}

.fullscreen-img::after {
    content: "✕";
    position: absolute;
    top: 20px;
    right: 30px;
    font-size: 40px;
    color: #fff;
    cursor: pointer;
}

.card img,
.foto-tambahan img {
    cursor: zoom-in;
}


</style>
</head>
<body>

<div class="header">
    <img src="/admin/logo/hpal-BG.png" alt="son"> 
    <div class="time" id="time-jayapura"></div>
</div>

<div class="container"  id="laporan-container">
<?php
foreach ($laporan_per_plan as $plan => $laporans) {
    echo '<div class="plan-column">';
    echo '<h2>Plan: ' . htmlspecialchars($plan) . '<br>Shift ' . $shift . '</h2>';
    echo '<div class="scroll-content">';
    if (count($laporans) === 0) {
        echo '<p style="text-align:center;">Tidak ada laporan hari ini.</p>';
    } else {
        foreach ($laporans as $l) {
            echo '<div class="card">';
            echo '<h4>' . htmlspecialchars($l['judul']) . '</h4>';
            echo '<p><b>Plan:</b> ' . htmlspecialchars($l['nama_kategori']) . '</p>';
            echo '<p><b>By:</b> ' . htmlspecialchars($l['username']) . '</p>';
            echo '<p><b>Tanggal:</b> ' . htmlspecialchars($l['tgl_posting']) . '</p>';
            echo '<hr>';
            echo '<div style="line-height:1.5;">' . $l['isi_berita'] . '</div>';

           // ===== GAMBAR UTAMA =====
if (!empty($l['gambar'])) {
    echo '
    <div class="mb-2 text-center">
        <img src="/admin/foto_berita/'.$l['gambar'].'"
             class="gallery-img"
             style="max-width:100%; max-height:300px; object-fit:cover; border-radius:12px;">
    </div>';
}

// ===== GALERI TAMBAHAN =====
if (!empty($l['fotos'])) {
    echo '<div class="foto-tambahan">';
    foreach ($l['fotos'] as $f) {
        echo '
        <img src="/admin/foto_berita/'.$f.'"
             class="gallery-img"
             style="width:120px; height:120px; object-fit:cover; border-radius:8px;">';
    }
    echo '</div>';
}


            echo '</div>'; // card
        }
    }
    echo '</div>'; // scroll-content
    echo '</div>'; // plan-column
}
?>
</div>

<div class="fullscreen-img" id="fullscreenImg">
    <img id="fullscreenTarget">
</div>



<script>
function waitImagesLoaded(container, callback) {
    const images = container.querySelectorAll('img');
    let loaded = 0;

    if (images.length === 0) {
        callback();
        return;
    }

    images.forEach(img => {
        if (img.complete) {
            loaded++;
        } else {
            img.onload = img.onerror = () => {
                loaded++;
                if (loaded === images.length) callback();
            };
        }
    });

    if (loaded === images.length) callback();
}
</script>

<!-- Bagian script update jam -->
<script>
function initAutoScroll() {

    document.querySelectorAll('.plan-column').forEach(function(col){

        const content = col.querySelector('.scroll-content');
        const containerHeight = col.offsetHeight;

        // reset posisi
        content.style.transform = 'translateY(0px)';

        // hitung tinggi konten asli
        const realHeight = content.scrollHeight;

        // ❌ JIKA KONTEN TIDAK LEBIH PANJANG → DIAM
        if (realHeight <= containerHeight + 20) {
            col.classList.remove('is-looping');
            return;
        }

        // ✅ ADA BANYAK LAPORAN → AKTIF LOOP
        col.classList.add('is-looping');

        // clone SEKALI saja
        if (!content.dataset.cloned) {
            content.innerHTML += content.innerHTML;
            content.dataset.cloned = 'true';
        }

        let offset = 0;
        let speed = 0.3; // 🔥 kecepatan scroll (pelan & halus)

        let isHover = false;
        let pauseAutoScroll = false;

        function scrollLoop(){
            if (!isHover && !pauseAutoScroll) {
                offset += speed;

                if (offset >= content.scrollHeight / 2) {
                    offset = 0; // loop mulus
                }

                content.style.transform =
                    `translate3d(0, ${-offset}px, 0)`;
            }
            requestAnimationFrame(scrollLoop);
        }

        requestAnimationFrame(scrollLoop);

        // hover → pause
        col.addEventListener('mouseenter', () => isHover = true);
        col.addEventListener('mouseleave', () => isHover = false);

        // wheel manual
        col.addEventListener('wheel', function(e){
            e.preventDefault();
            pauseAutoScroll = true;

            offset += e.deltaY;
            if (offset < 0) offset = 0;
            if (offset >= content.scrollHeight / 2) offset = 0;

            content.style.transform = `translateY(${-offset}px)`;

            clearTimeout(col._resumeScroll);
            col._resumeScroll = setTimeout(() => {
                pauseAutoScroll = false;
            }, 1500);
        }, { passive: false });

    });
}
</script>






<script>
// Update waktu realtime Jayapura (hari + tanggal + jam)
function updateTime() {
    const now = new Date();
    const wit = new Date(now.toLocaleString("en-US", { timeZone: "Asia/Jayapura" }));

    const hariNama = ["Minggu","Senin","Selasa","Rabu","Kamis","Jumat","Sabtu"];
    const bulanNama = ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"];

    const hari = hariNama[wit.getDay()];
    const tanggal = String(wit.getDate()).padStart(2,'0');
    const bulan = bulanNama[wit.getMonth()];
    const tahun = wit.getFullYear();

    const jam = String(wit.getHours()).padStart(2,'0');
    const menit = String(wit.getMinutes()).padStart(2,'0');
    const detik = String(wit.getSeconds()).padStart(2,'0');

    document.getElementById('time-jayapura').innerText =
        `${hari}, ${tanggal} ${bulan} ${tahun} ${jam}:${menit}:${detik} WIT`;
}

updateTime();
setInterval(updateTime, 1000);
</script>
<script>
const fsBox = document.getElementById('fullscreenImg');
const fsImg = document.getElementById('fullscreenTarget');

// klik gambar → fullscreen
document.addEventListener('click', function(e) {
    if (e.target.tagName === 'IMG' &&
        (e.target.closest('.card') || e.target.closest('.foto-tambahan'))) {

        fsImg.src = e.target.src;
        fsBox.style.display = 'flex';
    }
});

// klik area gelap / tombol X → tutup
fsBox.addEventListener('click', function() {
    fsBox.style.display = 'none';
    fsImg.src = '';
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    function openFullscreen() {
        const elem = document.documentElement;

        if (elem.requestFullscreen) {
            elem.requestFullscreen();
        } else if (elem.webkitRequestFullscreen) { // Safari
            elem.webkitRequestFullscreen();
        } else if (elem.msRequestFullscreen) { // IE11
            elem.msRequestFullscreen();
        }
    }

    // Delay sedikit supaya halaman siap
    setTimeout(openFullscreen, 500);

});
</script>
<div id="fs-overlay" style="
    position:fixed;
    inset:0;
    background:#000;
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:28px;
    z-index:99999;
    cursor:pointer;
">
    Klik untuk masuk Fullscreen
</div>

<script>
document.getElementById('fs-overlay').addEventListener('click', function(){
    const elem = document.documentElement;
    if (elem.requestFullscreen) elem.requestFullscreen();
    this.style.display = 'none';
});
</script>


<script>
function refreshLaporan() {
    fetch('load_slide.php?_=' + new Date().getTime())
        .then(res => res.text())
        .then(html => {
            const container = document.getElementById('laporan-container');
            container.innerHTML = html;

            // ⏱ tunggu DOM & layout benar-benar siap
            setTimeout(() => {
                // pertama kali load
                setTimeout(initAutoScroll, 300);

            }, 300);
        })
        .catch(err => console.error('Gagal refresh laporan:', err));
}

</script>


<script>
let lastUpdate = 0;

// 🔥 LOAD PERTAMA KALI
document.addEventListener("DOMContentLoaded", function () {
    refreshLaporan(); // WAJIB
    cekUpdate();      // mulai polling
});

// cek update berkala
setInterval(cekUpdate, 5000);

// FORCE RELOAD WALAU TIDAK ADA UPDATE
setInterval(() => {
    refreshLaporan();
}, 300000); // 5
function cekUpdate() {
    fetch('cek_update.php?_=' + new Date().getTime())
        .then(res => res.text())
        .then(serverTime => {
            serverTime = parseInt(serverTime);
            if (serverTime > lastUpdate) {
                lastUpdate = serverTime;
                refreshLaporan();
            }
        });
}
</script>


</body>
</html>
