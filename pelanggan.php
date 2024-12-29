<?php
require_once 'check_admin.php';
checkAdmin();

// Menghubungkan ke database menggunakan MySQLi
$mysqli = new mysqli("localhost", "root", "", "apotek");

// Memeriksa koneksi
if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

// Query untuk mengambil data pelanggan
$query = "SELECT * FROM tb_pelanggan";
$result = $mysqli->query($query);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pelanggan</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS untuk Penyesuaian -->
    <style>
        body {
            background-color: #f8f9fa;
            /* Warna latar belakang netral */
        }

        .table-wrapper {
            background-color: #ffffff;
            /* Latar belakang putih untuk tabel */
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            /* Bayangan untuk kedalaman */
        }

        .table-header {
            background-color: #198754;
            /* Warna hijau Bootstrap untuk header tabel */
            color: #ffffff;
            /* Teks putih */
        }

        .table-hover tbody tr:hover {
            background-color: #d1e7dd;
            /* Efek hover pada baris tabel */
        }

        .no-data {
            text-align: center;
            font-style: italic;
            color: #6c757d;
        }

        .btn-custom {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container my-5">
        <!-- Judul Halaman -->
        <div class="mb-4 text-center">
            <h2 class="text-success">Daftar Pelanggan</h2>
            <p class="text-secondary">Memantau informasi dan kontak pelanggan di apotek</p>
        </div>

        <!-- Pembungkus Tabel dengan Styling yang Ditingkatkan -->
        <div class="table-wrapper">
            <table class="table table-striped table-hover">
                <thead class="table-header">
                    <tr>
                        <th scope="col">ID Pelanggan</th>
                        <th scope="col">Nama Pelanggan</th>
                        <th scope="col">Email</th>
                        <th scope="col">Nomor Telepon</th>
                        <th scope="col">Alamat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        // Mengiterasi setiap baris hasil query
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['Id_pelanggan']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['no_tlp']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['alamat']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        // Menampilkan pesan jika tidak ada data
                        echo "<tr><td colspan='5' class='no-data'>Tidak ada data yang ditemukan.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap 5 JS dan Dependensi -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
// Menutup koneksi database
$mysqli->close();
?>