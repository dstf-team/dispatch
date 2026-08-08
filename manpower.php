<?php
session_start();
include 'koneksi.php';
date_default_timezone_set('Asia/Jayapura');

/* =========================
   AMBIL FILTER
========================= */
$filter_devisi  = $_GET['devisi'] ?? '';
$filter_jabatan = $_GET['jabatan'] ?? '';
$filter_nama = $_GET['nama'] ?? '';
$filter_status = $_GET['status'] ?? 'AKTIF';

if($filter_status == 'AKTIF'){

    $where = "WHERE status_bekerja='AKTIF'";

}
elseif($filter_status == 'NONAKTIF'){

    $where = "WHERE status_bekerja IN ('RESIGN','MUTASI')";

}
else{

    $where = "WHERE status_bekerja='AKTIF'";

}

if(!empty($filter_devisi)){
    $safe = mysqli_real_escape_string($koneksi,$filter_devisi);
    $where .= " AND devisi='$safe'";
}

if(!empty($filter_jabatan)){
    $safe = mysqli_real_escape_string($koneksi,$filter_jabatan);
    $where .= " AND jabatan='$safe'";
}

if(!empty($filter_nama)){
    $safe = mysqli_real_escape_string($koneksi,$filter_nama);
    $where .= " AND nama LIKE '%$safe%'";
}

/* =========================
   AMBIL DATA FILTER OPTION
========================= */
$devisi_list = [];
$q = mysqli_query($koneksi,"SELECT DISTINCT devisi FROM manpower ORDER BY devisi ASC");
while($r=mysqli_fetch_assoc($q)){
    $devisi_list[] = $r['devisi'];
}

$jabatan_list = [];
$q = mysqli_query($koneksi,"SELECT DISTINCT jabatan FROM manpower ORDER BY jabatan ASC");
while($r=mysqli_fetch_assoc($q)){
    $jabatan_list[] = $r['jabatan'];
}

/* =========================
   AMBIL DATA MANPOWER
========================= */
$manpower = [];
$q = mysqli_query($koneksi,"
    SELECT * FROM manpower 
    $where 
    ORDER BY devisi ASC, jabatan ASC, nama ASC
");

while($r=mysqli_fetch_assoc($q)){

$masaKerja = "-";

if (!empty($r['tanggal_masuk'])) {

    $tgl_masuk = new DateTime($r['tanggal_masuk']);

    // Jika RESIGN dan ada tanggal_resign → berhenti di tanggal resign
    if ($r['status_bekerja'] == 'RESIGN' && !empty($r['tanggal_resign'])) {
        $endDate = new DateTime($r['tanggal_resign']);
    } else {
        $endDate = new DateTime(); // masih aktif = hari ini
    }

    $diff = $endDate->diff($tgl_masuk);
    $masaKerja = $diff->y." Th ".$diff->m." Bln";
}

    $r['masa_kerja'] = $masaKerja;
    $manpower[] = $r;
}
?>


<?php

/* =========================
   TOTAL AKTIF PER DEVISI
========================= */
$totalDevisi = [];

$qTotal = mysqli_query($koneksi,"
    SELECT devisi, COUNT(*) as total 
    FROM manpower
    WHERE status_bekerja='AKTIF'
    GROUP BY devisi
    ORDER BY devisi ASC
");

while($r = mysqli_fetch_assoc($qTotal)){
    $totalDevisi[] = $r;
}

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>DSTF Manpower Dashboard</title>

<style>
body{margin:0;font-family:'Segoe UI';background:#f4f6fb;}
.header{
    background:#002147;color:#fff;padding:15px 30px;
    display:flex;justify-content:space-between;align-items:center;
}
.container{padding:30px;}

.filter-box{
    background:#fff;
    padding:20px;
    border-radius:12px;
    margin-bottom:25px;
    box-shadow:0 5px 15px rgba(0,0,0,0.08);
    display:flex;
    gap:15px;
    flex-wrap:wrap;
    align-items:center;
}

select,button{
    padding:8px 12px;
    border-radius:8px;
    border:1px solid #ccc;
}

button{
    background:#002147;
    color:#fff;
    cursor:pointer;
}

.grid{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(280px,1fr));
    gap:25px;
}

.card{
    background:#fff;
    border-radius:15px;
    box-shadow:0 8px 25px rgba(0,0,0,0.08);
    padding:20px;
    transition:.3s;
}
.card:hover{transform:translateY(-5px);}

.profile-img{
    width:80px;height:80px;border-radius:50%;
    object-fit:cover;border:3px solid #002147;
}

.badge{
    padding:4px 10px;border-radius:20px;
    font-size:11px;color:#fff;background:#28a745;
}

.nama{font-weight:600;margin-top:10px;font-size:16px;}
.jabatan{font-size:13px;color:#666;}
.info{font-size:12px;margin-top:10px;line-height:1.6;}


.ket-box{
    margin-top:12px;
    padding:10px;
    border-radius:10px;
    font-size:12px;
    line-height:1.5;
}

.ket-pelanggaran{
    background:#ffe5e5;
    border-left:4px solid #dc3545;
    color:#7a0000;
}

.ket-umum{
    background:#e7f1ff;
    border-left:4px solid #0d6efd;
    color:#003c8f;
}

/* ================= tiohle ================= */
.status-toggle{
    display:inline-flex;
    background:#e0e0e0;
    border-radius:50px;
    padding:5px;
    margin-bottom:20px;
}

.status-toggle a{
    padding:8px 20px;
    border-radius:50px;
    text-decoration:none;
    font-weight:600;
    font-size:13px;
    color:#333;
    transition:.3s;
}

.status-toggle a.active{
    background:#002147;
    color:#fff;
    box-shadow:0 4px 10px rgba(0,0,0,0.2);
}

/* ================= ZOOM FOTO ================= */
.zoom-overlay{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.9);
    display:none;
    align-items:center;
    justify-content:center;
    z-index:9999;
}

.zoom-overlay img{
    max-width:90%;
    max-height:90%;
    border-radius:12px;
    box-shadow:0 10px 40px rgba(0,0,0,0.5);
    animation:zoomIn .3s ease;
}

@keyframes zoomIn{
    from{transform:scale(.7);opacity:0;}
    to{transform:scale(1);opacity:1;}
}

.profile-img{
    cursor:zoom-in;
}

/* ================= SCROLL TO TOP ================= */
#scrollTopBtn{
    position:fixed;
    bottom:30px;
    right:30px;
    z-index:999;
    background:#002147;
    color:#fff;
    border:none;
    padding:12px 18px;
    border-radius:8px; /* kotak, bukan bulat */
    font-size:14px;
    font-weight:600;
    cursor:pointer;
    box-shadow:0 5px 15px rgba(0,0,0,0.3);
    display:none;
    transition:0.3s;
}

#scrollTopBtn:hover{
    background:#004080;
    transform:translateY(-3px);
}


