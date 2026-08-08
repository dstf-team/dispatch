<?php
include '../koneksi.php';

$proses = $_GET['proses'] ?? '';

switch ($proses) {

    // ================= INPUT =================
    case 'input':

        $kode = $_POST['kode_lokasi'];
        $nama = $_POST['nama_lokasi'];
        $ket  = $_POST['keterangan'];

        mysqli_query($koneksi, "
            INSERT INTO lokasi (kode_lokasi, nama_lokasi, keterangan)
            VALUES ('$kode', '$nama', '$ket')
        ");

        header("Location: ../index.php?page=lokasi&aksi=list");
        exit;
    break;


    // ================= UPDATE =================
    case 'update':

        $id   = $_POST['id'];
        $kode = $_POST['kode_lokasi'];
        $nama = $_POST['nama_lokasi'];
        $ket  = $_POST['keterangan'];

        mysqli_query($koneksi, "
            UPDATE lokasi SET
                kode_lokasi = '$kode',
                nama_lokasi = '$nama',
                keterangan  = '$ket'
            WHERE id_lokasi = '$id'
        ");

        header("Location: ../index.php?page=lokasi&aksi=list");
        exit;
    break;


    // ================= HAPUS =================
    case 'hapus':

        $id = $_GET['id'];

        mysqli_query($koneksi, "
            DELETE FROM lokasi WHERE id_lokasi = '$id'
        ");

        header("Location: ../index.php?page=lokasi&aksi=list");
        exit;
    break;


    default:
        echo "Aksi tidak dikenali";
    break;
}
?>
