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
$Id_pembelian = $_POST['Id_pembelian'] ?? null;
$tanggal_pembelian = $_POST['tanggal_pembelian'] ?? null;
$Id_suplier = $_POST['Id_suplier'] ?? null;
$total_item = $_POST['total_item'] ?? null;
$total_harga = $_POST['total_harga'] ?? null;
$total_bayar = $_POST['total_bayar'] ?? null;
$kembalian = $_POST['kembalian'] ?? null;
$Id_obat = $_POST['Id_obat'] ?? [];
$tanggal_kadarluarsa = $_POST['tanggal_kadarluarsa'] ?? [];
$jumlah_item = $_POST['jumlah_item'] ?? [];
$harga_satuan = $_POST['harga_satuan'] ?? [];

// Periksa mode berdasarkan action
if ($action === 'add' || $action === 'edit') {
    // Validasi total bayar
    if ($total_bayar < $total_harga) {
        die("Total bayar tidak boleh kurang dari total harga.");
    }

    if ($action === 'add') {
        // Tambah pembelian baru
        $queryPembelian = "INSERT INTO tb_pembelian (tanggal_pembelian, Id_suplier, total_item, total_harga, total_bayar, kembalian) 
                           VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($queryPembelian);
        $stmt->bind_param("ssiiii", $tanggal_pembelian, $Id_suplier, $total_item, $total_harga, $total_bayar, $kembalian);

        if ($stmt->execute()) {
            $Id_pembelian = $stmt->insert_id;
        } else {
            die("Gagal menyimpan data pembelian: " . $conn->error);
        }
    } elseif ($action === 'edit') {
        // Perbarui pembelian
        $queryPembelian = "UPDATE tb_pembelian 
                           SET tanggal_pembelian = ?, Id_suplier = ?, total_item = ?, total_harga = ?, total_bayar = ?, kembalian = ? 
                           WHERE Id_pembelian = ?";
        $stmt = $conn->prepare($queryPembelian);
        $stmt->bind_param("ssiiiii", $tanggal_pembelian, $Id_suplier, $total_item, $total_harga, $total_bayar, $kembalian, $Id_pembelian);

        if (!$stmt->execute()) {
            die("Gagal memperbarui data pembelian: " . $conn->error);
        }

        // Hapus detail lama
        $queryDeleteDetail = "DELETE FROM tb_pembelian_detail WHERE Id_pembelian = ?";
        $stmt = $conn->prepare($queryDeleteDetail);
        $stmt->bind_param("i", $Id_pembelian);
        $stmt->execute();
    }

    // Tambah detail pembelian
    $queryDetail = "INSERT INTO tb_pembelian_detail (Id_pembelian, Id_obat, tanggal_kadarluarsa, jumlah_item, harga_satuan) 
                    VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($queryDetail);

    foreach ($Id_obat as $i => $obatId) {
        $stmt->bind_param(
            "issii",
            $Id_pembelian,
            $obatId,
            $tanggal_kadarluarsa[$i],
            $jumlah_item[$i],
            $harga_satuan[$i]
        );

        if (!$stmt->execute()) {
            die("Gagal menyimpan detail pembelian: " . $conn->error);
        }

        // Update stok obat
        $queryUpdateStok = "UPDATE tb_obat SET Stok_obat = Stok_obat + ? WHERE Id_Obat = ?";
        $stmtUpdate = $conn->prepare($queryUpdateStok);
        $stmtUpdate->bind_param("ii", $jumlah_item[$i], $obatId);
        $stmtUpdate->execute();
    }

} elseif ($action === 'delete') {
    $Id_pembelian = $_POST['Id_pembelian'] ?? null;

    if ($Id_pembelian && is_numeric($Id_pembelian)) {
        // Mulai transaksi
        $conn->begin_transaction();
        try {
            // Validasi stok obat sebelum menghapus detail pembelian
            $queryDetail = "SELECT Id_obat, jumlah_item FROM tb_pembelian_detail WHERE Id_pembelian = ?";
            $stmt = $conn->prepare($queryDetail);
            $stmt->bind_param("i", $Id_pembelian);
            $stmt->execute();
            $resultDetail = $stmt->get_result();

            while ($detail = $resultDetail->fetch_assoc()) {
                $queryCekStok = "SELECT Stok_obat FROM tb_obat WHERE Id_Obat = ?";
                $stmtCek = $conn->prepare($queryCekStok);
                $stmtCek->bind_param("i", $detail['Id_obat']);
                $stmtCek->execute();
                $resultCek = $stmtCek->get_result();
                $stokObat = $resultCek->fetch_assoc()['Stok_obat'] ?? 0;

                if ($stokObat < $detail['jumlah_item']) {
                    throw new Exception("Stok obat tidak mencukupi untuk dikembalikan.");
                }

                $queryUpdateStok = "UPDATE tb_obat SET Stok_obat = Stok_obat - ? WHERE Id_Obat = ?";
                $stmtUpdate = $conn->prepare($queryUpdateStok);
                $stmtUpdate->bind_param("ii", $detail['jumlah_item'], $detail['Id_obat']);
                $stmtUpdate->execute();
            }

            // Hapus detail pembelian
            $queryDeleteDetail = "DELETE FROM tb_pembelian_detail WHERE Id_pembelian = ?";
            $stmt = $conn->prepare($queryDeleteDetail);
            $stmt->bind_param("i", $Id_pembelian);
            $stmt->execute();

            // Hapus data pembelian
            $queryDeletePembelian = "DELETE FROM tb_pembelian WHERE Id_pembelian = ?";
            $stmt = $conn->prepare($queryDeletePembelian);
            $stmt->bind_param("i", $Id_pembelian);
            $stmt->execute();

            // Commit transaksi
            $conn->commit();
            echo "<script>alert('Data pembelian berhasil dihapus.'); window.location.href = 'index.php?page=pembelian';</script>";
        } catch (Exception $e) {
            // Rollback jika terjadi kesalahan
            $conn->rollback();
            error_log("Kesalahan: " . $e->getMessage()); // Gunakan log error untuk debugging
            echo "<script>alert('Gagal menghapus data pembelian: {$e->getMessage()}');</script>";
        }
    } else {
        echo "<script>alert('ID Pembelian tidak valid.');</script>";
    }
}


header('Location: index.php?page=pembelian');
exit();
