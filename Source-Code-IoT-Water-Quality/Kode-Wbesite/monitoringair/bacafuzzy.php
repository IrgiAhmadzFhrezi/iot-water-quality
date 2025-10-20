<?php
$koneksi = mysqli_connect("localhost", "root", "", "monitoringair");

// Baca data terakhir dari tabel sensor (kolom fuzzy)
$sql = mysqli_query($koneksi, "SELECT * FROM sensor ORDER BY id DESC LIMIT 1");
$data = mysqli_fetch_array($sql);
$fuzzy = $data["fuzzy"]; // pastikan ada kolom bernama 'fuzzy' di tabel sensor

if ($fuzzy == "") $fuzzy = 0;

echo $fuzzy;
?>
