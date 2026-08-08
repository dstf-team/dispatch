<style>
/* ===== TABEL LOKASI ===== */
.table-lokasi {
    background-color: #ffffff;
    font-size: 14px;
}

.table-lokasi th {
    background-color: #f8fbff;   /* putih kebiruan, tidak silau */
    color: #333;
    text-align: center;
    vertical-align: middle;
    white-space: nowrap;
}

.table-lokasi td {
    vertical-align: middle;
    padding: 6px 8px;            /* bikin rapat */
}

/* batasi lebar kolom */
.col-no { width: 50px; }
.col-kode { width: 130px; }
.col-nama { width: 220px; }
.col-ket  { width: 280px; }
.col-aksi { width: 120px; }

/* tombol */
.table-lokasi .btn {
    padding: 3px 8px;
    font-size: 12px;
}

/* zebra lembut */
.table-lokasi tbody tr:nth-child(even) {
    background-color: #fdfefe;
}
</style>
<style>
/* ===== FIX WARNA TEXT (ANTI GREY) ===== */
.table-lokasi,
.table-lokasi td,
.table-lokasi th {
    color: #111 !important;   /* hitam lembut, tidak silau */
}

/* header tabel */
.table-lokasi th {
    font-weight: 600;
}

/* judul halaman */
h2 {
    color: #1f2937; /* hitam kebiruan */
}

/* link breadcrumb */
a {
    color: #2563eb;
}
a:hover {
    color: #1e40af;
    text-decoration: none;
}
</style>


<div class="d-flex left-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <a href="index.php">Home</a> / Lokasi
</div>

<?php
$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : 'list';

switch ($aksi) {

// ================= LIST =================
case 'list':
include '../koneksi.php';
?>

<a href="?page=lokasi&aksi=entri" class="btn btn-primary mb-2">
    + Entri Lokasi
</a>

<h2>List Lokasi</h2>

<table class="table table-bordered table-lokasi">
    <thead>
        <tr align="center">
            <th class="col-no">No</th>
            <th class="col-kode">Kode Lokasi</th>
            <th class="col-nama">Nama Lokasi</th>
            <th class="col-ket">Keterangan</th>
            <th class="col-aksi">Aksi</th>
        </tr>
    </thead>
    <tbody>
<?php
$no = 1;
$q = mysqli_query($koneksi,"SELECT * FROM lokasi ORDER BY id_lokasi ASC");
while($d = mysqli_fetch_assoc($q)){
?>
        <tr>
            <td align="center"><?= $no++; ?></td>
            <td><?= $d['kode_lokasi']; ?></td>
            <td><?= $d['nama_lokasi']; ?></td>
            <td><?= $d['keterangan']; ?></td>
            <td align="center">
                <a href="?page=lokasi&aksi=edit&id=<?= $d['id_lokasi']; ?>"
                   class="btn btn-warning btn-sm">Edit</a>

                <a href="dispatch/aksi_lokasi.php?proses=hapus&id=<?= $d['id_lokasi']; ?>"
                   onclick="return confirm('Yakin hapus data ini?')"
                   class="btn btn-danger btn-sm">Hapus</a>
            </td>
        </tr>
<?php } ?>
    </tbody>
</table>

<?php
break;


// ================= ENTRI =================
case 'entri':
?>

<a href="?page=lokasi&aksi=list" class="btn btn-danger mb-3">
    List Lokasi
</a>

<h2>Entri Lokasi</h2>

<form method="POST" action="dispatch/aksi_lokasi.php?proses=input">

<div class="form-group">
    <label>Kode Lokasi</label>
    <input type="text" name="kode_lokasi" class="form-control" required>
</div>

<div class="form-group">
    <label>Nama Lokasi</label>
    <input type="text" name="nama_lokasi" class="form-control" required>
</div>

<div class="form-group">
    <label>Keterangan</label>
    <input type="text" name="keterangan" class="form-control">
</div>

<button type="submit" class="btn btn-primary">Simpan</button>
<button type="reset" class="btn btn-secondary">Reset</button>

</form>

<?php
break;


// ================= EDIT =================
case 'edit':
include '../koneksi.php';

$id = $_GET['id'];
$q = mysqli_query($koneksi,"SELECT * FROM lokasi WHERE id_lokasi='$id'");
$r = mysqli_fetch_assoc($q);
?>

<a href="?page=lokasi&aksi=list" class="btn btn-danger mb-3">
    Kembali
</a>

<h2>Edit Lokasi</h2>

<form method="POST" action="dispatch/aksi_lokasi.php?proses=update">

<input type="hidden" name="id" value="<?= $r['id_lokasi']; ?>">

<div class="form-group">
    <label>Kode Lokasi</label>
    <input type="text" name="kode_lokasi" value="<?= $r['kode_lokasi']; ?>" class="form-control" required>
</div>

<div class="form-group">
    <label>Nama Lokasi</label>
    <input type="text" name="nama_lokasi" value="<?= $r['nama_lokasi']; ?>" class="form-control" required>
</div>

<div class="form-group">
    <label>Keterangan</label>
    <input type="text" name="keterangan" value="<?= $r['keterangan']; ?>" class="form-control">
</div>

<button type="submit" class="btn btn-primary">Update</button>

</form>

<?php
break;

default:
echo "<h3>Halaman tidak ditemukan</h3>";
break;
}
?>
