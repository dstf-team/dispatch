<style>
/* =========================
   WARNA TEKS – JANGAN GREY
========================= */
.table th,
.table td,
.form-control,
.form-control option {
    color: #000 !important;
}

/* =========================
   HEADER TABEL
========================= */
.table thead th {
    background-color: #f8f9fa;
    font-weight: 600;
    vertical-align: middle;
    padding: 6px 8px;
}

/* =========================
   TINGGI BARIS LEBIH RINGKAS
========================= */
.table td {
    padding: 4px 6px !important;
    vertical-align: middle;
}

/* =========================
   INPUT & SELECT LEBIH ELEGAN
========================= */
.form-control {
    height: 30px !important;
    padding: 3px 6px !important;
    font-size: 13px;
    border-radius: 4px;
}

/* TIME INPUT KHUSUS */
input[type="time"].form-control {
    padding: 2px 4px !important;
}

/* =========================
   BUTTON KECIL TAPI TEGAS
========================= */
.btn-sm {
    padding: 3px 8px;
    font-size: 13px;
}

/* =========================
   HOVER BARIS (ELEGAN)
========================= */
.table tbody tr:hover {
    background-color: #f3f6f9;
}

/* ===== TOP COMMAND BAR ===== */
.command-bar{
    position:fixed;
    top:14px;
    left:50%;
    transform:translateX(-50%);
    z-index:9999;
    background:linear-gradient(135deg,#1e90ff,#1565c0);
    border-radius:16px;
    box-shadow:0 10px 28px rgba(0,0,0,.45);
    padding:12px 20px;
    display:flex;
    align-items:center;
    gap:20px;
    font-family:"Segoe UI", Arial, sans-serif;
}

/* ===== FULLSCREEN BUTTON ===== */
.cmd-btn{
    background:linear-gradient(135deg,#ffffff,#e3f2fd);
    color:#000;
    border:none;
    padding:8px 18px;
    border-radius:12px;
    cursor:pointer;
    font-size:13px;
    font-weight:700;
    letter-spacing:.5px;
    box-shadow:0 4px 10px rgba(0,0,0,.25);
    transition:all .25s ease;
}
.cmd-btn:hover{
    transform:translateY(-1px) scale(1.05);
    box-shadow:0 6px 16px rgba(0,0,0,.35);
}
.cmd-btn:active{
    transform:scale(.96);
}

/* ===== CLOCK ===== */
.cmd-clock{
    display:flex;
    align-items:center;
    gap:10px;
    background:#e3f2fd;
    padding:6px 14px;
    border-radius:12px;
    box-shadow:inset 0 0 0 1px rgba(0,0,0,.1);
}

.cmd-clock .zone{
    font-size:12px;
    font-weight:700;
    color:#0d47a1;
}

.cmd-clock .time{
    font-size:22px;
    font-weight:800;
    letter-spacing:1.5px;
    color:#000; /* 🔥 ANGKA JAM HITAM */
}

.cmd-clock .ampm{
    font-size:12px;
    font-weight:700;
    padding:3px 8px;
    border-radius:8px;
    background:#0d47a1;
    color:#fff;
}



</style>


<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../koneksi.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$log_date = $_GET['log_date'] ?? '';
$shift    = $_GET['shift'] ?? '';

if (!$log_date || !$shift) {
    die("Tanggal / Shift tidak valid");
}

/* =====================
   Ambil unit yang sudah input di daily log
===================== */
$qUnit = mysqli_query($koneksi, "
    SELECT
        d.id AS daily_log_id,
        d.unit_id,
        u.unit_code,
        u.category
    FROM dispatch_daily_log d
    JOIN dispatch_unit_master u ON d.unit_id = u.id
    WHERE d.log_date = '$log_date'
      AND d.shift = '$shift'
    ORDER BY u.category, u.unit_code
");

if (!$qUnit) {
    die(mysqli_error($koneksi));
}

/* =====================
   Ambil specific trouble
===================== */
$kamus = mysqli_query($koneksi,"
    SELECT specific_name
    FROM dispatch_specific_dictionary
    WHERE is_active = 1
    ORDER BY specific_name
");

if(!$kamus){
    die('Query specific trouble error: '.mysqli_error($koneksi));
}

/* =====================
   Ambil master lokasi
===================== */
$qLokasi = mysqli_query($koneksi,"
    SELECT id_lokasi, kode_lokasi, nama_lokasi
    FROM lokasi
    ORDER BY kode_lokasi
");

if(!$qLokasi){
    die('Query lokasi error: '.mysqli_error($koneksi));
}
?>

<a href="index.php?page=dispatch_daily_log" class="btn btn-secondary mb-3">
← Kembali
</a>

<div class="command-bar">


    <div id="clock-wit" class="cmd-clock">
        <span class="zone">WIT</span>
        <span class="time">--:--:--</span>
        <span class="ampm">--</span>
    </div>

</div>





<h4>
Input Breakdown  
<br>
<small>Tanggal: <?= $log_date ?> | Shift: <?= strtoupper($shift) ?></small>
</h4>

<form method="POST" action="dispatch/aksi_breakdown.php">

<table class="table table-bordered">
<thead class="table-light">
<tr align="center">
    <th>Unit</th>
    <th>BD Start</th>
    <th>BD End</th>
    <th>Trouble</th>
    <th>Specific Trouble</th>
    <th>Lokasi Breakdown</th>
    <th>Aksi</th>
</tr>
</thead>

<tbody id="bdTable">
<tr>
    <td>
        <select name="daily_log_id[]" class="form-control" required>
            <option value="">-- Pilih Unit --</option>
            <?php while($u=mysqli_fetch_assoc($qUnit)): ?>
                <option value="<?= $u['daily_log_id']; ?>"
                        data-unit="<?= $u['unit_id']; ?>">
                    <?= $u['unit_code']; ?> (<?= $u['category']; ?>)
                </option>
            <?php endwhile; ?>
        </select>
        <input type="hidden" name="unit_id[]" class="unit_id">
    </td>

    <td>
        <input type="time" name="bd_start[]" class="form-control" required>
    </td>

    <td>
        <input type="time" name="bd_end[]" class="form-control" required>
    </td>

    <td>
        <input type="text" name="trouble_desc[]" class="form-control">
    </td>

    <td>
        <select name="specific_trouble[]" class="form-control" required>
            <option value="">-- Pilih --</option>
            <?php
            mysqli_data_seek($kamus,0);
            while($k=mysqli_fetch_assoc($kamus)){
                echo "<option value='{$k['specific_name']}'>{$k['specific_name']}</option>";
            }
            ?>
        </select>
    </td>

 <td>
    <select name="location[]" class="form-control" required>
        <option value="">-- Pilih Lokasi --</option>
        <?php
        mysqli_data_seek($qLokasi,0);
        while($l=mysqli_fetch_assoc($qLokasi)){
            echo "<option value='{$l['kode_lokasi']}'>
                    {$l['kode_lokasi']} - {$l['nama_lokasi']}
                  </option>";
        }
        ?>
    </select>
</td>


    <td align="center">
        <button type="button" class="btn btn-success btn-sm" onclick="addRow()">+</button>
    </td>
</tr>
</tbody>
</table>

<button type="submit" class="btn btn-primary">
Simpan Semua Breakdown
</button>

</form>



<script>
function addRow(){

    const table = document.getElementById('bdTable');
    const row   = table.rows[0]; // row pertama sebagai template
    const clone = row.cloneNode(true);

    // reset value input & select
    clone.querySelectorAll('input, select').forEach(el=>{
        if(el.type === 'time' || el.type === 'text'){
            el.value = '';
        }
        if(el.tagName === 'SELECT'){
            el.selectedIndex = 0;
        }
    });

    table.appendChild(clone);
}
</script>

<script>
// isi otomatis unit_id berdasarkan unit yang dipilih
document.addEventListener('change', function(e){
    if(e.target.name === 'daily_log_id[]'){
        const selected = e.target.options[e.target.selectedIndex];
        const unitId   = selected.getAttribute('data-unit');

        // cari input hidden unit_id di baris yang sama
        const row = e.target.closest('tr');
        row.querySelector('.unit_id').value = unitId || '';
    }
});
</script>


<script>
function updateClockWIT(){
    const now = new Date();

    // UTC → WIT (UTC +9)
    const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
    const wit = new Date(utc + 9 * 3600000);

    let h = wit.getHours();
    let m = wit.getMinutes();
    let s = wit.getSeconds();

    let ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;

    document.querySelector('#clock-wit .time').innerText =
        `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;

    document.querySelector('#clock-wit .ampm').innerText = ampm;
}

setInterval(updateClockWIT,1000);
updateClockWIT();
</script>

