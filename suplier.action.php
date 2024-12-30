<?php
require_once 'check_admin.php';
checkAdmin();

// Fungsi untuk memvalidasi nomor telepon
function validatePhoneNumber($phone)
{
    return preg_match('/^\d+$/', $phone);
}

$action = $_POST['action'];
$mysqli = new mysqli("localhost", "root", "", "apotek");

// Memeriksa koneksi
if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

if ($action == 'add') { // Jika mode "add"
    $id = $_POST['Id_suplier'];
    $nama = isset($_POST['Nama_suplier']) ? $_POST['Nama_suplier'] : '';
    $alamat = isset($_POST['Alamat']) ? $_POST['Alamat'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $telepon = isset($_POST['no_tlp']) ? $_POST['no_tlp'] : '';

    // Validasi input
    if (!validatePhoneNumber($telepon)) {
        die("Error: Nomor telepon harus berupa angka.");
    }

    $query = "INSERT INTO tb_suplier (Id_suplier, Nama_suplier, Alamat, email, no_tlp) 
              VALUES ('$id', '$nama', '$alamat', '$email', '$telepon');";

    $result = $mysqli->query($query);

    if (!$result) {
        die("Error: " . $mysqli->error);
    }
} else if ($action == 'edit') { // Jika mode "edit"
    $id = $_POST['Id_suplier'];
    $nama = isset($_POST['Nama_suplier']) ? $_POST['Nama_suplier'] : '';
    $alamat = isset($_POST['Alamat']) ? $_POST['Alamat'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $telepon = isset($_POST['no_tlp']) ? $_POST['no_tlp'] : '';

    // Validasi input
    if (!validatePhoneNumber($telepon)) {
        die("Error: Nomor telepon harus berupa angka.");
    }

    $query = "UPDATE tb_suplier 
              SET Nama_suplier='$nama', Alamat='$alamat', email='$email', no_tlp='$telepon' 
              WHERE Id_suplier='$id';";

    $result = $mysqli->query($query);

    if (!$result) {
        die("Error: " . $mysqli->error);
    }
} else if ($action == 'delete') { // Jika mode "delete"
    $id = $_POST['Id_suplier'];

    $query = "DELETE FROM tb_suplier WHERE Id_suplier='$id';";

    $result = $mysqli->query($query);

    if (!$result) {
        die("Error: " . $mysqli->error);
    }
}

// Menutup koneksi
$mysqli->close();

// Redirect ke halaman utama
header('Location: index.php?page=suplier');
exit();
