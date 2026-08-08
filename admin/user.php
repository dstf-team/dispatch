<?php
// ================= SESSION & KONEKSI =================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';

// validasi login
if (!isset($_SESSION['level']) || !isset($_SESSION['iduser'])) {
    echo "<script>alert('Silakan login terlebih dahulu');window.location='index.php';</script>";
    exit;
}

$id_login    = intval($_SESSION['iduser']); // ⬅️ FIX UTAMA
$level_login = $_SESSION['level'];
?>

<div class="d-flex left-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom"> 
    <a href="index.php">Home</a> / User
</div>

<?php
$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : 'list';

switch ($aksi) {

/* ================= LIST USER ================= */
case 'list':
?>

<?php if ($level_login === 'Administrator') { ?>
    <a href="?page=user&aksi=entri" class="btn btn-primary fa fa-plus"> Entri User</a>
<?php } ?>

<h2>List User</h2>

<table class="table table-bordered table-striped">
<tr align="center">
    <th>No</th>
    <th>Username</th>
    <th>Level</th>
    <th>Plan</th>
    <th>Aksi</th>
</tr>

<?php
$no = 1;

if ($level_login === 'Administrator') {
    $tampil = mysqli_query($koneksi, "SELECT * FROM user ORDER BY id ASC");
} else {
    $tampil = mysqli_query($koneksi, "SELECT * FROM user WHERE id='$id_login'");
}

while ($data = mysqli_fetch_assoc($tampil)) {
?>
<tr>
    <td><?= $no++; ?></td>
    <td><?= htmlspecialchars($data['username']); ?></td>
    <td><?= htmlspecialchars($data['level']); ?></td>
    <td><?= htmlspecialchars($data['plan']); ?></td>
    <td align="center">

        <?php if ($level_login === 'Administrator' || $id_login === (int)$data['id']) { ?>
            <a href="?page=user&aksi=edit&id=<?= $data['id']; ?>"
               class="btn btn-success btn-sm fa fa-pencil"> Edit</a>
        <?php } ?>

        <?php if ($level_login === 'Administrator') { ?>
            <a href="aksi_user.php?page=user&proses=hapus&id=<?= $data['id']; ?>"
               onclick="return confirm('Yakin akan menghapus data ?');"
               class="btn btn-danger btn-sm fa fa-trash-o"> Hapus</a>
        <?php } ?>

    </td>
</tr>
<?php } ?>
</table>

<?php
break;


/* ================= ENTRI USER ================= */
case 'entri':

if ($level_login !== 'Administrator') {
    echo "<script>alert('Akses ditolak');window.location='?page=user';</script>";
    exit;
}
?>

<a href="?page=user&aksi=list" class="btn btn-danger fa fa-table"> List User</a>
<h2>Entri User</h2>

<form method="POST" action="aksi_user.php?page=user&proses=input">

<div class="form-group">
    <label>Username</label>
    <input type="text" name="username" class="form-control" required>
</div>

<div class="form-group">
    <label>User</label>
    <input type="text" name="user" class="form-control" required>
</div>

<div class="form-group">
    <label>Password</label>
    <input type="password" name="pass" class="form-control" id="myPass" required>
    <input type="checkbox" onclick="togglePass('myPass')"> Show Password
</div>

<div class="form-group">
    <label>Level</label>
    <select name="level" class="form-control">
        <option>Administrator</option>
        <option>Moderator</option>
        <option>Operator</option>
    </select>
</div>

<div class="form-group">
    <label>Plan</label>
    <select name="plan" class="form-control">
        <option>Earthwork</option>
        <option>Hydrology & Hydrogeology</option>
        <option>Waste Water Treatment Plant</option>
        <option>Instrument & Geotechnical</option>
        <option>Tailing Facility Planning</option>
        <option>Dispatcher</option>
    </select>
</div>

<button type="submit" class="btn btn-primary fa fa-floppy-o"> Simpan</button>
</form>

<?php
break;


/* ================= EDIT USER ================= */
case 'edit':

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$ambil = mysqli_query($koneksi, "SELECT * FROM user WHERE id='$id'");
$r = mysqli_fetch_assoc($ambil);

if (!$r) {
    die('Data tidak ditemukan');
}

// validasi akses edit
if ($level_login !== 'Administrator' && $id_login !== (int)$r['id']) {
    echo "<script>alert('Akses ditolak');window.location='?page=user';</script>";
    exit;
}
?>

<a href="?page=user&aksi=list" class="btn btn-danger fa fa-table"> List User</a>
<h2>Edit User</h2>

<form method="POST" action="aksi_user.php?page=user&proses=update">

<input type="hidden" name="id" value="<?= $r['id']; ?>">

<div class="form-group">
    <label>Username</label>
    <input type="text" class="form-control" value="<?= htmlspecialchars($r['username']); ?>" readonly>
</div>

<div class="form-group">
    <label>User</label>
    <input type="text" name="user" class="form-control" value="<?= htmlspecialchars($r['user']); ?>">
</div>

<div class="form-group">
    <label>Password (kosongkan jika tidak mengubah)</label>
    <input type="password" name="pass" class="form-control" id="myPass">
    <input type="checkbox" onclick="togglePass('myPass')"> Show Password
</div>

<div class="form-group">
<label>Level</label>
<select name="level" class="form-control" <?= ($level_login!=='Administrator')?'disabled':''; ?>>
    <option <?= ($r['level']=='Administrator')?'selected':''; ?>>Administrator</option>
    <option <?= ($r['level']=='Moderator')?'selected':''; ?>>Moderator</option>
    <option <?= ($r['level']=='Operator')?'selected':''; ?>>Operator</option>
</select>
<?php if ($level_login!=='Administrator') { ?>
<input type="hidden" name="level" value="<?= $r['level']; ?>">
<?php } ?>
</div>

<div class="form-group">
<label>Plan</label>
<select name="plan" class="form-control" <?= ($level_login!=='Administrator')?'disabled':''; ?>>
    <option <?= ($r['plan']=='Earthwork')?'selected':''; ?>>Earthwork</option>
    <option <?= ($r['plan']=='Hydrology & Hydrogeology')?'selected':''; ?>>Hydrology & Hydrogeology</option>
    <option <?= ($r['plan']=='Waste Water Treatment Plant')?'selected':''; ?>>Waste Water Treatment Plant</option>
    <option <?= ($r['plan']=='Instrument & Geotechnical')?'selected':''; ?>>Instrument & Geotechnical</option>
    <option <?= ($r['plan']=='Tailing Facility Planning')?'selected':''; ?>>Tailing Facility Planning</option>
    <option <?= ($r['plan']=='Dispatcher')?'selected':''; ?>>Dispatcher</option>
</select>
<?php if ($level_login!=='Administrator') { ?>
<input type="hidden" name="plan" value="<?= $r['plan']; ?>">
<?php } ?>
</div>

<button type="submit" class="btn btn-primary fa fa-floppy-o"> Simpan</button>
</form>

<?php
break;
}
?>

<script>
function togglePass(id){
    var x = document.getElementById(id);
    x.type = (x.type === "password") ? "text" : "password";
}
</script>
