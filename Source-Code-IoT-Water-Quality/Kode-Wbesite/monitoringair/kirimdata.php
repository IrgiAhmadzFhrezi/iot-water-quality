<?php
// koneksi ke database
$konek = mysqli_connect("localhost", "root", "", "monitoringair");

// baca data yang dikirim dari ESP32
$ph = $_GET['ph'];
$tds = $_GET['tds'];
$turbidity = $_GET['turbidity'];

// simpan ke tabel tb_sensor
// auto increment = 1 (jika diperlukan untuk reset ID)
 mysqli_query($konek, "ALTER TABLE sensor AUTO_INCREMENT=1");

// simpan data sensor ke tabel tb_sensor
$simpan = mysqli_query($konek, "INSERT INTO sensor(ph, tds, turbidity) VALUES('$ph', '$tds', '$turbidity')");

// uji simpan untuk memberikan respon
if ($simpan)
    echo "Berhasil dikirim";
else
    echo "Gagal terkirim";
?>
