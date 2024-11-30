<div class="d-grid gap-2 p-2 d-md-flex justify-content-md">
    <a class="btn btn-primary me-md-2" href="index.php?page=pembelian.form&action=add" role="button">Tambah</a>
</div>

<?php
$mysqli = new mysqli("localhost", "root", "", "apotek");
$result = $mysqli->query("SELECT tb_pembelian_detail.Id_pembelian, tb_pembelian.tanggal_pembelian,
tb_pembelian_detail.jumlah_item,tb_pembelian_detail.harga_satuan, tb_suplier.Nama_suplier 
AS Suplier,
tb_obat.Nama_Obat AS nama_obat,tb_pembelian_detail.Id_obat 
FROM tb_pembelian
JOIN tb_suplier ON tb_suplier.Id_suplier = tb_pembelian.Id_suplier
JOIN tb_pembelian_detail ON tb_pembelian_detail.Id_pembelian = tb_pembelian.Id_pembelian
JOIN tb_obat ON tb_pembelian_detail.`Id_obat` = tb_obat.`Id_Obat`");
?>
<table class="table table-info table-striped">
    <tr>
        <th>ID PEMBELIAN</th>
        <th>TANGGAL PEMBELIAN</th>
        <th>NAMA OBAT</th>
        <th>JUMLAH ITEM</th>
        <th>HARGA SATUAN</th>
        <th>SUPLIER</th>
        <th>ACTION</th>
    </tr>
    <?php
    foreach ($result as $row) {
        echo "<tr>";

        echo "<td>";
        echo $row['Id_pembelian'];
        echo "</td>";

        echo "<td>";
        echo $row['tanggal_pembelian'];
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
        echo $row['Suplier'];
        echo "</td>";

        echo "<td>";
        echo "<a class='btn btn-primary me-md-2 btn-sm' href='index.php?page=pembelian.form&Id_pembelian=" . $row['Id_pembelian'] . "&action=edit'>Perbarui</a>";

        // echo "<a class='btn btn-danger me-md-2 btn-sm' href='Pegawai.action.php?NIP=".$row['NIP']."&action=delete'>Delete</a>";
        echo "<form action='pembelian.action.php' method='POST' style ='display:inline;'>";
        echo "<input type='hidden' name='Id_pembelian' value='" . $row['Id_pembelian'] . "'>";
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