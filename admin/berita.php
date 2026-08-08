<style>
.cke_notification {
    display:none !important;
}
</style>
<style>
/* Semua input, textarea, select agar teks hitam dan background putih */
input,
textarea,
select {
    color: #000 !important;          /* teks hitam */
    background-color: #fff !important; /* background putih */
    opacity: 1 !important;            /* hilangkan transparansi default */
}

/* Semua option di select juga hitam */
select option {
    color: #000 !important;
    background-color: #fff !important;
}

/* CKEditor */
.cke_contents {
    color: #000 !important;
    background-color: #fff !important;
}
</style>

<style>
/* === KHUSUS PREVIEW === */
.preview-wrapper,
.preview-wrapper p,
.preview-wrapper div,
.preview-wrapper span {
    color: #000 !important;
}

.preview-wrapper {
    background: #fff !important;
}

.preview-wrapper .card {
    background: #fff !important;
    border: 1px solid #ddd;
}

/* gambar */
.preview-wrapper img {
    background: #fff;
}
</style>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../koneksi.php'; // sesuaikan jika koneksi di parent folder

$iduser = isset($_SESSION['iduser']) ? $_SESSION['iduser'] : '';
$level  = isset($_SESSION['level'])  ? $_SESSION['level']  : '';
$plan   = isset($_SESSION['plan'])   ? $_SESSION['plan']   : '';


