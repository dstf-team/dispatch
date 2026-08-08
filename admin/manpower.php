<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';

if (!isset($_SESSION['level']) || !isset($_SESSION['iduser'])) {
    echo "<script>alert('Silakan login terlebih dahulu');window.location='index.php';</script>";
    exit;
}

$id_login    = intval($_SESSION['iduser']);
$level_login = $_SESSION['level'];
?>

<div class="d-flex left-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom"> 
    <a href="index.php">Home</a> / Manpower
</div>

<?php
$aksi = $_GET['aksi'] ?? 'list';

switch ($aksi) {

/* ============================================================
   LIST
============================================================ */
case 'list':
?>

<?php if ($level_login === 'Administrator') { ?>
<a href="?page=manpower&aksi=entri" class="btn btn-primary fa fa-plus"> Entri Manpower</a>
<?php } ?>

<h2>List Manpower</h2>

<table class="table table-bordered table-striped table-sm">
<tr align="center">
    <th>No</th>
    <th>Nama</th>
    <th>NIK</th>
    <th>Jabatan</th>
    <th>Devisi</th>
    <th>Status</th>
    <th>Masa Kerja</th>
    <th>Aksi</th>
</tr>

<?php
$no = 1;
$tampil = mysqli_query($koneksi, "SELECT * FROM manpower ORDER BY nama ASC");

while ($data = mysqli_fetch_assoc($tampil)) {

    /* =========================
       HITUNG MASA KERJA
    ========================== */

    $masaKerja = "-";

    if (!empty($data['tanggal_masuk'])) {

        $tgl_masuk = new DateTime($data['tanggal_masuk']);

        // Jika resign → berhenti di tanggal resign
        if ($data['status_bekerja'] == 'RESIGN' && !empty($data['tanggal_resign'])) {
            $endDate = new DateTime($data['tanggal_resign']);
        } else {
            $endDate = new DateTime(); // selain resign tetap hitung sampai hari ini
        }

        $diff = $endDate->diff($tgl_masuk);
        $masaKerja = $diff->y . " Th " . $diff->m . " Bln";
    }
?>
<tr>
    <td><?= $no++; ?></td>

    <td><?= htmlspecialchars($data['nama']); ?></td>
    <td><?= htmlspecialchars($data['nik']); ?></td>
    <td><?= htmlspecialchars($data['jabatan']); ?></td>
    <td><?= htmlspecialchars($data['devisi']); ?></td>

    <!-- STATUS BADGE -->
    <td align="center">
        <?php
        if($data['status_bekerja']=='AKTIF'){
            echo '<span class="badge badge-success">AKTIF</span>';
        }
        elseif($data['status_bekerja']=='MUTASI'){
            echo '<span class="badge badge-primary">MUTASI</span>';
        }
        else{
            echo '<span class="badge badge-danger">RESIGN</span>';
        }
        ?>
    </td>

    <!-- MASA KERJA + INFO TANGGAL -->
    <td>
        <?= $masaKerja; ?>

        <?php if($data['status_bekerja']=='RESIGN' && !empty($data['tanggal_resign'])){ ?>
            <br><small class="text-danger">
            (Resign: <?= date('d-m-Y', strtotime($data['tanggal_resign'])); ?>)
            </small>
        <?php } ?>

        <?php if($data['status_bekerja']=='MUTASI' && !empty($data['tanggal_mutasi'])){ ?>
            <br><small class="text-primary">
            (Mutasi: <?= date('d-m-Y', strtotime($data['tanggal_mutasi'])); ?>)
            </small>
        <?php } ?>
    </td>

    <td align="center">
        <a href="?page=manpower&aksi=edit&id=<?= $data['id_manpower']; ?>"
           class="btn btn-success btn-sm fa fa-pencil"></a>

        <?php if ($level_login === 'Administrator') { ?>
        <a href="aksi_manpower.php?page=manpower&proses=hapus&id=<?= $data['id_manpower']; ?>"
           onclick="return confirm('Yakin hapus data?');"
           class="btn btn-danger btn-sm fa fa-trash"></a>
        <?php } ?>
    </td>
</tr>
<?php } ?>
</table>

<?php
break;


/* ============================================================
   ENTRI
============================================================ */
case 'entri':

if ($level_login !== 'Administrator') {
    echo "<script>alert('Akses ditolak');window.location='?page=manpower';</script>";
    exit;
}
?>

<a href="?page=manpower&aksi=list" class="btn btn-danger fa fa-table"> List</a>
<h2>Entri Manpower</h2>

<form method="POST" action="aksi_manpower.php?page=manpower&proses=input" enctype="multipart/form-data">

<div class="form-group">
<label>Nama</label>
<input type="text" name="nama" class="form-control" required>
</div>

<div class="form-group">
<label>NIK</label>
<input type="text" name="nik" class="form-control" required>
</div>

<div class="form-group">
<label>Jabatan</label>
<input type="text" name="jabatan" class="form-control" required>
</div>

<div class="form-group">
<label>Jabatan Tambahan</label>
<input type="text" name="jabatan_tambahan" class="form-control">
</div>

<div class="form-group">
<label>Devisi</label>
<input type="text" name="devisi" class="form-control" required>
</div>

<div class="form-group">
<label>Status Bekerja</label>
<select name="status_bekerja" class="form-control">
<option value="AKTIF">AKTIF</option>
<option value="RESIGN">RESIGN</option>
</select>
</div>

<div class="form-group">
<label>Status Kerja</label>
<select name="status_kerja" class="form-control">
<option>Permanent</option>
<option>Kontrak</option>
<option>THL</option>
</select>
</div>

<div class="form-group">
<label>Tanggal Masuk</label>
<input type="date" name="tanggal_masuk" class="form-control">
</div>



<div class="form-group">
<label>POH</label>
<input type="text" name="poh" class="form-control">
</div>

<div class="form-group">
<label>Keterangan Pelanggaran</label>
<textarea name="keterangan_pelanggaran" class="form-control"></textarea>
</div>

<div class="form-group">
<label>Keterangan Umum</label>
<textarea name="keterangan" class="form-control"></textarea>
</div>

<div class="form-group">
<label>Foto</label>
<input type="file" name="foto" class="form-control">
</div>

<button type="submit" class="btn btn-primary fa fa-floppy-o"> Simpan</button>
</form>

<?php
break;


/* ============================================================
   EDIT
============================================================ */
case 'edit':

$id = intval($_GET['id']);
$ambil = mysqli_query($koneksi, "SELECT * FROM manpower WHERE id_manpower='$id'");
$r = mysqli_fetch_assoc($ambil);

if (!$r) {
    die('Data tidak ditemukan');
}
?>

<a href="?page=manpower&aksi=list" class="btn btn-danger fa fa-table"> List</a>
<h2>Edit Manpower</h2>

<form method="POST" action="aksi_manpower.php?page=manpower&proses=update" enctype="multipart/form-data">

<input type="hidden" name="id" value="<?= $r['id_manpower']; ?>">

<?php
$fields = [
'nama'=>'Nama',
'nik'=>'NIK',
'jabatan'=>'Jabatan',
'jabatan_tambahan'=>'Jabatan Tambahan',
'devisi'=>'Devisi',
'poh'=>'POH'
];

foreach($fields as $name=>$label){
echo "<div class='form-group'>
<label>$label</label>
<input type='text' name='$name' class='form-control' value='".htmlspecialchars($r[$name])."'>
</div>";
}
?>

<div class="form-group">
<label>Status Bekerja</label>
<select name="status_bekerja" class="form-control" id="statusBekerja">
<option value="AKTIF" <?= ($r['status_bekerja']=='AKTIF')?'selected':''; ?>>AKTIF</option>
<option value="RESIGN" <?= ($r['status_bekerja']=='RESIGN')?'selected':''; ?>>RESIGN</option>
<option value="MUTASI" <?= ($r['status_bekerja']=='MUTASI')?'selected':''; ?>>MUTASI</option>
</select>
</div>

<div class="form-group">
<label>Status Kerja</label>
<select name="status_kerja" class="form-control">
<option value="Permanent" <?= ($r['status_kerja']=='Permanent')?'selected':''; ?>>Permanent</option>
<option value="Kontrak" <?= ($r['status_kerja']=='Kontrak')?'selected':''; ?>>Kontrak</option>
<option value="THL" <?= ($r['status_kerja']=='THL')?'selected':''; ?>>THL</option>
</select>
</div>

<div class="form-group">
<label>Tanggal Masuk</label>
<input type="date" 
       name="tanggal_masuk" 
       class="form-control" 
       value="<?= !empty($r['tanggal_masuk']) ? date('Y-m-d', strtotime($r['tanggal_masuk'])) : ''; ?>">
</div>

<!-- TANGGAL RESIGN -->
<div class="form-group" id="boxResign" style="display:none;">
<label>Tanggal Resign</label>
<input type="date" 
       name="tanggal_resign" 
       class="form-control"
       value="<?= !empty($r['tanggal_resign']) ? date('Y-m-d', strtotime($r['tanggal_resign'])) : ''; ?>">
</div>

<!-- TANGGAL MUTASI -->
<div class="form-group" id="boxMutasi" style="display:none;">
<label>Tanggal Mutasi</label>
<input type="date" 
       name="tanggal_mutasi" 
       class="form-control"
       value="<?= !empty($r['tanggal_mutasi']) ? date('Y-m-d', strtotime($r['tanggal_mutasi'])) : ''; ?>">
</div>

<div class="form-group">
<label>Keterangan Pelanggaran</label>
<textarea name="keterangan_pelanggaran" class="form-control"><?= htmlspecialchars($r['keterangan_pelanggaran']); ?></textarea>
</div>

<div class="form-group">
<label>Keterangan Umum</label>
<textarea name="keterangan" class="form-control"><?= htmlspecialchars($r['keterangan']); ?></textarea>
</div>

<div class="form-group">
<label>Foto Sekarang</label><br>
<?php if($r['foto']){ ?>
<img src="../uploads/manpower/<?= $r['foto']; ?>" width="100">
<?php } ?>
</div>

<div class="form-group">
<label>Ganti Foto</label>
<input type="file" name="foto" class="form-control">
</div>

<button type="submit" class="btn btn-primary fa fa-floppy-o"> Update</button>

<script>
function toggleTanggal() {
    let status = document.getElementById("statusBekerja").value;
    let boxResign = document.getElementById("boxResign");
    let boxMutasi = document.getElementById("boxMutasi");

    boxResign.style.display = "none";
    boxMutasi.style.display = "none";

    if (status === "RESIGN") {
        boxResign.style.display = "block";
    } 
    else if (status === "MUTASI") {
        boxMutasi.style.display = "block";
    }
}

document.getElementById("statusBekerja").addEventListener("change", toggleTanggal);

// Jalankan saat pertama load
toggleTanggal();
</script>

</form>

<?php
break;
}
?>