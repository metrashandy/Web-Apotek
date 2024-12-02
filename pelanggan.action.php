<?php

if ($_POST['action'] == 'add') {                //jika mode "add"
    $id = $_POST['Id_pelanggan'];
    $nama = isset($_POST['username']) ? $_POST['username'] : '';
    $alamat = isset($_POST['alamat']) ? $_POST['alamat'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $telepon = isset($_POST['no_tlp']) ? $_POST['no_tlp'] : '';


    $mysqli = new mysqli("localhost", "root", "", "apotek");
    $query = "INSERT INTO tb_suplier VALUES('" . $id . "', '" . $nama . "', '" . $alamat . "', '" . $email . "', '" . $no_tlp . "');";

    echo $query;
    $result = $mysqli->query($query);

    if (!$result) {
        die("Error: " . $mysqli->error);
    }

    // $mysqli->query($query);


} else if ($_POST['action'] == 'edit') {
    $id = $_POST['Id_suplier'];
    $nama = isset($_POST['Nama_suplier']) ? $_POST['Nama_suplier'] : '';
    $alamat = isset($_POST['Alamat']) ? $_POST['Alamat'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $telepon = isset($_POST['no_tlp']) ? $_POST['no_tlp'] : '';

    $mysqli = new mysqli("localhost", "root", "", "apotek");
    $query = "UPDATE tb_suplier SET Nama_suplier='" . $nama . "', Alamat='" . $alamat . "', email='" . $email . "', no_tlp='" . $telepon . "' WHERE Id_suplier='" . $id . "';";

    $result = $mysqli->query($query);
} else if ($_POST['action'] == 'delete') {
    $id = $_POST['Id_suplier'];

    $mysqli = new mysqli("localhost", "root", "", "apotek");
    $query = "DELETE FROM tb_suplier WHERE Id_suplier ='" . $id . "';";

    $result = $mysqli->query($query);
}

header('Location: index.php?page=suplier');
exit();