// === UPDATE AKTIVITAS USER UNTUK ONLINE ===
if(!empty($iduser)){
    mysqli_query($koneksi, "
        UPDATE user 
        SET last_activity = NOW() 
        WHERE iduser = '".intval($iduser)."'
    ");
}


$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : 'list';
?>

<div class="d-flex left-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <a href="index.php">Home</a> / Laporan
</div>

<?php
switch ($aksi) {

case 'list':
// ================= PAGINATION =================
$opsi_limit = [5, 10, 50, 100];
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
if (!in_array($limit, $opsi_limit)) $limit = 10;

$halaman = isset($_GET['hal']) ? (int)$_GET['hal'] : 1;
$halaman = ($halaman < 1) ? 1 : $halaman;
$offset  = ($halaman - 1) * $limit;
// =============================================
?>




<a href="?page=berita&aksi=entri" class="btn btn-primary fa fa-plus"> Entri Laporan BA</a>
<form method="get" class="mt-3 mb-2">
    <input type="hidden" name="page" value="berita">
    <input type="hidden" name="aksi" value="list">

    <label>Tampilkan</label>
    <select name="limit" onchange="this.form.submit()" class="form-select d-inline w-auto">
        <?php foreach ($opsi_limit as $l) { ?>
            <option value="<?= $l ?>" <?= ($limit==$l)?'selected':'' ?>>
                <?= $l ?>
            </option>
        <?php } ?>
    </select>
    <span>data</span>
</form>

<h2>List Laporan</h2>
<style>
.table td,
.table th {
  color:#000 !important;
}
</style>

<table class="table table-bordered table-striped">
    <tr align="center">
        <th>No</th>
        <th>Kategori</th>
        <th>User</th>
        <th>Judul</th>
        <th>Tanggal</th>
        <th>Status</th>
        <th>Aksi</th>
    </tr>

<?php
$no = $offset + 1;

$cari = isset($_GET['cari']) ? mysqli_real_escape_string($koneksi, $_GET['cari']) : '';
$filter = ($cari != '') ? "
AND (
    b.judul LIKE '%$cari%' OR
    b.isi_berita LIKE '%$cari%' OR
    b.tgl_posting LIKE '%$cari%' OR
    u.username LIKE '%$cari%'
)
" : "";

// ================= PLAN FILTER =================
$plan_sql = ''; // DEFAULT WAJIB ADA

if (strtolower($level) !== 'administrator' && $plan != '') {
    $plan_sql = "AND b.plan='".mysqli_real_escape_string($koneksi, $plan)."'";
}

// ===============================================


// ===== HITUNG TOTAL DATA =====
$sql_total = "
SELECT COUNT(*) AS total
FROM berita b
LEFT JOIN user u ON b.id_user = u.id
WHERE 1=1
AND b.deleted_at IS NULL
$plan_sql
$filter
";


$q_total = mysqli_query($koneksi, $sql_total);
$total = mysqli_fetch_assoc($q_total)['total'];
$total_halaman = ceil($total / $limit);

// ===== QUERY DATA =====
$sql = "
SELECT b.*, u.username
FROM berita b
LEFT JOIN user u ON b.id_user = u.id
WHERE 1=1
AND b.deleted_at IS NULL
$plan_sql
$filter
ORDER BY b.id_berita DESC
LIMIT $offset, $limit
";


$tampil = mysqli_query($koneksi, $sql);

while ($data = mysqli_fetch_assoc($tampil)) {
$bolehApprove = (
    $data['status'] === 'pending' &&                // masih pending
    $data['id_user'] != $iduser &&                   // bukan input sendiri
    strtolower($level) !== 'viewer'                  // bukan viewer
);

$k = mysqli_fetch_assoc(mysqli_query(
    $koneksi,
    "SELECT nama_kategori FROM kategori WHERE id='".intval($data['id_kategori'])."'"
)) ?: ['nama_kategori'=>'-'];

$u = mysqli_fetch_assoc(mysqli_query(
    $koneksi,
    "SELECT username FROM user WHERE id='".intval($data['id_user'])."'"
)) ?: ['username'=>'-'];
?>
<tr>
    <td><?= $no++; ?></td>
    <td><?= htmlspecialchars($k['nama_kategori']); ?></td>
    <td><?= htmlspecialchars($u['username']); ?></td>
    <td><?= htmlspecialchars($data['judul']); ?></td>
    <td><?= htmlspecialchars($data['tgl_posting']); ?></td>
    <td align="center">
    <?php if ($data['status'] == 'approved') { ?>
        <span class="badge bg-success">APPROVED</span>
    <?php } else { ?>
        <span class="badge bg-warning text-dark">PENDING</span>
    <?php } ?>
</td>

     <td align="center">
        <?php if ($bolehApprove) { ?>
        <a href="aksi_berita.php?page=berita&proses=approve&id=<?= $data['id_berita']; ?>"
           onclick="return confirm('Approve laporan ini?')"
           class="btn btn-warning btn-sm">
           Approve
        </a>
        <?php } ?>

        <a href="?page=berita&aksi=preview&id=<?= $data['id_berita']; ?>" class="btn btn-info btn-sm">Preview</a>
        <a href="preview_pdf.php?id=<?= $data['id_berita']; ?>" target="_blank" class="btn btn-secondary btn-sm">PDF</a>

        <?php
        // Tombol edit/hapus
        if (strtolower($level) === 'administrator' || $data['id_user'] == $iduser) { ?>
            <a href="?page=berita&aksi=edit&id=<?= $data['id_berita']; ?>" class="btn btn-success btn-sm">Edit</a>
            <a href="aksi_berita.php?page=berita&proses=hapus&id=<?= $data['id_berita']; ?>"
               onclick="return confirm('Yakin hapus?')"
               class="btn btn-danger btn-sm">Hapus</a>
        <?php } ?>
    </td>


</tr>
<?php } ?>
</table>

<!-- PAGINATION -->
<?php
$range = 5; // <<< INI YANG KAMU ATUR (MAU 10 JUGA BOLEH)

echo '<nav><ul class="pagination justify-content-center">';

// ===== PREV =====
$prev = ($halaman > 1) ? $halaman - 1 : 1;
echo '<li class="page-item '.($halaman==1?'disabled':'').'">
        <a class="page-link" href="?page=berita&aksi=list&hal='.$prev.'&limit='.$limit.'">«</a>
      </li>';

// ===== HALAMAN AWAL =====
if ($halaman > $range + 2) {
    echo '<li class="page-item">
            <a class="page-link" href="?page=berita&aksi=list&hal=1&limit='.$limit.'">1</a>
          </li>';
    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
}

// ===== HALAMAN TENGAH =====
for ($i = max(1, $halaman-$range); $i <= min($total_halaman, $halaman+$range); $i++) {

    echo '<li class="page-item '.($i==$halaman?'active':'').'">
            <a class="page-link" href="?page=berita&aksi=list&hal='.$i.'&limit='.$limit.'">'.$i.'</a>
          </li>';
}

// ===== HALAMAN AKHIR =====
if ($halaman < $total_halaman - $range - 1) {
    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
    echo '<li class="page-item">
            <a class="page-link" href="?page=berita&aksi=list&hal='.$total_halaman.'&limit='.$limit.'">'.$total_halaman.'</a>
          </li>';
}

// ===== NEXT =====
$next = ($halaman < $total_halaman) ? $halaman + 1 : $total_halaman;
echo '<li class="page-item '.($halaman==$total_halaman?'disabled':'').'">
        <a class="page-link" href="?page=berita&aksi=list&hal='.$next.'&limit='.$limit.'">»</a>
      </li>';

echo '</ul></nav>';
?>


<?php
break;

case 'entri':
?>
<a href="?page=berita&aksi=list" class="btn btn-danger fa fa-table"> List Laporan</a>
<h2>Entri Laporan</h2>

<form role="form" method="post" enctype="multipart/form-data"
      action="aksi_berita.php?page=berita&proses=input">

    <input type="hidden" name="plan" value="<?= htmlspecialchars($plan); ?>">
    <input type="hidden" name="id_user" value="<?= htmlspecialchars($iduser); ?>">

    <div class="form-group">
        <label>Nama Kategori</label>
        <select name="kategori" class="form-control">
            <?php
            $qKategori = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY id ASC");
            while ($d = mysqli_fetch_assoc($qKategori)) {
                echo "<option value='".intval($d['id'])."'>".htmlspecialchars($d['nama_kategori'])."</option>";
            }
            ?>
        </select>
    </div>

<!-- JUDUL OTOMATIS (READONLY / TIDAK DIKETIK USER) -->
<div class="form-group">
    <label>Judul</label>
    <input type="text" id="judul_auto" class="form-control" readonly
           value="BA Overshift <?= ucfirst($plan); ?>">
</div>

<!-- INPUT TANGGAL & SHIFT BERDEKATAN -->
<div class="form-group">
    <label>Tanggal & Shift</label>
    <div class="row">
        <div class="col-md-6">
            <input type="date" name="tgl_laporan" id="tgl_laporan"
                   class="form-control" required>
        </div>
        <div class="col-md-6">
            <select name="shift" id="shift" class="form-control" required>
                <option value="">-- Pilih Shift --</option>
                <option value="Pagi">Shift Pagi</option>
                <option value="Malam">Shift Malam</option>
            </select>
        </div>
    </div>
</div>




    <div class="form-group">
        <label>Isi Laporan</label>
        <textarea id="tinyBerita" name="berita" class="form-control" rows="10" required></textarea>
    </div>

    <div class="form-group">
       <input type="file" name="file_image"> 
       <label>Gambar (utama)</label>
        
    </div>

    <div class="form-group">
         <input type="file" name="foto_lain[]" multiple>
        <label>Dokumentasi Tambahan (multiple Gambar)</label>
        <label>Tekan CTRL Untuk Klik Banyak Gambar</label>
    </div>

    <button type="submit" class="btn btn-primary fa fa-floppy-o"> Simpan</button>


    <script>
function formatTanggal(tgl) {
    if (!tgl) return '';
    const d = new Date(tgl);
    const day   = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year  = d.getFullYear();
    return `${day}/${month}/${year}`;
}

function updateJudul() {
    const plan  = "<?= ucfirst($plan); ?>";
    const shift = document.getElementById('shift').value;
    const tgl   = document.getElementById('tgl_laporan').value;

    let judul = `BA Overshift ${plan}`;

    if (shift) judul += ` Shift ${shift}`;
    if (tgl)   judul += ` ${formatTanggal(tgl)}`;

    document.getElementById('judul_auto').value = judul;
}

document.getElementById('shift').addEventListener('change', updateJudul);
document.getElementById('tgl_laporan').addEventListener('change', updateJudul);
</script>

</form>

<script src="../ckeditor/ckeditor.js"></script>

<script>
    CKEDITOR.replace('tinyBerita', {
        height: 420,
         removePlugins: 'notification,notificationaggregator',
         contentsCss: [
            'body { color:#000; font-size:14px; }'
        ]
    });

    CKEDITOR.replace('tinyBerita', {
    removePlugins: 'notificationaggregator'
});
</script>


<?php
break;

case 'edit':
$id = intval($_GET['id']);
$r = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM berita WHERE id_berita=$id"));
?>
<a href="?page=berita&aksi=list" class="btn btn-danger fa fa-table"> List Laporan</a>
<h2>Edit Laporan</h2>

<form role="form" method="post" enctype="multipart/form-data"
      action="aksi_berita.php?page=berita&proses=update">

    <input type="hidden" name="id" value="<?= intval($r['id_berita']); ?>">
    <input type="hidden" name="plan" value="<?= htmlspecialchars($r['plan']); ?>">

    <div class="form-group">
        <label>Nama Kategori</label>
        <select name="kategori" class="form-control">
            <?php
            $qKategori = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY id ASC");
            while ($d = mysqli_fetch_assoc($qKategori)) {
                $sel = ($r['id_kategori'] == $d['id']) ? "selected" : "";
                echo "<option value='".intval($d['id'])."' $sel>".htmlspecialchars($d['nama_kategori'])."</option>";
            }
            ?>
        </select>
    </div>

    <!-- JUDUL OTOMATIS (EDIT MODE) -->
<div class="form-group">
    <label>Judul</label>
    <input type="text"
           id="judul_auto"
           name="judul"
           class="form-control"
           readonly
           value="<?= htmlspecialchars($r['judul']); ?>">
</div>

<!-- INPUT TANGGAL & SHIFT -->
<div class="form-group">
    <label>Tanggal & Shift</label>
    <div class="row">
        <div class="col-md-6">
            <?php
            // Ambil tanggal dari DB, format HTML date
            $tgl_db = !empty($r['tgl_posting']) ? date('Y-m-d', strtotime($r['tgl_posting'])) : '';
            ?>
            <input type="date"
                   id="tgl_laporan"
                   name="tgl_laporan"
                   class="form-control"
                   value="<?= $tgl_db; ?>"
                   required>
        </div>
        <div class="col-md-6">
            <select id="shift"
                    name="shift"
                    class="form-control"
                    required>
                <option value="">-- Pilih Shift --</option>
                <option value="Pagi" <?= ($r['judul'] && strpos($r['judul'], 'Shift Pagi')!==false)?'selected':'' ?>>Shift Pagi</option>
                <option value="Malam" <?= ($r['judul'] && strpos($r['judul'], 'Shift Malam')!==false)?'selected':'' ?>>Shift Malam</option>
            </select>
        </div>
    </div>
</div>



    <div class="form-group">
        <label>Isi Berita</label>
        <textarea id="tinyBerita" name="berita" class="form-control" rows="10"><?= htmlspecialchars($r['isi_berita']); ?></textarea>
    </div>
<script src="../ckeditor/ckeditor.js"></script>
<script>
CKEDITOR.replace('tinyBerita', {
    height: 420,
    contentsCss: 'body { color:#000; font-size:14px; }'
});
</script>
    <div class="form-group">
        <label>Gambar Utama</label>
        <input type="file" name="file_image"><br>
        <?php if (!empty($r['gambar'])) { ?>
            <img src="./foto_berita/<?= htmlspecialchars($r['gambar']); ?>" width="100">
        <?php } else { ?>
            <p>Tidak ada image</p>
        <?php } ?>
    </div>
    <!-- FOTO TAMBAHAN -->
<div class="form-group mt-3">
    <label>Foto Tambahan</label><br>

    <?php
    $qFoto = mysqli_query($koneksi, "SELECT * FROM berita_foto WHERE id_berita=".$r['id_berita']);
    while ($f = mysqli_fetch_assoc($qFoto)) { ?>
        <div style="display:inline-block; margin:10px; text-align:center;">
            <img src="./foto_berita/<?= $f['foto']; ?>" width="120"><br>
            <a href="aksi_berita.php?page=berita&proses=hapus_foto&id=<?= $f['id_foto']; ?>&id_berita=<?= $r['id_berita']; ?>"
               onclick="return confirm('Hapus foto ini?')"
               class="btn btn-danger btn-sm mt-1">Hapus</a>
        </div>
    <?php } ?>
</div>

<div class="form-group mt-3">
    <label>Upload Foto Tambahan Baru</label>
    <input type="file" name="foto_lain[]" multiple>
</div>


    <button type="submit" class="btn btn-primary fa fa-floppy-o"> Simpan</button>


<script>
function formatTanggal(tgl) {
    if (!tgl) return '';
    const d = new Date(tgl);
    const day   = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year  = d.getFullYear();
    return `${day}/${month}/${year}`;
}

function updateJudul() {
    const plan  = "<?= ucfirst($r['plan']); ?>"; // pakai data dari DB
    const shift = document.getElementById('shift').value;
    const tgl   = document.getElementById('tgl_laporan').value;

    let judul = `BA Overshift ${plan}`;
    if (shift) judul += ` Shift ${shift}`;
    if (tgl)   judul += ` ${formatTanggal(tgl)}`;

    document.getElementById('judul_auto').value = judul;
}

// Event listener
document.getElementById('shift').addEventListener('change', updateJudul);
document.getElementById('tgl_laporan').addEventListener('change', updateJudul);

// Jalankan sekali saat halaman load supaya judul menyesuaikan data lama
document.addEventListener('DOMContentLoaded', function() {
    updateJudul();
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const judul = document.getElementById('judul_auto').value;

    /*
      Contoh judul:
      BA Overshift Earthwork Shift Pagi 10/11/2026
    */

    // === DETEKSI SHIFT ===
    if (judul.includes('Shift Pagi')) {
        document.getElementById('shift').value = 'Pagi';
    } else if (judul.includes('Shift Malam')) {
        document.getElementById('shift').value = 'Malam';
    }

    // === DETEKSI TANGGAL (DD/MM/YYYY) ===
   function formatTanggal(tgl) {
    if (!tgl) return '';
    const d = new Date(tgl);
    const day   = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year  = d.getFullYear();
    return `${day}/${month}/${year}`; // <-- DD/MM/YYYY
}

function updateJudul() {
    const plan  = "<?= ucfirst($plan); ?>";
    const shift = document.getElementById('shift').value;
    const tgl   = document.getElementById('tgl_laporan').value;

    let judul = `BA Overshift ${plan}`;

    if (shift) judul += ` Shift ${shift}`;
    if (tgl)   judul += ` ${formatTanggal(tgl)}`;

    document.getElementById('judul_auto').value = judul;
}

});
</script>

</form>


<?php
break;

//preview
case 'preview':
$id = intval($_GET['id']);

$berita = mysqli_fetch_assoc(mysqli_query(
    $koneksi,
    "SELECT b.*, 
            k.nama_kategori, 
            u.username,
            ua.username AS approved_name
     FROM berita b
     LEFT JOIN kategori k ON b.id_kategori = k.id
     LEFT JOIN user u ON b.id_user = u.id
     LEFT JOIN user ua ON b.approved_by = ua.id
     WHERE b.id_berita = '$id'
       AND b.deleted_at IS NULL"
));



// ambil foto tambahan
$fotos = mysqli_query($koneksi, "SELECT foto FROM berita_foto WHERE id_berita='$id'");
?>
<a href="?page=berita&aksi=list" class="btn btn-secondary mb-3">⬅ Kembali</a>



<div class="card p-4" style="max-width:900px; margin:auto;">
    <h2 style="text-align:center;"><?= htmlspecialchars($berita['judul']); ?></h2>
    <p><b>Kategori:</b> <?= htmlspecialchars($berita['nama_kategori']); ?></p>
    <p><b>User Input:</b> <?= htmlspecialchars($berita['username']); ?></p>
    <p><b>Tanggal:</b> <?= htmlspecialchars($berita['tgl_posting']); ?></p>

    <hr>

   

    <!-- Isi Berita -->
    <div class="preview-wrapper">
    <div style="font-size:15px; line-height:1.6; text-align:justify;">
        <?= $berita['isi_berita']; ?>
    </div>

    <hr>

    <h4>📷 Dokumentasi:</h4>
     <!-- Gambar Utama -->
    <?php if (!empty($berita['gambar'])) { ?>
        <div style="text-align:center; margin-bottom:20px;">
            <img src="foto_berita/<?= $berita['gambar']; ?>" 
                 style="max-width:500px; width:100%; border-radius:8px;">
        </div>
    <?php } ?>

    <div style="display:flex; flex-wrap:wrap; gap:15px;">
        <?php while ($f = mysqli_fetch_assoc($fotos)) { ?>
            <div style="width:200px;">
                <img src="foto_berita/<?= $f['foto']; ?>" 
                     style="width:100%; border-radius:6px;">
            </div>
        <?php } ?>
    </div>
</div>

<hr>

<p>
    <b>Status:</b>
    <?php if ($berita['status'] === 'approved') { ?>
        <span style="color:green; font-weight:bold;">
            APPROVED
        </span>
        <br>
        <b>Approved by:</b>
<?= htmlspecialchars($berita['approved_name']); ?>

        <br>
        <b>Approved at:</b>
        <?= date('d/m/Y H:i', strtotime($berita['approved_at'])); ?>
    <?php } else { ?>
        <span style="color:orange; font-weight:bold;">
            PENDING APPROVAL
        </span>
    <?php } ?>
</p>

<hr>

   
</div>

<?php
break;

}
?>
