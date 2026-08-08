<?php
session_start();
include 'koneksi.php';

/* =============================
   ok LOGIN → redirect ke
   ============================= */
if(isset($_SESSION['iduser'])){
    header("Location: index.php"); // arahkan ke dashboard backend
    exit;
}

/* =============================
   PROSES LOGIN
   ============================= */
if(isset($_POST['btnSubmit'])){

  $username = mysqli_real_escape_string($koneksi, $_POST['username']);
  $pass     = md5($_POST['pass']);

  $cek   = mysqli_query($koneksi,"SELECT * FROM user WHERE user='$username' AND password='$pass'");
  $data  = mysqli_fetch_array($cek);
  $result = mysqli_num_rows($cek);

  if($result==1){
      session_regenerate_id(true);
      $_SESSION['last_activity'] = time();

      $_SESSION['user']   = $data['username'];
      $_SESSION['user2']  = $data['user'];
      $_SESSION['iduser'] = $data['id'];
      $_SESSION['level']  = $data['level'];
      $_SESSION['plan']   = $data['plan'];

      // ====== UPDATE STATUS ONLINE ======
      mysqli_query($koneksi,"
        UPDATE user 
        SET 
          is_online = 1,
          last_activity = NOW()
        WHERE id = '".$data['id']."'
      ");

      header('Location: index.php');
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
      background: linear-gradient(135deg, #6C63FF, #00C6FF);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      font-family: Arial, sans-serif;
    }
    .login-card {
      background: #fff;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.2);
      width: 100%;
      max-width: 400px;
      text-align: center;
    }
    .login-card h1 {
      margin-bottom: 1.5rem;
      font-weight: bold;
      color: #333;
    }
    .form-control {
      margin-bottom: 1rem;
      border-radius: 8px;
      padding: 0.75rem;
    }
    .btn-login {
      width: 100%;
      padding: 0.75rem;
      border-radius: 8px;
      font-weight: bold;
      background: #6C63FF;
      border: none;
      color: #fff;
      transition: 0.3s;
    }
    .btn-login:hover {
      background: #574bff;
    }
    .alert-login {
      margin-top: 1rem;
      color: red;
    }

  </style>


</head>
<body>

  <div class="login-card">
    <h1>Welcome</h1>

    <form method="post" action="">
      <input type="text" name="username" class="form-control" placeholder="Email/Username" required autofocus>
      <input type="password" name="pass" class="form-control" placeholder="Password" required>
      <input type="submit" name="btnSubmit" class="btn btn-login" value="Login">
    </form>

<p><a href="../index.php"> <<< Kembali </a></p>
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