/* ================= SUm BOX ================= */

.summary-wrapper{
    display:flex;
    justify-content:center; /* tengah */
    margin-bottom:35px;
}

.summary-box{
    display:flex;
    gap:25px;
    flex-wrap:wrap;
    justify-content:center; /* card tetap center */
}

.summary-card{
    background:#ffffff;
    padding:20px 30px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.08);
    min-width:220px;
    text-align:center;
    transition:0.3s;
    border-top:4px solid #002147;
}

.summary-card:hover{
    transform:translateY(-5px);
}

.summary-title{
    font-size:14px;
    color:#555;
    margin-bottom:8px;
}

.summary-number{
    font-size:30px;
    font-weight:700;
    color:#002147;
}

.summary-label{
    font-size:12px;
    color:#888;
}


/* ================= STICKY FILTER ================= */



.sticky-filter{
    position:sticky;
    top:70px; /* sesuaikan tinggi header */
    z-index:1000;
    background:#ffffff;
}
.sticky-filter{
    position:sticky;
    top:70px;
    z-index:1000;
    background:#ffffff;
    box-shadow:0 4px 12px rgba(0,0,0,0.08);
}
/* ================= FLOATING FILTER ================= */

.filter-fixed{
    position:fixed;
    top:0;
    left:0;
    width:100%;
    z-index:2000;
    border-radius:0;
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
}

/* supaya konten tidak loncat saat filter jadi fixed */
.filter-spacer{
    height:100px; /* nanti disesuaikan otomatis */
}
/* ================= FILTER NORMAL ================= */
#stickyFilter{
    transition:all 0.3s ease;
}

/* ================= SAAT JADI STICKY ================= */
.filter-fixed{
    position:fixed;
    top:0;
    left:0;
    width:100%;
    z-index:2000;
    border-radius:0;
    background:#002147; /* berubah biru */
    box-shadow:0 5px 20px rgba(0,0,0,0.2);
}

/* Ubah warna teks & label saat sticky */
.filter-fixed label,
.filter-fixed b{
    color:#ffffff;
}

/* Input & select tetap putih */
.filter-fixed input,
.filter-fixed select{
    background:#ffffff;
    color:#000000;
}
</style>
</head>

