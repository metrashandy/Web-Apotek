<?php
require_once 'check_admin.php';
checkAdmin();

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

        // Hitung total biaya (harga total + biaya kirim)
        $biaya_pengiriman = 10000; // Tetapkan biaya pengiriman Rp 10.000
        $total_biaya = $pesanan['Harga_total'] + $biaya_pengiriman;

        // Pastikan total bayar sama dengan total biaya
        $total_bayar = $total_biaya;
        $kembalian = 0;

        // Tambahkan ke tabel tb_penjualan
        $queryPenjualan = "INSERT INTO tb_penjualan (Tanggal_penjualan, Id_pelanggan, Total_item, harga_total, biaya_kirim, total_biaya, Total_bayar, Kembalian) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($queryPenjualan);
        $stmt->bind_param(
            "siiiiiii",
            $pesanan['tanggal_pemesanan'],
            $pesanan['Id_pelanggan'],
            $pesanan['Total_item'],
            $pesanan['Harga_total'],
            $biaya_pengiriman,
            $total_biaya,
            $total_bayar,
            $kembalian
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
            // Validasi stok obat sebelum pengurangan
            $queryCekStok = "SELECT Stok_obat FROM tb_obat WHERE Id_obat = ?";
            $stmtCekStok = $conn->prepare($queryCekStok);
            $stmtCekStok->bind_param("i", $detail['Id_obat']);
            $stmtCekStok->execute();
            $resultStok = $stmtCekStok->get_result();
            $stokObat = $resultStok->fetch_assoc();

            if ($stokObat['Stok_obat'] < $detail['jumlah_item']) {
                throw new Exception("Stok obat tidak mencukupi untuk obat dengan ID " . $detail['Id_obat']);
            }

            // Tambahkan detail ke tb_penjualan_detail
            $stmtInsertDetail->bind_param(
                "iiii",
                $Id_penjualan,
                $detail['Id_obat'],
                $detail['jumlah_item'],
                $detail['harga_satuan']
            );
            $stmtInsertDetail->execute();

            // Kurangi stok obat
            $queryUpdateStok = "UPDATE tb_obat SET Stok_obat = Stok_obat - ? WHERE Id_obat = ?";
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
        header('Location: index.php?page=pesanan');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        die("Gagal memproses pesanan: " . $e->getMessage());
    }
}

