<div class="d-grid gap-2 p-2 d-md-flex justify-content-md">
    <a class="btn btn-primary me-md-2" href="index.php?page=penjualan.form&action=add" role="button">Tambah</a>
</div>
<script>
    function tampilkanStruk(data) {
    // Isi data ke modal
    document.getElementById("strukId").innerText = data.ID_penjualan;
    document.getElementById("strukPelanggan").innerText = data.pelanggan;
    document.getElementById("strukTanggal").innerText = data.tanggal;

    // Parse rincian item
    const items = data.Rincian_Item ? data.Rincian_Item.split(", ") : [];
    const tbody = document.getElementById("strukItems");
    tbody.innerHTML = ""; // Kosongkan tabel

    let totalHarga = 0;

    if (items.length === 0) {
        tbody.innerHTML = "<tr><td colspan='4'>Tidak ada data</td></tr>";
    } else {
        items.forEach((item) => {
            const [nama, jumlah, harga] = item.split(";");
            const subtotal = (jumlah && harga) ? jumlah * harga : 0;
            totalHarga += subtotal;

            const row = `<tr>
                <td>${nama}</td>
                <td>${jumlah || 0}</td>
                <td>${harga || 0}</td>
                <td>${subtotal || 0}</td>
            </tr>`;
            tbody.innerHTML += row;
        });
    }

    // Masukkan Total Harga, Total Bayar, dan Kembalian ke modal
    document.getElementById("strukTotalHarga").innerText = totalHarga;
    document.getElementById("strukTotalBayar").innerText = data.Total_bayar || 0; // Total Bayar yang benar
    document.getElementById("strukKembalian").innerText = data.Kembalian || 0;

    // Tampilkan modal
    const modal = new bootstrap.Modal(document.getElementById("modalStruk"));
    modal.show();
}

</script>
<?php
// Koneksi ke database
$mysqli = new mysqli("localhost", "root", "", "apotek");
if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

// Query data penjualan
$result = $mysqli->query("
   SELECT 
    p.`Id_penjualan` AS ID_penjualan,
    p.`Tanggal_penjualan` AS tanggal,
    np.`username` AS pelanggan,
    GROUP_CONCAT(o.Nama_Obat SEPARATOR ', ') AS Daftar_Item,
    GROUP_CONCAT(CONCAT(o.Nama_Obat, ';', pd.jumlah_item, ';', pd.harga_satuan) SEPARATOR ', ') AS Rincian_Item,
    p.Total_item AS Item,
    p.`harga_total` AS Total_harga, -- Total harga barang
    p.`total_bayar` AS Total_bayar, -- Uang yang dibayarkan pelanggan
    (p.total_bayar - p.harga_total) AS Kembalian, -- Perhitungan kembalian
    pd.Id_obat AS Id_obat
FROM 
    tb_penjualan p
JOIN 
    tb_pelanggan np ON p.`Id_pelanggan` = np.`Id_pelanggan`
JOIN 
    tb_penjualan_detail pd ON p.`Id_penjualan` = pd.`Id_penjualan`
JOIN 
    tb_obat o ON pd.Id_obat = o.Id_Obat
GROUP BY 
    p.`Id_penjualan`;


");
if (!$result) {
    die("Query gagal: " . $mysqli->error);
}
?>
<div class="modal fade" id="modalStruk" tabindex="-1" aria-labelledby="modalStrukLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalStrukLabel">Struk Penjualan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Isi struk akan diisi oleh JavaScript -->
                <div id="strukContent">
                    <p><strong>ID Penjualan:</strong> <span id="strukId"></span></p>
                    <p><strong>Nama Pelanggan:</strong> <span id="strukPelanggan"></span></p>
                    <p><strong>Tanggal Penjualan:</strong> <span id="strukTanggal"></span></p>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nama Item</th>
                                <th>Jumlah</th>
                                <th>Harga Satuan</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="strukItems"></tbody>
                    </table>
                    <p><strong>Total Harga:</strong> <span id="strukTotalHarga"></span></p>
                    <p><strong>Total Bayar:</strong> <span id="strukTotalBayar"></span></p>
                    <p><strong>Kembalian:</strong> <span id="strukKembalian"></span></p>
                </div>
            </div>
        </div>
    </div>
</div>
<table class="table table-info table-striped">
    <thead>
        <tr>
            <th>ID PENJUALAN</th>
            <th>TANGGAL PENJUALAN</th>
            <th>NAMA PELANGGAN</th>
            <th>DAFTAR ITEM</th>
            <th>JUMLAH ITEM</th>
            <th>HARGA TOTAL</th>
            <th>ACTION</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($result as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['ID_penjualan']) ?></td>
                <td><?= htmlspecialchars($row['tanggal']) ?></td>
                <td><?= htmlspecialchars($row['pelanggan']) ?></td>
                <td><?= htmlspecialchars($row['Daftar_Item']) ?></td> <!-- Hanya nama barang -->
                <td><?= htmlspecialchars($row['Item']) ?></td>
                <td><?= htmlspecialchars($row['Total_bayar']) ?></td>
                <td>
                    <button type="button" class="btn btn-info btn-sm" onclick="tampilkanStruk(<?= htmlspecialchars(json_encode($row)) ?>)">
                        Tampilkan Struk
                    </button>
                    <a class="btn btn-primary me-md-2 btn-sm" href="index.php?page=penjualan.form&Id_penjualan=<?= $row['ID_penjualan'] ?>&action=edit">
                        Perbarui
                    </a>
                    <form action="penjualan.action.php" method="POST" style="display:inline;">
                        <input type="hidden" name="Id_penjualan" value="<?= $row['ID_penjualan'] ?>">
                        <input type="hidden" name="Id_obat" value="<?= $row['Id_obat'] ?>">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" class="btn btn-danger me-md-2 btn-sm" onclick="return confirm('Ingin menghapus data ini?');">
                            Hapus
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>