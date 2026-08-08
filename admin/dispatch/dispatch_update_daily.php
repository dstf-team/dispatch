<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

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
// AMBIL DATA POST
// ===========================
$log_date = $_POST['log_date'] ?? '';
$shift    = $_POST['shift'] ?? '';
$unit_ids = $_POST['unit_id'] ?? [];

if (!$log_date || !$shift || empty($unit_ids)) {
    die("Tanggal, shift atau unit tidak ada.");
}

$total = 720; // menit per shift

foreach($unit_ids as $i => $unit){

    // ===========================
    // AMBIL DATA FORM DENGAN ESCAPE
    // ===========================
    $op  = mysqli_real_escape_string($koneksi, trim($_POST['operator_name'][$i] ?? ''));
    $job = mysqli_real_escape_string($koneksi, $_POST['job_desc'][$i] ?? '');
    $td  = mysqli_real_escape_string($koneksi, $_POST['trouble_desc'][$i] ?? '');
    $bd1 = $_POST['bd_start'][$i] ?? '';
    $bd2 = $_POST['bd_end'][$i] ?? '';
    $loc = mysqli_real_escape_string($koneksi, $_POST['location'][$i] ?? '');
    $st  = mysqli_real_escape_string($koneksi, $_POST['specific_trouble'][$i] ?? '');

    // ===========================
    // BANGUN DATETIME UNTUK SIMPAN
    // ===========================
    $bd_start_dt = (!empty($bd1)) ? "$log_date $bd1" : null;
    $bd_end_dt   = (!empty($bd2)) ? "$log_date $bd2" : null;

    // ===========================
    // HITUNG WORK, BD, STANDBY
    // ===========================
    $work_minutes = 0;
    $bd_minutes   = 0;
    $standby      = 0;

    if($op == ''){
        $standby      = $total;
    } else {
        if(!empty($bd1) && !empty($bd2)){
            $t1 = strtotime($bd1);
            $t2 = strtotime($bd2);
            if($t2 < $t1){ $t2 += 24*3600; } // melewati tengah malam
            $bd_minutes = ($t2 - $t1)/60;
        }
        $work_minutes = max($total - $bd_minutes, 0);
        $standby = 0;
    }

    // ===========================
    // KPI
    // ===========================
    $pa = ($work_minutes / $total) * 100;
    $ma = (($total - $bd_minutes) / $total) * 100;
    $den = $total - $standby;
    if($den <= 0){ $den = 1; }
    $ua = ($work_minutes / $den) * 100;

    // ===========================
    // UPDATE DAILY LOG
    // ===========================
    $sql = "
        UPDATE dispatch_daily_log SET
            operator_name    = '$op',
            location         = '$loc',
            job_desc         = '$job',
            trouble_desc     = '$td',
            specific_trouble = '$st',
            bd_start         = ".($bd_start_dt ? "'$bd_start_dt'" : "NULL").",
            bd_end           = ".($bd_end_dt   ? "'$bd_end_dt'"   : "NULL").",
            work_minutes     = '$work_minutes',
            bd_minutes       = '$bd_minutes',
            standby_minutes  = '$standby',
            total_minutes    = '$total',
            pa               = '$pa',
            ma               = '$ma',
            ua               = '$ua'
          
        WHERE unit_id = '$unit' AND log_date='$log_date' AND shift='$shift'
    ";

    $ok = mysqli_query($koneksi, $sql);
    if(!$ok){
        die("GAGAL UPDATE DAILY — ".mysqli_error($koneksi));
    }

    // ===========================
    // UPDATE BREAKDOWN LOG JIKA ADA
    // ===========================
    if($bd_start_dt && $bd_end_dt){
        $cek = mysqli_query($koneksi,"
            SELECT id 
            FROM dispatch_breakdown_log 
            WHERE daily_log_id = (
                SELECT id 
                FROM dispatch_daily_log 
                WHERE unit_id='$unit' AND log_date='$log_date' AND shift='$shift'
            ) 
            LIMIT 1
        ");
        $id_break = mysqli_fetch_assoc($cek)['id'] ?? null;

        if($id_break){
            // update
            $sql2 = "
                UPDATE dispatch_breakdown_log SET
                    bd_start         = '$bd_start_dt',
                    bd_end           = '$bd_end_dt',
                    bd_minutes       = '$bd_minutes',
                    trouble_desc     = '$td',
                    specific_trouble = '$st'
                WHERE id = '$id_break'
            ";
            mysqli_query($koneksi, $sql2);
        } else {
            // insert baru
            $daily_id = mysqli_query($koneksi,"
                SELECT id 
                FROM dispatch_daily_log 
                WHERE unit_id='$unit' AND log_date='$log_date' AND shift='$shift' 
                LIMIT 1
            ");
            $daily_id = mysqli_fetch_assoc($daily_id)['id'] ?? null;

            if($daily_id){
                $sql2 = "
                    INSERT INTO dispatch_breakdown_log SET
                        daily_log_id     = '$daily_id',
                        unit_id          = '$unit',
                        bd_start         = '$bd_start_dt',
                        bd_end           = '$bd_end_dt',
                        bd_minutes       = '$bd_minutes',
                        trouble_desc     = '$td',
                        specific_trouble = '$st'
                ";
                mysqli_query($koneksi, $sql2);
            }
        }
    }
}

echo "<script>
    alert('Data daily berhasil diperbarui');
    location.href='index.php?page=dispatch_daily_log';
</script>";