// Tambah atau edit data penjualan
if ($action === 'add' || $action === 'edit') {
    $Id_penjualan = $_POST['Id_penjualan'] ?? null;
    $Tanggal_penjualan = $_POST['Tanggal_penjualan'] ?? null;
    $Id_pelanggan = $_POST['Id_pelanggan'] ?? null;
    $total_item = $_POST['Total_item'] ?? 0;
    $total_harga = $_POST['harga_total'] ?? 0;
    $total_bayar = $_POST['Total_bayar'] ?? 0;
    $Id_obat = $_POST['Id_obat'] ?? [];
    $jumlah_item = $_POST['jumlah_item'] ?? [];
    $harga_satuan = $_POST['harga_satuan'] ?? [];
    $biaya_kirim = 0;
    $total_biaya = $total_harga + $biaya_kirim;
    $kembalian = $total_bayar - $total_biaya;

    $conn->begin_transaction();
    try {
        if ($action === 'add') {
            $queryPenjualan = "INSERT INTO tb_penjualan (Tanggal_penjualan, Id_pelanggan, Total_item, harga_total, biaya_kirim, total_biaya, Total_bayar, Kembalian) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($queryPenjualan);
            $stmt->bind_param("siiiiiii", $Tanggal_penjualan, $Id_pelanggan, $total_item, $total_harga, $biaya_kirim, $total_biaya, $total_bayar, max(0, $kembalian));
            $stmt->execute();
            $Id_penjualan = $stmt->insert_id;
        } elseif ($action === 'edit') {
            $queryPenjualan = "UPDATE tb_penjualan SET Tanggal_penjualan = ?, Id_pelanggan = ?, Total_item = ?, harga_total = ?, biaya_kirim = ?, total_biaya = ?, Total_bayar = ?, Kembalian = ? 
                               WHERE Id_penjualan = ?";
            $stmt = $conn->prepare($queryPenjualan);
            $stmt->bind_param("siiiiiiii", $Tanggal_penjualan, $Id_pelanggan, $total_item, $total_harga, $biaya_kirim, $total_biaya, $total_bayar, max(0, $kembalian), $Id_penjualan);
            $stmt->execute();

            // Kembalikan stok obat sebelum menghapus detail lama
            $queryDetail = "SELECT Id_obat, jumlah_item FROM tb_penjualan_detail WHERE Id_penjualan = ?";
            $stmt = $conn->prepare($queryDetail);
            $stmt->bind_param("i", $Id_penjualan);
            $stmt->execute();
            $resultDetail = $stmt->get_result();
            while ($detail = $resultDetail->fetch_assoc()) {
                $queryUpdateStok = "UPDATE tb_obat SET Stok_obat = Stok_obat + ? WHERE Id_obat = ?";
                $stmtUpdate = $conn->prepare($queryUpdateStok);
                $stmtUpdate->bind_param("ii", $detail['jumlah_item'], $detail['Id_obat']);
                $stmtUpdate->execute();
            }

            // Hapus detail lama
            $queryDeleteDetail = "DELETE FROM tb_penjualan_detail WHERE Id_penjualan = ?";
            $stmt = $conn->prepare($queryDeleteDetail);
            $stmt->bind_param("i", $Id_penjualan);
            $stmt->execute();
        }

        // Tambah detail baru
        $queryDetail = "INSERT INTO tb_penjualan_detail (Id_penjualan, Id_obat, jumlah_item, harga_satuan) 
                        VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($queryDetail);
        foreach ($Id_obat as $i => $obatId) {
            // Validasi stok
            $queryCekStok = "SELECT Stok_obat FROM tb_obat WHERE Id_obat = ?";
            $stmtCekStok = $conn->prepare($queryCekStok);
            $stmtCekStok->bind_param("i", $obatId);
            $stmtCekStok->execute();
            $resultStok = $stmtCekStok->get_result();
            $stokObat = $resultStok->fetch_assoc();
            if ($stokObat['Stok_obat'] < $jumlah_item[$i]) {
                throw new Exception("Stok tidak mencukupi untuk obat dengan ID $obatId");
            }

            // Masukkan detail baru
            $stmt->bind_param("iiii", $Id_penjualan, $obatId, $jumlah_item[$i], $harga_satuan[$i]);
            $stmt->execute();

            // Kurangi stok obat
            $queryUpdateStok = "UPDATE tb_obat SET Stok_obat = Stok_obat - ? WHERE Id_obat = ?";
            $stmtUpdateStok = $conn->prepare($queryUpdateStok);
            $stmtUpdateStok->bind_param("ii", $jumlah_item[$i], $obatId);
            $stmtUpdateStok->execute();
        }

        $conn->commit();
        header('Location: index.php?page=penjualan');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        die("Gagal menyimpan data: " . $e->getMessage());
    }
}

// Hapus data penjualan
if ($action === 'delete') {
    $Id_penjualan = $_POST['Id_penjualan'] ?? null;

    $conn->begin_transaction();
    try {
        // Kembalikan stok obat
        $queryDetail = "SELECT Id_obat, jumlah_item FROM tb_penjualan_detail WHERE Id_penjualan = ?";
        $stmt = $conn->prepare($queryDetail);
        $stmt->bind_param("i", $Id_penjualan);
        $stmt->execute();
        $resultDetail = $stmt->get_result();
        while ($detail = $resultDetail->fetch_assoc()) {
            $queryUpdateStok = "UPDATE tb_obat SET Stok_obat = Stok_obat + ? WHERE Id_obat = ?";
            $stmtUpdate = $conn->prepare($queryUpdateStok);
            $stmtUpdate->bind_param("ii", $detail['jumlah_item'], $detail['Id_obat']);
            $stmtUpdate->execute();
        }

        // Hapus data detail
        $queryDeleteDetail = "DELETE FROM tb_penjualan_detail WHERE Id_penjualan = ?";
        $stmt = $conn->prepare($queryDeleteDetail);
        $stmt->bind_param("i", $Id_penjualan);
        $stmt->execute();

        // Hapus data penjualan
        $queryDeletePenjualan = "DELETE FROM tb_penjualan WHERE Id_penjualan = ?";
        $stmt = $conn->prepare($queryDeletePenjualan);
        $stmt->bind_param("i", $Id_penjualan);
        $stmt->execute();

        $conn->commit();
        header('Location: index.php?page=penjualan');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        die("Gagal menghapus data: " . $e->getMessage());
    }
}

header('Location: index.php?page=penjualan');
exit();