<body>

<div class="header"> 
    <div class="status-toggle">
        <a href="?page=manpower_card&status=AKTIF"
           class="<?= ($filter_status=='AKTIF')?'active':''; ?>">
           AKTIF
        </a>

        <a href="?page=manpower_card&status=NONAKTIF"
           class="<?= ($filter_status=='NONAKTIF')?'active':''; ?>">
           NONAKTIF
        </a>
    </div>

    <h2>DSTF MANPOWER</h2>
   
    <div id="clock"></div>
</div>



<div class="container">

<form method="GET" class="filter-box" id="stickyFilter">
    <input type="hidden" name="page" value="manpower_card">
    <input type="hidden" name="status" value="<?= $filter_status; ?>">

    <label><b>Nama:</b></label>
    <input type="text" 
       name="nama"
       id="searchNama"
       placeholder="Cari nama..."
       value="<?= htmlspecialchars($filter_nama); ?>"style="padding:8px 12px;border-radius:8px;border:1px solid #ccc;">


    <label><b>Devisi:</b></label>
    <select name="devisi">
        <option value="">Semua</option>
        <?php foreach($devisi_list as $d){ ?>
            <option value="<?= $d ?>" <?= ($filter_devisi==$d)?'selected':''; ?>>
                <?= $d ?>
            </option>
        <?php } ?>
    </select>

    <label><b>Jabatan:</b></label>
    <select name="jabatan">
        <option value="">Semua</option>
        <?php foreach($jabatan_list as $j){ ?>
            <option value="<?= $j ?>" <?= ($filter_jabatan==$j)?'selected':''; ?>>
                <?= $j ?>
            </option>
        <?php } ?>
    </select>


    
</form>

<div class="summary-box">
    <?php foreach($totalDevisi as $td){ ?>
        <div class="summary-card">
            <div class="summary-title"><?= htmlspecialchars($td['devisi']); ?></div>
            <div class="summary-number"><?= $td['total']; ?></div>
            <div class="summary-label">Manpower Aktif</div>
        </div>
    <?php } ?>
</div>


<div class="grid">


<?php if(empty($manpower)){ ?>
    <p>Tidak ada data.</p>
<?php } ?>

<?php 
$lastDevisi = '';
$lastJabatan = '';

foreach($manpower as $m){ 

    // Header Devisi
    if($lastDevisi != $m['devisi']){
        echo "<div style='grid-column:1/-1;margin-top:30px;margin-bottom:10px;'>
                <h2 style=\"margin:0;color:#002147;border-left:6px solid #002147;padding-left:10px;\">
                    ".htmlspecialchars($m['devisi'])."
                </h2>
              </div>";
        $lastDevisi = $m['devisi'];
        $lastJabatan = '';
    }

    // Header Jabatan
    if($lastJabatan != $m['jabatan']){
        echo "<div style='grid-column:1/-1;margin-bottom:10px;'>
                <h3 style=\"margin:0;color:#555;padding-left:15px;\">
                    ".htmlspecialchars($m['jabatan'])."
                </h3>
              </div>";
        $lastJabatan = $m['jabatan'];
    }
?>
<div class="card">

    <div style="display:flex;justify-content:space-between;">
        <img src="uploads/manpower/<?php echo $m['foto'] ?: 'no-image.png'; ?>" class="profile-img">
        <?php
            $warna = '#28a745';
            if($m['status_bekerja']=='RESIGN') $warna = '#dc3545';
            if($m['status_bekerja']=='MUTASI') $warna = '#ffc107';
            ?>

            <span class="badge" style="background:<?= $warna; ?>">
            <?= $m['status_bekerja']; ?>
            </span>
    </div>

    <div class="nama"><?= htmlspecialchars($m['nama']); ?></div>
    <div class="jabatan"><?= htmlspecialchars($m['jabatan']); ?></div>

    <div class="info">

            <?php if(!empty($m['nik'])){ ?>
                <b>NIK:</b> <?= htmlspecialchars($m['nik']); ?><br>
            <?php } ?>

            <?php if(!empty($m['jabatan_tambahan'])){ ?>
                <b>Simper:</b> <?= htmlspecialchars($m['jabatan_tambahan']); ?><br>
            <?php } ?>

            <?php if(!empty($m['devisi'])){ ?>
                <b>Devisi:</b> <?= htmlspecialchars($m['devisi']); ?><br>
            <?php } ?>

            <?php if(!empty($m['status_kerja'])){ ?>
                <b>Status Kerja:</b> <?= htmlspecialchars($m['status_kerja']); ?><br>
            <?php } ?>

            <?php if(!empty($m['tanggal_masuk']) && $m['tanggal_masuk'] != "-"){ ?>
                <b>Tanggal Masuk:</b> <?= $m['tanggal_masuk']; ?><br>
            <?php } ?>

            <?php if(!empty($m['masa_kerja']) && $m['masa_kerja'] != "-"){ ?>
                <b>Masa Kerja:</b> <?= $m['masa_kerja']; ?><br>
            <?php } ?>

            <?php if(!empty($m['poh'])){ ?>
                <b>POH:</b> <?= htmlspecialchars($m['poh']); ?><br>
            <?php } ?>
            <?php if($m['status_bekerja']=='RESIGN' && !empty($m['tanggal_resign'])){ ?>
            <b>Tanggal Resign:</b> <?= date('d-m-Y', strtotime($m['tanggal_resign'])); ?><br>
             <?php } ?>

            <?php if(!empty($m['keterangan_pelanggaran'])){ ?>
                <div class="ket-box ket-pelanggaran">
                    <b>⚠ Pelanggaran:</b><br>
                    <?= nl2br(htmlspecialchars($m['keterangan_pelanggaran'])); ?>
                </div>
            <?php } ?>

            <?php if(!empty($m['keterangan'])){ ?>
                <div class="ket-box ket-umum">
                    <b>ℹ Keterangan:</b><br>
                    <?= nl2br(htmlspecialchars($m['keterangan'])); ?>
                </div>
            <?php } ?>

    </div>

</div>
<?php } ?>

