<?php
$koneksi = mysqli_connect("localhost", "root", "", "monitoringair");

// Baca data terakhir dari tabel sensor
$sql = mysqli_query($koneksi, "SELECT * FROM sensor ORDER BY id DESC LIMIT 1");
$data = mysqli_fetch_array($sql);
$turbidity = $data["turbidity"];

if ($turbidity == "") $turbidity = 0;

echo $turbidity;
?>
