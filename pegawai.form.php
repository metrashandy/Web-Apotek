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
$email = "";
$telepon = "";
$nip = "";
$passwd = "";

if ($action == "add") {
  // Mendapatkan ID pegawai tertinggi dan menambahkannya untuk ID baru
  $result = $mysqli->query("SELECT MAX(Id_pegawai) AS currentid FROM tb_pegawai;");

  if (!$result) {
    die("Error: " . $mysqli->error);
  }

  $row = $result->fetch_assoc(); // Khusus single result
  $max_number = (int)$row['currentid'];
  $new_number = $max_number + 1;

  $currentid = $new_number; // Tambahkan dengan 1 untuk ID baru
} elseif ($action == "edit") {
  // Mengambil data pegawai berdasarkan ID yang dipilih untuk diedit
  $currentid = $_GET['Id_pegawai'];
  $query = "SELECT * FROM tb_pegawai WHERE Id_pegawai = '" . $currentid . "';";

  $result = $mysqli->query($query);
  $row = $result->fetch_assoc(); // Khusus single result

  $nama = $row['Nama_pegawai'];
  $email = $row['email'];
  $telepon = $row['No_tlp'];
  $nip = $row['Nip'];
  $passwd = $row['passwd'];
}

// Menutup koneksi database
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo ($action == "add") ? "Tambah Pegawai" : "Edit Pegawai"; ?></title>
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
          <?php echo ($action == "add") ? "Tambah Pegawai" : "Edit Pegawai"; ?>
        </h2>
      </div>

      <!-- Formulir Pegawai -->
      <form action="pegawai.action.php" method="POST">
        <!-- ID Pegawai -->
        <div class="mb-3">
          <label for="Id_pegawai" class="form-label">ID Pegawai</label>
          <input type="text" class="form-control" name="Id_pegawai" id="Id_pegawai" readonly value="<?php echo htmlspecialchars($currentid); ?>">
        </div>

        <!-- Nama Pegawai -->
        <div class="mb-3">
          <label for="Nama_pegawai" class="form-label">Nama Pegawai</label>
          <input type="text" class="form-control" name="Nama_pegawai" id="Nama_pegawai" placeholder="Tuliskan nama pegawai di sini" value="<?php echo htmlspecialchars($nama); ?>" required>
        </div>

        <!-- Telepon Pegawai -->
        <div class="mb-3">
          <label for="No_tlp" class="form-label">Telepon</label>
          <input type="text" class="form-control" name="No_tlp" id="No_tlp" placeholder="Tuliskan nomor telepon di sini" value="<?php echo htmlspecialchars($telepon); ?>" required>
        </div>

        <!-- Email Pegawai -->
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" name="email" id="email" placeholder="Tuliskan email di sini" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>

        <!-- NIP Pegawai -->
        <div class="mb-3">
          <label for="Nip" class="form-label">NIP</label>
          <input type="text" class="form-control" name="Nip" id="Nip" placeholder="Tuliskan NIP di sini" value="<?php echo htmlspecialchars($nip); ?>" required>
        </div>

        <!-- Password Pegawai -->
        <div class="mb-3">
          <label for="passwd" class="form-label">Password</label>
          <input type="password" class="form-control" name="passwd" id="passwd" placeholder="Tuliskan password akun di sini" value="<?php echo htmlspecialchars($passwd); ?>" <?php echo ($action == "add") ? "required" : ""; ?>>
          <?php if ($action == "edit") { ?>
            <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password.</small>
          <?php } ?>
        </div>

        <!-- Aksi -->
        <input type="hidden" name="action" value="<?php echo htmlspecialchars($action); ?>">
        <div class="d-grid gap-2 p-2 d-md-flex justify-content-md-end">
          <button class="btn btn-<?php echo ($action == "add") ? "success" : "primary"; ?>" type="submit">
            <?php echo ($action == "add") ? "Tambah" : "Perbarui"; ?>
          </button>
          <a href="index.php?page=pegawai" class="btn btn-secondary">Kembali</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Bootstrap 5 JS dan Dependensi -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>