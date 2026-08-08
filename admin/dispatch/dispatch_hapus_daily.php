<?php
if(session_status()==PHP_SESSION_NONE){ session_start(); }
include '../koneksi.php';

$log_date = $_GET['log_date'] ?? '';
$shift    = $_GET['shift'] ?? '';

if(!$log_date || !$shift){ die('Tanggal / shift tidak valid'); }

// hapus semua unit untuk tanggal + shift
mysqli_query($koneksi,"DELETE FROM dispatch_daily_log WHERE log_date='$log_date' AND shift='$shift'");
mysqli_query($koneksi,"DELETE FROM dispatch_breakdown_log WHERE DATE(bd_start)='$log_date' AND shift='$shift'"); // optional

echo "<script>alert('Data berhasil dihapus');location.href='index.php?page=dispatch_daily_log';</script>";
