<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['iduser'])) {
   header("Location: login.php");
   exit;
}
?>




<style>
.table td .btn {
    margin-right: 5px;      /* jarak antar tombol */
    white-space: nowrap;     /* mencegah tombol ter-break */
}
</style>

<div class="d-flex left-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom"> 
  <a href="index.php">Home</a> / Unit Master
</div>

<?php
$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : 'list';

switch($aksi){

// ================= LIST ==================
case 'list':

// ================= PAGINATION =================
$opsi_limit = [5, 10, 25, 50, 100];
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
if (!in_array($limit, $opsi_limit)) $limit = 10;

$halaman = isset($_GET['hal']) ? (int)$_GET['hal'] : 1;
$halaman = ($halaman < 1) ? 1 : $halaman;
$offset  = ($halaman - 1) * $limit;
// =============================================

?>

<a href="?page=dispatch_unit_master&aksi=entri" class="btn btn-primary mb-2">
  + Tambah Unit
</a>
<style>
.table td,
.table th {
  color:#000 !important;
}
</style>

<form method="get" class="mb-3">
    <input type="hidden" name="page" value="dispatch_unit_master">
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

<h2>List Unit</h2>
<?php
$category = isset($_GET['category']) ? $_GET['category'] : '';
?>
<table class="table table-bordered table-striped">
  <thead>
    <tr align="center">
      <th>No</th>
      <th>Unit Code</th>
      <th>Unit Name</th>
      <th>Category</th>
      <th>Status</th>
      <th width="120px">Aksi</th>
    </tr>
  </thead>
  <tbody>
<?php
include '../koneksi.php';

// hitung total data
$q_total = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM dispatch_unit_master");
$total = mysqli_fetch_assoc($q_total)['total'];
$total_halaman = ceil($total / $limit);

// ambil data per halaman
$sql = "SELECT * FROM dispatch_unit_master 
        ORDER BY category ASC, unit_code ASC 
        LIMIT $offset, $limit";

$tampil = mysqli_query($koneksi, $sql);

$no = $offset + 1;
while($r = mysqli_fetch_assoc($tampil)){
?>
<tr>
    <td><?= $no++; ?></td>
    <td align="center"><?= $r['unit_code']; ?></td>
    <td><?= $r['unit_name']; ?></td>
    <td><?= $r['category']; ?></td>
    <td align="center"><?= strtoupper($r['status']); ?></td>
    <td align="center">
  <div class="btn-group" role="group">
    <a href="?page=dispatch_unit_master&aksi=edit&id=<?= $r['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
    <a href="dispatch/aksi_unit_master.php?proses=hapus&id=<?= $r['id']; ?>" 
       onclick="return confirm('Yakin hapus unit ini?')" 
       class="btn btn-danger btn-sm">Hapus</a>
  </div>
</td>

</tr>
<?php } ?>
  </tbody>
</table>

<!-- PAGINATION -->
<nav>
<ul class="pagination justify-content-center">
<?php for($i=1; $i<=$total_halaman; $i++) { ?>
    <li class="page-item <?= ($i==$halaman)?'active':'' ?>">
        <a class="page-link" href="?page=dispatch_unit_master&aksi=list&hal=<?= $i ?>&limit=<?= $limit ?>">
            <?= $i ?>
        </a>
    </li>
<?php } ?>
</ul>
</nav>

<?php
break;


// ================= ENTRI / MULTI INPUT ==================
case 'entri':
?>

<a href="?page=dispatch_unit_master&aksi=list" class="btn btn-danger mb-3">
  List Unit
</a>

<h2>Tambah Unit (Gunakan + untukMulti Input)</h2>

<form method="POST" action="dispatch/aksi_unit_master.php?proses=input_multi">

<table class="table table-bordered">
<thead>
  <tr align="center">
    <th>Unit Kode</th>
    <th>Unit Name</th>
    <th>kategory</th>
    <th>Status</th>
    <th>Tambah</th>
  
  </tr>
</thead>


  <tbody id="unitTable">
    <tr>
      <td><input type="text" name="unit_code[]" class="form-control" required></td>
      <td>
      <select name="unit_name[]" class="form-control">
        <option value="HDT-HO">HDT-HO</option>
        <option value="HX-SA">HX-SA</option>
        <option value="HX-XC">HX-XC</option>
        <option value="HLX-SA">HLX-SA</option>
        <option value="OX-SA">OX-SA</option>
        <option value="HD-SH">HD-SH</option>
        <option value="OD-SH">OD-SH</option>
        <option value="DD-SH">DD-SH</option>
        <option value="HC-XC">HC-XC</option>
        <option value="HWT-HO">HWT-HO</option>
        <option value="HMG-KM">HMG-KM</option>
        <option value="HTL-PDKS">HTL-PDKS</option>
        </select>
    </td>
      <td>
        <select name="category[]" class="form-control">
          <option value="Dump Truck">Dump Truck</option>
          <option value="Excavator">Excavator</option>
          <option value="Bulldozer">Bulldozer</option>
          <option value="Compactor">Compactor</option>
          <option value="Motor Greader">Motor Greader</option>
          <option value="Water Truck">Water Truck</option>
          <option value="LT/LV">Light Truck/Light vehicle</option>
          <option value="Tower Lamp">Tower Lamp</option>
        </select>

      </td>
      <td>
        <select name="status[]" class="form-control">
          <option value="aktif">ACTIVE</option>
          <option value="nonaktif">NON ACTIVE</option>
        </select>
      </td>
      <td align="center">
        <button type="button" class="btn btn-success btn-sm" onclick="addRow()">+</button>
      </td>
    </tr>
  </tbody>
</table>

<button type="submit" class="btn btn-primary">
  Simpan Semua
</button>

</form>

<script>
function addRow(){
  let row = `
  <tr>
      <td><input type="text" name="unit_code[]" class="form-control" required></td>
      <td>
      <select name="unit_name[]" class="form-control">
          <option value="HDT-HO">HDT-HO</option>
    <option value="HX-XC">HX-XC</option>
        <option value="HX-SA">HX-SA</option>
        <option value="HLX-SA">HLX-SA</option>
        <option value="OX-SA">OX-SA</option>
        <option value="HD-SH">HD-SH</option>
        <option value="OD-SH">OD-SH</option>
        <option value="DD-SH">DD-SH</option>
        <option value="HC-XC">HC-XC</option>
        <option value="HTL-PDKS">HTL-PDKS</option>
        </select>
    </td>
      <td>
        <select name="category[]" class="form-control">
         <option value="Dump Truck">Dump Truck</option>
          <option value="Excavator">Excavator</option>
          <option value="Bulldozer">Bulldozer</option>
          <option value="Compactor">Compactor</option>
          <option value="Motor Greader">Motor Greader</option>
          <option value="Water Truck">Water Truck</option>
          <option value="LT/LV">Light Truck/Light vehicle</option>
          <option value="Tower Lamp">Tower Lamp</option>
        </select>

      </td>
      <td>
        <select name="status[]" class="form-control">
          <option value="aktif">ACTIVE</option>
          <option value="nonaktif">NON ACTIVE</option>
        </select>
      </td>
    <td align="center">
      <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">x</button>
    </td>
  </tr>`;
  document.getElementById('unitTable').insertAdjacentHTML('beforeend', row);
}

function removeRow(btn){
  btn.parentNode.parentNode.remove();
}
</script>

<?php
break;

default:
  echo "<h3>Halaman tidak ditemukan</h3>";
  break;

  case 'edit':

include '../koneksi.php';
$id = $_GET['id'];

$data = mysqli_query($koneksi,"SELECT * FROM dispatch_unit_master WHERE id='$id'");
$r = mysqli_fetch_assoc($data);
?>

<a href="?page=dispatch_unit_master&aksi=list" class="btn btn-danger mb-3">
  Kembali
</a>

<h2>Edit Unit</h2>

<form method="POST" action="dispatch/aksi_unit_master.php?proses=update">

<input type="hidden" name="id" value="<?= $r['id']; ?>">

<table class="table table-bordered">
  <tr>
    <th>Unit Code</th>
    <td><input type="text" name="unit_code" class="form-control" value="<?= $r['unit_code']; ?>"></td>
  </tr>

  <tr>
    <th>Unit Name</th>
    <td><input type="text" name="unit_name" class="form-control" value="<?= $r['unit_name']; ?>"></td>
  </tr>

  <tr>
    <th>Category</th>
    <td>
      <select name="category" class="form-control" required>
        <?php
        $opsi = [
          "Dump Truck",
          "Excavator",
          "Bulldozer",
          "Compactor",
          "Motor Grader",
          "Water Truck",
          "LT/LV",
          "Tower Lamp"
        ];

        foreach($opsi as $o){
          $sel = ($r['category'] == $o) ? "selected" : "";
          echo "<option value='$o' $sel>$o</option>";
        }
        ?>
      </select>
    </td>
  </tr>


  <tr>
    <th>Status</th>
    <td>
      <select name="status" class="form-control">
        <option value="aktif" <?= $r['status']=='aktif'?'selected':''; ?>>ACTIVE</option>
        <option value="nonaktif" <?= $r['status']=='nonaktif'?'selected':''; ?>>NON ACTIVE</option>
      </select>
    </td>
  </tr>

</table>

<button type="submit" class="btn btn-primary">Update</button>

</form>

<?php
break;


}
?>
