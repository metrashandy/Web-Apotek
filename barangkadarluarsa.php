<?php
require_once 'check_admin.php';
checkAdmin();

// Menghubungkan ke database menggunakan MySQLi
$mysqli = new mysqli("localhost", "root", "", "apotek");

// Memeriksa koneksi
if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

// Query untuk mengambil data barang yang sudah kadaluarsa
$query = "
    SELECT 
        tb_pembelian_detail.`Id_pembelian`, 
        tb_obat.`Id_Obat`, 
        tb_obat.`Nama_Obat`, 
        tb_pembelian_detail.`tanggal_kadarluarsa`, 
        tb_pembelian_detail.`jumlah_item`
    FROM 
        tb_pembelian_detail
    JOIN 
        tb_obat 
        ON tb_obat.`Id_Obat` = tb_pembelian_detail.`Id_obat`
    WHERE 
        tb_pembelian_detail.`tanggal_kadarluarsa` <= CURDATE()
";

$result = $mysqli->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Barang Sudah Kadaluarsa</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS untuk Penyesuaian -->
    <style>
        body {
            background-color: #f8f9fa; /* Warna latar belakang netral */
        }
        .table-wrapper {
            background-color: #ffffff; /* Latar belakang putih untuk tabel */
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1); /* Bayangan untuk kedalaman */
        }
        .table-header {
            background-color: #dc3545; /* Warna merah Bootstrap untuk header tabel */
            color: #ffffff; /* Teks putih */
        }
        .table-hover tbody tr:hover {
            background-color: #f1b0b7; /* Efek hover pada baris tabel */
        }
        .no-data {
            text-align: center;
            font-style: italic;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <!-- Judul Halaman -->
        <div class="mb-4">
            <h2 class="text-center text-danger">Daftar Barang Sudah Kadaluarsa</h2>
            <p class="text-center text-secondary">Memantau stok barang yang telah melewati tanggal kadaluarsa</p>
        </div>

        <!-- Pembungkus Tabel dengan Styling yang Ditingkatkan -->
        <div class="table-wrapper">
            <table class="table table-striped table-hover">
                <thead class="table-header">
                    <tr>
                        <th scope="col">ID Pembelian</th>
                        <th scope="col">ID Barang</th>
                        <th scope="col">Nama Barang</th>
                        <th scope="col">Tanggal Kadaluarsa</th>
                        <th scope="col">Stok</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        // Mengiterasi setiap baris hasil query
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['Id_pembelian']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Id_Obat']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Nama_Obat']) . "</td>";
                            echo "<td>" . htmlspecialchars(date("d-m-Y", strtotime($row['tanggal_kadarluarsa']))) . "</td>";
                            echo "<td>" . htmlspecialchars($row['jumlah_item']) . "</td>";
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