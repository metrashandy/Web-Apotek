<?php
require_once 'check_admin.php';
checkAdmin();

// Menghubungkan ke database menggunakan MySQLi
$mysqli = new mysqli("localhost", "root", "", "apotek");

// Memeriksa koneksi
if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

// Query untuk mengambil data obat beserta jenisnya
$query = "
    SELECT 
        tb_obat.Id_Obat, 
        tb_obat.Nama_Obat, 
        tb_jenis_obat.Nama_jenis AS Jenis_obat, 
        tb_obat.Stok_obat, 
        tb_obat.Harga_satuan, 
        tb_obat.foto_obat 
    FROM tb_obat
    JOIN tb_jenis_obat ON tb_obat.Id_jenis = tb_jenis_obat.Id_jenis
";

$result = $mysqli->query($query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Obat</title>
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
            background-color: #0d6efd;
            /* Warna biru Bootstrap untuk header tabel */
            color: #ffffff;
            /* Teks putih */
        }

        .table-hover tbody tr:hover {
            background-color: #e2e6ea;
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

        .img-thumbnail {
            width: 100px;
            height: auto;
        }
    </style>
</head>

<body>
    <div class="container my-5">
        <!-- Judul Halaman -->
        <div class="mb-4 text-center">
            <h2 class="text-primary">Daftar Obat</h2>
            <p class="text-secondary">Memantau stok dan informasi obat di apotek</p>
        </div>

        <!-- Tombol Tambah Obat -->
        <div class="d-grid gap-2 d-md-flex justify-content-md-end btn-custom">
            <a class="btn btn-success" href="index.php?page=obat.form&action=add" role="button">Tambah Obat</a>
        </div>

        <!-- Pembungkus Tabel dengan Styling yang Ditingkatkan -->
        <div class="table-wrapper">
            <table class="table table-striped table-hover">
                <thead class="table-header">
                    <tr>
                        <th scope="col">Kode Obat</th>
                        <th scope="col">Nama Obat</th>
                        <th scope="col">Jenis Obat</th>
                        <th scope="col">Stok</th>
                        <th scope="col">Harga Satuan</th>
                        <th scope="col">Foto</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        // Mengiterasi setiap baris hasil query
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['Id_Obat']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Nama_Obat']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Jenis_obat']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Stok_obat']) . "</td>";
                            echo "<td>Rp " . number_format($row['Harga_satuan'], 0, ',', '.') . "</td>";

                            // Menampilkan gambar dari kolom foto_obat
                            echo "<td>";
                            if (!empty($row['foto_obat'])) {
                                echo "<img src='data:image/jpeg;base64," . base64_encode($row['foto_obat']) . "' alt='Foto Obat' class='img-thumbnail'>";
                            } else {
                                echo "Tidak ada gambar";
                            }
                            echo "</td>";

                            // Tombol Aksi: Perbarui dan Hapus
                            echo "<td>";
                            echo "<a class='btn btn-primary btn-sm me-2' href='index.php?page=obat.form&Id_Obat=" . $row['Id_Obat'] . "&action=edit'>Perbarui</a>";
                            echo "<form action='obat.action.php' method='POST' class='d-inline'>";
                            echo "<input type='hidden' name='Id_Obat' value='" . $row['Id_Obat'] . "'>";
                            echo "<input type='hidden' name='action' value='delete'>";
                            echo "<button type='submit' class='btn btn-danger btn-sm' onclick='return confirm(\"Apakah Anda yakin ingin menghapus data ini?\");'>Hapus</button>";
                            echo "</form>";
                            echo "</td>";

                            echo "</tr>";
                        }
                    } else {
                        // Menampilkan pesan jika tidak ada data
                        echo "<tr><td colspan='7' class='no-data'>Tidak ada data yang ditemukan.</td></tr>";
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