<?php
include "koneksi.php";
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ====== SET TIMEOUT (10 menit) ======
$timeout = 12 * 60 * 60; // 12Kam

// ====== CEK LOGIN ======
if(!isset($_SESSION['iduser'])){

    echo "
    <script>
        window.location='login.php';
    </script>
    ";

    exit;
}
// ====== CEK TIMEOUT ======
if(isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout){

    // (opsional) update status online ke DB di sini
    include "koneksi.php";
    mysqli_query($koneksi,"UPDATE user SET status_online = 0 WHERE iduser=".$_SESSION['iduser']);

    session_unset();
    session_destroy();

    echo "
<script>
    window.location='login.php?exp=1';
</script>
";

exit;
}

// ====== UPDATE WAKTU AKTIVITAS ======
$_SESSION['last_activity'] = time();


// ====== VARIABEL LEVEL ======
$level = isset($_SESSION['level']) ? strtolower($_SESSION['level']) : '';
$plan  = isset($_SESSION['plan']) ? strtolower($_SESSION['plan']) : '';

$is_dispatcher_operator = ($level === 'operator' && $plan === 'dispatcher');
?>


<?php
$iduser = $_SESSION['iduser'];

$sql = "
SELECT COUNT(*) AS jml
FROM komentar k
INNER JOIN berita b ON k.id_berita = b.id_berita
WHERE b.id_user = '$iduser'
AND (k.status = 0 OR k.status IS NULL)
";

$q = mysqli_query($koneksi, $sql);

if(!$q){
    die('Query Error: '.mysqli_error($koneksi));
}

$d = mysqli_fetch_assoc($q);
$jml_notif = $d['jml'];


?>


<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">

  <title>Dashboard</title>

  <!-- Custom fonts for this template-->
  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Custom styles for this template-->
  <link href="css/sb-admin-2.min.css" rel="stylesheet">
  <link href="../font-awesome/css/font-awesome.css" rel="stylesheet">


<link href="css/custom.css" rel="stylesheet">
</head>

<body id="page-top">

  <!-- Page Wrapper -->
  <div id="wrapper">

    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

      <!-- Sidebar - Brand -->
      <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
        <div class="sidebar-brand-icon rotate-n-15">
          <i class="fas fa-fw fa-comments"></i>
        </div>
        <div class="sidebar-brand-text mx-3">DSTF<sup></sup></div>
      </a>

      <!-- Divider -->
      <hr class="sidebar-divider my-0">

      <!-- Nav Item - Dashboard -->
      <li class="nav-item active">
        <a class="nav-link" href="?page=home">
          <i class="fas fa-fw fa-tachometer-alt"></i>
          <span>Home</span></a>
      </li>

 <?php if (!($level === 'operator' && $plan === 'dispatcher')): ?>
      <!-- Divider -->
      <hr class="sidebar-divider">
<div class="sidebar-heading">
    Menu Laporan BA Over Shift
  </div>
      <!-- Nav Item - Charts -->

<li class="nav-item">
  <a class="nav-link" href="index.php?page=berita">
    <i class="fas fa-fw fa-archive"></i>
    <span>Laporan</span>
  </a>
</li>
<?php endif; ?>


      <!-- Nav Item - Tables -->
<?php if ($_SESSION['level'] === 'Administrator') { ?>
<li class="nav-item">
    <a class="nav-link" href="index.php?page=kategori">
        <i class="fas fa-fw fa-bars"></i>
        <span>Kategori</span>
    </a>
</li>
<?php } ?>




<!-- ========== MENU DISPATCH — HANYA UNTUK OPERATOR + PLAN DISPATCHER ========== -->
<?php 
$level = isset($_SESSION['level']) ? strtolower($_SESSION['level']) : '';
$plan  = isset($_SESSION['plan']) ? strtolower($_SESSION['plan']) : '';

$is_dispatcher_operator = ($level === 'operator' && $plan === 'dispatcher');
?>

<!-- ========== MENU DISPATCH — ADMIN & DISPATCHER ========== -->
<?php if (
    $level === 'administrator' ||
    ($level === 'operator' && in_array($plan, [
        'dispatcher',
        'tailing facility planning'
    ]))
): ?>


  <hr class="sidebar-divider">

  <div class="sidebar-heading">
   Menu Lapooran Dispatch Operations
  </div>

  <li class="nav-item">
    <a class="nav-link" href="index.php?page=dispatch_unit_master">
      <i class="fas fa-fw fa-truck"></i>
      <span>Unit Master</span>
    </a>
  </li>

  <li class="nav-item">
    <a class="nav-link" href="index.php?page=dispatch_daily_log">
      <i class="fas fa-fw fa-clipboard-list"></i>
      <span>Daily Unit Log</span>
    </a>
  </li>



   <li class="nav-item">
    <a class="nav-link" href="index.php?page=breakdown_unit">
      <i class="fas fa-fw fa-gear"></i>
      <span>Unit Breakdown</span>
    </a>
  </li>


<?php if (
    $level === 'administrator' ||
    ($level === 'operator' && $plan === 'tailing facility planning')
): ?>
<li class="nav-item">
  <a class="nav-link" href="index.php?page=dispatch_rekap">
    <i class="fas fa-fw fa-clipboard-list"></i>
    <span>Daily Dispatch Rekap</span>
  </a>
