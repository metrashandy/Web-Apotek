<?php

if ($_POST['action'] == 'add') {                //jika mode "add"
    $id = $_POST['Id_Obat'];
    $nama = isset($_POST['Nama_Obat']) ? $_POST['Nama_Obat'] : '';
    $jenis = isset($_POST['Id_jenis']) ? $_POST['Id_jenis'] : '';
    $stok = isset($_POST['Stok_obat']) ? $_POST['Stok_obat'] : '';
    $harga = isset($_POST['Harga_satuan']) ? $_POST['Harga_satuan'] : '';

    $foto = isset($_FILES['foto_obat']) ? $_FILES['foto_obat'] : null;
    if ($foto && $foto['size'] > 1048576) {
        die("Error: Ukuran file gambar tidak boleh lebih dari 1 MB.");
    }

    $fotoData = $foto ? addslashes(file_get_contents($foto['tmp_name'])) : null;

    $mysqli = new mysqli("localhost", "root", "", "apotek");
    $query = "INSERT INTO tb_obat (Id_Obat, Nama_Obat, Stok_obat, Harga_satuan, foto_obat, Id_jenis) VALUES('" . $id . "', '" . $nama . "', '" . $stok . "', '" . $harga . "', '" . $fotoData . "', '" . $jenis . "');";

    $result = $mysqli->query($query);

    if (!$result) {
        die("Error: " . $mysqli->error);
    }

} else if ($_POST['action'] == 'edit') {
    $id = $_POST['Id_Obat'];
    $nama = isset($_POST['Nama_Obat']) ? $_POST['Nama_Obat'] : '';
    $jenis = isset($_POST['Id_jenis']) ? $_POST['Id_jenis'] : '';
    $stok = isset($_POST['Stok_obat']) ? $_POST['Stok_obat'] : '';
    $harga = isset($_POST['Harga_satuan']) ? $_POST['Harga_satuan'] : '';

    $foto = isset($_FILES['foto_obat']) ? $_FILES['foto_obat'] : null;
    $fotoData = null;

    if ($foto && $foto['size'] > 0) {
        if ($foto['size'] > 1048576) {
            die("Error: Ukuran file gambar tidak boleh lebih dari 1 MB.");
        }
        $fotoData = addslashes(file_get_contents($foto['tmp_name']));
    }

    $mysqli = new mysqli("localhost", "root", "", "apotek");

    if ($fotoData) {
        $query = "UPDATE tb_obat SET Nama_Obat='" . $nama . "', Stok_obat='" . $stok . "', Harga_satuan='" . $harga . "', Id_jenis='" . $jenis . "', foto_obat='" . $fotoData . "' WHERE Id_Obat='" . $id . "';";
    } else {
        $query = "UPDATE tb_obat SET Nama_Obat='" . $nama . "', Stok_obat='" . $stok . "', Harga_satuan='" . $harga . "', Id_jenis='" . $jenis . "' WHERE Id_Obat='" . $id . "';";
    }

    $result = $mysqli->query($query);

    if (!$result) {
        die("Error: " . $mysqli->error);
    }

} else if ($_POST['action'] == 'delete') {
    $id = $_POST['Id_Obat'];

    $mysqli = new mysqli("localhost", "root", "", "apotek");
    $query = "DELETE FROM tb_obat WHERE Id_Obat ='" . $id . "';";

    $result = $mysqli->query($query);

    if (!$result) {
        die("Error: " . $mysqli->error);
    }
}

header('Location: index.php?page=obat');
exit();
