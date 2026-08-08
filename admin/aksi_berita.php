<?php
date_default_timezone_set('Asia/Jayapura');
session_start();
include "koneksi.php";

$page   = isset($_GET['page']) ? $_GET['page'] : "";
$proses = isset($_GET['proses']) ? $_GET['proses'] : "";

/* ============================================================
   FUNGSI UPLOAD GAMBAR
   ============================================================ */
function upload_single($file)
{
    $namaFile = time() . "_" . basename($file['name']);
    $target = "foto_berita/" . $namaFile;

    if ($file['name'] != "") {
        move_uploaded_file($file['tmp_name'], $target);
        return $namaFile;
    }
    return "";
}

/* ============================================================
   INPUT BERITA
   ============================================================ */
if ($page == "berita" && $proses == "input") {

    // === DATA UTAMA ===
    $isi        = mysqli_real_escape_string($koneksi, $_POST['berita']);
    $kategori   = intval($_POST['kategori']);
    $plan       = mysqli_real_escape_string($koneksi, $_POST['plan']);
    $id_user    = intval($_POST['id_user']);

    // === INPUT BARU (SHIFT & TANGGAL) ===
    $shift       = mysqli_real_escape_string($koneksi, $_POST['shift']);
    $tgl_laporan = $_POST['tgl_laporan']; // YYYY-MM-DD

    // === BENTUK JUDUL OTOMATIS ===
    $tgl_format = date('d/m/Y', strtotime($tgl_laporan));
    $judul      = "BA Overshift " . ucfirst($plan) . " Shift " . $shift . " " . $tgl_format;

    // waktu posting
    $tanggal = date("Y-m-d H:i:s");

    // Upload gambar utama
    $gambar = upload_single($_FILES['file_image']);

    // Simpan ke tabel berita
    $q = "
        INSERT INTO berita 
        (id_kategori, id_user, judul, isi_berita, gambar, tgl_posting, plan)
        VALUES 
        ('$kategori', '$id_user', '$judul', '$isi', '$gambar', '$tanggal', '$plan')
    ";

    mysqli_query($koneksi, $q);

    // Ambil ID berita terakhir
    $last_id = mysqli_insert_id($koneksi);

    /* ======== Upload banyak foto (foto_lain[]) ======== */
    if (!empty($_FILES['foto_lain']['name'][0])) {

        foreach ($_FILES['foto_lain']['name'] as $i => $nm) {

            if ($nm == '') continue;

            $namaFile = time() . "_multi_" . basename($nm);
            $target = "foto_berita/" . $namaFile;

            move_uploaded_file($_FILES['foto_lain']['tmp_name'][$i], $target);

            mysqli_query($koneksi,
                "INSERT INTO berita_foto (id_berita, foto)
                 VALUES ('$last_id', '$namaFile')"
            );
        }
    }

    header("location:index.php?page=berita");
    exit;
}

/* ============================================================
   UPDATE BERITA (TIDAK DIUBAH LOGIKANYA)
   ============================================================ */
if ($page == "berita" && $proses == "update") {

    $id        = intval($_POST['id']);
    $judul     = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $isi       = mysqli_real_escape_string($koneksi, $_POST['berita']);
    $kategori  = intval($_POST['kategori']);
    $plan      = mysqli_real_escape_string($koneksi, $_POST['plan']);

    // cek gambar utama baru
    if ($_FILES['file_image']['name'] != "") {
        $gambar = upload_single($_FILES['file_image']);
        $set_gambar = ", gambar='$gambar' ";
    } else {
        $set_gambar = "";
    }

    mysqli_query($koneksi,"
        UPDATE berita SET
            id_kategori = '$kategori',
            judul       = '$judul',
            isi_berita  = '$isi',
            plan        = '$plan'
            $set_gambar
        WHERE id_berita = '$id'
    ");

    /* ========= UPLOAD FOTO TAMBAHAN BARU ========= */
    if (!empty($_FILES['foto_lain']['name'][0])) {

        foreach ($_FILES['foto_lain']['name'] as $i => $nm) {

            if ($nm == '') continue;

            $namaFile = time() . "_multi_" . basename($nm);
            $target   = "foto_berita/" . $namaFile;

            move_uploaded_file($_FILES['foto_lain']['tmp_name'][$i], $target);

            mysqli_query($koneksi,"
                INSERT INTO berita_foto (id_berita, foto)
                VALUES ('$id', '$namaFile')
            ");
        }
    }

    header("location:index.php?page=berita");
    exit;
}


/* ============================================================
   HAPUS BERITA (HARD DELETE - REAL DELETE)
   ============================================================ */
if ($page == "berita" && $proses == "hapus") {

    $id = intval($_GET['id']);

    // ===============================
    // 1. HAPUS FOTO TAMBAHAN (FILE)
    // ===============================
    $qFoto = mysqli_query($koneksi, "
        SELECT foto 
        FROM berita_foto 
        WHERE id_berita = '$id'
    ");

    while ($f = mysqli_fetch_assoc($qFoto)) {
        $path = "foto_berita/" . $f['foto'];
        if (file_exists($path)) {
            unlink($path);
        }
    }

    // ===============================
    // 2. HAPUS DATA FOTO TAMBAHAN
    // ===============================
    mysqli_query($koneksi, "
        DELETE FROM berita_foto 
        WHERE id_berita = '$id'
    ");

    // ===============================
    // 3. HAPUS GAMBAR UTAMA (FILE)
    // ===============================
    $q = mysqli_query($koneksi, "
        SELECT gambar 
        FROM berita 
        WHERE id_berita = '$id'
    ");

    if ($g = mysqli_fetch_assoc($q)) {
        if (!empty($g['gambar'])) {
            $path = "foto_berita/" . $g['gambar'];
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    // ===============================
    // 4. HAPUS DATA BERITA (HARD)
    // ===============================
    mysqli_query($koneksi, "
        DELETE FROM berita 
        WHERE id_berita = '$id'
    ");

    header("Location: index.php?page=berita");
    exit;
}


/* ============================================================
   HAPUS FOTO TAMBAHAN SAAT EDIT
   ============================================================ */
if ($page == "berita" && $proses == "hapus_foto") {

    $id_foto   = intval($_GET['id']);
    $id_berita = intval($_GET['id_berita']);

    $q = mysqli_fetch_assoc(mysqli_query(
        $koneksi, 
        "SELECT foto FROM berita_foto WHERE id_foto='$id_foto'"
    ));

    if ($q && file_exists("foto_berita/" . $q['foto'])) {
        unlink("foto_berita/" . $q['foto']);
    }

    mysqli_query($koneksi, "DELETE FROM berita_foto WHERE id_foto='$id_foto'");

    header("location:index.php?page=berita&aksi=edit&id=$id_berita");
    exit;
}

/* ===============================
   APPROVE BERITA
   =============================== */
if ($page == "berita" && $proses == "approve") {

    session_start();
    include "../koneksi.php";

    $id      = intval($_GET['id']);
    $iduser  = intval($_SESSION['iduser']);

    mysqli_query($koneksi, "
        UPDATE berita SET
            status = 'approved',
            approved_by = '$iduser',
            approved_at = NOW()
        WHERE id_berita = '$id'
          AND status = 'pending'
    ");

    header("Location: ../admin/index.php?page=berita&aksi=list");
    exit;
}

?>
