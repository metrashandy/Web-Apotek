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
    p.Id_pembelian
ORDER BY 
    p.tanggal_pembelian DESC;

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
    <!-- Tailwind/Custom CSS (mirroring style from history_belanja) -->
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

        /* Modal overlay style (similar to history_belanja) */
        #modalRincian {
            display: none;
            /* Hidden by default */
            position: fixed;
            inset: 0;
            z-index: 9999;
            /* Above bootstrap modal z-index */
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
    <!-- Tambahkan library html2canvas dan jsPDF untuk menyimpan struk -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
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

        <!-- Pembungkus Tabel -->
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
                                    <button type="button"
                                        class="btn btn-info btn-sm me-2"
                                        onclick='tampilkanPembelian(<?= json_encode($row) ?>)'>
                                        Tampilkan Struk
                                    </button>
                                    <a class="btn btn-primary btn-sm me-2"
                                        href="index.php?page=pembelian.form&Id_pembelian=<?= urlencode($row['ID_pembelian']) ?>&action=edit">
                                        Perbarui
                                    </a>
                                    <form action="pembelian.action.php" method="POST" class="d-inline">
                                        <input type="hidden" name="Id_pembelian" value="<?= htmlspecialchars($row['ID_pembelian']) ?>">
                                        <input type="hidden" name="Id_obat" value="<?= htmlspecialchars($row['Id_obat']) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Ingin menghapus data ini?');">
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

    <!-- Modal Detail (mirroring style from history_belanja) -->
    <div id="modalRincian">
        <div id="modalInner" class="p-4">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center border-bottom pb-3">
                <h4 class="fw-bold">Detail Pembelian</h4>
                <span class="close-button text-muted" onclick="tutupModal()">
                    <svg width="24" height="24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </span>
            </div>

            <!-- Container struk (mirroring history_belanja) -->
            <div class="mt-4" id="struk-container">
                <!-- Info Pembelian -->
                <div class="row mb-3">
                    <div class="col-6">
                        <p class="text-secondary mb-1">ID Pembelian</p>
                        <p class="fw-medium" id="pembelianId"></p>
                    </div>
                    <div class="col-6">
                        <p class="text-secondary mb-1">Nama Suplier</p>
                        <p class="fw-medium" id="pembelianSuplier"></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <p class="text-secondary mb-1">Tanggal Pembelian</p>
                        <p class="fw-medium" id="pembelianTanggal"></p>
                    </div>
                </div>

                <!-- Tabel Rincian -->
                <div class="table-responsive border rounded">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Nama Item</th>
                                <th scope="col">Jumlah</th>
                                <th scope="col">Harga Satuan</th>
                                <th scope="col">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="pembelianItems">
                            <!-- Baris item pembelian -->
                        </tbody>
                    </table>
                </div>

                <!-- Ringkasan Biaya -->
                <div class="mt-3">
                    <p class="mb-1"><strong>Total Harga:</strong> <span id="pembelianTotalHarga"></span></p>
                    <p class="mb-1"><strong>Total Bayar:</strong> <span id="pembelianTotalBayar"></span></p>
                    <p class="mb-1"><strong>Kembalian:</strong> <span id="pembelianKembalian"></span></p>
                </div>
            </div>

            <!-- Tombol Simpan Struk -->
            <div class="text-end mt-4">
                <button class="btn btn-primary" onclick="simpanStruk()">Simpan Struk</button>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS & Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function tampilkanPembelian(data) {
            document.getElementById("pembelianId").innerText = data.ID_pembelian;
            document.getElementById("pembelianSuplier").innerText = data.suplier;
            document.getElementById("pembelianTanggal").innerText = data.tanggal;

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

                    const row = `
                        <tr>
                            <td>${nama}</td>
                            <td>${jumlah || 0}</td>
                            <td>Rp ${parseInt(harga).toLocaleString('id-ID')}</td>
                            <td>Rp ${subtotal.toLocaleString('id-ID')}</td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            }

            document.getElementById("pembelianTotalHarga").innerText = `Rp ${totalHarga.toLocaleString('id-ID')}`;
            document.getElementById("pembelianTotalBayar").innerText = data.Total_bayar ?
                `Rp ${parseInt(data.Total_bayar).toLocaleString('id-ID')}` :
                "Rp 0";
            document.getElementById("pembelianKembalian").innerText = data.Kembalian ?
                `Rp ${parseInt(data.Kembalian).toLocaleString('id-ID')}` :
                "Rp 0";

            // Tampilkan overlay
            document.getElementById("modalRincian").style.display = 'block';
        }

        function tutupModal() {
            document.getElementById("modalRincian").style.display = 'none';
        }

        // Menutup modal jika klik di luar area konten
        document.getElementById("modalRincian").addEventListener("click", function(e) {
            if (e.target.id === "modalRincian") {
                tutupModal();
            }
        });

        // Fungsi menyimpan struk
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

                doc.addImage(imageData, 'PNG', 0, 0, pdfWidth, pdfHeight);
            });
            doc.save('struk-pembelian.pdf');
        }
    </script>
</body>

</html>
<?php
$mysqli->close();
?>