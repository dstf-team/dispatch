<style>
.table .form-control{
    color:#000 !important;
}
.table .form-control::placeholder{
    color:#888 !important;
}

/* 🔴 Nomor unit merah kalau ada trouble */
td.unit-bd {
    color:#ff0000;
}

/* select2 biasa */
.select2-container--default .select2-selection--single{
    background:#fff;
    color:#000;
}

/* 🔴 kalau BD aktif di Select2 */
.select2-container--default.bd-active .select2-selection--single{
    background:#ffb3b3 !important;
    border-color:#ff0000 !important;
}

/* teks tetap hitam */
.select2-selection__rendered{
    color:#000 !important;

}
    /* Header tabel hitam background putih, teks hitam */
.table th {
    background-color: #fff !important;
    color: #000 !important;
}

/* Sel tabel teks tetap hitam */
.table tr {
    color: #000 !important;
}


/*minimize kan*/
.kategori-header{
    background:#f5f5f5;
    padding:8px 10px;
    border:1px solid #ddd;
    margin-bottom:0;
}

.toggle-icon{
    font-weight:bold;
}



/* indikator status unit */
.unit-indicator{
    display:inline-block;
    width:10px;
    height:10px;
    border-radius:50%;
    margin-right:6px;
    vertical-align:middle;
}

/* 🟢 READY */
.unit-ready{
    background:#2ecc71;
}

/* 🔴 BREAKDOWN */
.unit-breakdown{
    background:#e74c3c;
}


/* FULLSCREEN */
.fullscreen-btn{
    background:#2196f3;
    color:#000;
    border:none;
    padding:6px 14px;
    border-radius:20px;
    cursor:pointer;
    font-size:14px;
    transition:0.2s;
}

.fullscreen-btn:hover{
    background:#1e88e5;
    transform:scale(1.05);
}


/* ===== TOP CENTER BAR ===== */
.top-center-ui{
    position:fixed;
    top:10px;
    left:50%;
    transform:translateX(-50%);
    z-index:9999;
    display:flex;
    align-items:center;
    gap:12px;
    background:#e3f2fd;      /* biru muda */
    padding:8px 14px;
    border-radius:30px;
    box-shadow:0 4px 10px rgba(0,0,0,0.15);
}

/* JAM */
.clock-btn{
    background:#bbdefb;      /* biru */
    color:#000;              /* text hitam */
    padding:6px 14px;
    border-radius:20px;
    font-size:15px;
    font-weight:bold;
    cursor:default;
}

/* FULLSCREEN */
.fullscreen-btn{
    background:#2196f3;      /* biru solid */
    color:#000;              /* text hitam */
    border:none;
    padding:6px 14px;
    border-radius:20px;
    cursor:pointer;
    font-size:14px;
    transition:0.2s;
}

.fullscreen-btn:hover{
    background:#1e88e5;
    transform:scale(1.05);
}



</style>


<?php
include '../koneksi.php';

