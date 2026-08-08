<?php
if(session_status() === PHP_SESSION_NONE) session_start();
include '../koneksi.php';

$created_by = $_SESSION['iduser'] ?? null;
if(!$created_by){
  die("User belum login");
}

// ==== AMBIL daily_log_id dari URL ====
$daily_log_id = isset($_GET['daily_log_id']) ? (int)$_GET['daily_log_id'] : 0;

if($daily_log_id <= 0){
  die("daily_log_id tidak ditemukan di URL");
}

// ==== AMBIL DATA DAILY LOG ====
$sql = "
  SELECT d.*, u.unit_code 
  FROM dispatch_daily_log d 
  JOIN dispatch_unit_master u ON u.id = d.unit_id
  WHERE d.id = '$daily_log_id'
";

$q = mysqli_query($koneksi,$sql);
$log = mysqli_fetch_assoc($q);

if(!$log){
  die("Daily Log tidak ditemukan");
}
?>

<h4>Input Breakdown — <?= $log['unit_code']; ?> (Shift <?= $log['shift'];?>)</h4>

<form method="POST" action="dispatch/simpan_breakdown_multi.php">
<input type="hidden" name="daily_log_id" value="<?= $daily_log_id; ?>">
<input type="hidden" name="unit_id" value="<?= $log['unit_id']; ?>">
<input type="hidden" name="log_date" value="<?= $log['log_date']; ?>">

<table class="table table-bordered" id="bdTable">
  <tr>
    <th>BD Start</th>
    <th>BD End</th>
    <th>Trouble</th>
    <th>Specific</th>
    <th>#</th>
  </tr>

  <tr>
    <td><input type="time" name="bd_start[]" class="form-control" required></td>
    <td><input type="time" name="bd_end[]" class="form-control" required></td>
    <td><input name="trouble_desc[]" class="form-control"></td>
    <td><input name="specific_trouble[]" class="form-control"></td>
    <td><button type="button" class="btn btn-success" onclick="addRow()">+</button></td>
  </tr>
</table>

<button type="submit" class="btn btn-primary">Simpan Breakdown</button>
</form>

<script>
function addRow(){
  let row = `
  <tr>
    <td><input type="time" name="bd_start[]" class="form-control" required></td>
    <td><input type="time" name="bd_end[]" class="form-control" required></td>
    <td><input name="trouble_desc[]" class="form-control"></td>
    <td><input name="specific_trouble[]" class="form-control"></td>
    <td><button type="button" class="btn btn-danger" onclick="this.closest('tr').remove()">x</button></td>
  </tr>`;
  document.getElementById('bdTable').insertAdjacentHTML('beforeend', row);
}
</script>
