<?php
include '../koneksi.php';

$proses = $_GET['proses'];

switch($proses){

// ================= SIMPAN MULTI =================
case 'input_multi':

  $unit_code = $_POST['unit_code'];
  $unit_name = $_POST['unit_name'];
  $category  = $_POST['category'];
  $status    = $_POST['status'];

  $jumlah = count($unit_code);
  $sukses = 0;

  for($i=0;$i<$jumlah;$i++){

    if(trim($unit_code[$i])=="") continue;

    $sql = "INSERT INTO dispatch_unit_master
           (unit_code,unit_name,category,status)
           VALUES
           ('$unit_code[$i]','$unit_name[$i]','$category[$i]','$status[$i]')";

    if(mysqli_query($koneksi,$sql)) $sukses++;
  }

  echo "<script>
    alert('Berhasil simpan $sukses unit');
    window.location='../index.php?page=dispatch_unit_master&aksi=list';
  </script>";
break;


// ================= UPDATE =================
case 'update':

  $id = $_POST['id'];

  mysqli_query($koneksi,"
    UPDATE dispatch_unit_master SET
      unit_code  = '$_POST[unit_code]',
      unit_name  = '$_POST[unit_name]',
      category   = '$_POST[category]',
      status     = '$_POST[status]'
    WHERE id='$id'
  ");

  header("Location: ../index.php?page=dispatch_unit_master&aksi=list");
break;


// ================= HAPUS =================
case 'hapus':

  $id = $_GET['id'];

  mysqli_query($koneksi,"DELETE FROM dispatch_unit_master WHERE id='$id'");

  header("Location: ../index.php?page=dispatch_unit_master&aksi=list");
break;

}
?>
