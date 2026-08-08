<?php

$koneksi = mysqli_connect(
    "localhost",
    "root",
    "",
    "db_dispatch"
);

if(!$koneksi){
    die("Koneksi gagal");
}

?>