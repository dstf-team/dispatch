<?php
include 'koneksi.php';
date_default_timezone_set('Asia/Jayapura');

$q = mysqli_query($koneksi,"
SELECT * FROM media_log
WHERE display_target IN ('display1', 'all')
ORDER BY id ASC
");
$media = [];
while($row = mysqli_fetch_assoc($q)){ $media[] = $row; }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>TV DISPLAY - DISPLAY 1</title>
    <style>
        /* Base Styling */
        html, body { margin:0; padding:0; width:100%; height:100%; overflow:hidden; background:#050a12; font-family: 'Segoe UI', sans-serif; color:#e5e7eb; user-select:none; }
        
        /* Background Glow */
        body::before { content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle at 30% 30%, rgba(0, 210, 255, 0.05) 0%, transparent 60%); z-index: 0; pointer-events: none; }

        /* Header Premium */
        .header { position:fixed; top:0; left:0; right:0; height:85px; background: linear-gradient(180deg, rgba(6, 15, 30, 0.95) 0%, rgba(4, 10, 20, 0.8) 100%); backdrop-filter: blur(20px); border-bottom: 2px solid rgba(0, 210, 255, 0.25); display:flex; justify-content:space-between; align-items:center; padding:0 40px; z-index:1000; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .header img { height:46px; filter: drop-shadow(0 0 10px rgba(0, 210, 255, 0.3)); }
        #dateTime { font-size:20px; font-weight:800; color:#00d2ff; text-shadow: 0 0 12px rgba(0, 210, 255, 0.5); background: rgba(0, 210, 255, 0.08); padding: 8px 20px; border-radius: 6px; border: 1px solid rgba(0, 210, 255, 0.2); }

        /* Viewer */
        .viewer { position:absolute; top:85px; bottom:44px; left:0; width:100%; background:#000; z-index: 1; }
        .viewer img, .viewer video { position:absolute; width:100%; height:100%; object-fit:contain; transition: opacity 0.8s; }

        /* Controls */
        .controls { position:fixed; bottom:70px; left:50%; transform:translateX(-50%); display:flex; gap:15px; background: rgba(6, 15, 30, 0.9); backdrop-filter: blur(15px); padding:10px 25px; border-radius: 40px; border: 1px solid rgba(0, 210, 255, 0.3); z-index:9999; opacity:0; transition:0.4s; }
        body:hover .controls { opacity:1; }
        .controls button { background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(0, 210, 255, 0.3); color:#00d2ff; padding:10px 20px; border-radius:20px; cursor:pointer; font-size:16px; transition:0.3s; }
        .controls button:hover { background: #00d2ff; color: #050a12; transform: scale(1.1); }

        /* Footer */
        .footer { position:fixed; bottom:0; left:0; right:0; height:40px; background: #030712; border-top: 1px solid rgba(0, 210, 255, 0.1); display: flex; align-items: center; justify-content: center; font-size: 11px; letter-spacing: 2px; color: #6b7280; z-index:1000; }
    </style>
</head>
<body>

<div class="header">
    <img src="admin/logo/hpal-BG.png">
    <div id="dateTime"></div>
</div>

<div class="viewer" id="viewer" onclick="toggleFullscreen()"></div>

<div class="controls">
    <button onclick="prev()">⬅ PREV</button>
    <button onclick="togglePlay()" id="playBtn">PAUSE</button>
    <button onclick="next()">NEXT ➡</button>
</div>

<div class="footer">MEDIA CONTROL TAILING & WASTE MANAGEMENT DISPLAY SYSTEM</div>

<script>
let media = <?php echo json_encode($media); ?>;
let index = 0;
let viewer = document.getElementById("viewer");
let isPlaying = true;
let lastId = media.length > 0 ? media[media.length - 1].id : 0;

function toggleFullscreen() {
    if (!document.fullscreenElement) document.documentElement.requestFullscreen();
    else document.exitFullscreen();
}

function showMedia(idx) {
    if (!media || media.length === 0) return;
    index = idx;
    let item = media[index];
    viewer.innerHTML = "";
    let el;
    if (item.file_type === "video") {
        el = document.createElement("video");
        el.src = "uploads/media/" + item.file_name;
        el.autoplay = true; el.muted = true;
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

if(media.length > 0) showMedia(0);

// Jam & Auto Update
setInterval(() => {
    const now = new Date();
    document.getElementById("dateTime").innerHTML = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' }) + " | " + now.toLocaleTimeString('id-ID') + " WIT";
}, 1000);

setInterval(() => {
    fetch('http://10.20.34.2/cek_media_update.php?_=' + Date.now())
    .then(r => r.text())
    .then(id => {
        if(parseInt(id) > lastId) location.reload();
    });
}, 50000);
</script>
</body>
</html>