<?php
include 'koneksi.php';
date_default_timezone_set('Asia/Jayapura');

$q = mysqli_query($koneksi,"
SELECT * FROM media_log
WHERE display_target IN ('display2', 'all')
ORDER BY id ASC
");
$media = [];
while($row = mysqli_fetch_assoc($q)){ $media[] = $row; }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>TV DISPLAY MEDIA</title>
    <style>
        html, body { margin:0; padding:0; width:100%; height:100%; overflow:hidden; background:#050a12; font-family: 'Segoe UI', sans-serif; }
        .header { position:fixed; top:0; left:0; right:0; height:80px; background: rgba(6, 15, 30, 0.85); backdrop-filter: blur(15px); border-bottom: 1px solid rgba(0, 210, 255, 0.2); display:flex; justify-content:space-between; align-items:center; padding:0 40px; z-index:1000; }
        #dateTime { font-size:20px; font-weight:700; color:#00d2ff; text-align:right; }
        .viewer { position:absolute; top:80px; bottom:0; left:0; width:100%; background:#000; z-index: 1; }
        .viewer img, .viewer video { position:absolute; width:100%; height:100%; object-fit:contain; background:#000; transition: opacity 0.8s; }
        .controls { position:fixed; bottom:30px; left:50%; transform:translateX(-50%); display:flex; gap:15px; background: rgba(6, 15, 30, 0.8); backdrop-filter: blur(10px); padding:10px 25px; border-radius: 40px; border: 1px solid rgba(0, 210, 255, 0.3); z-index:9999; opacity:0; transition:0.4s; }
        body:hover .controls { opacity:1; }
        .controls button { background:none; border:1px solid #00d2ff; color:#00d2ff; padding:10px 20px; border-radius:20px; cursor:pointer; }
    </style>
</head>
<body>

<div class="header">
    <img src="admin/logo/hpal-BG.png" style="height:40px;">
    <div id="dateTime"></div>
</div>

<div class="viewer" id="viewer" onclick="toggleFullscreen()"></div>

<div class="controls">
    <button onclick="prev()">⬅ PREV</button>
    <button onclick="togglePlay()" id="playBtn">PAUSE</button>
    <button onclick="next()">NEXT ➡</button>
</div>

<script>
let media = <?php echo json_encode($media); ?>;
let index = 0;
let viewer = document.getElementById("viewer");
let isPlaying = true;

// --- FULLSCREEN TOGGLE ---
function toggleFullscreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(err => console.log(err));
    } else {
        document.exitFullscreen();
    }
}

// --- LOGIKA MEDIA ---
function showMedia(idx) {
    if (!media || media.length === 0) return;
    index = idx;
    let item = media[index];
    viewer.innerHTML = "";

    let el;
    if (item.file_type === "video") {
        el = document.createElement("video");
        el.src = "uploads/media/" + item.file_name;
        el.autoplay = true;
        el.muted = true;
        el.onended = () => { if(isPlaying) next(); };
        el.onerror = () => next();
    } else {
        el = document.createElement("img");
        el.src = "uploads/media/" + item.file_name;
        if (isPlaying) setTimeout(() => next(), 10000);
    }
    viewer.appendChild(el);
}

function next() { index = (index + 1) % media.length; showMedia(index); }
function prev() { index = (index - 1 + media.length) % media.length; showMedia(index); }
function togglePlay() {
    isPlaying = !isPlaying;
    document.getElementById("playBtn").innerText = isPlaying ? "PAUSE" : "PLAY";
    if(isPlaying) showMedia(index);
}

// --- INITIALIZATION ---
if(media.length > 0) showMedia(0);

// --- TANGGAL DAN JAM ---
setInterval(() => {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const now = new Date();
    const dateStr = now.toLocaleDateString('id-ID', options);
    const timeStr = now.toLocaleTimeString('id-ID');
    document.getElementById("dateTime").innerHTML = dateStr + "<br>" + timeStr + " WIT";
}, 1000);
</script>


<script>
function checkUpdate(){
    // Menggunakan jalur absolut karena file berada di root (luar folder DSTF)
    fetch('http://10.20.34.2/cek_media_update.php?_=' + Date.now())
    .then(r => r.text())
    .then(id => {
        let currentId = parseInt(id);
        // Pastikan variabel 'media' sudah terdefinisi di atas
        let lastId = media.length > 0 ? media[media.length - 1].id : 0;
        if(currentId > lastId) {
            location.reload();
        }
    });
}
setInterval(checkUpdate, 50000); // Cek setiap 5 detik
</script>
</body>
</html>