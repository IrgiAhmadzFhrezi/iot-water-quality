<?php
// Koneksi ke database
$koneksi = mysqli_connect("localhost", "root", "", "monitoringair");
if (mysqli_connect_errno()) {
  die("Koneksi gagal: " . mysqli_connect_error());
}

if ($koneksi->connect_error) {
  die("Koneksi gagal: " . $koneksi->connect_error);
}

file_put_contents("debug_log.txt", print_r($_POST, true));

// Ambil data dari POST
$nama_usaha = $_POST['nama_usaha'];
$kecamatan = $_POST['kecamatan'];
$ph = $_POST['ph'];
$tds = $_POST['tds'];
$turbidity = $_POST['turbidity'];
$status_air = $_POST['status_air'];
$tanggal = date("Y-m-d H:i:s");

// Simpan ke database
$stmt = $koneksi->prepare("INSERT INTO data_sensor (nama_usaha, kecamatan, ph, tds, turbidity, status_air, tanggal) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssdddss", $nama_usaha, $kecamatan, $ph, $tds, $turbidity, $status_air, $tanggal);

if ($stmt->execute()) {
  echo "Data berhasil disimpan";
} else {
  echo "Gagal menyimpan: " . $stmt->error;
}

$stmt->close();
$koneksi->close();
?>
