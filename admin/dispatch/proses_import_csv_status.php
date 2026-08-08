<?php

include '../koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================================
   VALIDASI REQUEST
========================================= */
if ($_SERVER['REQUEST_METHOD'] != 'POST') {

    echo "
    <script>
        alert('Request tidak valid');
        window.location='../index.php?page=bulk_status';
    </script>
    ";

    exit;
}

/* =========================================
   VALIDASI FILE CSV
========================================= */
if (!isset($_FILES['file_csv'])) {

    echo "
    <script>
        alert('File CSV tidak ditemukan');
        window.location='../index.php?page=bulk_status';
    </script>
    ";

    exit;
}

/* =========================================
   AMBIL TANGGAL & SHIFT
========================================= */
$log_date = mysqli_real_escape_string(
    $koneksi,
    $_POST['log_date']
);

$shift = mysqli_real_escape_string(
    $koneksi,
    $_POST['shift']
);

/* =========================================
   HAPUS DATA LAMA
========================================= */
mysqli_query($koneksi, "
    DELETE FROM dispatch_unit_status
    WHERE log_date='$log_date'
    AND shift='$shift'
");

/* =========================================
   AMBIL MASTER UNIT
========================================= */
$qMaster = mysqli_query($koneksi, "
    SELECT
        id,
        unit_code,
        category
    FROM dispatch_unit_master
    WHERE status='aktif'
");

$dataUnit = [];

/* =========================================
   DEFAULT SEMUA READY
========================================= */
while ($m = mysqli_fetch_assoc($qMaster)) {

    $kode = trim(strtoupper($m['unit_code']));

    $dataUnit[$kode] = [

        'log_date'         => $log_date,
        'shift'            => $shift,
        'equipment'        => $m['category'],
        'unit_id'          => $m['id'],
        'unit_code'        => $m['unit_code'],
        'location_code'    => '',
        'status_unit'      => 'READY',
        'breakdown_status' => ''

    ];
}

/* =========================================
   BACA FILE CSV
========================================= */
$file = $_FILES['file_csv']['tmp_name'];

$handle = fopen($file, 'r');

if ($handle === false) {

    echo "
    <script>
        alert('Gagal membaca CSV');
        window.location='../index.php?page=bulk_status';
    </script>
    ";

    exit;
}

$baris = 0;

/* =========================================
   LOOP CSV
========================================= */
while (($row = fgetcsv($handle, 1000, ';')) !== false) {

    $baris++;

    /* =====================================
       SKIP HEADER
    ===================================== */
    if ($baris == 1) {
        continue;
    }

    /* =====================================
       VALIDASI KOLOM
    ===================================== */
    if (count($row) < 4) {
        continue;
    }

    /* =====================================
       AMBIL DATA CSV
    ===================================== */
    $unit_code      = trim(strtoupper($row[0]));
    $status_unit    = trim(strtoupper($row[1]));
    $keterangan     = trim($row[2]);
    $location_code  = trim($row[3]);

    /* =====================================
       VALIDASI STATUS
    ===================================== */
    if ($status_unit == '') {
        $status_unit = 'BREAKDOWN';
    }

    /* =====================================
       UPDATE UNIT SESUAI CSV
    ===================================== */
    if (isset($dataUnit[$unit_code])) {

        $dataUnit[$unit_code]['status_unit']
            = $status_unit;

        $dataUnit[$unit_code]['breakdown_status']
            = $keterangan;

        $dataUnit[$unit_code]['location_code']
            = $location_code;
    }
}

fclose($handle);

/* =========================================
   BUILD INSERT
========================================= */
$dataInsert = [];

foreach ($dataUnit as $d) {

    $log_date2        = mysqli_real_escape_string($koneksi, $d['log_date']);
    $shift2           = mysqli_real_escape_string($koneksi, $d['shift']);
    $equipment        = mysqli_real_escape_string($koneksi, $d['equipment']);
    $unit_id          = mysqli_real_escape_string($koneksi, $d['unit_id']);
    $unit_code        = mysqli_real_escape_string($koneksi, $d['unit_code']);
    $location_code    = mysqli_real_escape_string($koneksi, $d['location_code']);
    $status_unit      = mysqli_real_escape_string($koneksi, $d['status_unit']);
    $breakdown_status = mysqli_real_escape_string($koneksi, $d['breakdown_status']);

    $dataInsert[] = "(
        '$log_date2',
        '$shift2',
        '$equipment',
        '$unit_id',
        '$unit_code',
        '$location_code',
        '$status_unit',
        '$breakdown_status'
    )";
}

/* =========================================
   INSERT DATABASE
========================================= */
if (count($dataInsert) > 0) {

$sql = "
INSERT INTO dispatch_unit_status (
    log_date,
    shift,
    equipment,
    unit_id,
    unit_code,
    location_code,
    status_unit,
    breakdown_status
)
VALUES
" . implode(',', $dataInsert) . "
ON DUPLICATE KEY UPDATE
    location_code = VALUES(location_code),
    status_unit = VALUES(status_unit),
    breakdown_status = VALUES(breakdown_status)
";

    $insert = mysqli_query($koneksi, $sql);

    if ($insert) {

        echo "
        <script>
            alert('Import CSV berhasil');
            window.location='../index.php?page=bulk_status';
        </script>
        ";

    } else {

        echo "
        <script>
            alert('Gagal insert database');
            window.location='../index.php?page=bulk_status';
        </script>
        ";
    }

} else {

    echo "
    <script>
        alert('Data kosong');
        window.location='../index.php?page=bulk_status';
    </script>
    ";
}
?>