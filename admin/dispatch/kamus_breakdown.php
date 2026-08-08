<style>
/* ===== TABLE DICTIONARY BREAKDOWN ===== */
.table-dictionary {
    background-color: #ffffff;
    font-size: 14px;
}

/* HEADER */
.table-dictionary th {
    background-color: #f8fafc;   /* putih kebiruan */
    color: #111 !important;
    text-align: center;
    vertical-align: middle;
    white-space: nowrap;
    font-weight: 600;
}

/* BODY */
.table-dictionary td {
    color: #111 !important;      /* FIX GREY */
    vertical-align: middle;
    padding: 6px 8px;            /* lebih rapat */
}

/* BATAS LEBAR KOLOM */
.col-no      { width: 50px; }
.col-name    { width: 260px; }
.col-aktif   { width: 80px; }
.col-aksi    { width: 130px; }

/* TOMBOL */
.table-dictionary .btn {
    padding: 3px 8px;
    font-size: 12px;
}

/* ZEBRA HALUS */
.table-dictionary tbody tr:nth-child(even) {
    background-color: #fdfefe;
}

/* JUDUL */
h2 {
    color: #1f2937; /* hitam kebiruan */
}

/* LINK */
a {
    color: #2563eb;
}
a:hover {
    color: #1e40af;
    text-decoration: none;
}
</style>


<div class="d-flex left-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <a href="index.php">Home</a> / Dispatch Specific Dictionary
</div>

<?php
$aksi = $_GET['aksi'] ?? 'list';

switch ($aksi) {

// ================= LIST =================
case 'list':
include '../koneksi.php';
?>
<a href="?page=dictionary_breakdown&aksi=entri" class="btn btn-primary mb-2">
    + Entri Specific
</a>

<h2>List Dispatch Specific Dictionary</h2>

<table class="table table-bordered table-striped table-dictionary">
    <tr align="center">
        <th class="col-no">No</th>
        <th class="col-name">Specific Name</th>
        <th class="col-aktif">Aktif</th>
        <th class="col-aksi">Aksi</th>
    </tr>
<?php
$no = 1;
$q = mysqli_query($koneksi,"SELECT * FROM dispatch_specific_dictionary ORDER BY id ASC");
while($d = mysqli_fetch_assoc($q)){
?>
<tr>
    <td align="center"><?= $no++; ?></td>
    <td><?= $d['specific_name']; ?></td>
    <td align="center"><?= $d['is_active'] ? 'Ya' : 'Tidak'; ?></td>
    <td align="center">
        <a href="?page=dictionary_breakdown&aksi=edit&id=<?= $d['id']; ?>"
           class="btn btn-warning btn-sm">Edit</a>

        <a href="dispatch/aksi_specific.php?proses=hapus&id=<?= $d['id']; ?>"
           onclick="return confirm('Yakin hapus data ini?')"
           class="btn btn-danger btn-sm">Hapus</a>
    </td>
</tr>
<?php } ?>
</table>
<?php break; ?>



<?php
// ================= ENTRI =================
case 'entri':
?>
<a href="?page=dictionary_breakdown&aksi=list" class="btn btn-danger mb-3">
    List Specific
</a>

<h2>Entri Specific</h2>

<form method="POST" action="dispatch/aksi_specific.php?proses=input">
    <div class="form-group">
        <label>Specific Name</label>
        <input type="text" name="specific_name" class="form-control" required>
    </div>

    <div class="form-group">
        <label>Aktif</label>
        <select name="is_active" class="form-control">
            <option value="1">Ya</option>
            <option value="0">Tidak</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
</form>
<?php break; ?>



<?php
// ================= EDIT =================
case 'edit':
include '../koneksi.php';

$id = $_GET['id'];
$q = mysqli_query($koneksi,"SELECT * FROM dispatch_specific_dictionary WHERE id='$id'");
$r = mysqli_fetch_assoc($q);
?>
<a href="?page=dictionary_breakdown&aksi=list" class="btn btn-danger mb-3">
    Kembali
</a>

<h2>Edit Specific</h2>

<form method="POST" action="dispatch/aksi_specific.php?proses=update">
    <input type="hidden" name="id" value="<?= $r['id']; ?>">

    <div class="form-group">
        <label>Specific Name</label>
        <input type="text" name="specific_name"
               value="<?= $r['specific_name']; ?>"
               class="form-control" required>
    </div>

    <div class="form-group">
        <label>Aktif</label>
        <select name="is_active" class="form-control">
            <option value="1" <?= $r['is_active']==1?'selected':''; ?>>Ya</option>
            <option value="0" <?= $r['is_active']==0?'selected':''; ?>>Tidak</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Update</button>
</form>
<?php break; } ?>
