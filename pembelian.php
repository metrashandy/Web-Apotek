<div class="d-grid gap-2 p-2 d-md-flex justify-content-md">
    <a class="btn btn-primary me-md-2" href="index.php?page=pembelian.form&action=add" role="button">Tambah</a>
</div>

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
        p.total_item AS Item,
        p.total_harga AS Harga,
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

<table class="table table-info table-striped">
    <thead>
        <tr>
            <th>ID PEMBELIAN</th>
            <th>TANGGAL PEMBELIAN</th>
            <th>NAMA SUPLIER</th>
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
                <td><?= htmlspecialchars($row['suplier']) ?></td>
                <td><?= htmlspecialchars($row['Daftar_Item']) ?></td>
                <td><?= htmlspecialchars($row['Item']) ?></td>
                <td><?= htmlspecialchars($row['Harga']) ?></td>
                <td>
                    <!-- Tombol Perbarui -->
                    <a class="btn btn-primary me-md-2 btn-sm" 
                       href="index.php?page=pembelian.form&Id_pembelian=<?= $row['ID_pembelian'] ?>&action=edit">
                       Perbarui
                    </a>
                    
                    <!-- Tombol Hapus -->
                    <form action="pembelian.action.php" method="POST" style="display:inline;">
                        <input type="hidden" name="Id_pembelian" value="<?= $row['ID_pembelian'] ?>">
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
