<?php
require_once 'check_admin.php';
checkAdmin();

if ($_POST['action'] == 'add') {                //jika mode "add"
    $id = $_POST['Id_pegawai'];
    $nama = isset($_POST['Nama_pegawai']) ? $_POST['Nama_pegawai'] : '';
    $telepon = isset($_POST['No_tlp']) ? $_POST['No_tlp'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $nip = isset($_POST['Nip']) ? $_POST['Nip'] : '';
    $passwd = isset($_POST['passwd']) ? $_POST['passwd'] : '';


    $mysqli = new mysqli("localhost", "root", "", "apotek");
    $query = "INSERT INTO tb_pegawai VALUES('" . $id . "', '" . $nama . "', '" . $telepon . "', '" . $email . "', '" . $nip . "', '" . $passwd . "');";

    echo $query;
    $result = $mysqli->query($query);

    if (!$result) {
        die("Error: " . $mysqli->error);
    }

    // $mysqli->query($query);


} else if ($_POST['action'] == 'edit') {
    $id = $_POST['Id_pegawai'];
    $nama = isset($_POST['Nama_pegawai']) ? $_POST['Nama_pegawai'] : '';
    $telepon = isset($_POST['No_tlp']) ? $_POST['No_tlp'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $nip = isset($_POST['Nip']) ? $_POST['Nip'] : '';
    $passwd = isset($_POST['passwd']) ? $_POST['passwd'] : '';

    $mysqli = new mysqli("localhost", "root", "", "apotek");
    $query = "UPDATE tb_pegawai SET Nama_pegawai='" . $nama . "', No_tlp='" . $telepon . "', email='" . $email . "', Nip='" . $nip . "', passwd='" . $passwd . "' WHERE Id_pegawai='" . $id . "';";

    $result = $mysqli->query($query);
} else if ($_POST['action'] == 'delete') {
    $id = $_POST['Id_pegawai'];

    $mysqli = new mysqli("localhost", "root", "", "apotek");
    $query = "DELETE FROM tb_pegawai WHERE Id_pegawai ='" . $id . "';";

    $result = $mysqli->query($query);
}

header('Location: index.php?page=pegawai');
exit();
