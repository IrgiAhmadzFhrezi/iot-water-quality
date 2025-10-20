<?php
$koneksi = new mysqli("localhost", "root", "", "monitoringair");

$nama = $_GET['nama'] ?? '';
$kecamatan = $_GET['kecamatan'] ?? '';

// Query dasar
$query = "SELECT * FROM data_sensor WHERE 1";

// Tambah filter jika ada
if (!empty($nama)) {
  $query .= " AND nama_usaha LIKE '%$nama%'";
}
if (!empty($kecamatan)) {
  $query .= " AND kecamatan = '$kecamatan'";
}

$query .= " ORDER BY tanggal DESC";

$result = $koneksi->query($query);
$data = [];

while ($row = $result->fetch_assoc()) {
  $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
?>
