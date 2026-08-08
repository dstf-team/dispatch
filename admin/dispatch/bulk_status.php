<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['iduser'])) {
   header("Location: login.php");
   exit;
}

include '../koneksi.php';

/* =========================================
   AMBIL DATA UNIT
========================================= */
$q = mysqli_query($koneksi,"
    SELECT
        id,
        unit_code,
        category
    FROM dispatch_unit_master
    WHERE status='aktif'
    ORDER BY category, unit_code
");

/* =========================================
   AMBIL DATA LOKASI
========================================= */
$lokasi = mysqli_query($koneksi,"
    SELECT
        kode_lokasi,
        nama_lokasi
    FROM lokasi
    ORDER BY nama_lokasi
");


/* =========================================
   AMBIL DATA STATUS TERAKHIR
========================================= */
$status_map = [];
$location_map = [];
$ket_map = [];

$qStatus = mysqli_query($koneksi,"
    SELECT 
        unit_code,
        status_unit,
        location_code,
        breakdown_status
    FROM dispatch_unit_status
    WHERE log_date = CURDATE()
    AND shift = 'day'
");

while($s = mysqli_fetch_assoc($qStatus)){

    $code = strtoupper($s['unit_code']);

    $status_map[$code] = $s['status_unit'];
    $location_map[$code] = $s['location_code'];
    $ket_map[$code] = $s['breakdown_status'];
}
$kategori = '';
$first = true;
?>


<style>

.table td,
.table th{
    color:#000 !important;
    vertical-align:middle;
}

.table .form-control{
    color:#000 !important;
}

.kategori-header{
    background:#f5f5f5;
    padding:10px;
    border:1px solid #ddd;
    margin-bottom:0;
    cursor:pointer;
}

.toggle-icon{
    float:right;
    font-weight:bold;
}

.unit-indicator{
    display:inline-block;
    width:10px;
    height:10px;
    border-radius:50%;
    margin-right:6px;
}

.unit-ready{
    background:#2ecc71;
}

.unit-standby{
    background:#f1c40f;
}

.unit-breakdown{
    background:#e74c3c;
}

.csv-box{
    border:1px solid #ddd;
    padding:15px;
    border-radius:6px;
    background:#fafafa;
}

</style>

<!-- HEADER -->
<div class="d-flex left-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom"> 
  <a href="index.php">Home</a> / Bulk Status Unit
</div>

<!-- TITLE -->
<h2>Bulk Status Unit</h2>

<!-- =========================================
     FORM IMPORT CSV
========================================= -->
<form method="POST"
      action="dispatch/proses_import_csv_status.php"
      enctype="multipart/form-data">

<div class="csv-box mb-4">

    <div class="row">

        <div class="col-md-3">

            <label><b>Tanggal</b></label>

            <input type="date"
                   name="log_date"
                   class="form-control"
                   required>

        </div>

        <div class="col-md-3">

            <label><b>Shift</b></label>

            <select name="shift"
                    class="form-control"
                    required>

                <option value="day">
                    Shift 1 (Siang)
                </option>

                <option value="night">
                    Shift 2 (Malam)
                </option>

            </select>

        </div>

        <div class="col-md-4">

            <label><b>Upload CSV</b></label>

            <input type="file"
                   name="file_csv"
                   accept=".csv"
                   class="form-control"
                   required>

        </div>

        <div class="col-md-2 d-flex align-items-end">

            <button type="submit"
                    class="btn btn-success btn-block">

                <i class="fas fa-file-csv"></i>
                Import CSV

            </button>

        </div>

    </div>

    <hr>

<div class="alert alert-info mb-0">

    <b>Format CSV :</b><br>

    unit_code,status_unit,keterangan,location_code

    <br><br>

    <b>Contoh :</b><br>

    HDT-HO-01,READY,Normal,P89<br>
    HX-SA3-21,BREAKDOWN,Hose bocor,WS

</div>



</div>

</form>


<!-- =========================================
     FORM INPUT MANPOWER
========================================= -->







<!-- =========================================
     FORM INPUT MANUAL
========================================= -->
<form method="POST"
      action="dispatch/simpan_bulk_status.php">

<!-- FILTER -->
<div class="row mb-3">

    <div class="col-md-3">

        <label>Tanggal</label>

        <input type="date"
               name="log_date"
               class="form-control"
               required>

    </div>

    <div class="col-md-3">

        <label>Shift</label>

        <select name="shift"
                class="form-control"
                required>

            <option value="day">
                Shift 1 (Siang)
            </option>

            <option value="night">
                Shift 2 (Malam)
            </option>

        </select>

    </div>

</div>

<?php
while($r = mysqli_fetch_assoc($q)){

    if($kategori != $r['category']){

        if(!$first){
            echo "</tbody></table></div><br>";
        }

        $first = false;
        $kategori = $r['category'];

        $catId = preg_replace('/[^a-z0-9]/i','_',$kategori);

        echo "
        <h5 class='kategori-header'
            data-target='cat_$catId'>

            <b>$kategori</b>

            <span class='toggle-icon'>➖</span>

        </h5>

        <div id='cat_$catId'>

        <table class='table table-bordered table-striped'>

            <thead>
                <tr align='center'>
                    <th width='60'>No</th>
                    <th width='180'>Unit</th>
                    <th width='250'>Lokasi</th>
                    <th width='180'>Status Unit</th>
                    <th>Keterangan</th>
                </tr>
            </thead>

            <tbody>
        ";

        $no = 1;
    }
?>

<tr>

    <td align="center">
        <?= $no++; ?>
    </td>

    <td>

        <strong>

            <?php
$u_code = strtoupper($r['unit_code']);

$status = $status_map[$u_code] ?? 'READY';
$location_val = $location_map[$u_code] ?? '';
$ket_val = $ket_map[$u_code] ?? '';



$class = 'unit-ready';

if($status == 'STANDBY'){
    $class = 'unit-standby';
}
elseif($status == 'BREAKDOWN'){
    $class = 'unit-breakdown';
}
?>

<span class="unit-indicator <?= $class ?>"></span>

            <?= $r['unit_code']; ?>

        </strong>

        <input type="hidden"
               name="unit_id[]"
               value="<?= $r['id']; ?>">

        <input type="hidden"
               name="unit_code[]"
               value="<?= $r['unit_code']; ?>">

        <input type="hidden"
               name="equipment[]"
               value="<?= $r['category']; ?>">

    </td>

    <td>

    <?= $location_val ?: '-' ?>


    </td>

    <td>

           <select name="status_unit[]" class="form-control statusSelect">

    <option value="READY" <?= $status=='READY'?'selected':'' ?>>READY</option>
    <option value="STANDBY" <?= $status=='STANDBY'?'selected':'' ?>>STANDBY</option>
    <option value="BREAKDOWN" <?= $status=='BREAKDOWN'?'selected':'' ?>>BREAKDOWN</option>

</select>

    </td>

    <td>

        <input type="text"
       name="breakdown_status[]"
       value="<?= htmlspecialchars($ket_val) ?>"
       class="form-control"
       placeholder="Keterangan breakdown / standby">

    </td>

</tr>

<?php
}

if(!$first){
    echo "</tbody></table></div><br>";
}
?>

<button type="submit"
        class="btn btn-primary mb-4">

    <i class="fas fa-save"></i>
    Simpan Bulk Data

</button>

</form>

<script>

/* =========================================
   COLLAPSE CATEGORY
========================================= */
document.querySelectorAll('.kategori-header')
.forEach(function(el){

    el.addEventListener('click', function(){

        let target = this.getAttribute('data-target');

        let body = document.getElementById(target);

        let icon = this.querySelector('.toggle-icon');

        if(body.style.display === 'none'){

            body.style.display = 'block';
            icon.innerHTML = '➖';

        }else{

            body.style.display = 'none';
            icon.innerHTML = '➕';

        }

    });

});

/* =========================================
   STATUS COLOR
========================================= */
document.querySelectorAll('.statusSelect')
.forEach(function(select){

    select.addEventListener('change', function(){

        let row = this.closest('tr');

        let indicator = row.querySelector('.unit-indicator');

        indicator.classList.remove(
            'unit-ready',
            'unit-standby',
            'unit-breakdown'
        );

        if(this.value === 'READY'){

            indicator.classList.add('unit-ready');

        }
        else if(this.value === 'STANDBY'){

            indicator.classList.add('unit-standby');

        }
        else{

            indicator.classList.add('unit-breakdown');

        }

    });

});

</script>