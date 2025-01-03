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
        p.`harga_total` AS Total_harga, 
        p.`total_bayar` AS Total_bayar, 
        (p.total_bayar - p.harga_total) AS Kembalian,
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
        p.`Id_penjualan`
    ORDER BY 
        p.`Tanggal_penjualan` DESC;
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
    <!-- Tambahkan library html2canvas dan jsPDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <!-- Custom CSS untuk Penyesuaian -->
    <style>
        body {
            background-color: #f8f9fa;
        }

        .table-wrapper {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .table-header {
            background-color: #0d6efd;
            color: #ffffff;
        }

        .table-hover tbody tr:hover {
            background-color: #e9ecef;
        }

        .no-data {
            text-align: center;
            font-style: italic;
            color: #6c757d;
        }

        .btn-custom {
            margin-bottom: 20px;
        }

        /* Gaya overlay mirip history_belanja.php */
        #modalRincian {
            display: none;
            /* Tersembunyi secara default */
            position: fixed;
            inset: 0;
            z-index: 9999;
            background-color: rgba(0, 0, 0, 0.6);
        }

        #modalInner {
            position: relative;
            top: 5%;
            margin: 0 auto;
            max-width: 50rem;
            background-color: #fff;
            border-radius: 0.5rem;
            padding: 1rem;
        }

        .close-button {
            cursor: pointer;
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

        <!-- Pembungkus Tabel -->
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
                                    <button
                                        type="button"
                                        class="btn btn-info btn-sm me-2"
                                        onclick='tampilkanStruk(<?= json_encode($row) ?>)'>
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

    <!-- Modal Rincian (mirip style di history_belanja.php) -->
    <div id="modalRincian">
        <div id="modalInner" class="p-4">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center border-bottom pb-3">
                <h4 class="fw-bold">Struk Penjualan</h4>
                <span class="close-button text-muted" onclick="tutupModal()">
                    <svg width="24" height="24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </span>
            </div>

            <!-- Container Struk -->
            <div class="mt-4" id="struk-container">
                <p><strong>ID Penjualan:</strong> <span id="strukId"></span></p>
                <p><strong>Nama Pelanggan:</strong> <span id="strukPelanggan"></span></p>
                <p><strong>Tanggal Penjualan:</strong> <span id="strukTanggal"></span></p>

                <!-- Tabel Rincian Item -->
                <h5>Rincian Item</h5>
                <div class="table-responsive border rounded">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Item</th>
                                <th>Jumlah</th>
                                <th>Harga Satuan</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="strukItems"></tbody>
                    </table>
                </div>

                <!-- Ringkasan -->
                <div class="mt-3">
                    <p><strong>Total Harga:</strong> <span id="strukTotalHarga"></span></p>
                    <p><strong>Total Bayar:</strong> <span id="strukTotalBayar"></span></p>
                    <p><strong>Kembalian:</strong> <span id="strukKembalian"></span></p>
                </div>
            </div>

            <!-- Tombol Simpan Struk -->
            <div class="text-end mt-4">
                <button class="btn btn-primary" onclick="simpanStruk()">Simpan Struk</button>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fungsi untuk menampilkan struk pada overlay mirip history_belanja.php
        function tampilkanStruk(data) {
            document.getElementById("strukId").innerText = data.ID_penjualan;
            document.getElementById("strukPelanggan").innerText = data.pelanggan;
            // Format tanggal ke locale id-ID
            const tgl = new Date(data.tanggal).toLocaleDateString('id-ID');
            document.getElementById("strukTanggal").innerText = tgl;

            const items = data.Rincian_Item ? data.Rincian_Item.split(", ") : [];
            const tbody = document.getElementById("strukItems");
            tbody.innerHTML = "";
            let totalHarga = 0;

            if (items.length === 0) {
                tbody.innerHTML = "<tr><td colspan='4' class='text-center'>Tidak ada data</td></tr>";
            } else {
                items.forEach((item) => {
                    const [nama, jumlah, harga] = item.split(";");
                    const subtotal = (jumlah && harga) ? jumlah * harga : 0;
                    totalHarga += subtotal;

                    const row = `
                        <tr>
                            <td>${nama}</td>
                            <td>${jumlah || 0}</td>
                            <td>Rp ${parseInt(harga).toLocaleString('id-ID')}</td>
                            <td>Rp ${subtotal.toLocaleString('id-ID')}</td>
                        </tr>`;
                    tbody.innerHTML += row;
                });
            }

            document.getElementById("strukTotalHarga").innerText = `Rp ${totalHarga.toLocaleString('id-ID')}`;
            document.getElementById("strukTotalBayar").innerText = data.Total_bayar ?
                `Rp ${parseInt(data.Total_bayar).toLocaleString('id-ID')}` :
                "Rp 0";
            document.getElementById("strukKembalian").innerText = data.Kembalian ?
                `Rp ${parseInt(data.Kembalian).toLocaleString('id-ID')}` :
                "Rp 0";

            // Tampilkan overlay
            document.getElementById("modalRincian").style.display = 'block';
        }

        // Tutup modal jika klik close
        function tutupModal() {
            document.getElementById("modalRincian").style.display = 'none';
        }

        // Menutup modal jika klik di luar area konten
        document.getElementById("modalRincian").addEventListener("click", function(e) {
            if (e.target.id === "modalRincian") {
                tutupModal();
            }
        });

        // Fungsi menyimpan struk jadi PDF
        async function simpanStruk() {
            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF("p", "pt", "a4");
            const strukElement = document.getElementById('struk-container');

            await html2canvas(strukElement).then((canvas) => {
                const imageData = canvas.toDataURL('image/png');
                const pageWidth = doc.internal.pageSize.getWidth();
                const imgProps = doc.getImageProperties(imageData);
                const pdfWidth = pageWidth;
                const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

                doc.addImage(
                    imageData,
                    'PNG',
                    0,
                    0,
                    pdfWidth,
                    pdfHeight
                );
            });
            doc.save('struk-penjualan.pdf');
        }
    </script>
</body>

</html>
<?php
// Menutup koneksi database
$mysqli->close();
?>