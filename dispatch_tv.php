<?php
include 'koneksi.php';
date_default_timezone_set('Asia/Jayapura');

$q = mysqli_query($koneksi,"
SELECT 
    equipment,
    SUM(status_unit='READY') AS ready,
    SUM(status_unit='BREAKDOWN') AS breakdown,
    COUNT(*) AS total
FROM dispatch_unit_status
WHERE log_date = CURDATE()
AND shift = 'day'
GROUP BY equipment
ORDER BY equipment
");

$q_breakdown = mysqli_query($koneksi,"
SELECT unit_code, equipment, breakdown_status, location_code
FROM dispatch_unit_status
WHERE status_unit = 'BREAKDOWN'
AND log_date = CURDATE()
AND shift = 'day'
ORDER BY equipment, unit_id
");
?>



<!DOCTYPE html>
<html>
<head>
<title>DASHBOARD EQUIPMENT</title>

<style>
    html, body{
    height:100%;
    overflow:hidden; 
}
body{
    margin:0;
    font-family:Arial;
    background:#0b2a3d;
    color:white;
}

/* HEADER */
.header{
    position:fixed;
    top:0;
    left:0;
    right:0;
    height:70px;
    background:#002147;
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:0 20px;
}

.header img{height:50px;}
#clock{font-size:18px;font-weight:bold;}

/* CONTENT */
.container{
    padding-top:90px;
    padding-bottom:60px;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(300px,1fr));
    gap:20px;
}

*{
    cursor: none !important;
}
/* CARD */
.card{
    background:#123b52;
    border-radius:16px;

    padding:30px;
    text-align:center;

    box-shadow:0 0 20px rgba(0,0,0,0.5);

    transform:scale(1.05);
}

.card h3{
    margin-bottom:15px;
    font-size:30px;
}

/* IMAGE */
.icon{
    width:80px;
    margin-bottom:10px;
}

/* STATUS */
.status{
    margin-top:20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px;
}

.status div{
    flex:1;
    padding:10px;
}

.status div:first-child{
    text-align:left;
}

.status div:last-child{
    text-align:right;
}

/* ANGKA BESAR */
.big{
    font-size:80px;  
    font-weight:bold;
}

/* WARNA */
.ready{color:#2ecc71;}
.breakdown{color:#e74c3c;}

.total{
    margin-top:25px;
    border-top:1px solid #fff3;
    padding-Left:12px;
    font-size:20px;
}



/* BREAKDOWN LIST BAWAH */
.breakdown-bar{
    margin:20px;
    background:#111;
    border-top:2px solid #e74c3c;

    display:flex;
    flex-wrap:wrap;         
    align-items:flex-start;

    padding:15px;
    gap:15px;

    border-radius:10px;

    overflow-y:auto;      
}

/* ITEM UNIT */
.bd-item{
    width:280px;             
    background:#2c3e50;
    border-left:6px solid #e74c3c;

    padding:18px 20px;    
    border-radius:12px;

    font-size:18px;           

}
.bd-item{
    box-shadow:0 4px 10px rgba(0,0,0,0.4);
}


.bd-unit{
    font-weight:bold;
    font-size:22px;   /* sebelumnya 18px */
}

.bd-status{
    color:#f1c40f;
    font-size:16px;

}

.bd-location{
    color:#bdc3c7;
    font-size:15px;
}

.mnpw{
    margin-top:25px;
    border-top:1px solid #fff3;
    padding-top:15px;

    font-size:32px;
    font-weight:bold;

    color:#00e676;
    text-align:center;
}

/* SCROLL HALUS */
.breakdown-bar::-webkit-scrollbar{
    height:6px;
}
.breakdown-bar::-webkit-scrollbar-thumb{
    background:#555;
    border-radius:10px;
    border-left: 10 Px;
}

.icon{
    width:80px;
    height:80px;         
    object-fit:contain;   
    background:#fff;
    border-radius:50%;
    padding:8px;
    display:block;
    margin:auto;
}
</style>

</head>

<body>

<!-- HEADER -->
<div class="header">
    <img src="admin/logo/hpal-BG.png">
    <div id="clock"></div>
</div>

<!-- CONTENT -->
<div class="container">

<?php while($r = mysqli_fetch_assoc($q)){ ?>

<?php
$equip = strtolower($r['equipment']);

$map = [
    'dump truck' => 'assets/dump.png',
    'excavator' => 'assets/ex.png',
    'bulldozer' => 'assets/dz.png',
    'compactor' => 'assets/com.png',
    'tower lamp' => 'assets/tl.png'

];

$img = $map[$equip] ?? 'assets/default.png';
?>

<div class="card">

    <!-- GAMBAR (BISA KAMU GANTI) -->
    <img src="<?= $img; ?>" class="icon">

    <h3><?= strtoupper($r['equipment']); ?></h3>

    <div class="status">
        <div>
            READY <br>
            <span class="big ready"><?= $r['ready']; ?></span>
        </div>

        <div>
            BREAKDOWN <br>
            <span class="big breakdown"><?= $r['breakdown']; ?></span>
        </div>
    </div>

    <div class="total">
        TOTAL : <?= $r['total']; ?>
    </div>
    <div class="total">
        MANPOWER  : <?= $r['total']; ?>
    </div>

</div>

<?php } ?>

</div>

<script>
function clock(){
    const now = new Date();
    document.getElementById("clock").innerHTML =
        now.toLocaleString("id-ID", {
            timeZone: "Asia/Jayapura"
        });
}
setInterval(clock,1000);
clock();
</script>

<h3 style="margin:20px; color:#e74c3c;">
    UNIT BREAKDOWN
</h3>
<div class="breakdown-bar">

<?php if(mysqli_num_rows($q_breakdown) > 0){ ?>
    
    <?php while($bd = mysqli_fetch_assoc($q_breakdown)){ ?>
        
        <div class="bd-item">
        <div class="bd-unit"><?= $bd['unit_code']; ?></div>
        <div class="bd-status"><?= $bd['breakdown_status']; ?></div>
        <div class="bd-location"><?= $bd['location_code']; ?></div>
         </div>

    <?php } ?>

<?php } else { ?>

    <div style="color:#2ecc71;font-weight:bold;">
        TIDAK ADA UNIT BREAKDOWN
    </div>

<?php } ?>

</div>


<script>
function enterFullscreen(){
    let el = document.documentElement;

    if (el.requestFullscreen) {
        el.requestFullscreen();
    } else if (el.webkitRequestFullscreen) {
        el.webkitRequestFullscreen();
    }
}

/* coba otomatis saat load */
document.addEventListener("DOMContentLoaded", function () {
    setTimeout(() => {
        enterFullscreen();
    }, 1000);
});

/* backup: klik sekali langsung fullscreen */
document.addEventListener("click", function onceFS(){
    enterFullscreen();
    document.removeEventListener("click", onceFS);
});

</script>

<script>
function loadData(){

    fetch('load_dashboard.php?_=' + new Date().getTime())
    .then(res => res.json())
    .then(data => {

        let container = document.getElementById("container");
        let breakdown = document.getElementById("breakdown");

        container.innerHTML = "";
        breakdown.innerHTML = "";

        /* ===== SUMMARY CARD ===== */
        data.summary.forEach(r => {

            let equip = r.equipment.toLowerCase();

            let map = {
                "dump truck":"assets/dump.png",
                "excavator":"assets/ex.png",
                "bulldozer":"assets/dz.png",
                "compactor":"assets/com.png",
                "tower lamp":"assets/tl.png"
            };

            let img = map[equip] ?? "assets/default.png";

            container.innerHTML += `
                <div class="card">
                    <img src="${img}" class="icon">

                    <h3>${r.equipment.toUpperCase()}</h3>

                    <div class="status">
                        <div>
                            READY <br>
                            <span class="big ready">${r.ready}</span>
                        </div>

                        <div>
                            BREAKDOWN <br>
                            <span class="big breakdown">${r.breakdown}</span>
                        </div>
                    </div>

                    <div class="total">
                        TOTAL : ${r.total}
                    </div>
                <div class="mnpw">
                        TOTAL : ${r.mnapower}
                    </div>
                </div>
            `;
        });

        /* ===== BREAKDOWN LIST ===== */
        if(data.breakdown.length > 0){

            data.breakdown.forEach(bd => {

                breakdown.innerHTML += `
                    <div class="bd-item">
                        <div class="bd-unit">${bd.unit_code}</div>
                        <div class="bd-status">${bd.breakdown_status}</div>
                        <div class="bd-location">${bd.location_code}</div>
                    </div>
                `;
            });

        } else {

            breakdown.innerHTML = `
                <div style="color:#2ecc71;font-weight:bold;">
                    TIDAK ADA UNIT BREAKDOWN
                </div>
            `;
        }

    });
}

/* LOAD PERTAMA */
loadData();

/* AUTO UPDATE */
setInterval(loadData, 5000); // 
</script>
</body>
</html>