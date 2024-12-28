<?php

$mysqli = new mysqli("localhost", "root", "", "apotek");

$action = $_GET['action'];
$currentid = "";
$nama = "";
$jenis = "";
$stok = "";
$harga = "";
$foto = "";

if ($action == "add") {
    $result = $mysqli->query("SELECT MAX(Id_Obat) AS currentid FROM tb_obat;");

    if (!$result) {
        die("Error: " . $mysqli->error);
    }

    $row = $result->fetch_assoc(); //khusus single result
    $max_number = (int)$row['currentid'];
    $new_number = $max_number + 1;

    $currentid = $new_number; //tambahkan dengan 1 untuk id baru
} else if ($action == "edit") {
    $currentid = $_GET['Id_Obat'];
    $query = "SELECT * FROM tb_obat WHERE Id_Obat = '" . $currentid . "';";

    $result = $mysqli->query($query);
    $row = $result->fetch_assoc(); //khusus single result

    $nama = $row['Nama_Obat'];
    $jenis = $row['Id_jenis'];
    $stok = $row['Stok_obat'];
    $harga = $row['Harga_satuan'];
    $foto = $row['foto_obat'];
}

?>
<div class="col" style="padding-top: 20px;">
    <form action="obat.action.php" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="id" class="form-label">KODE OBAT</label>
            <input type="text" class="form-control" name="Id_Obat" id="Id_Obat" readonly value="<?php echo $currentid; ?>">
        </div>
        <div class="mb-3">
            <label for="nama" class="form-label">NAMA OBAT</label>
            <input type="text" class="form-control" name="Nama_Obat" id="Nama_Obat" placeholder="Tuliskan nama di sini" value="<?php echo $nama; ?>">
        </div>
        <div class="form-group">
            <label for="id_jenis" class="form-label">Jenis Obat</label>
            <select class="form-control" name="Id_jenis" id="Jenis_obat">
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
            <label for="stok" class="form-label">STOK OBAT</label>
            <input type="number" class="form-control" name="Stok_obat" id="Stok_obat" placeholder="Tuliskan stok di sini" value="<?php echo $stok; ?>">
        </div>
        <div class="mb-3">
            <label for="harga" class="form-label">HARGA SATUAN</label>
            <input type="number" class="form-control" name="Harga_satuan" id="Harga_satuan" placeholder="Tuliskan harga di sini" value="<?php echo $harga; ?>">
        </div>
        <div class="mb-3">
            <label for="foto" class="form-label">FOTO OBAT</label>
            <input type="file" class="form-control" name="foto_obat" id="foto_obat">
            <?php if ($action == "edit" && $foto) { ?>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($foto); ?>" alt="Foto Obat" style="width: 100px; height: auto; margin-top: 10px;">
            <?php } ?>
        </div>
        <input type="hidden" name="action" value="<?php echo $action; ?>">
        <div class="d-grid gap-2 p-2 d-md-flex justify-content-md-end">
            <button class="btn btn-primary" type="submit">Simpan</button>
        </div>
    </form>
</div>
