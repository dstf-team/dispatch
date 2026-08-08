<?php
session_start();
include './config/koneksi.php';

if(isset($_POST['btnSubmit'])){

  $username = mysqli_real_escape_string($koneksi_user, $_POST['username']);
  $pass     = md5($_POST['pass']);

  $cek = mysqli_query($koneksi_user,"
      SELECT * FROM user 
      WHERE user='$username' 
      AND password='$pass'
  ");

  $data   = mysqli_fetch_array($cek);
  $result = mysqli_num_rows($cek);

  if($result==1){

      $_SESSION['iduser'] = $data['id'];
      $_SESSION['user']   = $data['username'];
      $_SESSION['level']   = $data['level'];

      header("Location: app.php");
      exit;

  } else {
      $error = "Username atau Password salah";
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Halaman</title>
  <link rel="stylesheet" href="./css/bootstrap.min.css">
  <style>
   body {
  margin: 0;
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  font-family: 'Segoe UI', Tahoma, sans-serif;
  background: linear-gradient(135deg, #1e3c72, #2a5298);
  overflow: hidden;
}

/* Background effect */
body::before {
  content: "";
  position: absolute;
  width: 600px;
  height: 600px;
  background: rgba(255,255,255,0.08);
  border-radius: 50%;
  top: -200px;
  right: -200px;
  backdrop-filter: blur(20px);
}

body::after {
  content: "";
  position: absolute;
  width: 500px;
  height: 500px;
  background: rgba(255,255,255,0.05);
  border-radius: 50%;
  bottom: -200px;
  left: -200px;
}

/* Card */
.login-card {
  position: relative;
  background: rgba(255,255,255,0.15);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  padding: 40px 30px;
  width: 100%;
  max-width: 380px;
  box-shadow: 0 15px 40px rgba(0,0,0,0.3);
  text-align: center;
  animation: fadeIn 0.8s ease-in-out;
  color: #fff;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px);}
  to { opacity: 1; transform: translateY(0);}
}

.login-card h1 {
  font-size: 26px;
  margin-bottom: 25px;
  font-weight: 600;
  letter-spacing: 1px;
}

/* Input */
.form-control {
  background: rgba(255,255,255,0.2);
  border: none;
  border-radius: 10px;
  padding: 12px 15px;
  margin-bottom: 15px;
  color: #fff;
  transition: 0.3s;
}

.form-control::placeholder {
  color: rgba(255,255,255,0.7);
}

.form-control:focus {
  background: rgba(255,255,255,0.3);
  box-shadow: 0 0 0 2px rgba(255,255,255,0.4);
  outline: none;
}

/* Button */
.btn-login {
  width: 100%;
  padding: 12px;
  border-radius: 10px;
  border: none;
  background: linear-gradient(135deg, #00c6ff, #0072ff);
  font-weight: 600;
  letter-spacing: 1px;
  transition: 0.3s;
}

.btn-login:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.4);
}

/* Error */
.alert-login {
  margin-top: 15px;
  background: rgba(255,0,0,0.2);
  padding: 10px;
  border-radius: 8px;
  font-size: 14px;
}

/* Back link */
.login-card a {
  color: #fff;
  font-size: 13px;
  text-decoration: none;
  opacity: 0.8;
  transition: 0.3s;
}

.login-card a:hover {
  opacity: 1;
  text-decoration: underline;
}
  </style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

  <div class="login-card">
    <h1>Welcome</h1>

    <form method="post" action="">
      <input type="text" name="username" class="form-control" placeholder="Email/Username" required autofocus>
      <input type="password" name="pass" class="form-control" placeholder="Password" required>
      <input type="submit" name="btnSubmit" class="btn btn-login" value="Login">
    </form>


    <?php 
      if(isset($error)){
        echo '<div class="alert-login">'.$error.'</div>';
      }
    ?>
  </div>




<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

  if (!localStorage.getItem('infoLoginShown')) {

    Swal.fire({
      title: '🔐 Informasi Login',
      html: `
        <div style="text-align:left">
          <p><strong>Login default menggunakan:</strong></p>
          <ul>
            <li><b>Username :</b> NIK</li>
            <li><b>Password :</b> NIK</li>
          </ul>
          <p style="font-size:13px;color:#666">
            Silakan ubah password setelah berhasil login.
          </p>
        </div>
      `,
      icon: 'info',

      /* ===== AUTO CLOSE ===== */
      timer: 25000,                 // 5 detik
      timerProgressBar: true,
      showConfirmButton: false,    // tanpa tombol

      /* ===== OPSIONAL ===== */
      allowOutsideClick: true,
      allowEscapeKey: true,
      backdrop: true,

      didClose: () => {
        localStorage.setItem('infoLoginShown', 'yes');
      }
    });

  }

});
</script>


</body>
</html>




