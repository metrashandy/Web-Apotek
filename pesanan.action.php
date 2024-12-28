<?php
// Koneksi ke database
$koneksi = new mysqli("localhost", "root", "", "apotek");
session_start();

// Periksa apakah pelanggan sudah login
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'user') {
    die("Anda harus login terlebih dahulu.");
}

// Ambil ID pelanggan dari sesi
$id_pelanggan = $_SESSION['id_pelanggan'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_obat = $_POST['Id_Obat'] ?? [];
    $jumlah_item = $_POST['jumlah_item'] ?? [];
    $tipe_pembayaran = $_POST['tipe_pembayaran'] ?? null;
    $bukti_transfer = $_FILES['bukti_transfer'] ?? null;

    // Validasi tipe pembayaran
    $allowed_payment_types = ['COD', 'Transfer Bank Mandiri', 'Transfer Bank BNI', 'Transfer Bank BCA', 'Transfer Bank BRI'];
    if (!in_array($tipe_pembayaran, $allowed_payment_types)) {
        die("Tipe pembayaran tidak valid.");
    }

    // Validasi file bukti transfer
    if ($tipe_pembayaran !== 'COD' && $bukti_transfer && $bukti_transfer['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($bukti_transfer['type'], $allowed_types)) {
            die("Format file bukti transfer tidak valid.");
        }

        // Baca data file sebagai biner
        $file_content = file_get_contents($bukti_transfer['tmp_name']);
    } else {
        $file_content = null; // Jika tipe pembayaran COD atau tidak ada bukti transfer
    }

    $tanggal_pemesanan = date('Y-m-d');
    $koneksi->begin_transaction();

    try {
        // Buat ID pesanan unik
        $timestamp = time();
        $id_pesanan_unik = $id_pelanggan . '-' . $timestamp;

        // Hitung total item
        $total_item = array_sum($jumlah_item);

        // Simpan ke tb_pesanan
        $sqlPesanan = "INSERT INTO tb_pesanan (Id_pesanan, tanggal_pemesanan, Total_item, Harga_total, Id_pelanggan, `Tipe Pembayaran`, Bukti_transfer, status) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, 'PENDING')";
        $stmtPesanan = $koneksi->prepare($sqlPesanan);
        $stmtPesanan->bind_param("ssiiiss", $id_pesanan_unik, $tanggal_pemesanan, $total_item, $harga_total, $id_pelanggan, $tipe_pembayaran, $file_content);

        $total_harga = 0;

        // Simpan ke tb_pesanan_detail
        $sqlDetail = "INSERT INTO tb_pesanan_detail (Id_pesanan, Id_obat, jumlah_item, harga_satuan) 
                      VALUES (?, ?, ?, ?)";
        $stmtDetail = $koneksi->prepare($sqlDetail);

        foreach ($id_obat as $index => $id) {
            $jumlah = $jumlah_item[$index];

            // Ambil stok dan harga satuan obat
            $stmtStok = $koneksi->prepare("SELECT Stok_obat, harga_satuan FROM tb_obat WHERE Id_Obat = ?");
            $stmtStok->bind_param("i", $id);
            $stmtStok->execute();
            $result = $stmtStok->get_result()->fetch_assoc();

            if (!$result) {
                throw new Exception("Obat dengan ID $id tidak ditemukan.");
            }

            if ($jumlah > $result['Stok_obat']) {
                throw new Exception("Stok obat tidak mencukupi untuk ID Obat $id.");
            }

            $harga = $result['harga_satuan'];
            $total_harga += $harga * $jumlah;

            // Masukkan ke tb_pesanan_detail
            $stmtDetail->bind_param("siii", $id_pesanan_unik, $id, $jumlah, $harga);
            $stmtDetail->execute();

            // Kurangi stok obat
            $stmtUpdateStok = $koneksi->prepare("UPDATE tb_obat SET Stok_obat = Stok_obat - ? WHERE Id_Obat = ?");
            $stmtUpdateStok->bind_param("ii", $jumlah, $id);
            $stmtUpdateStok->execute();
        }

        // Simpan data pesanan utama
        $stmtPesanan->execute();

        // Update total harga di tb_pesanan
        $stmtUpdatePesanan = $koneksi->prepare("UPDATE tb_pesanan SET Harga_total = ? WHERE Id_pesanan = ?");
        $stmtUpdatePesanan->bind_param("ds", $total_harga, $id_pesanan_unik);
        $stmtUpdatePesanan->execute();

        $koneksi->commit();

        echo "Pesanan berhasil disimpan. ID Pesanan: $id_pesanan_unik. Total Harga: Rp " . number_format($total_harga, 0, ',', '.');
    } catch (Exception $e) {
        $koneksi->rollback();
        die("Pesanan gagal: " . $e->getMessage());
    }
}
