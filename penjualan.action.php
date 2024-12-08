<?php
// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "apotek");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
// Ambil data dari form
$action = $_POST['action'] ?? null;
$Id_pesanan = $_POST['Id_pesanan'] ?? null;

// Logika tambahan untuk memproses pesanan ke penjualan
if ($action === 'process' && $Id_pesanan) {
    $conn->begin_transaction();
    try {
        // Ambil data pesanan
        $queryPesanan = "SELECT * FROM tb_pesanan WHERE Id_pesanan = ?";
        $stmt = $conn->prepare($queryPesanan);
        $stmt->bind_param("s", $Id_pesanan);
        $stmt->execute();
        $pesanan = $stmt->get_result()->fetch_assoc();

        if (!$pesanan) {
            throw new Exception("Pesanan tidak ditemukan.");
        }

        // Tambahkan ke tabel tb_penjualan
        $queryPenjualan = "INSERT INTO tb_penjualan (Tanggal_penjualan, Id_pelanggan, Total_item, harga_total) 
                           VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($queryPenjualan);
        $stmt->bind_param(
            "siii",
            $pesanan['tanggal_pemesanan'],
            $pesanan['Id_pelanggan'],
            $pesanan['Total_item'],
            $pesanan['Harga_total']
        );
        $stmt->execute();
        $Id_penjualan = $stmt->insert_id;

        // Ambil detail pesanan
        $queryDetail = "SELECT * FROM tb_pesanan_detail WHERE Id_pesanan = ?";
        $stmt = $conn->prepare($queryDetail);
        $stmt->bind_param("s", $Id_pesanan);
        $stmt->execute();
        $resultDetail = $stmt->get_result();

        // Tambahkan detail penjualan
        $queryInsertDetail = "INSERT INTO tb_penjualan_detail (Id_penjualan, Id_obat, jumlah_item, harga_satuan) 
                              VALUES (?, ?, ?, ?)";
        $stmtInsertDetail = $conn->prepare($queryInsertDetail);

        while ($detail = $resultDetail->fetch_assoc()) {
            $stmtInsertDetail->bind_param(
                "iiii",
                $Id_penjualan,
                $detail['Id_obat'],
                $detail['jumlah_item'],
                $detail['harga_satuan']
            );
            $stmtInsertDetail->execute();

            // Kurangi stok obat
            $queryUpdateStok = "UPDATE tb_obat SET Stok_obat = Stok_obat - ? WHERE Id_Obat = ?";
            $stmtUpdateStok = $conn->prepare($queryUpdateStok);
            $stmtUpdateStok->bind_param("ii", $detail['jumlah_item'], $detail['Id_obat']);
            $stmtUpdateStok->execute();
        }

        // Update status pesanan menjadi SELESAI
        $queryUpdatePesanan = "UPDATE tb_pesanan SET status = 'SELESAI' WHERE Id_pesanan = ?";
        $stmt = $conn->prepare($queryUpdatePesanan);
        $stmt->bind_param("s", $Id_pesanan);
        $stmt->execute();

        $conn->commit();
        header('Location: index.php?page=pesanan'); // Redirect kembali ke halaman pesanan
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        die("Gagal memproses pesanan: " . $e->getMessage());
    }
}

// Ambil data dari form
$action = $_POST['action'] ?? null;
$Id_penjualan = $_POST['Id_penjualan'] ?? null;
$Tanggal_penjualan = $_POST['Tanggal_penjualan'] ?? null;
$Id_pelanggan = $_POST['Id_pelanggan'] ?? null;
$total_item = $_POST['Total_item'] ?? 0;
$total_harga = $_POST['harga_total'] ?? 0;
$Id_obat = $_POST['Id_obat'] ?? [];
$jumlah_item = $_POST['jumlah_item'] ?? [];
$harga_satuan = $_POST['harga_satuan'] ?? [];

// Periksa mode berdasarkan action
if ($action === 'add' || $action === 'edit') {
    if ($action === 'add') {
        // Tambah penjualan baru
        $queryPenjualan = "INSERT INTO tb_penjualan (Tanggal_penjualan, Id_pelanggan, Total_item, harga_total) 
                           VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($queryPenjualan);
        $stmt->bind_param("ssii", $Tanggal_penjualan, $Id_pelanggan, $total_item, $total_harga);

        if ($stmt->execute()) {
            $Id_penjualan = $stmt->insert_id;
        } else {
            die("Gagal menyimpan data penjualan: " . $conn->error);
        }
    } elseif ($action === 'edit') {
        // Perbarui penjualan
        $queryPenjualan = "UPDATE tb_penjualan 
                           SET Tanggal_penjualan = ?, Id_pelanggan = ?,Total_item = ?, harga_total = ? 
                           WHERE Id_penjualan = ?";
        $stmt = $conn->prepare($queryPenjualan);
        $stmt->bind_param("ssiii", $Tanggal_penjualan, $Id_pelanggan, $total_item, $total_harga, $Id_penjualan);

        if (!$stmt->execute()) {
            die("Gagal memperbarui data penjualan: " . $conn->error);
        }

        // Hapus detail lama
        $queryDeleteDetail = "DELETE FROM tb_penjualan_detail WHERE Id_penjualan = ?";
        $stmt = $conn->prepare($queryDeleteDetail);
        $stmt->bind_param("i", $Id_penjualan);
        $stmt->execute();
    }

    // Tambah detail penjualan
    $queryDetail = "INSERT INTO tb_penjualan_detail (Id_penjualan, Id_obat, jumlah_item, harga_satuan) 
                    VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($queryDetail);

    foreach ($Id_obat as $i => $obatId) {
        $stmt->bind_param(
            "iiii",
            $Id_penjualan,
            $obatId,
            $jumlah_item[$i],
            $harga_satuan[$i]
        );

        if (!$stmt->execute()) {
            die("Gagal menyimpan detail penjualan: " . $conn->error);
        }

        // Update stok obat
        $queryUpdateStok = "UPDATE tb_obat SET Stok_obat = Stok_obat - ? WHERE Id_obat = ?";
        $stmtUpdate = $conn->prepare($queryUpdateStok);
        $stmtUpdate->bind_param("ii", $jumlah_item[$i], $obatId);
        $stmtUpdate->execute();
    }
} elseif ($action === 'delete') {
    $Id_penjualan = $_POST['Id_penjualan'] ?? null;
    $Id_obat = $_POST['Id_obat'] ?? null;

    if ($Id_penjualan && $Id_obat) {
        // Hapus detail penjualan
        $query = "DELETE FROM tb_penjualan_detail WHERE Id_penjualan = ? AND Id_obat = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $Id_penjualan, $Id_obat);

        if (!$stmt->execute()) {
            die("Gagal menghapus detail penjualan: " . $conn->error);
        }
    }
}

header('Location: index.php?page=penjualan');
exit();
?>
