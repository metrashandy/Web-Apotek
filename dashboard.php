<?php
require "koneksi.php";
require_once 'check_admin.php';
checkAdmin();

// Function to retrieve transaction data based on the selected period
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

// Validate input period
$periode = $_GET['periode'] ?? "bulanan";
$allowed_periode = ["harian", "mingguan", "bulanan", "6bulan", "tahunan"];
if (!in_array($periode, $allowed_periode)) {
    $periode = "bulanan";
}

// Main queries
$data_stok = mysqli_query($koneksi, "SELECT SUM(Stok_obat) AS total_stok FROM tb_obat");
$data_pegawai = mysqli_query($koneksi, "SELECT COUNT(NIP) AS jumlah_pegawai FROM tb_pegawai");
$data_pelanggan = mysqli_query($koneksi, "SELECT COUNT(Id_pelanggan) AS jumlah_pelanggan FROM tb_pelanggan");

// Query for items nearing expiration
$data_akan_kadaluarsa = mysqli_query($koneksi, "
    SELECT COUNT(*) AS jumlah_akan_kadaluarsa
    FROM tb_pembelian_detail
    WHERE tanggal_kadarluarsa > CURDATE()
    AND tanggal_kadarluarsa <= DATE_ADD(CURDATE(), INTERVAL 3 MONTH)
");
$jumlah_akan_kadaluarsa = mysqli_fetch_assoc($data_akan_kadaluarsa)['jumlah_akan_kadaluarsa'] ?? 0;

// Query for already expired items
$data_sudah_kadaluarsa = mysqli_query($koneksi, "
    SELECT COUNT(*) AS jumlah_sudah_kadaluarsa
    FROM tb_pembelian_detail
    WHERE tanggal_kadarluarsa <= CURDATE()
");
$jumlah_sudah_kadaluarsa = mysqli_fetch_assoc($data_sudah_kadaluarsa)['jumlah_sudah_kadaluarsa'] ?? 0;

$data_pesanan = mysqli_query($koneksi, "SELECT COUNT(Id_pesanan) AS jumlah_pesanan FROM tb_pesanan WHERE STATUS='PENDING'");

// Transaction data for sales
$data_penjualan = getTransactionData($koneksi, "tb_penjualan", $periode);
$jumlah_penjualan = $data_penjualan['jumlah_transaksi'];
$nilai_penjualan = $data_penjualan['total_nilai'];

// Transaction data for purchases
$data_pembelian = getTransactionData($koneksi, "tb_pembelian", $periode);
$jumlah_pembelian = $data_pembelian['jumlah_transaksi'];
$nilai_pembelian = $data_pembelian['total_nilai'];

// Validate main query results
$d = mysqli_fetch_assoc($data_stok) ?: ['total_stok' => 0];
$p = mysqli_fetch_assoc($data_pegawai) ?: ['jumlah_pegawai' => 0];
$n = mysqli_fetch_assoc($data_pelanggan) ?: ['jumlah_pelanggan' => 0];
$o = mysqli_fetch_assoc($data_pesanan) ?: ['jumlah_pesanan' => 0];

// Store results in variables
$total_stok = $d['total_stok'];
$total_pegawai = $p['jumlah_pegawai'];
$total_pelanggan = $n['jumlah_pelanggan'];
$jumlah_pesanan = $o['jumlah_pesanan'];
?>

<!-- Tailwind CSS CDN -->
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

<div class="p-6 space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col items-center">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">DASHBOARD ADMIN</h1>
        <hr class="w-full border-gray-300 mt-4">
    </div>

    <!-- Period Filter Form -->
    <form method="GET" class="flex justify-center items-center bg-white p-4 rounded-lg shadow-md space-x-4">
        <label for="periode" class="text-gray-700 font-medium">Pilih Periode:</label>
        <select id="periode" name="periode" class="border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-blue-600">
            <option value="harian" <?= $periode === "harian" ? "selected" : ""; ?>>Harian</option>
            <option value="mingguan" <?= $periode === "mingguan" ? "selected" : ""; ?>>Mingguan</option>
            <option value="bulanan" <?= $periode === "bulanan" ? "selected" : ""; ?>>Bulanan</option>
            <option value="6bulan" <?= $periode === "6bulan" ? "selected" : ""; ?>>6 Bulan</option>
            <option value="tahunan" <?= $periode === "tahunan" ? "selected" : ""; ?>>Tahunan</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Tampilkan</button>
    </form>

    <!-- Transaction Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Sales Transaction Card -->
        <div class="bg-white text-black rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Transaksi Penjualan</h2>
            <p class="text-3xl font-bold"><?= number_format($jumlah_penjualan); ?> transaksi</p>
            <p class="text-lg text-blue-600">Nilai: Rp <?= number_format($nilai_penjualan); ?></p>
        </div>

        <!-- Purchase Transaction Card -->
        <div class="bg-white text-black rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Transaksi Pembelian</h2>
            <p class="text-3xl font-bold"><?= number_format($jumlah_pembelian); ?> transaksi</p>
            <p class="text-lg text-blue-600">Nilai: Rp <?= number_format($nilai_pembelian); ?></p>
        </div>
    </div>

    <!-- Detailed Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        <!-- Stock Quantity Card -->
        <div class="bg-white rounded-lg shadow-md p-6 flex flex-col justify-between hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-800">Jumlah Stok</h3>
                <!-- Icon -->
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586
                          a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
            </div>
            <p class="text-3xl font-bold text-gray-700"><?= number_format($total_stok); ?></p>
            <a href="index.php?page=obat" class="mt-2 text-blue-600 hover:text-blue-800 font-medium flex items-center">
                Lihat Detail
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <!-- Employee Count Card -->
        <div class="bg-white rounded-lg shadow-md p-6 flex flex-col justify-between hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-800">Jumlah Pegawai</h3>
                <!-- Icon -->
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1
                          a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <p class="text-3xl font-bold text-gray-700"><?= number_format($total_pegawai); ?></p>
            <a href="index.php?page=pegawai" class="mt-2 text-green-600 hover:text-green-800 font-medium flex items-center">
                Lihat Detail
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <!-- Items Near Expiration Card -->
        <div class="bg-white rounded-lg shadow-md p-6 flex flex-col justify-between hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-800">Barang Akan Kadaluarsa</h3>
                <!-- Icon -->
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-3xl font-bold text-gray-700"><?= number_format($jumlah_akan_kadaluarsa); ?></p>
            <a href="index.php?page=barangAkanKadarluarsa" class="mt-2 text-yellow-600 hover:text-yellow-800 font-medium flex items-center">
                Lihat Detail
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <!-- Order Count Card -->
        <div class="bg-white rounded-lg shadow-md p-6 flex flex-col justify-between hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-800">Jumlah Pesanan</h3>
                <!-- Icon -->
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
            <p class="text-3xl font-bold text-gray-700"><?= number_format($jumlah_pesanan); ?></p>
            <a href="index.php?page=pesanan" class="mt-2 text-purple-600 hover:text-purple-800 font-medium flex items-center">
                Lihat Detail
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <!-- Customer Count Card -->
        <div class="bg-white rounded-lg shadow-md p-6 flex flex-col justify-between hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-800">Jumlah Pelanggan</h3>
                <!-- Icon -->
                <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2
                          a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016
                          0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <p class="text-3xl font-bold text-gray-700"><?= number_format($total_pelanggan); ?></p>
            <a href="index.php?page=pelanggan" class="mt-2 text-pink-600 hover:text-pink-800 font-medium flex items-center">
                Lihat Detail
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <!-- Expired Items Count Card -->
        <div class="bg-white rounded-lg shadow-md p-6 flex flex-col justify-between hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-800">Barang Sudah Kadaluarsa</h3>
                <!-- Icon -->
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-3xl font-bold text-gray-700"><?= number_format($jumlah_sudah_kadaluarsa); ?></p>
            <a href="index.php?page=barangkadarluarsa" class="mt-2 text-red-600 hover:text-red-800 font-medium flex items-center">
                Lihat Detail
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>
</div>