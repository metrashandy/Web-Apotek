<?php
$mysqli = new mysqli("localhost", "root", "", "apotek");
$result = $mysqli->query("SELECT 
            p.Id_pesanan AS ID_Pesanan,
            pl.username AS Nama_Pelanggan,
            GROUP_CONCAT(o.Nama_Obat SEPARATOR ', ') AS Daftar_Obat,
            pl.alamat AS Alamat,
            pl.no_tlp AS No_Telepon
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
            p.Id_pesanan, pl.username, pl.alamat, pl.no_tlp");
?>
<table class="table table-info table-striped">
    <tr>
        <th>ID PESANAN</th>
        <th>NAMA PELANGGAN</th>
        <th>LIST BARANG</th>
        <th>ALAMAT</th>
        <th>NO TELEPON</th>
        <th>ACTION</th>
    </tr>
    <?php
    foreach ($result as $row) {
        echo "<tr>";

        echo "<td>";
        echo $row['ID_Pesanan'];
        echo "</td>";

        echo "<td>";
        echo $row['Nama_Pelanggan'];
        echo "</td>";

        echo "<td>";
        echo $row['Daftar_Obat'];
        echo "</td>";

        echo "<td>";
        echo $row['Alamat'];
        echo "</td>";

        echo "<td>";
        echo $row['No_Telepon'];
        echo "</td>";

        echo "<td>";
        echo "<form action='penjualan.action.php' method='POST' style='display:inline;'>
        <input type='hidden' name='action' value='process'>
        <input type='hidden' name='Id_pesanan' value='" . $row['ID_Pesanan'] . "'>
        <button type='submit' class='btn btn-success btn-sm'>TERKIRIM</button>
      </form>";


        echo "</tr>";
    }

    ?>
</table>