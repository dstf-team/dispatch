<?php
date_default_timezone_set('Asia/Jayapura');
if (session_status() == PHP_SESSION_NONE) session_start();
include '../koneksi.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$log_date = $_GET['log_date'] ?? '';
$shift    = $_GET['shift'] ?? '';

if(!$log_date || !$shift){
    die("Tanggal / shift tidak ada");
}

$shift_label = ($shift == 'day') ? 'Siang' : 'Malam';
$hari = hariIndonesia($log_date);
$tgl  = date('d F Y', strtotime($log_date));

/* =======================
   AMBIL DATA (1 UNIT = 1 RECORD TERAKHIR)
======================= */
$sql = "
SELECT 
    d.*,
    u.unit_code,
    u.category,
    l.nama_lokasi
FROM dispatch_daily_log d
JOIN (
    SELECT unit_id, MAX(id) AS last_id
    FROM dispatch_daily_log
    WHERE log_date = '$log_date'
      AND shift    = '$shift'
    GROUP BY unit_id
) x ON d.id = x.last_id
LEFT JOIN dispatch_unit_master u ON d.unit_id = u.id
LEFT JOIN lokasi l ON d.location = l.kode_lokasi
ORDER BY u.category, u.unit_code
";

$res = mysqli_query($koneksi, $sql);

// Kelompokkan per kategori
$data = [];
while($r = mysqli_fetch_assoc($res)){
    $data[$r['category']][] = $r;
}

/* =======================
   FUNGSI KLASIFIKASI (FIX TOTAL)
======================= */
function classify_units($units){

    $ready_driver   = [];
    $ready_nodriver = [];
    $breakdown      = [];

    $ready_total  = 0;
    $driver_total = 0;

    $now_ts = time(); // waktu sekarang

    foreach($units as $u){

        $bd_start = trim($u['bd_start'] ?? '');
        $bd_end   = trim($u['bd_end'] ?? '');
        $op       = trim($u['operator_name'] ?? '');

        $is_breakdown = false;

        // Jika pernah breakdown
        if($bd_start !== ''){
            $is_breakdown = true;

            // Jika bd_end ada dan sudah lewat
            if($bd_end !== ''){
                $bd_end_ts = strtotime($bd_end);

                if($bd_end_ts !== false && $bd_end_ts <= $now_ts){
                    $is_breakdown = false; // ✅ READY
                }
            }
        }

        if($is_breakdown){
            $breakdown[] = $u;
        } else {
            $ready_total++;

            if($op !== ''){
                $ready_driver[] = $u;
                $driver_total++;
            } else {
                $ready_nodriver[] = $u;
            }
        }
    }

    return [
        $ready_driver,
        $ready_nodriver,
        $breakdown,
        $ready_total,
        count($breakdown),
        $driver_total
    ];
}

/* =======================
   TRANSLATE HARI
======================= */
function hariIndonesia($date){
    $hariInggris = date('l', strtotime($date));
    $map = [
        'Sunday'    => 'Minggu',
        'Monday'    => 'Senin',
        'Tuesday'   => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday'  => 'Kamis',
        'Friday'    => 'Jumat',
        'Saturday'  => 'Sabtu'
    ];
    return $map[$hariInggris] ?? $hariInggris;
}

/* =======================
   LAPORAN
======================= */
$report  = "*✓• DAILY REPORT UNIT READY, BREAKDOWN & MANPOWER AREA DSTF*\n";
$report .= "*Hari\t: $hari, $tgl*\n";
$report .= "*Shift\t: $shift_label*\n\n";

foreach($data as $cat => $units){

    list(
        $ready_driver,
        $ready_nodriver,
        $breakdown,
        $ready_total,
        $breakdown_total,
        $drivers
    ) = classify_units($units);

    $report .= "*Job $cat*\n\n";

    // READY + DRIVER
    if($ready_driver){
        $groups = [];
        foreach($ready_driver as $u){
            $key = trim($u['job_desc']).'|'.trim($u['nama_lokasi']);
            $groups[$key][] = $u;
        }

        $no = 1;
        foreach($groups as $group){
            foreach($group as $u){
                $report .= "$no.\t{$u['unit_code']} | {$u['operator_name']}\n";
                $no++;
            }

            if(!empty($group[0]['job_desc']))
                $report .= "*Ket.* {$group[0]['job_desc']}\n";

            if(!empty($group[0]['nama_lokasi']))
                $report .= "*Lokasi.* {$group[0]['nama_lokasi']}\n";

            $report .= "\n";
        }
    }

    // READY / NO DRIVER
    if($ready_nodriver){
        $report .= "\n*{$cat} READY / NO DRIVER*\n\n";
        $i = 1;
        foreach($ready_nodriver as $u){
            $report .= "$i.\t{$u['unit_code']}\n";
            if(!empty($u['nama_lokasi']))
                $report .= "*Lokasi.* {$u['nama_lokasi']}\n";
            $i++;
        }
    }

    // BREAKDOWN
    if($breakdown){
        $report .= "\n*{$cat} BREAKDOWN*\n\n";
        $i = 1;
        foreach($breakdown as $u){
            $report .= "$i.\t{$u['unit_code']}";
            if(!empty($u['operator_name']))
                $report .= " | {$u['operator_name']}";
            $report .= "\n";

            if(!empty($u['trouble_desc']))
                $report .= "*Ket.* {$u['trouble_desc']}\n";

            if(!empty($u['nama_lokasi']))
                $report .= "*Lokasi.* {$u['nama_lokasi']}\n";

            $i++;
        }
    }

    $report .= "\n*Jumlah Unit $cat = ".count($units)."*\n";
    $report .= "*Jumlah Unit Ready = $ready_total*\n";
    $report .= "*Jumlah Unit Breakdown = $breakdown_total*\n";
    $report .= "*Jumlah Driver/Operator = $drivers*\n\n";
}

$report .= "*TERIMA KASIH*";
?>

<h4>Laporan Daily Dispatch</h4>
<button onclick="copyReport()" class="btn btn-success mb-2">Copy Laporan</button>

<textarea id="reportArea"
style="width:100%;height:600px;font-family:monospace;white-space:pre-wrap;">
<?= htmlspecialchars($report) ?>
</textarea>

<script>
function copyReport(){
    const el = document.getElementById("reportArea");
    el.select();
    el.setSelectionRange(0, 99999);
    document.execCommand("copy");
    alert("Laporan berhasil dicopy!");
}
</script>
