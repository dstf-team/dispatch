<?php
if(session_status() === PHP_SESSION_NONE) session_start();
include '../koneksi.php';

$created_by = $_SESSION['iduser'] ?? null;
if(!$created_by) die("User tidak dikenali");

$daily_log_id = $_POST['daily_log_id'] ?? 0;
$unit_id      = $_POST['unit_id'] ?? 0;
$log_date     = $_POST['log_date'] ?? '';

if($daily_log_id == 0) die("daily_log_id kosong");

// total menit per shift
$total = 720;

foreach($_POST['bd_start'] as $i=>$bd1){

    $bd2 = $_POST['bd_end'][$i] ?? '';
    $td  = $_POST['trouble_desc'][$i] ?? '';
    $st  = $_POST['specific_trouble'][$i] ?? '';

    if($bd1 == '' || $bd2 == '') continue;

    $bd_start_dt = "$log_date $bd1";
    $bd_end_dt   = "$log_date $bd2";

    $t1 = strtotime($bd_start_dt);
    $t2 = strtotime($bd_end_dt);

    if($t2 < $t1){
        $t2 += 24*3600;
    }

    $bd_minutes = ($t2 - $t1) / 60;
    if($bd_minutes < 0) $bd_minutes = 0;

    // ===== INSERT KE dispatch_breakdown_log =====
    mysqli_query($koneksi,"
      INSERT INTO dispatch_breakdown_log SET
        daily_log_id     = '$daily_log_id',
        unit_id          = '$unit_id',
        bd_start         = '$bd_start_dt',
        bd_end           = '$bd_end_dt',
        bd_minutes       = '$bd_minutes',
        trouble_desc     = '$td',
        specific_trouble = '$st',
        created_by       = '$created_by',
        created_at       = NOW()
    ") or die(mysqli_error($koneksi));
}


// ===== HITUNG ULANG TOTAL BREAKDOWN =====
$sum = mysqli_query($koneksi,"
  SELECT SUM(bd_minutes) AS total_bd 
  FROM dispatch_breakdown_log 
  WHERE daily_log_id='$daily_log_id'
");
$s = mysqli_fetch_assoc($sum);

$total_bd = $s['total_bd'] ?? 0;
$work = $total - $total_bd;
if($work < 0) $work = 0;

// ===== UPDATE DAILY LOG =====
mysqli_query($koneksi,"
  UPDATE dispatch_daily_log SET
    bd_minutes = '$total_bd',
    work_minutes = '$work',
    pa = ($work / $total) * 100,
    ma = (($total - $total_bd) / $total) * 100,
    ua = ($work / $total) * 100
  WHERE id='$daily_log_id'
") or die(mysqli_error($koneksi));


echo "<script>alert('Breakdown tersimpan');location.href='../index.php?page=dispatch_daily_log';</script>";
