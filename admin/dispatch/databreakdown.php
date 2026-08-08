<div class="d-flex left-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <a href="index.php">Home</a> / Breakdown
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
// FILTER USER
// ===========================
$whereUser = ($level === 'Administrator')
    ? "1=1"
    : "b.created_by = '$iduser'";

// ===========================
// QUERY RINGKASAN BREAKDOWN
// ===========================
$sql = "
SELECT
    d.log_date,
    d.shift,
    u.username,

    COUNT(DISTINCT b.unit_id) AS unit_count,
    MAX(b.created_at) AS last_input

FROM dispatch_breakdown_log b
JOIN dispatch_daily_log d 
    ON b.daily_log_id = d.id
LEFT JOIN user u 
    ON b.created_by = u.id

WHERE $whereUser
GROUP BY d.log_date, d.shift, b.created_by
ORDER BY d.log_date DESC, d.shift ASC
";

$res = mysqli_query($koneksi, $sql);
?>

<style>
.table td,
.table th {
    color:#000 !important;
}
</style>

<h4>Daftar Laporan Breakdown Dispatcher</h4>

<table class="table table-bordered table-striped">
    <thead style="background:#f1f1f1;">
        <tr>
            <th>Tanggal</th>
            <th>Shift</th>
            <th>Dibuat Oleh</th>
            <th>Jumlah Unit Breakdown</th>
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

            <td style="background:<?= $shift_color ?>; font-weight:bold;">
                <?= $shift_label; ?>
            </td>

            <td><?= htmlspecialchars($row['username']); ?></td>

            <td style="text-align:center; font-weight:bold;">
                <?= $row['unit_count']; ?>
            </td>

            <td><?= $row['last_input']; ?></td>

         <td>
    <a href="index.php?page=dispatch_breakdown_preview&log_date=<?= urlencode($row['log_date']); ?>&shift=<?= urlencode($row['shift']); ?>"
       target="_blank"
       class="btn btn-sm btn-info">
        Preview
    </a>

    <a href="index.php?page=dispatch_breakdown_edit&log_date=<?= urlencode($row['log_date']); ?>&shift=<?= urlencode($row['shift']); ?>"
       class="btn btn-sm btn-warning">
        Edit
    </a>

    <a href="index.php?page=dispatch_breakdown_hapus&log_date=<?= urlencode($row['log_date']); ?>&shift=<?= urlencode($row['shift']); ?>"
       class="btn btn-sm btn-danger"
       onclick="return confirm('Yakin ingin hapus data breakdown?')">
        Hapus
    </a>
</td>

        </tr>
<?php endwhile; ?>

<?php if(mysqli_num_rows($res) == 0): ?>
        <tr>
            <td colspan="6" class="text-center text-muted">
                Belum ada data breakdown
            </td>
        </tr>
<?php endif; ?>

    </tbody>
</table>
