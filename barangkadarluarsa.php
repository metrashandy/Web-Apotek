<?php
$mysqli = new mysqli("localhost", "root", "", "apotek");
$result = $mysqli->query("SELECT 
    tb_pembelian_detail.`Id_pembelian`, 
    tb_obat.`Id_Obat`, 
    tb_obat.`Nama_Obat`, 
    tb_pembelian_detail.`tanggal_kadarluarsa`, 
    tb_pembelian_detail.`jumlah_item`
FROM 
    tb_pembelian_detail
JOIN 
    tb_obat 
    ON tb_obat.`Id_Obat` = tb_pembelian_detail.`Id_obat`
WHERE 
    tb_pembelian_detail.`tanggal_kadarluarsa` <= CURDATE();
");
?>
<table class="table table-info table-striped">
    <tr>
        <th>ID PEMBELIAN</th>
        <th>ID BARANG</th>
        <th>NAMA BARANG</th>
        <th>TANGGAL KADARLUARSA</th>
        <th>STOK</th>
    </tr>
    <?php
    foreach ($result as $row) {
        echo "<tr>";

        echo "<td>";
        echo $row['Id_pembelian'];
        echo "</td>";

        echo "<td>";
        echo $row['Id_Obat'];
        echo "</td>";

        echo "<td>";
        echo $row['Nama_Obat'];
        echo "</td>";

        echo "<td>";
        echo $row['tanggal_kadarluarsa'];
        echo "</td>";

        echo "<td>";
        echo $row['jumlah_item'];
        echo "</tr>";
    }

    ?>
</table>