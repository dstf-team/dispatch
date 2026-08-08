

<?php
session_start();
include "koneksi.php"; // sesuaikan path dengan lokasi file

$iduser = isset($_SESSION['iduser']) ? intval($_SESSION['iduser']) : 0;

if($iduser){

    // Set offline
    mysqli_query($koneksi, "
        UPDATE user 
        SET 
            is_online = 0,
            last_activity = NULL
        WHERE id = $iduser
    ");
}

// Bersihkan session
session_unset();
session_destroy();

// Hapus cookie PHPSESSID kalau ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
?>

<script>
  // RESET POPUP LOGIN
  localStorage.removeItem('infoLoginShown');

  // REDIRECT KE LOGIN
  window.location.href = '../index.php';
</script>