</li>
<?php endif; ?>


   <li class="nav-item">
    <a class="nav-link" href="index.php?page=dictionary_breakdown">
      <i class="fas fa-fw fa-book-open"></i>
      <span>dictionary_breakdown</span>
    </a>
  </li>

    <li class="nav-item">
    <a class="nav-link" href="index.php?page=lokasi">
      <i class="fas fa-fw fa-map-marker"></i>
      <span>Lokasi</span>
    </a>
  </li>

<?php endif; ?>

<!-- ========================================================================== -->

 <hr class="sidebar-divider">
<div class="sidebar-heading">
    Menu Kelola Profil
  </div>

<?php if (in_array($_SESSION['level'], ['Administrator','Operator'])) { ?>
<li class="nav-item">
    <a class="nav-link" href="index.php?page=user">
        <i class="fas fa-fw fa-user-circle"></i>
        <span>User</span>
    </a>
</li>
<?php } ?>


<?php if (in_array($_SESSION['level'], ['Administrator'])) { ?>
<li class="nav-item">
    <a class="nav-link" href="index.php?page=manpower">
        <i class="fas fa-fw fa-users"></i>
        <span>Manpower</span>
    </a>
</li>
<?php } ?>


<?php if (in_array($_SESSION['level'], ['Administrator'])) { ?>
<li class="nav-item">
    <a class="nav-link" href="index.php?page=statuson">
        <i class="fas fa-fw fa-user-circle"></i>
        <span>Online</span>
    </a>
</li>
<?php } ?>


<!-- ================= MENU MEDIA ================= -->
<?php if (
    $level === 'administrator' ||
    ($level === 'operator' && $plan === 'dispatcher')
): ?>

<hr class="sidebar-divider">

<div class="sidebar-heading">
    DISPATCH INPUT DATA
</div>

<!-- BULK STATUS UNIT -->
<li class="nav-item">
  <a class="nav-link" href="index.php?page=bulk_status">
    <i class="fas fa-fw fa-clipboard-check"></i>
    <span>Bulk Status Unit</span>
  </a>
</li>



<?php endif; ?>

<!-- ================= MENU MEDIA ================= -->
<?php if (
    $level === 'administrator' ||
    ($level === 'operator')
): ?>

<hr class="sidebar-divider">

<div class="sidebar-heading">
    Media Dokumentasi
</div>


<!-- Upload -->
<li class="nav-item">
  <a class="nav-link" href="index.php?page=media_upload">
    <i class="fas fa-fw fa-tv"></i>
    <span>Display Monitor</span>
  </a>
</li>

<!-- CONTROL PANEL TV -->
<li class="nav-item">
  <a class="nav-link" href="index.php?page=tv_control_panel">
    <i class="fas fa-fw fa-tv"></i>
    <span>TV CONTROL PANEL</span>
  </a>
</li>

<?php endif; ?>




      <!-- Divider -->
      <hr class="sidebar-divider d-none d-md-block">

      <!-- Sidebar Toggler (Sidebar) -->
      <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
      </div>

    </ul>
    <!-- End of Sidebar -->

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

      <!-- Main Content -->
      <div id="content">

        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

          <!-- Sidebar Toggle (Topbar) -->
          <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
            <i class="fa fa-bars"></i>
          </button>

          <!-- Topbar Search -->
   <form action="index.php" method="GET" 
      class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">

    <input type="hidden" name="page" value="berita">
    <input type="hidden" name="aksi" value="list">

    <div class="input-group">
        <input type="text" name="cari" class="form-control bg-light border-0 small" 
               placeholder="Cari user/ judul / tanggal..." aria-label="Search">

        <div class="input-group-append">
            <button class="btn btn-primary" type="submit">
                <i class="fas fa-search fa-sm"></i>
            </button>
        </div>
    </div>
</form>


<ul class="navbar-nav ml-auto">

  <!-- Notifikasi -->
  <li class="nav-item mx-1">
    <a class="nav-link text-primary" href="index.php?page=notifikasi">
      <i class="fas fa-bell fa-fw"></i>

      <?php if($jml_notif > 0){ ?>
        <span class="badge badge-danger badge-counter">
          <?php echo $jml_notif; ?>
        </span>
      <?php } ?>
    </a>
  </li>

  <!-- Divider -->
  <li class="nav-item d-none d-sm-block">
      <span class="topbar-divider"></span>
  </li>

  <!-- Logout -->
  <li class="nav-item mx-1">
    <a href="logout.php" class="nav-link text-primary">
      <i class="fas fa-sign-out-alt fa-fw"></i>
      Logout
    </a>
  </li>

</ul>



        </nav>
        <!-- End of Topbar -->

        <div class="container-fluid">
          <div class="card">
               <main role="main" class="col-lg-12">
                  <?php
                    include 'main.php';
                  ?>
                </main>
          </div>
        </div>
       
        

      </div>
      <!-- End of Main Content -->

    </div>
    <!-- End of Content Wrapper -->

  </div>
  <!-- End of Page Wrapper -->

  <!-- Scroll to Top Button-->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <!-- Bootstrap core JavaScript-->
  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <!-- Core plugin JavaScript-->
  <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

  <!-- Custom scripts for all pages-->
  <script src="js/sb-admin-2.min.js"></script>

  <!-- Page level plugins -->
  <script src="vendor/chart.js/Chart.min.js"></script>

  <!-- Page level custom scripts -->
  <script src="js/demo/chart-area-demo.js"></script>
  <script src="js/demo/chart-pie-demo.js"></script>
  <script src="../js/jquery-3.4.1.min.js"></script>
<script src="../js/bootstrap.min.js"></script>

</body>

</html>
