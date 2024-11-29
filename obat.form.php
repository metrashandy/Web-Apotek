<?php

$mysqli = new mysqli("localhost", "root", "", "apotek");

$action = $_GET['action'];
$currentid = "";
$nama = "";
$alamat = "";

if ($action == "add") {
    $result = $mysqli->query("SELECT MAX(Id_Obat) AS currentid FROM tb_obat;");

    if (!$result) {
        die("Error: " . $mysqli->error);
    }

    $row = $result->fetch_assoc(); //khusus single result
    $max_number = (int)$row['currentid'];
    $new_number = $max_number + 1;

    $currentid = $new_number; //tambahkan dengan 1 untuk id baru
    $nama = "";
    $jenis = "";
    $stok = "";
    $harga = "";
} else if ($action == "edit") {
    $currentid = $_GET['Id_Obat'];
    $query = "SELECT * FROM tb_obat WHERE Id_Obat = '" . $currentid . "';";

    //print_r($query);
    $result = $mysqli->query($query);
    $row = $result->fetch_assoc(); //khusus single result

    //print_r($row);
    $nama = $row['Nama_Obat'];
    $jenis = $row['Id_jenis'];
    $stok = $row['Stok_obat'];
    $harga = $row['Harga_satuan'];
}

?>
<div class="col" style="padding-top: 20px;">
    <form action="obat.action.php" method="POST">
        <div class="mb-3">
            <label for="id" class="form-label">KODE OBAT</label>
            <input type="text" class="form-control" name="Id_Obat" id="NIP" placeholder="NIP" value="<?php echo $currentid; ?>">
        </div>
        <div class="mb-3">
            <label for="nama" class="form-label">NAMA OBAT</label>
            <input type="text" class="form-control" name="Nama_Obat" id="nama" placeholder="Tuliskan nama di sini" value="<?php echo $nama; ?>">
        </div>
        <div class="form-group">
            <label for="id_jenis" class="form-label">Jenis Obat</label>
            <select class="form-control" name="Id_jenis" id="Jenis_obat" placeholder="Pilih jenis obat di sini">
                <?php
                
                $query = "SELECT * FROM tb_jenis_obat ORDER BY Id_jenis";
                $result = mysqli_query($mysqli, $query);

                
                while ($hasil = mysqli_fetch_array($result)) {
                    $selected = ($hasil['Id_jenis'] == $jenis) ? "selected" : "";
                    echo "<option value='" . $hasil['Id_jenis'] . "' $selected>"
                        . $hasil['nama_jenis'] . " - " . $hasil['bentuk_obat']
                        . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="nama" class="form-label">STOK OBAT</label>
            <input type="text" class="form-control" name="Stok_obat" id="Stok_obat" placeholder="Tuliskan stok di sini" value="<?php echo $stok; ?>">
        </div>
        <div class="mb-3">
            <label for="nama" class="form-label">HARGA SATUAN</label>
            <input type="text" class="form-control" name="Harga_satuan" id="nama" placeholder="Tuliskan harga di sini" value="<?php echo $harga; ?>">
        </div>
        <input type="hidden" name="action" value="<?php echo $action; ?>">
        <div class="d-grid gap-2 p-2 d-md-flex justify-content-md-end">
            <button class="btn btn-primary" type="submit">Simpan</button>
        </div>
    </form>
</div>