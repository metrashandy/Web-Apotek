<div class="d-grid gap-2 p-2 d-md-flex justify-content-md">
    <a class="btn btn-primary me-md-2" href="index.php?page=suplier.form&action=add" role="button">Tambah</a>
</div>

<?php
$mysqli = new mysqli("localhost", "root", "", "apotek");
$result = $mysqli->query("SELECT * from tb_suplier");
?>
<table class="table table-info table-striped">
    <tr>
        <th>ID SUPLIER</th>
        <th>NAMA SUPLIER</th>
        <th>ALAMAT</th>
        <th>EMAIL</th>
        <th>NOMOR TELEPON</th>
        <th>ACTION</th>
    </tr>
    <?php
    foreach ($result as $row) {
        echo "<tr>";

        echo "<td>";
        echo $row['Id_suplier'];
        echo "</td>";

        echo "<td>";
        echo $row['Nama_suplier'];
        echo "</td>";

        echo "<td>";
        echo $row['Alamat'];
        echo "</td>";

        echo "<td>";
        echo $row['email'];
        echo "</td>";

        echo "<td>";
        echo $row['no_tlp'];
        echo "</td>";

        echo "<td>";
        echo "<a class='btn btn-primary me-md-2 btn-sm' href='index.php?page=suplier.form&Id_suplier=" . $row['Id_suplier'] . "&action=edit'>Perbarui</a>";

        // echo "<a class='btn btn-danger me-md-2 btn-sm' href='Pegawai.action.php?NIP=".$row['NIP']."&action=delete'>Delete</a>";
        echo "<form action='suplier.action.php' method='POST' style ='display:inline;'>";
        echo "<input type='hidden' name='Id_suplier' value='" . $row['Id_suplier'] . "'>";
        echo "<input type='hidden' name='action' value='delete'>";
        echo "<button type='submit' class='btn btn-danger me-md-2 btn-sm' onclick='return confirm(\"Ingin menghapus data ini?\");'>Hapus</button>";
        echo "</form>";
        echo "</td>";

        echo "</tr>";
    }

    ?>
</table>