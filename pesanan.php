<?php
$mysqli = new mysqli("localhost", "root", "", "apotek");
if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

// Query untuk daftar pesanan
$result = $mysqli->query("
    SELECT 
        p.Id_pesanan AS ID_Pesanan,
        pl.username AS Nama_Pelanggan,
        GROUP_CONCAT(o.Nama_Obat SEPARATOR ', ') AS Rincian_Obat,
        pl.alamat AS Alamat,
        pl.no_tlp AS No_Telepon,
        GROUP_CONCAT(CONCAT(o.Nama_Obat, ';', pd.jumlah_item, ';', pd.harga_satuan) SEPARATOR ', ') AS Detail_Obat,
        p.`Tipe Pembayaran` AS Tipe_Pembayaran,
        IFNULL(p.Bukti_transfer, '') AS Bukti_Transfer
    FROM 
        tb_pesanan p
    JOIN 
        tb_pelanggan pl ON p.Id_pelanggan = pl.Id_pelanggan
    JOIN 
        tb_pesanan_detail pd ON p.Id_pesanan = pd.Id_pesanan
    JOIN 
        tb_obat o ON pd.Id_obat = o.Id_Obat
    WHERE 
        p.status = 'PENDING'
    GROUP BY 
        p.Id_pesanan, pl.username, pl.alamat, pl.no_tlp, p.`Tipe Pembayaran`, p.Bukti_transfer
");
?>

<table class="table table-info table-striped">
    <thead>
        <tr>
            <th>ID PESANAN</th>
            <th>NAMA PELANGGAN</th>
            <th>LIST BARANG</th>
            <th>ALAMAT</th>
            <th>NO TELEPON</th>
            <th>TIPE PEMBAYARAN</th>
            <th>BUKTI TRANSFER</th>
            <th>ACTION</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($result as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['ID_Pesanan']) ?></td>
                <td><?= htmlspecialchars($row['Nama_Pelanggan']) ?></td>
                <td><?= htmlspecialchars($row['Rincian_Obat']) ?></td>
                <td><?= htmlspecialchars($row['Alamat']) ?></td>
                <td><?= htmlspecialchars($row['No_Telepon']) ?></td>
                <td><?= htmlspecialchars($row['Tipe_Pembayaran']) ?></td>
                <td>
                    <?php if (!empty($row['Bukti_Transfer'])): ?>
                        <a href="data:image/jpeg;base64,<?= base64_encode($row['Bukti_Transfer']) ?>" target="_blank">Lihat Bukti</a>
                    <?php else: ?>
                        Tidak Ada
                    <?php endif; ?>
                </td>
                <td>
                    <!-- Tombol Tampilkan Struk -->
                    <button type="button" class="btn btn-info btn-sm" onclick='tampilkanStruk(<?= json_encode([
                                                                                                    'ID_Pesanan' => $row['ID_Pesanan'],
                                                                                                    'Nama_Pelanggan' => $row['Nama_Pelanggan'],
                                                                                                    'Alamat' => $row['Alamat'],
                                                                                                    'Detail_Obat' => $row['Detail_Obat'],
                                                                                                    'Tipe_Pembayaran' => $row['Tipe_Pembayaran']
                                                                                                ]) ?>)'>
                        Tampilkan Struk
                    </button>

                    <!-- Tombol Konfirmasi Pesanan -->
                    <form action="penjualan.action.php" method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="process">
                        <input type="hidden" name="Id_pesanan" value="<?= $row['ID_Pesanan'] ?>">
                        <button type="submit" class="btn btn-success btn-sm">TERKIRIM</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="modal fade" id="modalStruk" tabindex="-1" aria-labelledby="modalStrukLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalStrukLabel">Struk Pesanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="strukContent">
                    <p><strong>ID Pesanan:</strong> <span id="strukId"></span></p>
                    <p><strong>Nama Pelanggan:</strong> <span id="strukPelanggan"></span></p>
                    <p><strong>Alamat:</strong> <span id="strukAlamat"></span></p>
                    <p><strong>Tipe Pembayaran:</strong> <span id="strukTipePembayaran"></span></p>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nama Barang</th>
                                <th>Jumlah</th>
                                <th>Harga Satuan</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="strukItems"></tbody>
                    </table>
                    <p><strong>Total Harga:</strong> <span id="strukTotalHarga"></span></p>
                    <p><strong>Biaya Pengiriman:</strong> Rp. 10,000</p>
                    <p><strong>Total Bayar:</strong> <span id="strukTotalBayar"></span></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function tampilkanStruk(data) {
        document.getElementById("strukId").innerText = data.ID_Pesanan;
        document.getElementById("strukPelanggan").innerText = data.Nama_Pelanggan;
        document.getElementById("strukAlamat").innerText = data.Alamat;
        document.getElementById("strukTipePembayaran").innerText = data.Tipe_Pembayaran;

        const items = data.Detail_Obat.split(", ");
        const tbody = document.getElementById("strukItems");
        tbody.innerHTML = "";

        let totalHarga = 0;

        items.forEach((item) => {
            const [nama, jumlah, harga] = item.split(";");
            const subtotal = jumlah * harga;
            totalHarga += subtotal;

            const row = `<tr>
            <td>${nama}</td>
            <td>${jumlah}</td>
            <td>${harga}</td>
            <td>${subtotal}</td>
        </tr>`;
            tbody.innerHTML += row;
        });

        const biayaPengiriman = 10000;
        document.getElementById("strukTotalHarga").innerText = `Rp. ${totalHarga}`;
        document.getElementById("strukTotalBayar").innerText = `Rp. ${totalHarga + biayaPengiriman}`;

        const modal = new bootstrap.Modal(document.getElementById("modalStruk"));
        modal.show();
    }
</script>