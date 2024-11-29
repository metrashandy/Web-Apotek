<?php

if ($_POST['action'] == 'add') {                //jika mode "add"
    $id = $_POST['Id_Obat'];
    $nama = isset($_POST['Nama_Obat']) ? $_POST['Nama_Obat'] : '';
    $jenis = isset($_POST['Id_jenis']) ? $_POST['Id_jenis'] : '';
    $stok = isset($_POST['Stok_obat']) ? $_POST['Stok_obat'] : '';
    $harga = isset($_POST['Harga_satuan']) ? $_POST['Harga_satuan'] : '';


    $mysqli = new mysqli("localhost", "root", "", "apotek");
    $query = "INSERT INTO tb_obat VALUES('" . $id . "', '" . $nama . "', '" . $stok . "', '" . $harga . "', '" . $jenis . "');";

    echo $query;
    $result = $mysqli->query($query);

    if (!$result) {
        die("Error: " . $mysqli->error);
    }

    // $mysqli->query($query);


} else if ($_POST['action'] == 'edit') {
    $id = $_POST['Id_Obat'];
    $nama = isset($_POST['Nama_Obat']) ? $_POST['Nama_Obat'] : '';
    $jenis = isset($_POST['Id_jenis']) ? $_POST['Id_jenis'] : '';
    $stok = isset($_POST['Stok_obat']) ? $_POST['Stok_obat'] : '';
    $harga = isset($_POST['Harga_satuan']) ? $_POST['Harga_satuan'] : '';

    $mysqli = new mysqli("localhost", "root", "", "apotek");
    $query = "UPDATE tb_obat SET Nama_Obat='" . $nama . "', Stok_obat='" . $stok . "', Harga_satuan='" . $harga . "', Id_jenis='" . $jenis . "' WHERE Id_Obat='" . $id . "';";

    $result = $mysqli->query($query);
} else if ($_POST['action'] == 'delete') {
    $id = $_POST['Id_Obat'];

    $mysqli = new mysqli("localhost", "root", "", "apotek");
    $query = "DELETE FROM tb_obat WHERE Id_Obat ='" . $id . "';";

    $result = $mysqli->query($query);
}

header('Location: index.php?page=obat');
exit();
