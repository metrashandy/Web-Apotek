<?php
require_once 'check_admin.php';
checkAdmin();

// Koneksi ke database menggunakan MySQLi
$mysqli = new mysqli("localhost", "root", "", "apotek");

// Memeriksa koneksi
if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

// Query data penjualan
$query = "
    SELECT 
        p.`Id_penjualan` AS ID_penjualan,
        p.`Tanggal_penjualan` AS tanggal,
        np.`username` AS pelanggan,
        GROUP_CONCAT(o.Nama_Obat SEPARATOR ', ') AS Daftar_Item,
        GROUP_CONCAT(CONCAT(o.Nama_Obat, ';', pd.jumlah_item, ';', pd.harga_satuan) SEPARATOR ', ') AS Rincian_Item,
        p.Total_item AS Item,
        p.`harga_total` AS Total_harga, -- Total harga barang
        p.`total_bayar` AS Total_bayar, -- Uang yang dibayarkan pelanggan
        (p.total_bayar - p.harga_total) AS Kembalian, -- Perhitungan kembalian
        pd.Id_obat AS Id_obat
    FROM 
        tb_penjualan p
    JOIN 
        tb_pelanggan np ON p.`Id_pelanggan` = np.`Id_pelanggan`
    JOIN 
        tb_penjualan_detail pd ON p.`Id_penjualan` = pd.`Id_penjualan`
    JOIN 
        tb_obat o ON pd.Id_obat = o.Id_Obat
    GROUP BY 
        p.`Id_penjualan`;
";
$result = $mysqli->query($query);

if (!$result) {
    die("Query gagal: " . $mysqli->error);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Penjualan</title>
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
            background-color: #e9ecef;
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

        /* Styling untuk Modal */
        .modal-header {
            background-color: #0d6efd;
            color: #ffffff;
        }
    </style>
</head>

<body>
    <div class="container my-5">
        <!-- Judul Halaman -->
        <div class="mb-4 text-center">
            <h2 class="text-primary">Daftar Penjualan</h2>
            <p class="text-secondary">Memantau data penjualan obat di apotek</p>
        </div>

        <!-- Tombol Tambah Penjualan -->
        <div class="d-grid gap-2 d-md-flex justify-content-md-end btn-custom">
            <a class="btn btn-success" href="index.php?page=penjualan.form&action=add" role="button">Tambah Penjualan</a>
        </div>

        <!-- Pembungkus Tabel dengan Styling yang Ditingkatkan -->
        <div class="table-wrapper">
            <table class="table table-striped table-hover table-responsive">
                <thead class="table-header">
                    <tr>
                        <th scope="col">ID Penjualan</th>
                        <th scope="col">Tanggal Penjualan</th>
                        <th scope="col">Nama Pelanggan</th>
                        <th scope="col">Daftar Item</th>
                        <th scope="col">Jumlah Item</th>
                        <th scope="col">Total Bayar</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php foreach ($result as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['ID_penjualan']) ?></td>
                                <td><?= htmlspecialchars(date("d-m-Y", strtotime($row['tanggal']))) ?></td>
                                <td><?= htmlspecialchars($row['pelanggan']) ?></td>
                                <td><?= htmlspecialchars($row['Daftar_Item']) ?></td>
                                <td><?= htmlspecialchars($row['Item']) ?></td>
                                <td>Rp <?= number_format($row['Total_bayar'], 0, ',', '.') ?></td>
                                <td>
                                    <button type="button" class="btn btn-info btn-sm me-2" onclick='tampilkanStruk(<?= json_encode($row) ?>)'>
                                        Tampilkan Struk
                                    </button>
                                    <a class="btn btn-primary btn-sm me-2" href="index.php?page=penjualan.form&Id_penjualan=<?= urlencode($row['ID_penjualan']) ?>&action=edit">
                                        Perbarui
                                    </a>
                                    <form action="penjualan.action.php" method="POST" class="d-inline">
                                        <input type="hidden" name="Id_penjualan" value="<?= htmlspecialchars($row['ID_penjualan']) ?>">
                                        <input type="hidden" name="Id_obat" value="<?= htmlspecialchars($row['Id_obat']) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Ingin menghapus data ini?');">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="no-data">Tidak ada data yang ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Struk Penjualan -->
    <div class="modal fade" id="modalStruk" tabindex="-1" aria-labelledby="modalStrukLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalStrukLabel">Struk Penjualan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Isi struk akan diisi oleh JavaScript -->
                    <div id="strukContent">
                        <p><strong>ID Penjualan:</strong> <span id="strukId"></span></p>
                        <p><strong>Nama Pelanggan:</strong> <span id="strukPelanggan"></span></p>
                        <p><strong>Tanggal Penjualan:</strong> <span id="strukTanggal"></span></p>
                        <h5>Rincian Item</h5>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nama Item</th>
                                    <th>Jumlah</th>
                                    <th>Harga Satuan</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="strukItems"></tbody>
                        </table>
                        <p><strong>Total Harga:</strong> <span id="strukTotalHarga"></span></p>
                        <p><strong>Total Bayar:</strong> <span id="strukTotalBayar"></span></p>
                        <p><strong>Kembalian:</strong> <span id="strukKembalian"></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS dan Dependensi -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function tampilkanStruk(data) {
            // Isi data ke modal
            document.getElementById("strukId").innerText = data.ID_penjualan;
            document.getElementById("strukPelanggan").innerText = data.pelanggan;
            document.getElementById("strukTanggal").innerText = new Date(data.tanggal).toLocaleDateString('id-ID');

            // Parse rincian item
            const items = data.Rincian_Item ? data.Rincian_Item.split(", ") : [];
            const tbody = document.getElementById("strukItems");
            tbody.innerHTML = ""; // Kosongkan tabel

            let totalHarga = 0;

            if (items.length === 0) {
                tbody.innerHTML = "<tr><td colspan='4' class='text-center'>Tidak ada data</td></tr>";
            } else {
                items.forEach((item) => {
                    const [nama, jumlah, harga] = item.split(";");
                    const subtotal = (jumlah && harga) ? jumlah * harga : 0;
                    totalHarga += subtotal;

                    const row = `<tr>
                        <td>${nama}</td>
                        <td>${jumlah || 0}</td>
                        <td>Rp ${parseInt(harga).toLocaleString('id-ID')}</td>
                        <td>Rp ${subtotal.toLocaleString('id-ID')}</td>
                    </tr>`;
                    tbody.innerHTML += row;
                });
            }

            document.getElementById("strukTotalHarga").innerText = `Rp ${totalHarga.toLocaleString('id-ID')}`;
            document.getElementById("strukTotalBayar").innerText = data.Total_bayar ? `Rp ${parseInt(data.Total_bayar).toLocaleString('id-ID')}` : "Rp 0";
            document.getElementById("strukKembalian").innerText = data.Kembalian ? `Rp ${parseInt(data.Kembalian).toLocaleString('id-ID')}` : "Rp 0";

            // Tampilkan modal
            const modal = new bootstrap.Modal(document.getElementById("modalStruk"));
            modal.show();
        }
    </script>
</body>

</html>

<?php
// Menutup koneksi database
$mysqli->close();
?>