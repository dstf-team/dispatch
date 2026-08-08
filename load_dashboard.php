<?php
include 'koneksi.php';

$data = [];

/* SUMMARY */
$q = mysqli_query($koneksi,"
SELECT 
    equipment,
    SUM(status_unit='READY') AS ready,
    SUM(status_unit='BREAKDOWN') AS breakdown,
    COUNT(*) AS total
FROM dispatch_unit_status
WHERE log_date = CURDATE()
AND shift = 'day'
GROUP BY equipment
ORDER BY equipment
");

$summary = [];
while($r = mysqli_fetch_assoc($q)){
    $summary[] = $r;
}

/* BREAKDOWN */
$q2 = mysqli_query($koneksi,"
SELECT unit_code, equipment, breakdown_status, location_code
FROM dispatch_unit_status
WHERE status_unit = 'BREAKDOWN'
AND log_date = CURDATE()
AND shift = 'day'
ORDER BY equipment, unit_id
");

$breakdown = [];
while($r = mysqli_fetch_assoc($q2)){
    $breakdown[] = $r;
}

echo json_encode([
    "summary"=>$summary,
    "breakdown"=>$breakdown
]);