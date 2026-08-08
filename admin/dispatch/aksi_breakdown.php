<?php
session_start();
include '../koneksi.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* =========================
   CEK LOGIN
========================= */
$created_by = $_SESSION['iduser'] ?? null;
if (!$created_by) {
    die("User belum login");
}

/* =========================
   DATA POST (PASTIKAN ARRAY)
========================= */
$daily_log_id     = $_POST['daily_log_id']     ?? [];
$unit_id          = $_POST['unit_id']          ?? [];
$bd_start         = $_POST['bd_start']         ?? [];
$bd_end           = $_POST['bd_end']           ?? [];
$trouble_desc     = $_POST['trouble_desc']     ?? [];
$specific_trouble = $_POST['specific_trouble'] ?? [];
$location         = $_POST['location']         ?? [];

/* =========================
   LOOP DATA (MULTI INSERT)
========================= */
$total = count($daily_log_id);

for ($i = 0; $i < $total; $i++) {

    // 🔴 VALIDASI WAJIB (INI KUNCI)
    if (
        empty($daily_log_id[$i]) ||
        empty($unit_id[$i]) ||
        empty($bd_start[$i]) ||
        empty($bd_end[$i])
    ) {
        continue;
    }

    $dailyLogId = intval($daily_log_id[$i]);
    $unitId     = intval($unit_id[$i]);

    /* =========================
       AMBIL TANGGAL DAILY LOG
    ========================= */
    $qDate = mysqli_query($koneksi, "
        SELECT log_date
        FROM dispatch_daily_log
        WHERE id = $dailyLogId
        LIMIT 1
    ");

    if (!$qDate || mysqli_num_rows($qDate) === 0) {
        continue;
    }

    $log_date = mysqli_fetch_assoc($qDate)['log_date'];

    /* =========================
       GABUNG DATETIME
    ========================= */
    $start_dt = $log_date . ' ' . $bd_start[$i];
    $end_dt   = $log_date . ' ' . $bd_end[$i];

    $t1 = strtotime($start_dt);
    $t2 = strtotime($end_dt);

    // lintas tengah malam
    if ($t2 < $t1) {
        $t2 += 86400;
    }

    $bd_minutes = max(0, round(($t2 - $t1) / 60));

    /* =========================
       ESCAPE STRING
    ========================= */
    $trouble  = mysqli_real_escape_string($koneksi, $trouble_desc[$i] ?? '');
    $specific = mysqli_real_escape_string($koneksi, $specific_trouble[$i] ?? '');
    $lokasi   = mysqli_real_escape_string($koneksi, $location[$i] ?? '');

    /* =========================
       INSERT BREAKDOWN
    ========================= */
    mysqli_query($koneksi, "
        INSERT INTO dispatch_breakdown_log
        (
            daily_log_id,
            unit_id,
            bd_start,
            bd_end,
            bd_minutes,
            trouble_desc,
            specific_trouble,
            location,
            created_by
        ) VALUES (
            $dailyLogId,
            $unitId,
            '$start_dt',
            '$end_dt',
            $bd_minutes,
            '$trouble',
            '$specific',
            '$lokasi',
            $created_by
        )
    ");
}

/* =========================
   REDIRECT
========================= */
header("Location: ../index.php?page=dispatch_daily_log");
exit;
