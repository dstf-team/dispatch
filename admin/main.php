<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['iduser'])) {
    header("Location: login.php");
    exit;
}

// ambil session
$page  = isset($_GET['page']) ? $_GET['page'] : 'home';
$level = strtolower($_SESSION['level']);
$plan  = isset($_SESSION['plan']) ? strtolower($_SESSION['plan']) : '';


// ================= BATASI AKSES =================

// halaman khusus ADMIN aaJA
$admin_only = ['kategori'];

// halaman khusus DISPATCH
$dispatch_only = [
    'dispatch_unit_master',
    'dispatch_daily_log',
    'bulk_status'
];


// ---- ADMIN PAGE ----
if (in_array($page, $admin_only)) {

    if ($level != 'administrator') {
        echo "<h3 style='color:red; text-align:center; margin-top:50px;'>
                ⚠️ Akses ditolak! Halaman ini hanya untuk Administrator.
              </h3>";
        exit;
    }
}


// ---- DISPATCH ONLY ----

if (in_array($page, $dispatch_only)) {

    $boleh_dispatch = (
        $level == 'administrator' ||
        ($level == 'operator' && $plan == 'dispatcher')
    );

    if (!$boleh_dispatch) {
        echo "<h3 style='color:red; text-align:center; margin-top:50px;'>
                ⚠️ Akses ditolak! Halaman ini hanya untuk 
                Administrator &  Dispatcher.
              </h3>";
        exit;
    }
}

?>

<?php
// ================= LOADER HALAMAN =================
switch ($page) {
    case 'home':
        include 'home.php';
        break;

    case 'kategori':
        include 'kategori.php';
        break;

    case 'user':
        include 'user.php';
        break;

    case 'manpower':
        include "manpower.php";
         break;

   case 'berita':
    // sembunyikan berita jika plan dispatcher
    if (!($level == 'operator' && $plan == 'Dispatcher')) {
        include 'berita.php';
    } else {
        include 'home.php'; // atau kosongkan jika mau
    }
    break;

    // ====== DISPATCH ======
    case 'dispatch_unit_master':
        include 'dispatch/unit_master.php';
        break;

    case 'dispatch_daily_log':
        include 'dispatch/daily_logs.php';
        break;

    case 'bulk_status':
        include 'dispatch/bulk_status.php';
        break;



    case 'breakdown_unit':
        include 'dispatch/databreakdown.php';
        break;

    case 'dispatch_breakdown_preview':
        include 'dispatch/dispatch_breakdown_preview.php';
    break;

    case 'dispatch_rekap':
        include 'dispatch/rekap_dispatch.php';
        break;
    case 'dispatch_rekap':
        include 'dispatch/detail_unit_breakdown.php';
        break;
    case 'lokasi':
        include 'dispatch/lokasi.php';
        break;

    case 'dictionary_breakdown':
        include 'dispatch/kamus_breakdown.php';
        break;

case 'dictionary_breakdown':
    include 'dispatch/kamus_breakdown.php';
    break;


        // ====== DISPATCH ======
    case 'dispatch_edit_daily':
        include 'dispatch/dispatch_edit_daily.php';
        break;

    case 'dispatch_hapus_daily':
        include 'dispatch/dispatch_hapus_daily.php';
        break;
    case 'dispatch_update_daily':
        include 'dispatch/dispatch_update_daily.php';
        break;
    case 'laporandispatch':
        include 'dispatch/daily_log.php';
        break;
    case 'dispatch_preview':
        include 'dispatch/dispatch_preview.php';
        break;
    case 'dispatch_breakdown_list':
        include 'dispatch/breakdown.php';
        break;
//admin
         case 'statuson':
        include 'online.php';
        break;
//all
    case 'notifikasi':
        include 'notifikasi.php';
        break;

case 'media_upload':
    include 'media/upload.php';
    break;

case 'media_display':
    include 'media/display.php';
    break;

case 'tv_control_panel':
    include 'media/tv_control_panel.php';
    break;

    default:
        include 'home.php';
        break;
}
?>
