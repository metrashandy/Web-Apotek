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
$alamat = "";
$email = "";
$telepon = "";

if ($action == "add") {
  // Mendapatkan ID suplier tertinggi dan menambahkannya untuk ID baru
  $result = $mysqli->query("SELECT MAX(Id_suplier) AS currentid FROM tb_suplier;");

  if (!$result) {
    die("Error: " . $mysqli->error);
  }

  $row = $result->fetch_assoc(); // Khusus single result
  $max_number = (int)$row['currentid'];
  $new_number = $max_number + 1;

  $currentid = $new_number; // Tambahkan dengan 1 untuk ID baru
} elseif ($action == "edit") {
  // Mengambil data suplier berdasarkan ID yang dipilih untuk diedit
  $currentid = $_GET['Id_suplier'];
  $query = "SELECT * FROM tb_suplier WHERE Id_suplier = '" . $currentid . "';";

  $result = $mysqli->query($query);
  $row = $result->fetch_assoc(); // Khusus single result

  $nama = $row['Nama_suplier'];
  $alamat = $row['Alamat'];
  $email = $row['email'];
  $telepon = $row['no_tlp'];
}

// Menutup koneksi database
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo ($action == "add") ? "Tambah Suplier" : "Edit Suplier"; ?></title>
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
  </style>
</head>

<body>
  <div class="container my-5">
    <!-- Pembungkus Formulir dengan Styling yang Ditingkatkan -->
    <div class="form-wrapper">
      <!-- Judul Halaman -->
      <div class="form-header">
        <h2 class="<?php echo ($action == "add") ? "text-success" : "text-primary"; ?>">
          <?php echo ($action == "add") ? "Tambah Suplier" : "Edit Suplier"; ?>
        </h2>
      </div>

      <!-- Formulir Suplier -->
      <form action="suplier.action.php" method="POST">
        <!-- ID Suplier -->
        <div class="mb-3">
          <label for="Id_suplier" class="form-label">ID Suplier</label>
          <input type="text" class="form-control" name="Id_suplier" id="Id_suplier" readonly value="<?php echo htmlspecialchars($currentid); ?>">
        </div>

        <!-- Nama Suplier -->
        <div class="mb-3">
          <label for="Nama_suplier" class="form-label">Nama Suplier</label>
          <input type="text" class="form-control" name="Nama_suplier" id="Nama_suplier" placeholder="Tuliskan nama suplier di sini" value="<?php echo htmlspecialchars($nama); ?>" required>
        </div>

        <!-- Alamat Suplier -->
        <div class="mb-3">
          <label for="Alamat" class="form-label">Alamat</label>
          <input type="text" class="form-control" name="Alamat" id="Alamat" placeholder="Tuliskan alamat suplier di sini" value="<?php echo htmlspecialchars($alamat); ?>" required>
        </div>

        <!-- Email Suplier -->
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" name="email" id="email" placeholder="Tuliskan email suplier di sini" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>

        <!-- No Telepon Suplier -->
        <div class="mb-3">
          <label for="no_tlp" class="form-label">No Telepon</label>
          <input type="text" class="form-control" name="no_tlp" id="no_tlp" placeholder="Tuliskan nomor telepon suplier di sini" value="<?php echo htmlspecialchars($telepon); ?>" required>
        </div>

        <!-- Aksi -->
        <input type="hidden" name="action" value="<?php echo htmlspecialchars($action); ?>">
        <div class="d-grid gap-2 p-2 d-md-flex justify-content-md-end">
          <button class="btn btn-<?php echo ($action == "add") ? "success" : "primary"; ?>" type="submit">
            <?php echo ($action == "add") ? "Tambah" : "Perbarui"; ?>
          </button>
          <a href="index.php?page=suplier" class="btn btn-secondary">Kembali</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Bootstrap 5 JS dan Dependensi -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>