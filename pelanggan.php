

<?php
$mysqli = new mysqli("localhost", "root", "", "apotek");
$result = $mysqli->query("SELECT * from tb_pelanggan");
?>
<table class="table table-info table-striped">
    <tr>
        <th>ID PELANGGAN</th>
        <th>NAMA PELANGGAN</th>
        <th>EMAIL</th>
        <th>NOMOR TELEPON</th>
        <th>ALAMAT</th>
    </tr>
    <?php
    foreach ($result as $row) {
        echo "<tr>";

        echo "<td>";
        echo $row['Id_pelanggan'];
        echo "</td>";

        echo "<td>";
        echo $row['username'];
        echo "</td>";

        echo "<td>";
        echo $row['email'];
        echo "</td>";

        echo "<td>";
        echo $row['no_tlp'];
        echo "</td>";

        echo "<td>";
        echo $row['alamat'];
        echo "</tr>";
    }

    ?>
</table>