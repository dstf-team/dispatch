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
$log_date = $_POST['log_date'] ?? '';
$shift    = $_POST['shift'] ?? '';

if(!$log_date || !$shift){
    die("Tanggal atau shift tidak terkirim");
}

if(!isset($_POST['unit_id'])){
    die("Tidak ada data unit dikirim");
}

// ===========================
// CEK APAKAH SUDAH INPUT SHIFT INI DI TANGGAL TERSEBUT
// ===========================
$cek_shift = mysqli_query($koneksi,"
    SELECT COUNT(*) AS jml
    FROM dispatch_daily_log
    WHERE log_date = '$log_date' AND shift = '$shift'
");

$ada_shift = intval(mysqli_fetch_assoc($cek_shift)['jml']);

if($ada_shift > 0){
    echo "<script>
        alert('‼ DUPLIKAT DATA TERDETEKSI ‼\\n\\nInput untuk tanggal $log_date shift $shift sudah pernah dilakukan.\\nSilakan gunakan EDIT jika ingin ubah.');
        window.location='../index.php?page=dispatch_input_daily';
    </script>";
    exit;
}

// ===========================
// MULAI LOOP PER UNIT
// ===========================
$total = 720; // menit per shift

foreach($_POST['unit_id'] as $i=>$unit){

    $op  = trim($_POST['operator_name'][$i] ?? '');
    $job = $_POST['job_desc'][$i] ?? '';
    $td  = $_POST['trouble_desc'][$i] ?? '';
    $bd1 = $_POST['bd_start'][$i] ?? '';
    $bd2 = $_POST['bd_end'][$i] ?? '';

    $loc = $_POST['location_select'][$i] ?? '';
    $st  = $_POST['specific_trouble'][$i] ?? '';

    $bd_start_dt = (!empty($bd1)) ? "$log_date $bd1" : null;
    $bd_end_dt   = (!empty($bd2)) ? "$log_date $bd2" : null;


// =========================== HITUNG BD (SELALU)
// ===========================
$total = 720;

$bd_minutes = 0;
if(!empty($bd1) && !empty($bd2)){
    $t1 = strtotime($bd1);
    $t2 = strtotime($bd2);
    if($t2 < $t1) $t2 += 24*3600;
    $bd_minutes = max(0, ($t2 - $t1)/60);
}
if($bd_minutes > $total) $bd_minutes = $total;

// =========================== HITUNG WORK & STANDBY
// ===========================
if($op == ''){
    // TANPA OPERATOR
    $work_minutes    = 0;
    $standby_minutes = $total - $bd_minutes;
} else {
    // ADA OPERATOR
    $work_minutes    = $total - $bd_minutes;
    $standby_minutes = 0;
}

if($work_minutes < 0)    $work_minutes = 0;
if($standby_minutes < 0) $standby_minutes = 0;


    // =========================== INSERT DAILY LOG
    // ===========================
    $sql = "
        INSERT INTO dispatch_daily_log SET
            unit_id          = '$unit',
            log_date         = '$log_date',
            shift            = '$shift',
            operator_name    = '$op',
            location         = '$loc',
            job_desc         = '$job',
            trouble_desc     = '$td',
            specific_trouble = '$st',
            bd_start         = ".($bd_start_dt ? "'$bd_start_dt'" : "NULL").",
            bd_end           = ".($bd_end_dt   ? "'$bd_end_dt'"   : "NULL").",
            work_minutes     = '$work_minutes',
            bd_minutes       = '$bd_minutes',
            standby_minutes  = '$standby_minutes',
            total_minutes    = '$total',
        
            created_by       = '$created_by',
            created_at       = NOW()
    ";

    $ok = mysqli_query($koneksi,$sql);
if(!$ok){

    // JIKA DUPLIKAT (SHIFT & TANGGAL SUDAH ADA)
    if(mysqli_errno($koneksi) == 1062){
        echo "<script>
            alert(
            '❌ INPUT DITOLAK\\n\\n' +
            'Tanggal : $log_date\\n' +
            'Shift   : $shift\\n\\n' +
            'Data shift ini sudah pernah diinput.\\n' +
            'Silakan kembali ke halaman input.'
            );
            window.location.href='?page=laporandistpatch';
        </script>";
        exit;
    }

    // ERROR LAIN
    die('GAGAL INSERT DAILY — '.mysqli_error($koneksi));
}


    $daily_log_id = mysqli_insert_id($koneksi);


// =========================== INSERT BREAKDOWN JIKA ADA
// ===========================
if($bd_start_dt && $bd_end_dt){
    $sql2 = "
        INSERT INTO dispatch_breakdown_log SET
            daily_log_id     = '$daily_log_id',
            unit_id          = '$unit',
            location         = '$loc',
            bd_start         = '$bd_start_dt',
            bd_end           = '$bd_end_dt',
            bd_minutes       = '$bd_minutes',
            trouble_desc     = '$td',
            specific_trouble = '$st',
            created_by       = '$created_by',
            created_at       = NOW()
    ";
    $ok2 = mysqli_query($koneksi,$sql2);
    if(!$ok2){
        die('GAGAL INSERT BREAKDOWN — '.mysqli_error($koneksi));
    }
}


}

echo "<script>
    alert('Data tersimpan berhasil (max 2 shift per hari)');
    location.href='../index.php?page=dispatch_daily_log';
</script>";
