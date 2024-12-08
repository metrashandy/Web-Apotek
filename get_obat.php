<?php
// Koneksi ke database
$koneksi = mysqli_connect("localhost", "root", "", "apotek");

// Periksa koneksi
if (!$koneksi) {
    die(json_encode(["error" => "Gagal terhubung ke database: " . mysqli_connect_error()]));
}

// Validasi ID dari input
$id = intval($_GET['id']);
if ($id <= 0) {
    echo json_encode(null);
    exit;
}

// Query ke database
$query = $koneksi->prepare("SELECT Id_Obat, Nama_Obat, Harga_satuan FROM tb_obat WHERE Id_Obat = ?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();

// Cek hasil query
if ($result->num_rows > 0) {
    $obat = $result->fetch_assoc();
    echo json_encode($obat);
} else {
    echo json_encode(null);
}

// Tutup koneksi
$query->close();
$koneksi->close();
?>

