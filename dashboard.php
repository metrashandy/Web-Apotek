<?php
require "koneksi.php";

// Fungsi untuk mendapatkan data transaksi berdasarkan periode
function getTransactionData($koneksi, $table, $periode)
{
    $valid_tables = [
        "tb_penjualan" => ["id_column" => "Id_penjualan", "tanggal_column" => "Tanggal_penjualan", "total_column" => "harga_total"],
        "tb_pembelian" => ["id_column" => "Id_pembelian", "tanggal_column" => "tanggal_pembelian", "total_column" => "total_harga"]
    ];

    if (!array_key_exists($table, $valid_tables)) {
        return ['jumlah_transaksi' => 0, 'total_nilai' => 0];
    }

    $id_column = $valid_tables[$table]['id_column'];
    $tanggal_column = $valid_tables[$table]['tanggal_column'];
    $total_column = $valid_tables[$table]['total_column'];

    $where_clause = "";
    switch ($periode) {
        case "harian":
            $where_clause = "DATE($tanggal_column) = CURDATE()";
            break;
        case "mingguan":
            $where_clause = "$tanggal_column BETWEEN DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY) AND CURDATE()";
            break;
        case "bulanan":
            $where_clause = "$tanggal_column BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND CURDATE()";
            break;
        case "6bulan":
            $where_clause = "$tanggal_column BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND CURDATE()";
            break;
        case "tahunan":
            $where_clause = "$tanggal_column BETWEEN DATE_FORMAT(CURDATE(), '%Y-01-01') AND CURDATE()";
            break;
        default:
            $where_clause = "$tanggal_column BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND CURDATE()";
            break;
    }

    $query = "
        SELECT COUNT($id_column) AS jumlah_transaksi, 
               SUM($total_column) AS total_nilai 
        FROM $table 
        WHERE $where_clause
    ";

    $result = mysqli_query($koneksi, $query);
    if (!$result) {
        die("Error query di tabel: $table, Periode: $periode - " . mysqli_error($koneksi));
    }

    return mysqli_fetch_assoc($result) ?: ['jumlah_transaksi' => 0, 'total_nilai' => 0];
}

// Validasi input periode
$periode = $_GET['periode'] ?? "bulanan";
$allowed_periode = ["harian", "mingguan", "bulanan", "6bulan", "tahunan"];
if (!in_array($periode, $allowed_periode)) {
    $periode = "bulanan";
}

// Query utama
$data_stok = mysqli_query($koneksi, "SELECT SUM(Stok_obat) AS total_stok FROM tb_obat");
$data_pegawai = mysqli_query($koneksi, "SELECT COUNT(NIP) AS jumlah_pegawai FROM tb_pegawai");
$data_pelanggan = mysqli_query($koneksi, "SELECT COUNT(Id_pelanggan) AS jumlah_pelanggan FROM tb_pelanggan");

