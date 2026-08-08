<?php
include '../koneksi.php';
date_default_timezone_set('Asia/Jayapura');
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* =====================
   PARAMETER
===================== */
$tanggal = $_GET['log_date'] ?? date('Y-m-d');
$shift = strtolower(trim($_GET['shift'] ?? 'pagi'));

if (in_array($shift, ['pagi', 'day', 'siang'])) {
    $shift_db = 'day';
} else {
    $shift_db = 'night';
}


/* =====================
   SHIFT TIME (FINAL & SINKRON)
===================== */
if (in_array($shift, ['pagi', 'day', 'siang'])) {
    $shift_start = $tanggal . ' 07:00:00';
    $shift_end   = $tanggal . ' 19:00:00';
} else {
    $shift_start = $tanggal . ' 19:00:00';
    $shift_end   = date('Y-m-d 07:00:00', strtotime($tanggal . ' +1 day'));
}


/* =====================
   HEADER
===================== */
$hari_id = [
    'Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa',
    'Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'
];

$hari = date('l', strtotime($tanggal));

$report  = "*✓• REPORT UNIT BREAKDOWN AREA DSTF*\n";
$report .= "*{$hari_id[$hari]}, ".date('d-F-Y', strtotime($tanggal))."*\n";
$report .= "*Shift : {$shift}*\n\n";

/* =====================
   QUERY BREAKDOWN
===================== */
$sql = "
SELECT
    um.category,
    um.unit_code,
    b.trouble_desc,
    b.location,
    b.bd_end
FROM dispatch_breakdown_log b
JOIN dispatch_daily_log d ON d.id = b.daily_log_id
JOIN dispatch_unit_master um ON um.id = b.unit_id
WHERE d.log_date = '$tanggal'
AND d.shift = '$shift_db'
ORDER BY um.category, um.unit_code
";


$q = mysqli_query($koneksi, $sql);
if (!$q) {
    die('QUERY BREAKDOWN ERROR : ' . mysqli_error($koneksi));
}

$data = [];
$bd_count = [];

while ($r = mysqli_fetch_assoc($q)) {

    /* =========================
       LOGIKA FINAL BREAKDOWN
    ========================= */

    $bd_end = $r['bd_end'];

    // default: dianggap breakdown
    $masih_breakdown = true;

    // JIKA ADA BD END DAN SELESAI SEBELUM SHIFT BERAKHIR → READY
    if (!empty($bd_end)) {
        if (strtotime($bd_end) < strtotime($shift_end)) {
            $masih_breakdown = false;
        }
    }

    if ($masih_breakdown) {
        $data[$r['category']][] = $r;
        $bd_count[$r['category']] = ($bd_count[$r['category']] ?? 0) + 1;
    }
}


/* =====================
   TOTAL UNIT
===================== */
$qTotal = mysqli_query($koneksi, "
    SELECT category, COUNT(*) total
    FROM dispatch_unit_master
    GROUP BY category
");
if (!$qTotal) {
    die(mysqli_error($koneksi));
}

$total_unit = [];
while ($r = mysqli_fetch_assoc($qTotal)) {
    $total_unit[$r['category']] = $r['total'];
}

/* =====================
   OUTPUT REPORT
===================== */
foreach ($total_unit as $kategori => $total) {

    $report .= "⚙️ *".strtoupper($kategori)."*\n\n";

    if (!empty($data[$kategori])) {
        $no = 1;
        foreach ($data[$kategori] as $r) {
            $report .= $no.".   ".$r['unit_code']."\n";
            $report .= "*Ket. ".$r['trouble_desc']."*\n";
            $report .= "*Lokasi. ".$r['location']."*\n\n";
            $no++;
        }
    } else {
        $report .= "-\n-\n\n";
    }

    $bd = $bd_count[$kategori] ?? 0;
    $ready = $total - $bd;

    $report .= "*Jumlah Unit {$kategori} = {$total}*\n";
    $report .= "*Jumlah Breakdown = {$bd} Unit*\n";
    $report .= "*Jumlah Unit Ready = {$ready} Unit*\n\n";
}

$report .= "*TERIMA KASIH*";
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Preview Breakdown</title>
<style>
textarea{
    width:100%;
    height:600px;
    font-family: Consolas, monospace;
    font-size:14px;
}
button{
    padding:10px 15px;
    background:#28a745;
    color:#fff;
    border:none;
    cursor:pointer;
    margin-bottom:10px;
}
</style>
</head>
<body>

<button onclick="copyReport()">📋 Copy Laporan</button>
<textarea id="report"><?php echo htmlspecialchars($report); ?></textarea>

<script>
function copyReport(){
    const el = document.getElementById('report');
    el.select();
    document.execCommand('copy');
    alert('Laporan berhasil di copy');
}
</script>

</body>
</html>
