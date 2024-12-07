<?php
// Koneksi ke database
include 'koneksi.php';
session_start(); // Pastikan session dimulai

// Periksa apakah pelanggan sudah login
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'user') {
    die("Anda harus login terlebih dahulu.");
}

// Ambil ID pelanggan dari sesi
$id_pelanggan = $_SESSION['id_pelanggan'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Data dari form
    $id_obat = $_POST['Id_Obat'];
    $jumlah_item = $_POST['jumlah_item'];

    // Validasi data
    if (empty($id_obat[0])) {
        die("Minimal satu obat harus diisi.");
    }

    // Tanggal pemesanan
    $tanggal_pemesanan = date('Y-m-d');

    // Simpan data ke tabel tb_pesanan
    $sqlPesanan = "INSERT INTO tb_pesanan (Id_pelanggan, tanggal_pemesanan, Total_item, Harga_total, status) 
                   VALUES (?, ?, ?, 0, 'PENDING')";
    $stmtPesanan = $conn->prepare($sqlPesanan);
    $total_item = array_sum($jumlah_item);
    $stmtPesanan->bind_param("isi", $id_pelanggan, $tanggal_pemesanan, $total_item);
    $stmtPesanan->execute();

    // Ambil ID pesanan yang baru dibuat
    $id_pesanan = $conn->insert_id;

    // Simpan data ke tabel tb_pesanan_detail
    $sqlDetail = "INSERT INTO tb_pesanan_detail (Id_pesanan, Id_obat, jumlah_item, harga_satuan) 
                  VALUES (?, ?, ?, ?)";
    $stmtDetail = $conn->prepare($sqlDetail);

    // Loop untuk setiap obat
    foreach ($id_obat as $index => $id) {
        if (!empty($id)) {
            $jumlah = $jumlah_item[$index];

            // Ambil harga satuan obat
            $result = $conn->query("SELECT harga_satuan FROM tb_obat WHERE Id_Obat = $id");
            $harga = $result->fetch_assoc()['harga_satuan'];

            // Masukkan ke detail pesanan
            $stmtDetail->bind_param("iiii", $id_pesanan, $id, $jumlah, $harga);
            $stmtDetail->execute();
        }
    }

    // Update harga total di tb_pesanan
    $result = $conn->query("SELECT SUM(jumlah_item * harga_satuan) AS total FROM tb_pesanan_detail WHERE Id_pesanan = $id_pesanan");
    $harga_total = $result->fetch_assoc()['total'];

    $conn->query("UPDATE tb_pesanan SET Harga_total = $harga_total WHERE Id_pesanan = $id_pesanan");

    echo "Pesanan berhasil disimpan dengan ID: $id_pesanan";
}
?>