// Query untuk barang akan kadaluarsa
$data_akan_kadaluarsa = mysqli_query($koneksi, "
    SELECT COUNT(*) AS jumlah_akan_kadaluarsa
    FROM tb_pembelian_detail
    WHERE tanggal_kadarluarsa > CURDATE()
    AND tanggal_kadarluarsa <= DATE_ADD(CURDATE(), INTERVAL 3 MONTH)
");
$jumlah_akan_kadaluarsa = mysqli_fetch_assoc($data_akan_kadaluarsa)['jumlah_akan_kadaluarsa'] ?? 0;

// Query untuk barang sudah kadaluarsa
$data_sudah_kadaluarsa = mysqli_query($koneksi, "
    SELECT COUNT(*) AS jumlah_sudah_kadaluarsa
    FROM tb_pembelian_detail
    WHERE tanggal_kadarluarsa <= CURDATE()
");
$jumlah_sudah_kadaluarsa = mysqli_fetch_assoc($data_sudah_kadaluarsa)['jumlah_sudah_kadaluarsa'] ?? 0;

$data_pesanan = mysqli_query($koneksi, "SELECT COUNT(Id_pesanan) AS jumlah_pesanan FROM tb_pesanan");

// Data transaksi penjualan
$data_penjualan = getTransactionData($koneksi, "tb_penjualan", $periode);
$jumlah_penjualan = $data_penjualan['jumlah_transaksi'];
$nilai_penjualan = $data_penjualan['total_nilai'];

// Data transaksi pembelian
$data_pembelian = getTransactionData($koneksi, "tb_pembelian", $periode);
$jumlah_pembelian = $data_pembelian['jumlah_transaksi'];
$nilai_pembelian = $data_pembelian['total_nilai'];

// Validasi hasil query utama
$d = mysqli_fetch_assoc($data_stok) ?: ['total_stok' => 0];
$p = mysqli_fetch_assoc($data_pegawai) ?: ['jumlah_pegawai' => 0];
$n = mysqli_fetch_assoc($data_pelanggan) ?: ['jumlah_pelanggan' => 0];
$o = mysqli_fetch_assoc($data_pesanan) ?: ['jumlah_pesanan' => 0];

// Simpan hasil ke variabel
$total_stok = $d['total_stok'];
$total_pegawai = $p['jumlah_pegawai'];
$total_pelanggan = $n['jumlah_pelanggan'];
$jumlah_pesanan = $o['jumlah_pesanan'];

?>

<div class="container dashboard">
    <h3 class="text-center">DASHBOARD</h3>
    <hr>
    <form method="GET" class="mb-4 text-center">
        <label for="periode">Pilih Periode:</label>
        <select id="periode" name="periode" class="form-select d-inline-block w-auto">
            <option value="harian" <?= $periode === "harian" ? "selected" : ""; ?>>Harian</option>
            <option value="mingguan" <?= $periode === "mingguan" ? "selected" : ""; ?>>Mingguan</option>
            <option value="bulanan" <?= $periode === "bulanan" ? "selected" : ""; ?>>Bulanan</option>
            <option value="6bulan" <?= $periode === "6bulan" ? "selected" : ""; ?>>6 Bulan</option>
            <option value="tahunan" <?= $periode === "tahunan" ? "selected" : ""; ?>>Tahunan</option>
        </select>
        <button type="submit" class="btn btn-primary">Tampilkan</button>
    </form>
    <div class="row g-3 text-white">
        <div class="col-6">
            <div class="card bg-primary">
                <div class="card-body">
                    <h5 class="card-title text-center">TRANSAKSI PENJUALAN</h5>
                    <div class="fs-5 text-center"><?= number_format($jumlah_penjualan); ?> transaksi</div>
                    <div class="fs-6 text-center">Nilai: Rp <?= number_format($nilai_penjualan); ?></div>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="card bg-secondary">
                <div class="card-body">
                    <h5 class="card-title text-center">TRANSAKSI PEMBELIAN</h5>
                    <div class="fs-5 text-center"><?= number_format($jumlah_pembelian); ?> transaksi</div>
                    <div class="fs-6 text-center">Nilai: Rp <?= number_format($nilai_pembelian); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container my-5">
    <div class="row g-3">
        <div class="col-md-4 col-sm-6">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Jumlah Stok</h5>
                    <p class="card-text fs-4"><?= number_format($total_stok); ?></p>
                    <a href="index.php?page=obat" class="text-white text-decoration-none">Lihat Detail &raquo;</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Jumlah Pegawai</h5>
                    <p class="card-text fs-4"><?= number_format($total_pegawai); ?></p>
                    <a href="index.php?page=pegawai" class="text-white text-decoration-none">Lihat Detail &raquo;</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title">Barang Akan Kadaluarsa</h5>
                    <p class="card-text fs-4"><?= number_format($jumlah_akan_kadaluarsa); ?></p>
                    <a href="index.php?page=barangAkanKadarluarsa" class="text-white text-decoration-none">Lihat Detail &raquo;</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Jumlah Pesanan</h5>
                    <p class="card-text fs-4"><?= number_format($jumlah_pesanan); ?></p>
                    <a href="index.php?page=pesanan" class="text-white text-decoration-none">Lihat Detail &raquo;</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Jumlah Pelanggan</h5>
                    <p class="card-text fs-4"><?= number_format($total_pelanggan); ?></p>
                    <a href="index.php?page=pelanggan" class="text-white text-decoration-none">Lihat Detail &raquo;</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title">Barang Sudah Kadaluarsa</h5>
                    <p class="card-text fs-4"><?= number_format($jumlah_sudah_kadaluarsa); ?></p>
                    <a href="index.php?page=barangkadarluarsa" class="text-white text-decoration-none">Lihat Detail &raquo;</a>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
