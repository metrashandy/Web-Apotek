<div class="d-grid gap-2 p-2 d-md-flex justify-content-md">
    <a class="btn btn-primary me-md-2" href="index.php?page=penjualan.form&action=add" role="button">Tambah</a>
</div>

<?php
$mysqli = new mysqli("localhost", "root", "", "apotek");
$result = $mysqli->query("SELECT tb_penjualan_detail.Id_penjualan, tb_penjualan.Tanggal_penjualan,
tb_penjualan_detail.jumlah_item,tb_penjualan_detail.harga_satuan, tb_pelanggan.Nama_pelanggan 
AS pelanggan,
tb_obat.Nama_Obat AS nama_obat,tb_penjualan_detail.Id_obat 
FROM tb_penjualan
JOIN tb_pelanggan ON tb_pelanggan.Id_pelanggan = tb_penjualan.Id_pelanggan
JOIN tb_penjualan_detail ON tb_penjualan_detail.Id_penjualan= tb_penjualan.Id_penjualan
JOIN tb_obat ON tb_penjualan_detail.Id_obat = tb_obat.Id_Obat");
?>
<table class="table table-info table-striped">
    <tr>
        <th>ID PENJUALAN</th>
        <th>TANGGAL PENJUALAN</th>
        <th>NAMA OBAT</th>
        <th>JUMLAH ITEM</th>
        <th>HARGA SATUAN</th>
        <th>PELANGGAN</th>
        <th>ACTION</th>
    </tr>
    <?php
    foreach ($result as $row) {
        echo "<tr>";

        echo "<td>";
        echo $row['Id_penjualan'];
        echo "</td>";

        echo "<td>";
        echo $row['Tanggal_penjualan'];
        echo "</td>";

        echo "<td>";
        echo $row['nama_obat'];
        echo "</td>";

        echo "<td>";
        echo $row['jumlah_item'];
        echo "</td>";

        echo "<td>";
        echo $row['harga_satuan'];
        echo "</td>";

        echo "<td>";
        echo $row['pelanggan'];
        echo "</td>";

        echo "<td>";
        echo "<a class='btn btn-primary me-md-2 btn-sm' href='index.php?page=penjualan.form&Id_penjualan=" . $row['Id_penjualan'] . "&action=edit'>Perbarui</a>";

        // echo "<a class='btn btn-danger me-md-2 btn-sm' href='Pegawai.action.php?NIP=".$row['NIP']."&action=delete'>Delete</a>";
        echo "<form action='penjualan.action.php' method='POST' style ='display:inline;'>";
        echo "<input type='hidden' name='Id_penjualan' value='" . $row['Id_penjualan'] . "'>";
        echo "<input type='hidden' name='Id_obat' value='" . $row['Id_obat'] . "'>"; // Tambahkan Id_obat
        echo "<input type='hidden' name='action' value='delete'>";
        echo "<input type='hidden' name='action' value='delete'>";
        echo "<button type='submit' class='btn btn-danger me-md-2 btn-sm' onclick='return confirm(\"Ingin menghapus data ini?\");'>Hapus</button>";
        echo "</form>";
        echo "</td>";

        echo "</tr>";
    }

    ?>
</table>