<?php
// ================= SESSION & KONEKSI =================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "koneksi.php";

// validasi login
if (!isset($_SESSION['iduser']) || !isset($_SESSION['level'])) {
    die("Akses ditolak");
}

$id_login    = intval($_SESSION['iduser']);
$level_login = $_SESSION['level'];

$page   = isset($_GET['page']) ? $_GET['page'] : '';
$proses = isset($_GET['proses']) ? $_GET['proses'] : '';

/* ============================================================
   HAPUS USER (ADMIN ONLY)
   ============================================================ */
if ($page === 'user' && $proses === 'hapus') {

    if ($level_login !== 'Administrator') {
        die("Akses ditolak");
    }

    $id = intval($_GET['id']);
    mysqli_query($koneksi, "DELETE FROM user WHERE id='$id'");

    header("Location: index.php?page=user");
    exit;
}


/* ============================================================
   INPUT USER (ADMIN ONLY)
   ============================================================ */
elseif ($page === 'user' && $proses === 'input') {

    if ($level_login !== 'Administrator') {
        die("Akses ditolak");
    }

    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $user     = mysqli_real_escape_string($koneksi, $_POST['user']);
    $password = md5($_POST['pass']);
    $level    = $_POST['level'];
    $plan     = isset($_POST['plan']) ? $_POST['plan'] : '';

    mysqli_query($koneksi, "INSERT INTO user (user, username, password, level, plan)
                            VALUES ('$user','$username','$password','$level','$plan')");

    header("Location: index.php?page=user");
    exit;
}


/* ============================================================
   UPDATE USER (ADMIN / USER SENDIRI)
   ============================================================ */
elseif ($page === 'user' && $proses === 'update') {

    $id   = intval($_POST['id']);
    $user = mysqli_real_escape_string($koneksi, $_POST['user']);

    // Ambil data user yang diedit
    $cek = mysqli_query($koneksi, "SELECT * FROM user WHERE id='$id'");
    $r   = mysqli_fetch_assoc($cek);

    if (!$r) {
        die("Data tidak ditemukan");
    }

    // Operator hanya boleh update akun sendiri
    if ($level_login !== 'Administrator' && $id_login !== (int)$r['id']) {
        die("Akses ditolak");
    }

    // Admin boleh update level & plan
    if ($level_login === 'Administrator') {
        $level = $_POST['level'];
        $plan  = $_POST['plan'];
    } else {
        // Operator tidak boleh ubah level & plan
        $level = $r['level'];
        $plan  = $r['plan'];
    }

    // Update dengan / tanpa password
    if (!empty($_POST['pass'])) {
        $password = md5($_POST['pass']);

        mysqli_query($koneksi, "UPDATE user SET 
                                user='$user',
                                password='$password',
                                level='$level',
                                plan='$plan'
                                WHERE id='$id'");
    } else {
        mysqli_query($koneksi, "UPDATE user SET 
                                user='$user',
                                level='$level',
                                plan='$plan'
                                WHERE id='$id'");
    }

    header("Location: index.php?page=user");
    exit;
}

else {
    die("Aksi tidak valid");
}
?>
