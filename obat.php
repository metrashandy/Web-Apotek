<div class="d-grid gap-2 p-2 d-md-flex justify-content-md">
    <a class="btn btn-primary me-md-2" href="index.php?page=obat.form&action=add" role="button">Tambah</a>
</div>

<?php
$mysqli = new mysqli("localhost", "root", "", "apotek");
$result = $mysqli->query("SELECT 
    tb_obat.Id_Obat, 
    tb_obat.Nama_Obat, 
    tb_jenis_obat.Nama_jenis AS Jenis_obat, 
    tb_obat.Stok_obat, 
    tb_obat.Harga_satuan, 
    tb_obat.foto_obat 
FROM tb_obat
JOIN tb_jenis_obat ON tb_obat.Id_jenis = tb_jenis_obat.Id_jenis");
?>
<table class="table table-info table-striped">
    <tr>
        <th>KODE OBAT</th>
        <th>NAMA OBAT</th>
        <th>JENIS OBAT</th>
        <th>STOK</th>
        <th>HARGA SATUAN</th>
        <th>FOTO</th>
        <th>ACTION</th>
    </tr>
    <?php
    foreach ($result as $row) {
        echo "<tr>";

        echo "<td>" . $row['Id_Obat'] . "</td>";
        echo "<td>" . $row['Nama_Obat'] . "</td>";
        echo "<td>" . $row['Jenis_obat'] . "</td>";
        echo "<td>" . $row['Stok_obat'] . "</td>";
        echo "<td>" . $row['Harga_satuan'] . "</td>";

        // Menampilkan gambar dari kolom foto_obat
        echo "<td>";
        if (!empty($row['foto_obat'])) {
            echo "<img src='data:image/jpeg;base64," . base64_encode($row['foto_obat']) . "' alt='Foto Obat' style='width: 100px; height: auto;'>";
        } else {
            echo "Tidak ada gambar";
        }
        echo "</td>";

        echo "<td>";
        echo "<a class='btn btn-primary me-md-2 btn-sm' href='index.php?page=obat.form&Id_Obat=" . $row['Id_Obat'] . "&action=edit'>Perbarui</a>";
        echo "<form action='obat.action.php' method='POST' style ='display:inline;'>";
        echo "<input type='hidden' name='Id_Obat' value='" . $row['Id_Obat'] . "'>";
        echo "<input type='hidden' name='action' value='delete'>";
        echo "<button type='submit' class='btn btn-danger me-md-2 btn-sm' onclick='return confirm(\"Ingin menghapus data ini?\");'>Hapus</button>";
        echo "</form>";
        echo "</td>";

        echo "</tr>";
    }
    ?>
</table>