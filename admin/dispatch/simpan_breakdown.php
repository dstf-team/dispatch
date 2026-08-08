<?php
session_start();
include '../koneksi.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ===========================
// USER LOGIN
// ===========================
$created_by = $_SESSION['iduser'] ?? null;
if(!$created_by){
    die("User belum login");
}

// ===========================
// DATA FORM
// ===========================
$daily_ids = $_POST['daily_log_id'] ?? [];
$unit_ids  = $_POST['unit_id'] ?? [];

$bd_start  = $_POST['bd_start'] ?? [];
$bd_end    = $_POST['bd_end'] ?? [];
$trouble   = $_POST['trouble_desc'] ?? [];
$specific  = $_POST['specific_trouble'] ?? [];

$total_shift = 720;

// ===========================
// LOOP PER UNIT
// ===========================
foreach($daily_ids as $i => $daily_log_id){

    if(empty($bd_start[$i]) || empty($bd_end[$i])) continue;

    $start = strtotime($bd_start[$i]);
    $end   = strtotime($bd_end[$i]);

    if($end < $start){
        $end += 24 * 3600; // lewat tengah malam
    }

    $bd_minutes = ($end - $start) / 60;
    if($bd_minutes <= 0) continue;

    // ===========================
    // INSERT BREAKDOWN
    // ===========================
    mysqli_query($koneksi,"
        INSERT INTO dispatch_breakdown_log SET
            daily_log_id     = '$daily_log_id',
            unit_id          = '{$unit_ids[$i]}',
            bd_start         = '{$bd_start[$i]}',
            bd_end           = '{$bd_end[$i]}',
            bd_minutes       = '$bd_minutes',
            trouble_desc     = '{$trouble[$i]}',
            specific_trouble = '{$specific[$i]}',
            created_by       = '$created_by',
            created_at       = NOW()
    ");

    // ===========================
    // HITUNG ULANG TOTAL BD
    // ===========================
    $q = mysqli_query($koneksi,"
        SELECT SUM(bd_minutes) AS total_bd
        FROM dispatch_breakdown_log
        WHERE daily_log_id = '$daily_log_id'
    ");

    $total_bd = intval(mysqli_fetch_assoc($q)['total_bd']);

    $work   = $total_shift - $total_bd;
    if($work < 0) $work = 0;

    $pa = ($work / $total_shift) * 100;
    $ma = (($total_shift - $total_bd) / $total_shift) * 100;
    $ua = ($work / $total_shift) * 100;

    // ===========================
    // UPDATE DAILY LOG
    // ===========================
    mysqli_query($koneksi,"
        UPDATE dispatch_daily_log SET
            bd_minutes      = '$total_bd',
            work_minutes    = '$work',
            standby_minutes = 0,
            pa              = '$pa',
            ma              = '$ma',
            ua              = '$ua',
            updated_at      = NOW(),
            updated_by      = '$created_by'
        WHERE id = '$daily_log_id'
    ");
}

echo "<script>
    alert('Breakdown tambahan berhasil disimpan');
    window.history.back();
</script>";
