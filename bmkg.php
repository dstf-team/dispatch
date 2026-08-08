<?php
date_default_timezone_set('Asia/Jayapura');

$url = "https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4=82.04.06.2021";
$json = @file_get_contents($url);

if(!$json){
    die("Gagal mengambil data BMKG");
}

$data = json_decode($json,true);
$current = $data['data'][0]['cuaca'][0][0];

$lokasi = "KAWASI - PULAU OBI";
$kabupaten = "HALMAHERA SELATAN";

$suhu       = $current['t'];
$humidity   = $current['hu'];
$angin      = $current['ws'];
$arah       = $current['wd'];
$cuaca      = $current['weather_desc'];
$visibility = $current['vs_text'];
$icon       = $current['image'];
$update     = $current['local_datetime'];

$status = "OPERASI NORMAL";
// Menggunakan kombinasi 2 warna gradient untuk tampilan lebih mewah
$gradientStatus = "linear-gradient(90deg, #11998e, #38ef7d)"; 
$glowColor = "rgba(56, 239, 125, 0.4)";

if(stripos($cuaca,'berawan') !== false){
    $status = "WASPADA CUACA";
    $gradientStatus = "linear-gradient(90deg, #f857a6, #ff5858)";
    $glowColor = "rgba(255, 88, 88, 0.4)";
}
if(stripos($cuaca,'ringan') !== false){
    $status = "HAULING TERBATAS";
    $gradientStatus = "linear-gradient(90deg, #ff9900, #f15b2a)";
    $glowColor = "rgba(241, 91, 42, 0.4)";
}
if(stripos($cuaca,'sedang') !== false){
    $status = "GANGGUAN OPERASI";
    $gradientStatus = "linear-gradient(90deg, #ff416c, #ff4b2b)";
    $glowColor = "rgba(255, 75, 43, 0.4)";
}
if(stripos($cuaca,'lebat') !== false){
    $status = "STOP OPERASI";
    $gradientStatus = "linear-gradient(90deg, #4d0000, #990000)";
    $glowColor = "rgba(153, 0, 0, 0.4)";
}
if(stripos($cuaca,'petir') !== false){
    $status = "EVAKUASI AREA TERBUKA";
    $gradientStatus = "linear-gradient(90deg, #8a2387, #e94057)";
    $glowColor = "rgba(233, 64, 87, 0.4)";
}

$labels = [];
$tempData = [];
$humData = [];
$windData = [];

