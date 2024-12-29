<?php
require_once 'check_admin.php';
checkAdmin();

// Koneksi ke database
$mysqli = new mysqli("localhost", "root", "", "apotek");
if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

// Query data pembelian
$query = "
    SELECT 
        p.Id_pembelian AS ID_pembelian,
        p.tanggal_pembelian AS tanggal,
        sp.Nama_suplier AS suplier,
        GROUP_CONCAT(o.Nama_Obat SEPARATOR ', ') AS Daftar_Item,
        GROUP_CONCAT(CONCAT(o.Nama_Obat, ';', pd.jumlah_item, ';', pd.harga_satuan) SEPARATOR ', ') AS Rincian_Item,
        p.total_item AS Item,
        p.total_harga AS Harga,
        p.Total_bayar AS Total_bayar,
        p.kembalian AS Kembalian,
        pd.Id_obat AS Id_obat
    FROM 
        tb_pembelian p
    JOIN 
        tb_suplier sp ON p.Id_suplier = sp.Id_suplier
    JOIN 
        tb_pembelian_detail pd ON p.Id_pembelian = pd.Id_pembelian
    JOIN 
        tb_obat o ON pd.Id_obat = o.Id_Obat
    GROUP BY 
        p.Id_pembelian;
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
    <title>Daftar Pembelian</title>
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
    <script>
        function tampilkanPembelian(data) {
            document.getElementById("pembelianId").innerText = data.ID_pembelian;
            document.getElementById("pembelianSuplier").innerText = data.suplier; // Nama suplier
            document.getElementById("pembelianTanggal").innerText = data.tanggal;

            // Rincian item
            const items = data.Rincian_Item ? data.Rincian_Item.split(", ") : [];
            const tbody = document.getElementById("pembelianItems");
            tbody.innerHTML = "";

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

            document.getElementById("pembelianTotalHarga").innerText = `Rp ${totalHarga.toLocaleString('id-ID')}`;
            document.getElementById("pembelianTotalBayar").innerText = data.Total_bayar ? `Rp ${parseInt(data.Total_bayar).toLocaleString('id-ID')}` : "Rp 0";
            document.getElementById("pembelianKembalian").innerText = data.Kembalian ? `Rp ${parseInt(data.Kembalian).toLocaleString('id-ID')}` : "Rp 0";

            const modal = new bootstrap.Modal(document.getElementById("modalPembelian"));
            modal.show();
        }
    </script>
</head>

<body>
    <div class="container my-5">
        <!-- Judul Halaman -->
        <div class="mb-4 text-center">
            <h2 class="text-primary">Daftar Pembelian</h2>
            <p class="text-secondary">Memantau data pembelian obat di apotek</p>
        </div>

        <!-- Tombol Tambah Pembelian -->
        <div class="d-grid gap-2 d-md-flex justify-content-md-end btn-custom">
            <a class="btn btn-success" href="index.php?page=pembelian.form&action=add" role="button">Tambah Pembelian</a>
        </div>

        <!-- Pembungkus Tabel dengan Styling yang Ditingkatkan -->
        <div class="table-wrapper">
            <table class="table table-striped table-hover">
                <thead class="table-header">
                    <tr>
                        <th scope="col">ID Pembelian</th>
                        <th scope="col">Tanggal Pembelian</th>
                        <th scope="col">Nama Suplier</th>
                        <th scope="col">Daftar Item</th>
                        <th scope="col">Jumlah Item</th>
                        <th scope="col">Harga Total</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php foreach ($result as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['ID_pembelian']) ?></td>
                                <td><?= htmlspecialchars(date("d-m-Y", strtotime($row['tanggal']))) ?></td>
                                <td><?= htmlspecialchars($row['suplier']) ?></td>
                                <td><?= htmlspecialchars($row['Daftar_Item']) ?></td>
                                <td><?= htmlspecialchars($row['Item']) ?></td>
                                <td>Rp <?= number_format($row['Harga'], 0, ',', '.') ?></td>
                                <td>
                                    <button type="button" class="btn btn-info btn-sm me-2" onclick='tampilkanPembelian(<?= json_encode($row) ?>)'>
                                        Detail
                                    </button>
                                    <a class="btn btn-primary btn-sm me-2" href="index.php?page=pembelian.form&Id_pembelian=<?= urlencode($row['ID_pembelian']) ?>&action=edit">
                                        Perbarui
                                    </a>
                                    <form action="pembelian.action.php" method="POST" class="d-inline">
                                        <input type="hidden" name="Id_pembelian" value="<?= htmlspecialchars($row['ID_pembelian']) ?>">
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

    <!-- Modal Detail Pembelian -->
    <div class="modal fade" id="modalPembelian" tabindex="-1" aria-labelledby="modalPembelianLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Pembelian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="pembelianContent">
                        <p><strong>ID Pembelian:</strong> <span id="pembelianId"></span></p>
                        <p><strong>Nama Suplier:</strong> <span id="pembelianSuplier"></span></p>
                        <p><strong>Tanggal Pembelian:</strong> <span id="pembelianTanggal"></span></p>
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
                            <tbody id="pembelianItems"></tbody>
                        </table>
                        <p><strong>Total Harga:</strong> <span id="pembelianTotalHarga"></span></p>
                        <p><strong>Total Bayar:</strong> <span id="pembelianTotalBayar"></span></p>
                        <p><strong>Kembalian:</strong> <span id="pembelianKembalian"></span></p>
                    </div>
                </div>
            </div>
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