/* ------------------------
   AMBIL DATA KAMUS SPECIFIC
-------------------------*/
$kamus_list = [];
$kamus_query = mysqli_query($koneksi,"
  SELECT specific_name
  FROM dispatch_specific_dictionary
  WHERE is_active = 1
  ORDER BY specific_name
");
while($k = mysqli_fetch_assoc($kamus_query)){
  $kamus_list[] = $k['specific_name'];
}

/* ------------------------
   AMBIL DATA UNIT & LAST LOG
-------------------------*/
$q = mysqli_query($koneksi,"
  SELECT *
  FROM (
    SELECT 
      u.id,
      u.unit_code,
      u.category,

      /* 🔥 AMBIL NAMA LOKASI TERAKHIR */
   (
      SELECT d.location
      FROM dispatch_daily_log d
      WHERE d.unit_id = u.id
      ORDER BY d.id DESC
      LIMIT 1
    ) AS last_location,

      (SELECT trouble_desc FROM dispatch_daily_log d 
       WHERE d.unit_id = u.id ORDER BY d.id DESC LIMIT 1) AS last_trouble,

      (SELECT specific_trouble FROM dispatch_daily_log d 
       WHERE d.unit_id = u.id ORDER BY d.id DESC LIMIT 1) AS last_specific

    FROM dispatch_unit_master u
    WHERE u.status='aktif'
  ) AS t
  ORDER BY category,
           CASE WHEN last_specific IS NOT NULL AND last_specific <> '' THEN 0 ELSE 1 END,
           unit_code
");

/* ------------------------
   AMBIL DATA LOKASI DARI TABEL lokasi
-------------------------*/
$lokasi_list = [];
$lokasi_query = mysqli_query($koneksi,"
  SELECT kode_lokasi, nama_lokasi
  FROM lokasi
  ORDER BY nama_lokasi
");
while($l = mysqli_fetch_assoc($lokasi_query)){
  $lokasi_list[] = $l;
}


$kategori = '';
$first = true;
?>

 
<div class="top-center-ui">

   

    <div id="clock-wit" class="clock-btn">
        🕒 WIT --:--:-- --
    </div>
<button type="button" id="btnFullscreen" class="fullscreen-btn">
        ⛶ Full Screen
    </button>
</div>



<form method="POST" action="dispatch/simpan_daily.php">
<h4>Input Daily Dispatch</h4>

<div class="row">
  <div class="col-md-3">
    <label>Shift</label>
    <select name="shift" class="form-control" required>
      <option value="1">Shift 1 (Siang) (07:00–19:00)</option>
      <option value="2">Shift 2 (Malam) (19:00–07:00)</option>
    </select>
  </div>

  <div class="col-md-3">
    <label>Tanggal</label>
    <input type="date" name="log_date" class="form-control" required>
  </div>
</div>

<br>

<?php
$kategori = '';
$first = true;

while($r = mysqli_fetch_assoc($q)){

    if($kategori != $r['category']){

        // tutup kategori sebelumnya
        if(!$first){
            echo "</table></div><br>";
        }

        $first = false;
        $kategori = $r['category'];
        $catId = preg_replace('/[^a-z0-9]/i', '_', $kategori);

        echo "
        <h5 class='kategori-header' style='cursor:pointer'
            data-target='cat_$catId'>
            <b>$kategori</b>
            <span class='toggle-icon' style='float:right'>➖</span>
        </h5>

        <div id='cat_$catId'>
        <table class='table table-bordered'>
            <tr>
                <th>Unit</th>
                <th>Operator/Driver</th>
                <th>Lokasi</th>
                <th>Pekerjaan</th>
                <th>Trouble Unit</th>
                <th>Specific</th>
                <th>Mulai BreakDown</th>
                <th>Selesai BreakDown </th>
            </tr>
        ";
    }

    $unit_class = $r['last_specific'] ? 'unit-bd' : '';
?>
<tr>
    <td class="<?= $unit_class; ?>"><strong>
        <?php if($r['last_specific']){ ?>
           <span class="unit-indicator unit-breakdown" title="BREAKDOWN"></span>

        <?php } else { ?>
            <span class="unit-indicator unit-ready" title="READY"></span>
        <?php } ?>

        <?= $r["unit_code"]; ?>
        <input type="hidden" name="unit_id[]" value="<?= $r["id"]; ?>">
   </strong> </td>


    <td><input name="operator_name[]" class="form-control"></td>

    <td>
        <?php $lastLoc = $r['last_location']; ?>
       <select name="location_select[]" class="form-control lokasiSelect">
          <option value="">-- Pilih Lokasi --</option>
          <?php foreach($lokasi_list as $lok){ ?>
              <option value="<?= $lok['kode_lokasi']; ?>"
                  <?= ($lastLoc == $lok['kode_lokasi'] ? 'selected' : '') ?>>
                  <?= $lok['nama_lokasi']; ?>
              </option>
          <?php } ?>
      </select>

    </td>

    <td><input name="job_desc[]" class="form-control"></td>
    <td><input name="trouble_desc[]" class="form-control" value="<?= $r['last_trouble']; ?>"></td>

    <td>
        <select name="specific_trouble[]" class="form-control specificSelect">
            <option value="">-- Pilih Specific --</option>
            <?php foreach($kamus_list as $k){ ?>
                <option value="<?= $k; ?>" <?= ($r['last_specific'] == $k ? 'selected' : '') ?>>
                    <?= $k ?>
                </option>
            <?php } ?>
        </select>
    </td>

    <td><input type="time" name="bd_start[]" class="form-control"></td>
    <td><input type="time" name="bd_end[]" class="form-control"></td>
</tr>
<?php
}
// tutup kategori terakhir
if(!$first){
    echo "</table></div><br>";
}
?>


<br>
<button type="submit" class="btn btn-primary">Simpan Semua</button>
</form>


<script>
$(document).ready(function(){

/* =====================================================
      SELECT2 SPECIFIC TROUBLE
=====================================================*/
$('.specificSelect').select2({
    placeholder:"Cari specific trouble...",
    allowClear:true,
    width:'100%'
});

/* === SET WARNA SAAT PAGE LOAD === */
$('.specificSelect').each(function(){

    let row = $(this).closest('tr');
    let tdUnit = row.find('td:first');
    let select2Container = $(this).next('.select2-container');
    let manual = row.find('.specificManual');

    if($(this).val() && $(this).val() !== '__manual__'){
        tdUnit.addClass('unit-bd');
        select2Container.addClass('bd-active');
    }

    if($(this).val() === '__manual__'){
        tdUnit.addClass('unit-bd');
        select2Container.addClass('bd-active');
        manual.show().prop('required',true);
    }
});

/* === SAAT SPECIFIC BERUBAH === */
$('.specificSelect').on('change', function(){

    let row = $(this).closest('tr');
    let tdUnit = row.find('td:first');
    let manual = row.find('.specificManual');
    let select2Container = $(this).next('.select2-container');

    if($(this).val() === '__manual__'){
        manual.show().val('').prop('required',true).focus();
        tdUnit.addClass('unit-bd');
        select2Container.addClass('bd-active');
    }
    else if($(this).val()){
        manual.hide().prop('required',false);
        tdUnit.addClass('unit-bd');
        select2Container.addClass('bd-active');
    }
    else{
        manual.hide().prop('required',false).val('');
        tdUnit.removeClass('unit-bd');
        select2Container.removeClass('bd-active');
    }
});

/* === INPUT MANUAL SPECIFIC === */
$('.specificManual').on('keyup change', function(){

    let row = $(this).closest('tr');
    let tdUnit = row.find('td:first');
    let select2Container = row.find('.select2-container');

    if($(this).val()){
        tdUnit.addClass('unit-bd');
        select2Container.addClass('bd-active');
    } else {
        tdUnit.removeClass('unit-bd');
        select2Container.removeClass('bd-active');
    }
});
</script>





<script>
document.addEventListener('DOMContentLoaded', function(){

    document.querySelectorAll('.kategori-header').forEach(function(el){

        el.addEventListener('click', function(){

            let target = this.getAttribute('data-target');
            let body   = document.getElementById(target);
            let icon   = this.querySelector('.toggle-icon');

            if(!body) return;

            if(body.style.display === 'none'){
                body.style.display = 'block';
                icon.innerText = '➖';
            } else {
                body.style.display = 'none';
                icon.innerText = '➕';
            }
        });

    });

});
</script>

<script>
document.getElementById('btnFullscreen').addEventListener('click', function(){

    if(!document.fullscreenElement){
        document.documentElement.requestFullscreen();
        this.innerHTML = '⛶ Exit Full Screen';
    }else{
        document.exitFullscreen();
        this.innerHTML = '⛶ Full Screen';
    }

});
</script>

<script>
function updateClockWIT(){
    const now = new Date();

    // UTC → WIT (UTC +9)
    const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
    const wit = new Date(utc + (9 * 3600000));

    let h = wit.getHours();
    let m = wit.getMinutes();
    let s = wit.getSeconds();

    let ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12;
    h = h ? h : 12;

    h = h.toString().padStart(2,'0');
    m = m.toString().padStart(2,'0');
    s = s.toString().padStart(2,'0');

    document.getElementById('clock-wit').innerHTML =
        `🕒 WIT : ${h}:${m}:${s} ${ampm}`;
}

setInterval(updateClockWIT, 1000);
updateClockWIT();
</script>