foreach($data['data'][0]['cuaca'] as $hari){
    foreach($hari as $row){
        $labels[] = date('d M H:i', strtotime($row['local_datetime']));
        $tempData[] = $row['t'];
        $humData[]  = $row['hu'];
        $windData[] = $row['ws'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DSTF Weather Dashboard</title>
    <meta http-equiv="refresh" content="300">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        :root {
            --bg-main: #060b16;
            --bg-card: rgba(15, 26, 46, 0.7);
            --border-color: rgba(0, 191, 255, 0.18);
            --neon-blue: #00bfff;
            --neon-green: #00ff99;
        }

        /* Memaksa Skala Tampilan 67% di semua Browser */
        body {
            background-color: var(--bg-main);
            background-image: radial-gradient(circle at 50% 10%, #111e36 0%, var(--bg-main) 75%);
            color: #e2e8f0;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            zoom: 67%; /* Chrome, Edge, Safari */
            -moz-transform: scale(0.67); /* Firefox */
            -moz-transform-origin: top center;
        }

        /* Header Style */
        .header {
            background: rgba(12, 22, 41, 0.85);
            border: 1px solid var(--border-color);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 22px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .title {
            font-family: 'Orbitron', sans-serif;
            font-size: 30px;
            font-weight: 700;
            letter-spacing: 2px;
            background: linear-gradient(45deg, #ffffff, var(--neon-blue));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            color: #8a99ad;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .clock {
            font-family: 'Orbitron', sans-serif;
            font-size: 34px;
            color: var(--neon-green);
            text-shadow: 0 0 12px rgba(0, 255, 153, 0.4);
        }

        /* Status Box Glow Animation */
        .status-box {
            text-align: center;
            color: white;
            padding: 18px;
            border-radius: 14px;
            font-size: 24px;
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            letter-spacing: 2px;
            margin-bottom: 25px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 20px <?= $glowColor ?>;
            animation: pulse 2.5s infinite alternate;
        }

        @keyframes pulse {
            0% { opacity: 0.9; box-shadow: 0 0 10px <?= $glowColor ?>; }
            100% { opacity: 1; box-shadow: 0 0 30px <?= $glowColor ?>; }
        }

        /* Card Style */
        .card-monitor {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            backdrop-filter: blur(12px);
            border-radius: 16px;
            padding: 26px;
            height: 100%;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.3);
        }

        .card-monitor::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 4px; height: 100%;
            background: var(--neon-blue);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .card-monitor:hover {
            transform: translateY(-5px);
            border-color: rgba(0, 191, 255, 0.4);
            box-shadow: 0 12px 35px rgba(0, 191, 255, 0.15);
        }

        .card-monitor:hover::before {
            opacity: 1;
        }

        .card-title {
            color: #8a99ad;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-title i {
            color: var(--neon-blue);
            font-size: 18px;
        }

        .card-value {
            font-family: 'Orbitron', sans-serif;
            font-size: 42px;
            font-weight: 700;
            color: #ffffff;
            line-height: 1;
        }

        .card-subvalue {
            font-family: 'Orbitron', sans-serif;
            font-size: 26px;
            font-weight: 700;
        }

        /* Progress Bar di Dalam Card */
        .custom-progress {
            background: rgba(255, 255, 255, 0.05);
            height: 6px;
            border-radius: 10px;
            margin-top: 10px;
            overflow: hidden;
        }

        hr {
            border-top: 1px solid rgba(255, 255, 255, 0.07);
            margin: 18px 0;
        }

        .weather-icon {
            width: 110px;
            filter: drop-shadow(0 0 15px rgba(255,255,255,0.25));
        }

        /* Container Jam Prediksi Berikutnya (Fix Teks Cerah) */
        .forecast-container {
            display: flex;
            gap: 14px;
            overflow-x: auto;
            padding-bottom: 12px;
        }

        .forecast-mini-card {
            flex: 1;
            min-width: 115px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            padding: 16px 10px;
            text-align: center;
            transition: all 0.2s ease;
        }

        /* FIX TEXT JAM PREDIKSI MENJADI PUTIH CERAH */
        .forecast-mini-card .time-text {
            color: #00bfff !important; /* Biru Neon agar sangat jelas */
            font-weight: 600;
            font-size: 12px;
        }

        .forecast-mini-card .desc-text {
            color: #ffffff !important; /* Putih Cerah */
            font-size: 12px;
            font-weight: 500;
        }

        .forecast-mini-card:hover {
            background: rgba(0, 191, 255, 0.1);
            border-color: rgba(0, 191, 255, 0.4);
            transform: scale(1.05);
        }

        /* Chart & Table Box */
        .chart-box, .table-box {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            backdrop-filter: blur(12px);
            border-radius: 16px;
            padding: 22px;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px; height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.01);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(0, 191, 255, 0.3);
            border-radius: 10px;
        }
    </style>
</head>

<body>

<div class="container-fluid p-4">

    <div class="header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="title">DSTF WEATHER DASHBOARD</div>
                <div class="subtitle">
                    <i class="bi bi-geo-alt-fill text-danger"></i> <?= $lokasi ?> &bull; <?= $kabupaten ?>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="clock" id="clock">00:00:00</div>
                <div class="text-white-50 small">
                    <i class="bi bi-arrow-clockwise text-info"></i> Data BMKG: <?= date('d M Y H:i', strtotime($update)); ?> WIT
                </div>
            </div>
        </div>
    </div>

    <div class="status-box" style="background: <?= $gradientStatus ?>;">
        <i class="bi bi-shield-fill-check me-2"></i> STATUS OPERASI : <?= $status ?>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="card-monitor d-flex flex-column justify-content-center align-items-center text-center">
                <span class="position-absolute top-0 end-0 m-3 badge bg-info text-dark fw-bold">Kondisi Aktual</span>
                <img src="<?= $icon ?>" class="weather-icon mb-2" alt="Weather Icon">
                <h2 class="fw-bold text-white mb-1" style="letter-spacing: 1px;"><?= $cuaca ?></h2>
                <span class="text-white-50 small">Kawasan Tambang Pulau Obi</span>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-monitor d-flex flex-column justify-content-between">
                <div>
                    <div class="card-title"><i class="bi bi-thermometer-sun text-warning"></i> SUHU UDARA</div>
                    <div class="card-value text-warning"><?= $suhu ?>°<span style="font-size: 24px; font-family:'Inter'">C</span></div>
                    <div class="custom-progress">
                        <div class="bg-warning" style="width: <?= ($suhu/50)*100 ?>%; height:100%"></div>
                    </div>
                </div>
                <hr>
                <div>
                    <div class="card-title"><i class="bi bi-moisture text-info"></i> KELEMBABAN (RH)</div>
                    <div class="card-subvalue text-info"><?= $humidity ?><span class="fs-5 fw-normal"> %</span></div>
                    <div class="custom-progress">
                        <div class="bg-info" style="width: <?= $humidity ?>%; height:100%"></div>
                    </div>
                </div>
                <hr>
                <div>
                    <div class="card-title"><i class="bi bi-eye-fill text-white"></i> VISIBILITY</div>
                    <div class="fw-bold text-white fs-5"><i class="bi bi-cloud-fog2 text-white-50 me-1"></i> <?= $visibility ?></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-monitor d-flex flex-column justify-content-between">
                <div>
                    <div class="card-title"><i class="bi bi-wind" style="color: var(--neon-green)"></i> KECEPATAN ANGIN</div>
                    <div class="card-value" style="color: var(--neon-green);"><?= $angin ?> <span style="font-size: 18px; font-family:'Inter'">km/h</span></div>
                    <div class="custom-progress">
                        <div style="background: var(--neon-green); width: <?= min(($angin/40)*100, 100) ?>%; height:100%"></div>
                    </div>
                </div>
                <hr>
                <div>
                    <div class="card-title"><i class="bi bi-compass text-danger"></i> ARAH ANGIN</div>
                    <div class="card-subvalue text-white"><i class="bi bi-cursor-fill text-warning-emphasis me-1"></i> <?= $arah ?></div>
                </div>
                <hr>
                <div class="text-white-50 small d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle-fill text-info"></i> Sistem memperbarui data setiap 5 menit.
                </div>
            </div>
        </div>
    </div>

    <div class="chart-box mb-4">
        <h5 class="mb-3 fw-bold text-white" style="font-family: 'Orbitron', sans-serif;">
            <i class="bi bi-graph-up text-info navigation-icon me-2"></i> METEOROLOGICAL FORECAST (72 JAM)
        </h5>
        <div id="chartForecast"></div>
    </div>

    <h5 class="mb-3 fw-bold text-white" style="font-family: 'Orbitron', sans-serif;">
        <i class="bi bi-clock-history text-primary me-2"></i> PREDIKSI JAM BERIKUTNYA
    </h5>
    <div class="forecast-container mb-4">
        <?php
        $forecastCount = 0;
        foreach($data['data'][0]['cuaca'] as $hari){
            foreach($hari as $row){
                if($forecastCount >= 8) break;
        ?>
        <div class="forecast-mini-card">
            <div class="time-text mb-2">
                <?= date('d M / H:i', strtotime($row['local_datetime'])) ?>
            </div>
            <img src="<?= $row['image'] ?>" width="42" class="my-1" alt="icon">
            <div class="desc-text text-truncate my-1" title="<?= $row['weather_desc'] ?>">
                <?= $row['weather_desc'] ?>
            </div>
            <div class="text-warning fw-bold mt-1" style="font-size: 15px; font-family:'Orbitron'"><?= $row['t'] ?>°C</div>
        </div>
        <?php
                $forecastCount++;
            }
        }
        ?>
    </div>

    <div class="table-box">
        <h5 class="mb-3 fw-bold text-white"><i class="bi bi-table text-warning me-2"></i> DATA TABULAR PREDIKSI BMKG</h5>
        <div style="max-height: 300px; overflow-y: auto;">
            <table class="table table-dark table-hover align-middle m-0" style="background: transparent;">
                <thead class="table-light opacity-75 sticky-top">
                    <tr>
                        <th>Waktu (WIT)</th>
                        <th>Kondisi Cuaca</th>
                        <th>Suhu Udara</th>
                        <th>Kelembaban (RH)</th>
                        <th>Kecepatan Angin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach($data['data'][0]['cuaca'] as $hari){
                        foreach($hari as $row){
                    ?>
                    <tr>
                        <td><span class="text-info fw-semibold"><?= date('d M Y - H:i', strtotime($row['local_datetime'])) ?></span></td>
                        <td>
                            <img src="<?= $row['image'] ?>" width="30" class="me-2" alt="">
                            <span class="text-white"><?= $row['weather_desc'] ?></span>
                        </td>
                        <td><span class="text-warning fw-bold"><?= $row['t'] ?>°C</span></td>
                        <td><span class="text-white-50"><?= $row['hu'] ?>%</span></td>
                        <td><i class="bi bi-compass text-info"></i> <span class="text-white"><?= $row['ws'] ?> km/h</span> <span class="text-white-50 small">(<?= $row['wd'] ?>)</span></td>
                    </tr>
                    <?php }} ?>
                </tbody>
            </table>
        </div>
    </div>

</div>



<div class="row mt-4">

    <div class="col-md-6">
        <div class="table-box">
            <h5 class="text-white mb-3">
                <i class="bi bi-cloud-fill text-info"></i>
                ENHANCED SATELLITE
            </h5>

            <img
                src="https://inderaja.bmkg.go.id/IMAGE/HIMA/H08_NC_Indonesia.png?t=<?=time()?>"
                class="img-fluid rounded"
            >
        </div>
    </div>

    <div class="col-md-6">
        <div class="table-box">
            <h5 class="text-white mb-3">
                <i class="bi bi-cloud-lightning-fill text-warning"></i>
                INFRARED SATELLITE
            </h5>

            <img
                src="https://inderaja.bmkg.go.id/IMAGE/HIMA/H08_EH_Indonesia.png?t=<?=time()?>"
                class="img-fluid rounded"
            >



            
        </div>
    </div>

</div>


<script>
    // Force Zoom Global 67% (Kompabilitas Semua Browser lintas Engine)
    function forceScale() {
        if(navigator.userAgent.indexOf("Firefox") != -1 ) {
            document.body.style.transform = "scale(0.67)";
            document.body.style.transformOrigin = "top center";
            document.body.style.width = "149%"; 
        }
    }
    window.onload = forceScale;

    // Live Digital Clock
    function updateClock(){
        let now = new Date();
        document.getElementById("clock").innerHTML = now.toLocaleTimeString("id-ID", {hour12: false}) + " WIT";
    }
    setInterval(updateClock, 1000);
    updateClock();

    // ApexCharts Config
    var labels = <?= json_encode($labels) ?>;
    new ApexCharts(
        document.querySelector("#chartForecast"),
        {
            chart: {
                height: 350,
                type: 'line',
                background: 'transparent',
                toolbar: { show: true },
                fontFamily: 'Inter, sans-serif'
            },
            theme: { mode: 'dark' },
            grid: {
                borderColor: 'rgba(255,255,255,0.05)',
                strokeDashArray: 3
            },
            stroke: {
                width: [4, 3, 2],
                curve: 'smooth'
            },
            colors: ['#ffc107', '#00bfff', '#00ff99'],
            series: [
                {
                    name: 'Suhu (°C)',
                    type: 'line',
                    data: <?= json_encode($tempData) ?>
                },
                {
                    name: 'Kelembaban (%)',
                    type: 'area',
                    data: <?= json_encode($humData) ?>
                },
                {
                    name: 'Angin (km/h)',
                    type: 'column',
                    data: <?= json_encode($windData) ?>
                }
            ],
            fill: {
                type: 'gradient',
                gradient: {
                    inverseColors: false,
                    shadeIntensity: 1,
                    opacityFrom: [0.85, 0.2, 0.6],
                    opacityTo: [0.85, 0.0, 0.1],
                    stops: [0, 90, 100]
                }
            },
            xaxis: {
                categories: labels,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: { colors: '#8a99ad' },
                    rotate: -45
                }
            },
            yaxis: {
                labels: { style: { colors: '#8a99ad' } }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                labels: { colors: '#e2e8f0' }
            },
            tooltip: { theme: 'dark' }
        }
    ).render();
</script>


<script>
// FULLSCREEN DOUBLE CLICK
document.addEventListener("dblclick", function () {

    if (!document.fullscreenElement) {

        let el = document.documentElement;

        if (el.requestFullscreen) {
            el.requestFullscreen();
        } else if (el.webkitRequestFullscreen) {
            el.webkitRequestFullscreen();
        } else if (el.msRequestFullscreen) {
            el.msRequestFullscreen();
        }

    } else {

        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }

    }

});
</script>

<script>
setInterval(function(){

    document.getElementById("satelitBMKG").src =
        "https://inderaja.bmkg.go.id/IMAGE/HIMA/H08_EH_ID.png?t=" + new Date().getTime();

},300000); // refresh 5 menit
</script>
</body>
</html>

