<?php
include "../koneksi.php";

// ================= FILTER ==================
$tgl1 = $_GET['tgl1'] ?? '';
$tgl2 = $_GET['tgl2'] ?? '';
$kategori = $_GET['kategori'] ?? '';
$keyword = mysqli_real_escape_string($koneksi, $_GET['q'] ?? '');

$where = " WHERE 1=1 ";

// filter tanggal
if($tgl1 != '' && $tgl2 != ''){
    $where .= " AND dl.log_date BETWEEN '$tgl1' AND '$tgl2' ";
}
elseif($tgl1 != ''){
    $where .= " AND dl.log_date = '$tgl1' ";
}

// filter kategori
if($kategori != ''){
    $where .= " AND um.category = '$kategori' ";
}

// keyword
if($keyword != ''){
    $where .= "
    AND (
        um.unit_code LIKE '%$keyword%'
        OR um.unit_name LIKE '%$keyword%'
        OR dl.trouble_desc LIKE '%$keyword%'
    )";
}


// ================= QUERY REKAP PER UNIT ==================
$sql = "
SELECT 
    um.id AS unit_id,
    um.unit_code,
    um.unit_name,
    um.category,

    SUM(dl.work_minutes) AS work_total,
    SUM(dl.bd_minutes) AS bd_total,
    SUM(dl.standby_minutes) AS standby_total,
    SUM(dl.total_minutes) AS total_minutes,

    -- PA
    CASE 
        WHEN SUM(dl.total_minutes) = 0 THEN 0
        ELSE ROUND(
            (SUM(dl.work_minutes) + SUM(dl.standby_minutes)) 
            / SUM(dl.total_minutes) * 100, 2
        )
    END AS pa,

    -- MA
    CASE 
        WHEN (SUM(dl.work_minutes) + SUM(dl.bd_minutes)) = 0 THEN 0
        ELSE ROUND(
            SUM(dl.work_minutes) 
            / (SUM(dl.work_minutes) + SUM(dl.bd_minutes)) * 100, 2
        )
    END AS ma,

    -- UA
    CASE 
        WHEN SUM(dl.total_minutes) = 0 THEN 0
        ELSE ROUND(
            SUM(dl.work_minutes) 
            / SUM(dl.total_minutes) * 100, 2
        )
    END AS ua

FROM dispatch_unit_master um
LEFT JOIN dispatch_daily_log dl 
    ON dl.unit_id = um.id
    $where
GROUP BY um.id
ORDER BY um.category ASC, um.unit_code ASC

";

$result = mysqli_query($koneksi,$sql) or die("LIST ERROR: ".mysqli_error($koneksi));


// ================== LIST KATEGORI ==================
$qkat = mysqli_query($koneksi,"SELECT DISTINCT category FROM dispatch_unit_master ORDER BY category ASC");
?>


<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

<div class="container mt-3">

<h4>Rekap Dispatch Per Unit</h4>

<form method="get" action="./index.php" class="row g-2" id="filterForm">

    <input type="hidden" name="page" value="dispatch">
    <input type="hidden" name="aksi" value="rekap">

    <div class="col-md-3">
        <label>Dari</label>
       <input type="date" name="tgl1" onchange="autoSubmitSmart()" class="form-control">
    </div>

    <div class="col-md-3">
        <label>Sampai</label>
        <input type="date" name="tgl2" onchange="autoSubmitSmart()" class="form-control">
    </div>

    <div class="col-md-3">
        <label>Kategori</label>
        <select name="kategori" class="form-select" onchange="autoSubmitSmart()">
            <option value="">Semua</option>
            <?php while($k=mysqli_fetch_assoc($qkat)){ ?>
                <option value="<?= $k['category'] ?>" <?=($kategori==$k['category'])?'selected':''?>>
                    <?= $k['category'] ?>
                </option>
            <?php } ?>
        </select>
    </div>

    <div class="col-md-2">
        <label>Cari</label>
        <input type="text" name="q" value="<?= $keyword ?>" class="form-control" oninput="autoSubmitDelay()">
    </div>

    <div class="col-md-1 mt-4">
        <button class="btn btn-primary w-100">OK</button>
    </div>

</form>


<hr>


<table class="table table-bordered table-striped">
<tr class="text-center fw-bold bg-light">
    <td>Kategori</td>
    <td>Unit</td>
    <td>Work</td>
    <td>BD</td>
    <td>Standby</td>
    <td>PA</td>
    <td>MA</td>
    <td>UA</td>
    <td>Detail</td>
</tr>

<?php while($row=mysqli_fetch_assoc($result)) { ?>
<tr>
<td><?= $row['category'] ?></td>
<td><?= $row['unit_code'] ?> - <?= $row['unit_name'] ?></td>

<td class="text-center"><?= $row['work_total'] ?></td>
<td class="text-center text-danger fw-bold"><?= $row['bd_total'] ?></td>
<td class="text-center"><?= $row['standby_total'] ?></td>

<td class="text-center fw-bold"><?= $row['pa'] ?>%</td>
<td class="text-center fw-bold"><?= $row['ma'] ?>%</td>
<td class="text-center fw-bold"><?= $row['ua'] ?>%</td>


<td class="text-center">
    <button 
        class="btn btn-info btn-sm"
        data-bs-toggle="modal"
        data-bs-target="#modalDetail"
        onclick="loadDetail(
            <?= $row['unit_id'] ?>,
            '<?= $tgl1 ?>',
            '<?= $tgl2 ?>'
        )">
        Detail
    </button>
</td>
</tr>
<?php } ?>
</table>

</div>


<!-- ================= MODAL DETAIL ================= -->
<div class="modal fade" id="modalDetail">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5>Detail Downtime Unit</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body" id="detailContent">
        Loading...
      </div>
    </div>
  </div>
</div>

<script>
function loadDetail(unit_id,t1,t2){

    let url = "detail_unit_breakdown.php?unit_id="+unit_id;

    if(t1 !== "") url += "&tgl1="+t1;
    if(t2 !== "") url += "&tgl2="+t2;

    document.getElementById("detailContent").innerHTML = "Loading...";

    fetch(url)
    .then(r=>r.text())
    .then(html=>{
        document.getElementById("detailContent").innerHTML = html;
    }).catch(()=>{
        document.getElementById("detailContent").innerHTML = "Gagal load data";
    });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


<script>
let typingTimer;

function autoSubmitSmart(){
    const form = document.getElementById('filterForm');

    const tgl1 = form.querySelector('[name="tgl1"]').value;
    const tgl2 = form.querySelector('[name="tgl2"]').value;

    // ❗ JANGAN SUBMIT kalau baru isi salah satu tanggal
    if(tgl1 !== '' && tgl2 === '') return;
    if(tgl1 === '' && tgl2 !== '') return;

    form.submit();
}

// delay untuk input keyword
function autoSubmitDelay(){
    clearTimeout(typingTimer);
    typingTimer = setTimeout(() => {
        autoSubmitSmart();
    }, 600);
}
</script>

