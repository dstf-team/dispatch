<?php
session_start();
include "../koneksi.php";

// validasi login
if (!isset($_SESSION['iduser'])) {
    header("location:login.php");
    exit;

}

$page   = $_GET['page']   ?? '';
$proses = $_GET['proses'] ?? '';

$folder = "../uploads/manpower/";

// pastikan folder ada
if (!is_dir($folder)) {
    mkdir($folder, 0777, true);
}

/* ============================================================
   FUNCTION UPLOAD FOTO
============================================================ */
function uploadFoto($file, $folder, $foto_lama = null)
{
    if (empty($file['name'])) {
        return $foto_lama;
    }

    $allowed = ['jpg','jpeg','png'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        return $foto_lama;
    }

    // hapus foto lama jika ada
    if ($foto_lama && file_exists($folder.$foto_lama)) {
        unlink($folder.$foto_lama);
    }

    $namaFile = time().'_'.uniqid().'.'.$ext;
    move_uploaded_file($file['tmp_name'], $folder.$namaFile);

    return $namaFile;
}

/* ============================================================
   INPUT
============================================================ */
if ($page == "manpower" && $proses == "input") {

    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $nik      = mysqli_real_escape_string($koneksi, $_POST['nik']);
    $jabatan  = mysqli_real_escape_string($koneksi, $_POST['jabatan']);
    $jabatan_tambahan = mysqli_real_escape_string($koneksi, $_POST['jabatan_tambahan']);
    $devisi   = mysqli_real_escape_string($koneksi, $_POST['devisi']);
    $status_bekerja = $_POST['status_bekerja'];
    $status_kerja   = $_POST['status_kerja'];
    $tanggal_masuk  = $_POST['tanggal_masuk'];
    $poh      = mysqli_real_escape_string($koneksi, $_POST['poh']);
    $keterangan_pelanggaran = mysqli_real_escape_string($koneksi, $_POST['keterangan_pelanggaran']);
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    // otomatis isi tanggal resign jika status RESIGN
   $tanggal_resign = NULL;

    if ($status_bekerja == "RESIGN" && !empty($_POST['tanggal_resign'])) {
        $tanggal_resign = $_POST['tanggal_resign'];
    }

    $foto = uploadFoto($_FILES['foto'], $folder);

    mysqli_query($koneksi,"INSERT INTO manpower
    (nama, nik, jabatan, jabatan_tambahan, devisi,
     status_bekerja, status_kerja, tanggal_masuk, tanggal_resign,
     poh, keterangan_pelanggaran, keterangan, foto)
    VALUES
    ('$nama','$nik','$jabatan','$jabatan_tambahan','$devisi',
     '$status_bekerja','$status_kerja','$tanggal_masuk',
     ".($tanggal_resign ? "'$tanggal_resign'" : "NULL").",
     '$poh','$keterangan_pelanggaran','$keterangan','$foto')");

    header("location:index.php?page=manpower");
    exit;
}


/* ============================================================
   UPDATE
============================================================ */
if ($page == "manpower" && $proses == "update") {

    $id = intval($_POST['id']);

    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $nik      = mysqli_real_escape_string($koneksi, $_POST['nik']);
    $jabatan  = mysqli_real_escape_string($koneksi, $_POST['jabatan']);
    $jabatan_tambahan = mysqli_real_escape_string($koneksi, $_POST['jabatan_tambahan']);
    $devisi   = mysqli_real_escape_string($koneksi, $_POST['devisi']);
    $status_bekerja = $_POST['status_bekerja'];
    $status_kerja   = $_POST['status_kerja'];
    $tanggal_masuk  = !empty($_POST['tanggal_masuk']) ? $_POST['tanggal_masuk'] : NULL;
    $tanggal_resign = !empty($_POST['tanggal_resign']) ? $_POST['tanggal_resign'] : NULL;
    $tanggal_mutasi = !empty($_POST['tanggal_mutasi']) ? $_POST['tanggal_mutasi'] : NULL;
    $poh      = mysqli_real_escape_string($koneksi, $_POST['poh']);
    $keterangan_pelanggaran = mysqli_real_escape_string($koneksi, $_POST['keterangan_pelanggaran']);
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    // Ambil foto lama
    $ambil = mysqli_query($koneksi,"SELECT foto FROM manpower WHERE id_manpower='$id'");
    $data  = mysqli_fetch_assoc($ambil);
    $foto_lama = $data['foto'] ?? '';

    $foto = uploadFoto($_FILES['foto'], $folder, $foto_lama);

    /* ==========================
       LOGIKA STATUS
    =========================== */

    if ($status_bekerja == "AKTIF") {
        $tanggal_resign = NULL;
        $tanggal_mutasi = NULL;
    }

    if ($status_bekerja == "RESIGN") {
        $tanggal_mutasi = NULL;
    }

    if ($status_bekerja == "MUTASI") {
        $tanggal_resign = NULL;
    }

    mysqli_query($koneksi,"UPDATE manpower SET
        nama='$nama',
        nik='$nik',
        jabatan='$jabatan',
        jabatan_tambahan='$jabatan_tambahan',
        devisi='$devisi',
        status_bekerja='$status_bekerja',
        status_kerja='$status_kerja',
        tanggal_masuk=".($tanggal_masuk ? "'$tanggal_masuk'" : "NULL").",
        tanggal_resign=".($tanggal_resign ? "'$tanggal_resign'" : "NULL").",
        tanggal_mutasi=".($tanggal_mutasi ? "'$tanggal_mutasi'" : "NULL").",
        poh='$poh',
        keterangan_pelanggaran='$keterangan_pelanggaran',
        keterangan='$keterangan',
        foto='$foto'
        WHERE id_manpower='$id'");

    header("location:index.php?page=manpower");
    exit;
}
/* ============================================================
   HAPUS
============================================================ */
if ($page == "manpower" && $proses == "hapus") {

    $id = intval($_GET['id']);

    $ambil = mysqli_query($koneksi,"SELECT foto FROM manpower WHERE id_manpower='$id'");
    $data  = mysqli_fetch_assoc($ambil);

    if (!empty($data['foto']) && file_exists($folder.$data['foto'])) {
        unlink($folder.$data['foto']);
    }

    mysqli_query($koneksi,"DELETE FROM manpower WHERE id_manpower='$id'");

    header("location:index.php?page=manpower");
    exit;
}
?>