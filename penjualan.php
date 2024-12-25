<div class="d-grid gap-2 p-2 d-md-flex justify-content-md">
    <a class="btn btn-primary me-md-2" href="index.php?page=penjualan.form&action=add" role="button">Tambah</a>
</div>

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
        p.Total_item AS Item,
        p.`harga_total` AS Harga,
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
                <td><?= htmlspecialchars($row['Daftar_Item']) ?></td>
                <td><?= htmlspecialchars($row['Item']) ?></td>
                <td><?= htmlspecialchars($row['Harga']) ?></td>
                <td>
                    <!-- Tombol Perbarui -->
                    <a class="btn btn-primary me-md-2 btn-sm" 
                       href="index.php?page=penjualan.form&Id_penjualan=<?= $row['ID_penjualan'] ?>&action=edit">
                       Perbarui
                    </a>
                    
                    <!-- Tombol Hapus -->
                    <form action="penjualan.action.php" method="POST" style="display:inline;">
                        <input type="hidden" name="Id_penjualan" value="<?= $row['ID_penjualan'] ?>">
                        <input type="hidden" name="Id_obat" value="<?= $row['Id_obat'] ?>">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" class="btn btn-danger me-md-2 btn-sm" 
                                onclick="return confirm('Ingin menghapus data ini?');">
                                Hapus
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