</div>
</div>
<div class="zoom-overlay" id="zoomOverlay">
    <img id="zoomImg">
</div>




<button id="scrollTopBtn" title="Kembali ke atas">⬆</button>


<script>
const zoomOverlay = document.getElementById('zoomOverlay');
const zoomImg = document.getElementById('zoomImg');

// klik foto PP
document.querySelectorAll('.profile-img').forEach(img=>{
    img.addEventListener('click', function(){
        if(this.src.includes('no-image.png')) return;
        zoomImg.src = this.src;
        zoomOverlay.style.display = 'flex';
    });
});

// klik background tuk tutup
zoomOverlay.addEventListener('click', function(){
    zoomOverlay.style.display = 'none';
    zoomImg.src = '';
});

// tekan ESC =tuk tutup
document.addEventListener('keydown', function(e){
    if(e.key === "Escape"){
        zoomOverlay.style.display = 'none';
        zoomImg.src = '';
    }
});
</script>

<script>
function updateClock(){
    const now = new Date();
    const wit = new Date(now.toLocaleString("en-US",{timeZone:"Asia/Jayapura"}));
    document.getElementById('clock').innerText ="Site Kawasi, Obi, Halmahera Selatan |"+
        wit.toLocaleDateString('id-ID')+" "+wit.toLocaleTimeString('id-ID')+" WIT";
}
updateClock();
setInterval(updateClock,1000);
</script>
<script>
const scrollBtn = document.getElementById("scrollTopBtn");

// Muncul kalau scroll lebih dari 300px
window.addEventListener("scroll", function(){
    if (window.scrollY > 300){
        scrollBtn.style.display = "block";
    } else {
        scrollBtn.style.display = "none";
    }
});

// Klik = scroll halus ke atas
scrollBtn.addEventListener("click", function(){
    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });
});
</script>

<script>
const filter = document.getElementById("stickyFilter");
const filterOffset = filter.offsetTop;

// Buat spacer
const spacer = document.createElement("div");
spacer.classList.add("filter-spacer");
spacer.style.display = "none";
filter.parentNode.insertBefore(spacer, filter);

window.addEventListener("scroll", function(){
    if(window.pageYOffset > filterOffset){
        filter.classList.add("filter-fixed");
        spacer.style.display = "block";
        spacer.style.height = filter.offsetHeight + "px";
    }else{
        filter.classList.remove("filter-fixed");
        spacer.style.display = "none";
    }
});
</script>

<script>
const form = document.getElementById("stickyFilter");
const searchInput = document.getElementById("searchNama");
const selects = form.querySelectorAll("select");

let typingTimer;
const delay = 500; // delay 0.5 detik biar tidak reload tiap huruf

// Real-time saat ketik nama
searchInput.addEventListener("keyup", function(){
    clearTimeout(typingTimer);
    typingTimer = setTimeout(() => {
        form.submit();
    }, delay);
});

// Langsung submit saat ganti select
selects.forEach(select => {
    select.addEventListener("change", function(){
        form.submit();
    });
});
</script>

</body>
</html>