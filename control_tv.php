<!DOCTYPE html>
<html>
<head>
<title>TV CONTROL</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
body{
    font-family:Arial;
    text-align:center;
    background:#111;
    color:white;
    padding-top:50px;
}

button{
    width:80%;
    padding:20px;
    margin:10px;
    font-size:20px;
    border:none;
    border-radius:10px;
}

.next{ background:green; color:white; }
.prev{ background:blue; color:white; }
.pause{ background:red; color:white; }
</style>
</head>

<body>

<h2>TV CONTROL</h2>

<button class="prev" onclick="send('prev')">⬅ Prev</button>
<button class="pause" onclick="send('toggle')">⏯ Play/Pause</button>
<button class="next" onclick="send('next')">Next ➡</button>



<script>
function send(action){
    fetch("http://10.20.34.2/remote_action.php?action=" + action)
    .then(res => res.text())
    .then(res => {
        console.log(res);
    });
}
</script>

</body>
</html>