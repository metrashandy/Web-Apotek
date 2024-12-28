<?php
session_start();

// Validasi login dan role user
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'user') {
    die("Anda harus login terlebih dahulu.");
}

// Ambil ID pelanggan dari sesi
$id_pelanggan = $_SESSION['id_pelanggan'];

$mysqli = new mysqli("localhost", "root", "", "apotek");
if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

// Query untuk riwayat pesanan pelanggan
$result = $mysqli->query("
    SELECT 
        p.Id_pesanan AS ID_Pesanan,
        p.tanggal_pemesanan AS Tanggal_Pemesanan,
        GROUP_CONCAT(o.Nama_Obat SEPARATOR ', ') AS List_Barang,
        p.Harga_total AS Total_Harga,
        p.`Tipe Pembayaran` AS Tipe_Pembayaran,
        p.status AS Status_Pesanan,
        GROUP_CONCAT(CONCAT(o.Nama_Obat, ';', pd.jumlah_item, ';', pd.harga_satuan) SEPARATOR ', ') AS Detail_Obat
    FROM 
        tb_pesanan p
    JOIN 
        tb_pesanan_detail pd ON p.Id_pesanan = pd.Id_pesanan
    JOIN 
        tb_obat o ON pd.Id_obat = o.Id_Obat
    WHERE 
        p.Id_pelanggan = '$id_pelanggan'
    GROUP BY 
        p.Id_pesanan, p.tanggal_pemesanan, p.Harga_total, p.`Tipe Pembayaran`, p.status
");

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Belanja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Riwayat Belanja</h1>
        <table class="table table-info table-striped">
            <thead>
                <tr>
                    <th>ID PESANAN</th>
                    <th>TANGGAL PEMESANAN</th>
                    <th>LIST BARANG</th>
                    <th>STATUS</th>
                    <th>TIPE PEMBAYARAN</th>
                    <th>DETAIL</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['ID_Pesanan']) ?></td>
                        <td><?= htmlspecialchars($row['Tanggal_Pemesanan']) ?></td>
                        <td><?= htmlspecialchars($row['List_Barang']) ?></td>
                        <td><?= htmlspecialchars($row['Status_Pesanan']) ?></td>
                        <td><?= htmlspecialchars($row['Tipe_Pembayaran']) ?></td>
                        <td>
                            <button type="button" class="btn btn-info btn-sm" onclick="tampilkanRincian(<?= htmlspecialchars(json_encode($row)) ?>)">
                                Lihat Detail
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal Rincian -->
    <div class="modal fade" id="modalRincian" tabindex="-1" aria-labelledby="modalRincianLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRincianLabel">Detail Pesanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>ID Pesanan:</strong> <span id="rincianId"></span></p>
                    <p><strong>Tanggal Pemesanan:</strong> <span id="rincianTanggal"></span></p>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nama Barang</th>
                                <th>Jumlah</th>
                                <th>Harga Satuan</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="rincianItems"></tbody>
                    </table>
                    <p><strong>Total Harga:</strong> Rp. <span id="rincianTotalHarga"></span></p>
                    <p><strong>Biaya Pengiriman:</strong> Rp. 10,000</p>
                    <p><strong>Total Biaya:</strong> Rp. <span id="rincianTotalBiaya"></span></p>
                    <p><strong>Tipe Pembayaran:</strong> <span id="rincianTipePembayaran"></span></p>
                    <p><strong>Status Pesanan:</strong> <span id="rincianStatus"></span></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function tampilkanRincian(data) {
            document.getElementById("rincianId").innerText = data.ID_Pesanan;
            document.getElementById("rincianTanggal").innerText = data.Tanggal_Pemesanan;

            const items = data.Detail_Obat.split(", ");
            const tbody = document.getElementById("rincianItems");
            tbody.innerHTML = "";

            let totalHarga = 0;

            items.forEach((item) => {
                const [nama, jumlah, harga] = item.split(";");
                const subtotal = jumlah * harga;
                totalHarga += subtotal;

                const row = `<tr>
                    <td>${nama}</td>
                    <td>${jumlah}</td>
                    <td>Rp. ${parseInt(harga).toLocaleString('id-ID')}</td>
                    <td>Rp. ${subtotal.toLocaleString('id-ID')}</td>
                </tr>`;
                tbody.innerHTML += row;
            });

            const biayaPengiriman = 10000;
            document.getElementById("rincianTotalHarga").innerText = totalHarga.toLocaleString('id-ID');
            document.getElementById("rincianTotalBiaya").innerText = (totalHarga + biayaPengiriman).toLocaleString('id-ID');
            document.getElementById("rincianTipePembayaran").innerText = data.Tipe_Pembayaran;
            document.getElementById("rincianStatus").innerText = data.Status_Pesanan;

            const modal = new bootstrap.Modal(document.getElementById("modalRincian"));
            modal.show();
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
