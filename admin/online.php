<?php
// ================= START SESSION =================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ================= KONEKSI DATABASE =================
include "koneksi.php"; // sesuaikan path koneksi

// ================= AMBIL ID USER SESSION =================
$iduser = isset($_SESSION['iduser']) ? intval($_SESSION['iduser']) : 0;

// ================= UPDATE LAST ACTIVITY =================
if($iduser){
    mysqli_query($koneksi, "
        UPDATE user 
        SET last_activity = NOW(), status_online = 1
        WHERE id = $iduser
    ");
}

// ================= CEK ONLINE FUNCTION =================
function isOnline($last){
    if(!$last) return false;
    $now  = strtotime(date("Y-m-d H:i:s"));
    $time = strtotime($last);
    return ($now - $time <= 300); // 5 menit
}

// ================= AMBIL DATA USER =================
$data = mysqli_query($koneksi, "
    SELECT id, username, last_activity, is_online
    FROM user
    ORDER BY username ASC
");

if(!$data){
    die("Query error: ".mysqli_error($koneksi));
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Daftar User Online</title>
    <meta http-equiv="refresh" content="10"> <!-- auto refresh tiap 10 detik -->
    <style>
        body{font-family:Arial;}
        .online{color:green;font-weight:bold;}
        .offline{color:red;font-weight:bold;}
        table{border-collapse:collapse;}
        td,th{padding:8px;border:1px solid #999;}
    </style>
</head>
<body>
<div class="d-flex left-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom"> 
    <a href="index.php">Home</a> / User Online
</div>
<h3>List User</h3>

<table class="table table-bordered table-striped">
<tr align="center">
    <th>Nama</th>
    <th>Status</th>
    <th>Last Activity</th>
</tr>

<?php while($r = mysqli_fetch_assoc($data)){ ?>
<tr>
    <td><?= htmlspecialchars($r['username']); ?></td>
    <td align="center">
        <?php 
        if($r['is_online']==1 && isOnline($r['last_activity'])){
            echo '<span class="online">ONLINE</span>';
        } else {
            echo '<span class="offline">OFFLINE</span>';
        }
        ?>
    </td>
    <td align="center"><?= $r['last_activity'] ?? '-'; ?></td>
</tr>
<?php } ?>

</table>

</body>
</html>
