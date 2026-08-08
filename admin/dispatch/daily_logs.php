<div class="d-flex left-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <a href="index.php">Home</a> / Daily Dispatch
</div>

<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include '../koneksi.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ===========================
// USER LOGIN
// ===========================
$iduser = $_SESSION['iduser'] ?? null;
$level  = $_SESSION['level'] ?? '';

if(!$iduser){
    die("User belum login");
}

// ===========================
// AMBIL DATA DAILY LOG
// ===========================
// Jika level admin, tampilkan semua dataL
// Jika user biasa, tampilkan hanya data yang dia buat
$where = ($level === 'Administrator') ? "1=1" : "d.created_by = '$iduser'";

$sql = "
    SELECT 
        d.log_date,
        d.shift,
        d.created_by,
        u.username,
        COUNT(DISTINCT d.unit_id) AS unit_count,
        MAX(d.created_at) AS last_input
    FROM dispatch_daily_log d
    LEFT JOIN user u ON d.created_by = u.id
    WHERE $where
    GROUP BY d.log_date, d.shift, d.created_by
    ORDER BY d.log_date DESC, d.shift ASC

";

$res = mysqli_query($koneksi, $sql);
?>

<a href="?page=laporandispatch" class="btn btn-primary mb-2">
  + Tambah Laporan
</a>


<style>
.table td,
.table th {
  color:#000 !important;
}
</style>

<h4>Daftar Laporan Dispatcher</h4>

<table class="table table-bordered table-striped">
    <thead style="background:#f1f1f1;">
        <tr>
            <th>Tanggal</th>
            <th>Shift</th>
            <th>Dibuat Oleh</th>
            <th>Jumlah Unit</th>
            <th>Terakhir Input</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
    <?php while($row = mysqli_fetch_assoc($res)): 
        $shift_label = ($row['shift'] == 'day') ? 'Siang' : 'Malam';
        $shift_color = ($row['shift'] == 'day') ? '#d4edda' : '#cce5ff';
    ?>
        <tr>
            <td><?= $row['log_date']; ?></td>
            <td style="background:<?= $shift_color ?>; font-weight:bold;"><?= $shift_label; ?></td>
            <td><?= htmlspecialchars($row['username']); ?></td>
            <td><?= $row['unit_count']; ?></td>
            <td><?= $row['last_input']; ?></td>
       <td>
    <a href="index.php?page=dispatch_preview&log_date=<?= $row['log_date']; ?>&shift=<?= $row['shift']; ?>" target="_blank" class="btn btn-sm btn-info">Preview</a>
    <a href="index.php?page=dispatch_breakdown_list&log_date=<?= $row['log_date']; ?>&shift=<?= $row['shift']; ?>" class="btn btn-sm btn-dark">+ Breakdown</a>
    <a href="index.php?page=dispatch_edit_daily&log_date=<?= $row['log_date']; ?>&shift=<?= $row['shift']; ?>" class="btn btn-sm btn-warning">Edit</a>
    <a href="index.php?page=dispatch_hapus_daily&log_date=<?= $row['log_date']; ?>&shift=<?= $row['shift']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin hapus data?')">Hapus</a>
</td>


        </tr>
    <?php endwhile; ?>
    </tbody>
</table>
