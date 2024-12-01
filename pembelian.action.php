<?php
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
$Id_obat = $_POST['Id_obat'] ?? [];
$tanggal_kadarluarsa = $_POST['tanggal_kadarluarsa'] ?? [];
$jumlah_item = $_POST['jumlah_item'] ?? [];
$harga_satuan = $_POST['harga_satuan'] ?? [];

// Periksa mode berdasarkan action
if ($action === 'add' || $action === 'edit') {
    if ($action === 'add') {
        // Tambah pembelian baru
        $queryPembelian = "INSERT INTO tb_pembelian (tanggal_pembelian, Id_suplier, total_item, total_harga) 
                           VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($queryPembelian);
        $stmt->bind_param("ssii", $tanggal_pembelian, $Id_suplier, $total_item, $total_harga);

        if ($stmt->execute()) {
            $Id_pembelian = $stmt->insert_id;
        } else {
            die("Gagal menyimpan data pembelian: " . $conn->error);
        }
    } elseif ($action === 'edit') {
        // Perbarui pembelian
        $queryPembelian = "UPDATE tb_pembelian 
                           SET tanggal_pembelian = ?, Id_suplier = ?, total_item = ?, total_harga = ? 
                           WHERE Id_pembelian = ?";
        $stmt = $conn->prepare($queryPembelian);
        $stmt->bind_param("ssiii", $tanggal_pembelian, $Id_suplier, $total_item, $total_harga, $Id_pembelian);

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
    $Id_obat = $_POST['Id_obat'] ?? null;

    if ($Id_pembelian && $Id_obat) {
        // Hapus detail pembelian
        $query = "DELETE FROM tb_pembelian_detail WHERE Id_pembelian = ? AND Id_obat = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $Id_pembelian, $Id_obat);

        if (!$stmt->execute()) {
            die("Gagal menghapus detail pembelian: " . $conn->error);
        }
    }
}

header('Location: index.php?page=pembelian');
exit();
