<div class="d-grid gap-2 p-2 d-md-flex justify-content-md-end">
      <a class="btn btn-primary me-md-2" href="index.php?page=barang.form&action=add" role="button">Tambah</a>
    </div>
    
    <?php 
      $koneksi = new mysqli("localhost", "root", "", "apotek");
      
    ?>
  <table class="table table-info table-striped">
    <tr>
        <th>KODE OBAT</th>
        <th>NAMA OBAT</th>
        <th>JENIS OBAT</th>
        <th>STOK OBAT</th>
        <th>HARGA SATUAN</th>
    </tr>  

<?php
// Query untuk mengambil data obat dan jenis obat
$query = "
    SELECT tb_obat.Id_Obat, tb_obat.Nama_Obat, tb_jenis_obat.Nama_jenis AS Jenis_obat, tb_obat.Stok_obat, tb_obat.Harga_satuan 
    FROM tb_obat
    JOIN tb_jenis_obat ON tb_obat.Id_jenis = tb_jenis_obat.Id_jenis
";
$result = mysqli_query($koneksi, $query);

if ($result && mysqli_num_rows($result) > 0) {
    // Jika ada hasil query, tampilkan dengan while
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['Id_Obat'] . "</td>";
        echo "<td>" . $row['Nama_Obat'] . "</td>";
        echo "<td>" . $row['Jenis_obat'] . "</td>";
        echo "<td>" . $row['Stok_obat'] . "</td>";
        echo "<td>" . $row['Harga_satuan'] . "</td>";
        echo "</tr>";
    }
} else {
    // Jika tidak ada data, tampilkan pesan
    echo "<tr><td colspan='5'>Tidak ada data</td></tr>";
}
?>
</table>

