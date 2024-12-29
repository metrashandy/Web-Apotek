<?php
require_once 'check_admin.php';
checkAdmin();

// Koneksi ke database menggunakan MySQLi
$mysqli = new mysqli("localhost", "root", "", "apotek");

// Memeriksa koneksi
if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

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

    $row = $result->fetch_assoc(); // Khusus single result
    $max_number = (int)$row['currentid'];
    $new_number = $max_number + 1;

    $currentid = $new_number; // Tambahkan dengan 1 untuk id baru
} elseif ($action == "edit") {
    $currentid = $_GET['Id_Obat'];
    $query = "SELECT * FROM tb_obat WHERE Id_Obat = '" . $currentid . "';";

    $result = $mysqli->query($query);
    $row = $result->fetch_assoc(); // Khusus single result

    $nama = $row['Nama_Obat'];
    $jenis = $row['Id_jenis'];
    $stok = $row['Stok_obat'];
    $harga = $row['Harga_satuan'];
    $foto = $row['foto_obat'];
}

// Menutup koneksi database
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($action == "add") ? "Tambah Obat" : "Edit Obat"; ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS untuk Penyesuaian -->
    <style>
        body {
            background-color: #f8f9fa;
            /* Warna latar belakang netral */
        }

        .form-wrapper {
            background-color: #ffffff;
            /* Latar belakang putih untuk konten */
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            /* Bayangan untuk kedalaman */
            max-width: 600px;
            margin: auto;
        }

        .form-header {
            margin-bottom: 20px;
            text-align: center;
        }

        .img-preview {
            margin-top: 10px;
            max-width: 150px;
            height: auto;
        }
    </style>
</head>

<body>
    <div class="container my-5">
        <!-- Pembungkus Formulir dengan Styling yang Ditingkatkan -->
        <div class="form-wrapper">
            <!-- Judul Halaman -->
            <div class="form-header">
                <h2 class="<?php echo ($action == "add") ? "text-success" : "text-primary"; ?>">
                    <?php echo ($action == "add") ? "Tambah Obat" : "Edit Obat"; ?>
                </h2>
            </div>

            <!-- Formulir Obat -->
            <form action="obat.action.php" method="POST" enctype="multipart/form-data">
                <!-- Kode Obat -->
                <div class="mb-3">
                    <label for="Id_Obat" class="form-label">Kode Obat</label>
                    <input type="text" class="form-control" name="Id_Obat" id="Id_Obat" readonly value="<?php echo htmlspecialchars($currentid); ?>">
                </div>

                <!-- Nama Obat -->
                <div class="mb-3">
                    <label for="Nama_Obat" class="form-label">Nama Obat</label>
                    <input type="text" class="form-control" name="Nama_Obat" id="Nama_Obat" placeholder="Tuliskan nama obat di sini" value="<?php echo htmlspecialchars($nama); ?>" required>
                </div>

                <!-- Jenis Obat -->
                <div class="mb-3">
                    <label for="Jenis_obat" class="form-label">Jenis Obat</label>
                    <select class="form-select" name="Id_jenis" id="Jenis_obat" required>
                        <option value="" disabled selected>Pilih Jenis Obat</option>
                        <?php
                        // Koneksi ulang untuk memuat jenis obat
                        $mysqli = new mysqli("localhost", "root", "", "apotek");
                        if ($mysqli->connect_error) {
                            die("Koneksi gagal: " . $mysqli->connect_error);
                        }

                        $query = "SELECT * FROM tb_jenis_obat ORDER BY Id_jenis";
                        $result = $mysqli->query($query);

                        if ($result) {
                            while ($hasil = mysqli_fetch_array($result)) {
                                $selected = ($hasil['Id_jenis'] == $jenis) ? "selected" : "";
                                echo "<option value='" . htmlspecialchars($hasil['Id_jenis']) . "' $selected>"
                                    . htmlspecialchars($hasil['nama_jenis']) . " - " . htmlspecialchars($hasil['bentuk_obat'])
                                    . "</option>";
                            }
                        }

                        // Menutup koneksi database
                        $mysqli->close();
                        ?>
                    </select>
                </div>

                <!-- Stok Obat -->
                <div class="mb-3">
                    <label for="Stok_obat" class="form-label">Stok Obat</label>
                    <input type="number" class="form-control" name="Stok_obat" id="Stok_obat" placeholder="Tuliskan stok obat di sini" value="<?php echo htmlspecialchars($stok); ?>" min="0" required>
                </div>

                <!-- Harga Satuan -->
                <div class="mb-3">
                    <label for="Harga_satuan" class="form-label">Harga Satuan (Rp.)</label>
                    <input type="number" class="form-control" name="Harga_satuan" id="Harga_satuan" placeholder="Tuliskan harga satuan di sini" value="<?php echo htmlspecialchars($harga); ?>" min="0" required>
                </div>

                <!-- Foto Obat -->
                <div class="mb-3">
                    <label for="foto_obat" class="form-label">Foto Obat</label>
                    <input type="file" class="form-control" name="foto_obat" id="foto_obat" accept="image/*">
                    <?php if ($action == "edit" && $foto) { ?>
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($foto); ?>" alt="Foto Obat" class="img-preview">
                    <?php } ?>
                </div>

                <!-- Aksi -->
                <input type="hidden" name="action" value="<?php echo htmlspecialchars($action); ?>">
                <div class="d-grid gap-2 p-2 d-md-flex justify-content-md-end">
                    <button class="btn btn-<?php echo ($action == "add") ? "success" : "primary"; ?>" type="submit">
                        <?php echo ($action == "add") ? "Tambah" : "Perbarui"; ?>
                    </button>
                    <a href="index.php?page=obat" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap 5 JS dan Dependensi -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>