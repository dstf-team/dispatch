<?php
include "../koneksi.php";

$unit_id = intval($_GET['unit_id'] ?? 0);
$tgl1 = $_GET['tgl1'] ?? '';
$tgl2 = $_GET['tgl2'] ?? '';

$where = " WHERE dl.unit_id = $unit_id ";

if($tgl1 != '' && $tgl2 != ''){
    $where .= " AND dl.log_date BETWEEN '$tgl1' AND '$tgl2' ";
}
elseif($tgl1 != ''){
    $where .= " AND dl.log_date = '$tgl1' ";
}

$sql = "
SELECT 
 dl.log_date,
 dl.shift,
 dl.trouble_desc,
 dl.specific_trouble,
 dl.bd_minutes,
 dl.work_minutes,
 dl.standby_minutes
FROM dispatch_daily_log dl
$where
ORDER BY dl.log_date ASC, dl.shift ASC
";

$q = mysqli_query($koneksi,$sql) or die(mysqli_error($koneksi));
?>

<table class="table table-bordered">
<tr class="bg-light fw-bold text-center">
<td>Tanggal</td>
<td>Shift</td>
<td>Trouble</td>
<td>Specific</td>
<td>BD (min)</td>
<td>Work</td>
<td>Standby</td>
</tr>

<?php
$total_bd = 0;

while($r=mysqli_fetch_assoc($q)){ 
$total_bd += $r['bd_minutes'];
?>
<tr>
<td><?= $r['log_date'] ?></td>
<td><?= strtoupper($r['shift']) ?></td>
<td><?= $r['trouble_desc'] ?></td>
<td><?= $r['specific_trouble'] ?></td>
<td class="text-center fw-bold text-danger"><?= $r['bd_minutes'] ?></td>
<td class="text-center"><?= $r['work_minutes'] ?></td>
<td class="text-center"><?= $r['standby_minutes'] ?></td>
</tr>
<?php } ?>
</table>

<h5 class="text-danger">TOTAL DOWNTIME : <?= $total_bd ?> menit</h5>
