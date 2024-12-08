<?php
require "koneksi.php";

// Query untuk data utama
$data_stok = mysqli_query($koneksi, "SELECT SUM(total_item) AS total_stok FROM tb_pembelian");
$data_jenis_obat = mysqli_query($koneksi, "SELECT COUNT(DISTINCT nama_jenis) AS total_jenis_obat FROM tb_jenis_obat");
$data_pegawai = mysqli_query($koneksi, "SELECT COUNT(NIP) AS jumlah_pegawai FROM tb_pegawai");
$data_pelanggan = mysqli_query($koneksi, "SELECT COUNT(ID_PELANGGAN) AS jumlah_pelanggan FROM tb_pelanggan");
$data_pembelian = mysqli_query($koneksi, "SELECT COUNT(ID_PEMBELIAN) AS jumlah_pembelian FROM tb_pembelian");
$data_suplier = mysqli_query($koneksi, "SELECT COUNT(ID_SUPLIER) AS jumlah_suplier FROM tb_suplier");

// Ambil data dari hasil query
$d = mysqli_fetch_assoc($data_stok);
$j = mysqli_fetch_assoc($data_jenis_obat);
$p = mysqli_fetch_assoc($data_pegawai);
$n = mysqli_fetch_assoc($data_pelanggan);
$pm = mysqli_fetch_assoc($data_pembelian);
$s = mysqli_fetch_assoc($data_suplier);

// Simpan hasil ke variabel
$total_stok = $d['total_stok'] ?? 0;
$total_jenis_obat = $j['total_jenis_obat'] ?? 0;
$total_pegawai = $p['jumlah_pegawai'] ?? 0;
$total_pelanggan = $n['jumlah_pelanggan'] ?? 0;
$total_pembelian = $pm['jumlah_pembelian'] ?? 0;
$total_suplier = $s['jumlah_suplier'] ?? 0;
?>

<link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Open Sans', sans-serif;
        font-weight: bold;
    }
    .card-title {
        font-weight: bold;
        color: black;
    }
    .card-text {
        font-size: 0.85rem; /* Ukuran teks "Lihat Detail" lebih kecil */
    }
</style>
<div class="container dashboard">
        <h3 class="text-center">DASHBOARD</h3>
        <hr>
        <div class="row g-3 text-white">
            <!-- Jumlah Stok -->
            <div class="col-3">
            <div class="card bg-info">
                    <div class="card-body">
                        <h5 class="card-title">JUMLAH STOK</h5>
                        <div class="fs-5 text-center">
                            <?= number_format($total_stok); ?>
                        </div>
                        <a class="card-text text-decoration-none text-dark" href="pembelian.action.php">Lihat Detail >></a>
                    </div>
                </div>
            </div>
            <!-- Jenis Barang -->
            <div class="col-3">
            <div class="card bg-success">
                    <div class="card-body">
                        <h5 class="card-title">JENIS BARANG</h5>
                        <div class="fs-5 text-center">
                            <?= number_format($total_jenis_obat); ?>
                        </div>
                        <a class="card-text text-decoration-none text-dark" href="index.php?page=barang">Lihat Detail >></a>
                    </div>
                </div>
            </div>
            <!-- Jumlah Pegawai -->
            <div class="col-3">
            <div class="card" style="background-color: #DC3546;">
                    <div class="card-body">
                        <h5 class="card-title">JUMLAH PEGAWAI</h5>
                        <div class="fs-5 text-center">
                            <?= number_format($total_pegawai); ?>
                        </div>
                        <a class="card-text text-decoration-none text-dark" href="pegawai.action.php">Lihat Detail >></a>
                    </div>
                </div>
            </div>
            <!-- Jumlah Pesanan -->
            <div class="col-3">
            <div class="card bg-info">
                    <div class="card-body">
                        <h5 class="card-title">JUMLAH PESANAN</h5>
                        <div class="fs-5 text-center">
                            <?= number_format($total_pembelian); ?>
                        </div>
                        <a class="card-text text-decoration-none text-dark" href="penjualan.action.php">Lihat Detail >></a>
                    </div>
                </div>
            </div>
            <!-- Jumlah Pelanggan -->
            <div class="col-3">
            <div class="card bg-success">
                    <div class="card-body">
                        <h5 class="card-title">JUMLAH PELANGGAN</h5>
                        <div class="fs-5 text-center">
                            <?= number_format($total_pelanggan); ?>
                        </div>
                        <a class="card-text text-decoration-none text-dark" href="pelanggan.action.php">Lihat Detail >></a>
                    </div>
                </div>
            </div>
            <!-- Jumlah Suplier -->
            <div class="col-3">
            <div class="card" style="background-color: #DC3546;">
                    <div class="card-body">
                        <h5 class="card-title">JUMLAH SUPLIER</h5>
                        <div class="fs-5 text-center">
                            <?= number_format($total_suplier); ?>
                        </div>
                        <a class="card-text text-decoration-none text-dark" href="suplier.action.php">Lihat Detail >></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
