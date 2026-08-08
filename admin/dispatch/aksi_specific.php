<?php
include '../koneksi.php';

$proses = $_GET['proses'] ?? '';

switch ($proses) {

    // ================= INPUT =================
    case 'input':
        $name   = mysqli_real_escape_string($koneksi, $_POST['specific_name']);
        $active = $_POST['is_active'];

        mysqli_query($koneksi,"
            INSERT INTO dispatch_specific_dictionary (specific_name, is_active)
            VALUES ('$name', '$active')
        ");

        header("Location: ../index.php?page=dictionary_breakdown");
        exit;
    break;


    // ================= UPDATE =================
    case 'update':
        $id     = $_POST['id'];
        $name   = mysqli_real_escape_string($koneksi, $_POST['specific_name']);
        $active = $_POST['is_active'];

        mysqli_query($koneksi,"
            UPDATE dispatch_specific_dictionary
            SET specific_name='$name', is_active='$active'
            WHERE id='$id'
        ");

        header("Location: ../index.php?page=dictionary_breakdown");
        exit;
    break;


    // ================= HAPUS =================
    case 'hapus':
        $id = $_GET['id'];

        mysqli_query($koneksi,"
            DELETE FROM dispatch_specific_dictionary
            WHERE id='$id'
        ");

        header("Location: ../index.php?page=dictionary_breakdown");
        exit;
    break;


    default:
        header("Location: ../index.php");
        exit;
    break;
}
