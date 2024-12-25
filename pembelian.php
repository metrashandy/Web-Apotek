<div class="d-grid gap-2 p-2 d-md-flex justify-content-md">
    <a class="btn btn-primary me-md-2" href="index.php?page=pembelian.form&action=add" role="button">Tambah</a>
</div>
<script>
    function tampilkanPembelian(data) {
        document.getElementById("pembelianId").innerText = data.ID_pembelian;
        document.getElementById("pembelianSuplier").innerText = data.suplier; // Nama suplier
        document.getElementById("pembelianTanggal").innerText = data.tanggal;

        // Rincian item
        const items = data.Rincian_Item ? data.Rincian_Item.split(", ") : [];
        const tbody = document.getElementById("pembelianItems");
        tbody.innerHTML = "";

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

        document.getElementById("pembelianTotalHarga").innerText = totalHarga;
        document.getElementById("pembelianTotalBayar").innerText = data.Total_bayar || 0;
        document.getElementById("pembelianKembalian").innerText = data.Kembalian || 0;

        const modal = new bootstrap.Modal(document.getElementById("modalPembelian"));
        modal.show();
    }
</script>

<?php
// Koneksi ke database
$mysqli = new mysqli("localhost", "root", "", "apotek");
if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

// Query data pembelian
$result = $mysqli->query("
     SELECT 
        p.Id_pembelian AS ID_pembelian,
        p.tanggal_pembelian AS tanggal,
        sp.Nama_suplier AS suplier,
        GROUP_CONCAT(o.Nama_Obat SEPARATOR ', ') AS Daftar_Item,
        GROUP_CONCAT(CONCAT(o.Nama_Obat, ';', pd.jumlah_item, ';', pd.harga_satuan) SEPARATOR ', ') AS Rincian_Item,
        p.total_item AS Item,
        p.total_harga AS Harga,
        p.Total_bayar AS Total_bayar,
        p.kembalian AS Kembalian,
        pd.Id_obat AS Id_obat
    FROM 
        tb_pembelian p
    JOIN 
        tb_suplier sp ON p.Id_suplier = sp.Id_suplier
    JOIN 
        tb_pembelian_detail pd ON p.Id_pembelian = pd.Id_pembelian
    JOIN 
        tb_obat o ON pd.Id_obat = o.Id_Obat
    GROUP BY 
        p.Id_pembelian;
");
if (!$result) {
    die("Query gagal: " . $mysqli->error);
}
?>

<div class="modal fade" id="modalPembelian" tabindex="-1" aria-labelledby="modalPembelianLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPembelianLabel">Detail Pembelian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="pembelianContent">
                    <p><strong>ID Pembelian:</strong> <span id="pembelianId"></span></p>
                    <p><strong>Nama Suplier:</strong> <span id="pembelianSuplier"></span></p>
                    <p><strong>Tanggal Pembelian:</strong> <span id="pembelianTanggal"></span></p>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nama Item</th>
                                <th>Jumlah</th>
                                <th>Harga Satuan</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="pembelianItems"></tbody>
                    </table>
                    <p><strong>Total Harga:</strong> <span id="pembelianTotalHarga"></span></p>
                    <p><strong>Total Bayar:</strong> <span id="pembelianTotalBayar"></span></p>
                    <p><strong>Kembalian:</strong> <span id="pembelianKembalian"></span></p>
                </div>
            </div>
        </div>
    </div>
</div>
<table class="table table-info table-striped">
    <thead>
        <tr>
            <th>ID PEMBELIAN</th>
            <th>TANGGAL PEMBELIAN</th>
            <th>NAMA SUPLIER</th> <!-- Label kolom diperbarui -->
            <th>DAFTAR ITEM</th>
            <th>JUMLAH ITEM</th>
            <th>HARGA TOTAL</th>
            <th>ACTION</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($result as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['ID_pembelian']) ?></td>
                <td><?= htmlspecialchars($row['tanggal']) ?></td>
                <td><?= htmlspecialchars($row['suplier']) ?></td> <!-- Output nama suplier -->
                <td><?= htmlspecialchars($row['Daftar_Item']) ?></td>
                <td><?= htmlspecialchars($row['Item']) ?></td>
                <td><?= htmlspecialchars($row['Harga']) ?></td>
                <td>
                    <button type="button" class="btn btn-info btn-sm" onclick="tampilkanPembelian(<?= htmlspecialchars(json_encode($row)) ?>)">
                        Detail
                    </button>
                    <a class="btn btn-primary me-md-2 btn-sm" href="index.php?page=pembelian.form&Id_pembelian=<?= $row['ID_pembelian'] ?>&action=edit">
                        Perbarui
                    </a>
                    <form action="pembelian.action.php" method="POST" style="display:inline;">
                        <input type="hidden" name="Id_pembelian" value="<?= $row['ID_pembelian'] ?>">
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