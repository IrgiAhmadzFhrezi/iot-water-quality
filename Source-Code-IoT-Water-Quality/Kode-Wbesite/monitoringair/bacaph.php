<?php
$koneksi = mysqli_connect("localhost", "root", "", "monitoringair");

// Baca data terakhir dari tabel tb_ph
$sql = mysqli_query($koneksi, "SELECT * FROM sensor ORDER BY id DESC LIMIT 1");
$data = mysqli_fetch_array($sql);
$ph = $data["ph"];

if ($ph == "") $ph = 0;

echo $ph;
?>

