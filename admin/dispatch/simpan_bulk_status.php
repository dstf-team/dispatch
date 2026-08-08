<?php

include '../koneksi.php';

/* =========================================
   CEK HARUS DARI POST
========================================= */
if($_SERVER['REQUEST_METHOD'] != 'POST'){

    header("Location: ../index.php?page=simpan_bulk_status");
    exit;

}

/* =========================================
   CEK DATA ADA
========================================= */
if(
    !isset($_POST['unit_id']) ||
    empty($_POST['unit_id'])
){

    echo "
    <script>
        alert('Data kosong');
        window.location='../index.php?page=simpan_bulk_status';
    </script>
    ";

    exit;
}

/* =========================================
   AMBIL DATA POST
========================================= */
$log_date = $_POST['log_date'];
$shift    = $_POST['shift'];

$unit_id          = $_POST['unit_id'];
$unit_code        = $_POST['unit_code'];
$equipment        = $_POST['equipment'];
$location_code    = $_POST['location_code'];
$status_unit      = $_POST['status_unit'];
$breakdown_status = $_POST['breakdown_status'];

$dataInsert = [];

/* =========================================
   LOOP DATA
========================================= */
for($i=0; $i<count($unit_id); $i++){

    $uid   = mysqli_real_escape_string($koneksi,$unit_id[$i]);
    $ucode = mysqli_real_escape_string($koneksi,$unit_code[$i]);
    $equip = mysqli_real_escape_string($koneksi,$equipment[$i]);
    $loc   = mysqli_real_escape_string($koneksi,$location_code[$i]);
    $stat  = mysqli_real_escape_string($koneksi,$status_unit[$i]);
    $bd    = mysqli_real_escape_string($koneksi,$breakdown_status[$i]);

    $dataInsert[] = "(
        '$log_date',
        '$shift',
        '$equip',
        '$uid',
        '$ucode',
        '$loc',
        '$stat',
        '$bd'
    )";
}

/* =========================================
   BULK INSERT
========================================= */
if(count($dataInsert) > 0){

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
        ".implode(",", $dataInsert);

    $insert = mysqli_query($koneksi,$sql);

    if($insert){

        echo "
        <script>
            alert('Bulk insert berhasil');
            window.location='../index.php?page=simpan_bulk_status';
        </script>
        ";

    }else{

        echo "
        <script>
            alert('Gagal insert data');
            window.location='../index.php?page=simpan_bulk_status';
        </script>
        ";

    }

}else{

    echo "
    <script>
        alert('Data kosong');
        window.location='../index.php?page=simpan_bulk_status';
    </script>
    ";
}
?>