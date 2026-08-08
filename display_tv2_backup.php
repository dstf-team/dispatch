<?php
include 'koneksi.php';
date_default_timezone_set('Asia/Jayapura');

$q = mysqli_query($koneksi,"
SELECT * FROM media_log
WHERE display_target IN ('display2')
ORDER BY id ASC
");

$media = [];
while($row = mysqli_fetch_assoc($q)){
    $media[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>TV DISPLAY MEDIA</title>

<style>
html, body{
    margin:0;
    padding:0;
    width:100%;
    height:100%;
    overflow:hidden;
    background:black;
    font-family:Arial;
    color:white;
     user-select:none;
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
    z-index:1000;
}

.header img{ height:50px; }

#clock{
    font-size:20px;
    font-weight:bold;
}

/* VIEWER */

.viewer{
    position:absolute;
    top:70px;
    bottom:32px;
    left:0;
    width:100%;
    background:black;
    overflow:hidden;
}


/* FOTO */
.viewer img{
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:100%;
    object-fit:contain;
    object-position:center;
    background:black;
}


/* VIDEO */
.viewer video{
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:100%;
    object-fit:contain;
    object-position:center;
    background:black;
}


/* FADE EFFECT */
.fade-out{
    opacity:0;
    transition:opacity 0.6s ease;
}

.fade-in{
    opacity:1;
    transition:opacity 0.6s ease;
}



/* FOOTER */
.footer{
    position:fixed;
    bottom:0;
    left:0;
    right:0;
    background:#111;
    text-align:center;
    padding:6px;
    font-size:14px;
    z-index:1000;
}


/* CONTROL SIDEBAR */
.controls{
    position:fixed;
    top:50%;
    transform:translateY(-50%);
    width:100%;
    display:flex;
    justify-content:space-between;
    padding:0 20px;
    z-index:9999;
    opacity:0;
    transition:0.3s;
    pointer-events:none;
}

/* tombol kiri kanan */
.controls button{
    background:rgba(0,0,0,0.6);
    border:none;
    color:white;
    font-size:18px;
    padding:15px 18px;
    border-radius:8px;
    cursor:pointer;
    pointer-events:auto;
}

/* muncul saat aktif */
.controls.show{
    opacity:1;
}

.qr-box{
    position:fixed;
    bottom:10px;
    left:10px;
    z-index:9999;
    background:white;
    padding:6px;
    border-radius:6px;
    box-shadow:0 0 10px rgba(0,0,0,0.5);
}

.qr-box img{
    width:50px;
    height:50px;
}


.sound-icon{
    position:fixed;
    bottom:20px;
    right:20px;
    font-size:28px;
    background:rgba(0,0,0,0.6);
    padding:12px 14px;
    border-radius:50%;
    z-index:99999;
    transition:0.3s;
    opacity:0;
    cursor:pointer;
    user-select:none;
}

</style>

</head>

<body>
<div id="soundIcon" class="sound-icon" onclick="toggleMute()">
    🔊
</div>
<!-- HEADER -->
<div class="header">
    <img src="admin/logo/hpal-BG.png" alt="LOGO">
    <div id="clock"></div>
</div>




<!-- VIEWER -->
<div class="viewer" id="viewer"></div>

<!-- FOOTER -->
<div class="footer">
    MEDIA CONTROL TAILING & WASTE MANAGEMENT DISPLAY SYSTEM
</div>


<div class="qr-box">
    <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=http://10.20.34.2/DSTF/control_tv.php&color=000000&bgcolor=ffffff00">
</div>


<!-- CONTROL -->
<div class="controls" id="controls">
    <button onclick="prev()">⬅</button>
    <button onclick="togglePlay()" id="playBtn">⏸</button>
    <button onclick="next()">➡</button>
</div>

<script>

let media = <?php echo json_encode($media); ?>;
let index = 0;
let viewer = document.getElementById("viewer");

let timer = null;
let isPlaying = true;

let soundEnabled = true;
let volumeLevel = 100;

let isVideoPlaying = false;

// 🔥 penting: unlock audio sekali saja
let audioUnlocked = false;

function unlockAudio(){
    audioUnlocked = true;

let video = viewer.querySelector("video");
    if(video){
        video.muted = false;
        video.volume = volumeLevel / 100;
        video.play().catch(()=>{});
    }
}  

// ================= SHOW MEDIA =================
function showMedia(i){

    let item = media[i];
    let old = viewer.firstChild;

    if(timer) clearTimeout(timer);

    let el;

    // ================= IMAGE =================
    if(item.file_type === "image"){

        isVideoPlaying = false;

        el = document.createElement("img");
        el.src = "uploads/media/" + item.file_name;

        if(isPlaying){
            timer = setTimeout(()=> next(), 60000);
        }
    }

    // ================= VIDEO =================
    else {

        isVideoPlaying = true;

        el = document.createElement("video");
        el.src = "uploads/media/" + item.file_name;

        el.playsInline = true;
        el.controls = false;
        el.preload = "auto";

        el.muted = true; // default aman autoplay

        // 🔥 delay 2 detik sebelum play
      setTimeout(() => {

    el.play().then(() => {

        if(audioUnlocked){
            el.muted = false;
            el.volume = volumeLevel / 100;
        }

    }).catch(err => {
        console.log("autoplay blocked:", err);
    });

}, 2000);

        el.onended = function(){
            if(isPlaying){
                next();
            }
        };
    }

    // ================= TRANSISI =================
    el.style.opacity = 0;
    el.style.transition = "opacity 0.8s ease";

    el.style.position = "absolute";
    el.style.top = 0;
    el.style.left = 0;

    viewer.appendChild(el);

    requestAnimationFrame(()=> el.style.opacity = 1);

    if(old){
        old.style.opacity = 0;
        setTimeout(()=> old.remove(), 800);
    }
}

// ================= NEXT =================
function next(){

    if(timer) clearTimeout(timer);

    index++;

    if(index >= media.length){
        index = 0;
    }

    showMedia(index);
}

// ================= PREV =================
function prev(){
    index--;
    if(index < 0) index = media.length - 1;
    showMedia(index);
}

// ================= PLAY / PAUSE =================
function togglePlay(){

    isPlaying = !isPlaying;

    document.getElementById("playBtn").innerText =
        isPlaying ? "⏸ Pause" : "▶ Play";

    if(!isPlaying){
        clearTimeout(timer);
        return;
    }

    showMedia(index);
}

// ================= START =================
showMedia(index);

</script>

<script>
// 🔥 unlock audio saat user interaksi pertama
document.addEventListener("click", function(){
    unlockAudio();

    let video = viewer.querySelector("video");
    if(video){
        video.muted = false;
        video.volume = volumeLevel / 100;
    }
}, { once: true });
</script>

<!-- CLOCK -->
<script>
function updateClock(){
    const now = new Date();
    const wit = new Date(now.toLocaleString("en-US",{timeZone:"Asia/Jayapura"}));

    const hari = ["Minggu","Senin","Selasa","Rabu","Kamis","Jumat","Sabtu"];

    let h = hari[wit.getDay()];
    let d = String(wit.getDate()).padStart(2,'0');
    let m = String(wit.getMonth()+1).padStart(2,'0');
    let y = wit.getFullYear();

    let hh = String(wit.getHours()).padStart(2,'0');
    let mm = String(wit.getMinutes()).padStart(2,'0');
    let ss = String(wit.getSeconds()).padStart(2,'0');

    document.getElementById("clock").innerHTML =
        `${h}, ${d}-${m}-${y} ${hh}:${mm}:${ss} WIT`;
}

setInterval(updateClock,1000);
updateClock();
</script>

<!-- AUTO FULLSCREEN -->
<script>
document.addEventListener("DOMContentLoaded", function () {




function openFS(){
    let el = document.documentElement;

    if (el.requestFullscreen) {
        el.requestFullscreen({
            navigationUI: "hide"
        });
    }
}



setTimeout(() => {
    document.documentElement.requestFullscreen().catch(()=>{});
}, 1000);
});
</script>

<!-- AUTO UPDATE DATABASE -->
<script>
function reloadMedia(){

    fetch('load_media.php?_=' + new Date().getTime())
        .then(res => res.json())
        .then(data => {

            media = data;
            index = 0;
            lastId = media.length > 0 ? media[media.length - 1].id : 0;

            showMedia(index);
        });
}

function checkUpdate(){

    fetch('cek_media_update.php?_=' + new Date().getTime())
        .then(res => res.text())
        .then(id => {

            id = parseInt(id);

            if(id > lastId){
                let lastId = media.length > 0 ? media[media.length - 1].id : 0;
                reloadMedia();
            }
        });
}

setInterval(checkUpdate, 3000);
</script>


<script>
// ================= AUTO HIDE CONTROLS =================
let controls = document.getElementById("controls");
let hideTimer = null;

function showControls(){
    controls.classList.add("show");

    clearTimeout(hideTimer);
    hideTimer = setTimeout(()=>{
        controls.classList.remove("show");
    }, 2000); // hilang 2 detik
}

// tampil saat mouse bergerak
document.addEventListener("mousemove", showControls);
document.addEventListener("touchstart", showControls);

// tampil pertama kali
showControls();
</script>




<script>
document.addEventListener("click", function enterFS() {
    let el = document.documentElement;

    if (el.requestFullscreen) {
        el.requestFullscreen();
    } else if (el.webkitRequestFullscreen) {
        el.webkitRequestFullscreen();
    }
     unlockAudio();

    // hapus listener setelah sekali klik
    document.removeEventListener("click", enterFS);
});
</script>

<script>
let lastTime = 0;

setInterval(()=>{

    fetch("remote.txt?_=" + Date.now())
    .then(r => r.json())
    .then(data => {

        if(!data) return;

        // ❗ kalau sama, skip
        if(data.time === lastTime) return;

        lastTime = data.time;

        let cmd = data.cmd;

        if(cmd === "next") next(true);
        else if(cmd === "prev") prev();
        else if(cmd === "toggle") togglePlay();
        else if(cmd === "mute") toggleMute();
    });

}, 1000);

window.onload = function(){
    showMedia(index);

    // 🔥 auto unlock audio setelah user interaction
    document.body.addEventListener("click", enableAudioOnce, { once: true });
};

function enableAudioOnce(){
    let video = viewer.querySelector("video");
    if(video){
        video.muted = false;
        video.volume = volumeLevel / 100;
    }
}
</script>

<script>


let soundEnabled = true;
let soundIcon = document.getElementById("soundIcon");
function toggleMute(){

    soundEnabled = !soundEnabled;

    let video = viewer.querySelector("video");

    if(video){
        video.muted = !soundEnabled;
        video.volume = soundEnabled ? volumeLevel / 100 : 0;
    }

    updateSoundIcon();
}

function updateSoundIcon(){

    soundIcon.innerHTML = soundEnabled ? "🔊" : "🔇";
}

let hideIconTimer = null;

function showSoundIcon(){
    soundIcon.style.opacity = 1;

    clearTimeout(hideIconTimer);

    hideIconTimer = setTimeout(()=>{
        soundIcon.style.opacity = 0;
    }, 2000);
}

document.addEventListener("mousemove", showSoundIcon);
document.addEventListener("touchstart", showSoundIcon);
updateSoundIcon();
</script>


</body>
</html>
