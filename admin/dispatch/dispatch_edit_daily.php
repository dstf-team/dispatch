

<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include '../koneksi.php';
error_reporting(E_ALL);
ini_set('display_errors',1);

$log_date = $_GET['log_date'] ?? '';
$shift    = $_GET['shift'] ?? '';

if(!$log_date || !$shift){
    die("Tanggal atau shift tidak ada.");
}

// Ambil daftar specific trouble
$kamus_list = [];
$kamus_query = mysqli_query($koneksi,"
    SELECT specific_name
    FROM dispatch_specific_dictionary
    WHERE is_active=1
    ORDER BY specific_name
");
while($k = mysqli_fetch_assoc($kamus_query)){
    $kamus_list[] = $k['specific_name'];
}

// Ambil daftar lokasi dari tabel lokasi
$lokasi_list = [];
$lokasi_query = mysqli_query($koneksi,"
    SELECT kode_lokasi, nama_lokasi
    FROM lokasi
    ORDER BY nama_lokasi
");
while($l = mysqli_fetch_assoc($lokasi_query)){
    $lokasi_list[$l['kode_lokasi']] = $l['nama_lokasi'];
}

// Ambil data daily log
$sql = "
    SELECT d.*, u.unit_code, u.category
    FROM dispatch_daily_log d
    LEFT JOIN dispatch_unit_master u ON d.unit_id = u.id
    WHERE d.log_date='$log_date' AND d.shift='$shift'
    ORDER BY u.category, u.unit_code
";
$res = mysqli_query($koneksi,$sql);

// Kelompokkan per kategori
$kategori_data = [];
while($r = mysqli_fetch_assoc($res)){
    $kategori_data[$r['category']][] = $r;
}
?>
<div class="d-flex left-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <a href="index.php">Home</a> / Edit Daily Dispatch — <?= $log_date ?> (<?= $shift=='day'?'Shift Siang':'Shift Malam' ?>)
</div>

<a href="?page=dispatch_daily_log" class="btn btn-danger mb-2">
  << LIST
</a>

<form method="POST" action="index.php?page=dispatch_update_daily">

<?php foreach($kategori_data as $kategori => $rows): ?>
    <h5><b><?= $kategori ?></b></h5>
    <table class="table table-bordered">
        <tr>
            <th>Unit</th>
            <th>Operator</th>
            <th>Jobdesk</th>
            <th>Location</th>
            <th>Trouble</th>
            <th>Specific</th>
            <th>BD Start</th>
            <th>BD End</th>
        </tr>

        <?php foreach($rows as $r): ?>
        <tr>
            <td><?= $r['unit_code'] ?><input type="hidden" name="unit_id[]" value="<?= $r['unit_id'] ?>"></td>
            <td><input type="text" name="operator_name[]" value="<?= $r['operator_name'] ?>" class="form-control"></td>
            <td><input type="text" name="job_desc[]" value="<?= $r['job_desc'] ?>" class="form-control"></td>
            <td>
                <select name="location[]" class="form-control">
                    <option value="">-- Pilih Lokasi --</option>
                    <?php foreach($lokasi_list as $kode => $nama): ?>
                        <option value="<?= $kode ?>" <?= (trim($r['location']) == trim($kode) ? 'selected' : '') ?>><?= $nama ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td><input type="text" name="trouble_desc[]" value="<?= $r['trouble_desc'] ?>" class="form-control"></td>
           <td>
    <select name="specific_trouble[]" class="form-control specificSelect">
        <option value="">-- Pilih Specific --</option>
        <?php foreach($kamus_list as $k): ?>
           <option value="<?= $k ?>" <?= (strcasecmp(trim($r['specific_trouble']), trim($k)) == 0 ? 'selected' : '') ?>>
    <?= $k ?>
</option>


        <?php endforeach; ?>
    </select>
</td>
            <td><input type="time" name="bd_start[]" value="<?= substr($r['bd_start'],11,5) ?>" class="form-control"></td>
            <td><input type="time" name="bd_end[]" value="<?= substr($r['bd_end'],11,5) ?>" class="form-control"></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endforeach; ?>

<input type="hidden" name="log_date" value="<?= $log_date ?>">
<input type="hidden" name="shift" value="<?= $shift ?>">
<button type="submit" class="btn btn-primary">Simpan Perubahan</button>
</form>

<script>
$(document).ready(function(){
    // Specific trouble Select2
    $('.specificSelect').select2({
        placeholder:"Cari specific trouble...",
        allowClear:true,
        width:'100%'
    });
});
</script>
