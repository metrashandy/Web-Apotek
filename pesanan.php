<?php
require_once 'check_admin.php';
checkAdmin();

// Koneksi ke database
$mysqli = new mysqli("localhost", "root", "", "apotek");
if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

// Query untuk daftar pesanan
$result = $mysqli->query("
 SELECT 
        p.Id_pesanan AS ID_Pesanan,
        pl.username AS Nama_Pelanggan,
        GROUP_CONCAT(o.Nama_Obat SEPARATOR ', ') AS Rincian_Obat,
        pl.alamat AS Alamat,
        pl.no_tlp AS No_Telepon,
        GROUP_CONCAT(CONCAT(o.Nama_Obat, ';', pd.jumlah_item, ';', pd.harga_satuan) SEPARATOR ', ') AS Detail_Obat,
        p.`Tipe Pembayaran` AS Tipe_Pembayaran,
        IFNULL(p.Bukti_transfer, '') AS Bukti_Transfer,
        p.tanggal_pemesanan AS Tanggal_Pemesanan
    FROM 
        tb_pesanan p
    JOIN 
        tb_pelanggan pl ON p.Id_pelanggan = pl.Id_pelanggan
    JOIN 
        tb_pesanan_detail pd ON p.Id_pesanan = pd.Id_pesanan
    JOIN 
        tb_obat o ON pd.Id_obat = o.Id_Obat
    WHERE 
        p.status = 'PENDING'
    GROUP BY 
        p.Id_pesanan
    ORDER BY 
        p.tanggal_pemesanan DESC;
");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Daftar Pesanan</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Tambahkan library html2canvas dan jsPDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <!-- Custom CSS agar mirip pembelian.php / history_belanja.php -->
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

        /* Overlay mirip history_belanja.php */
        #modalRincian {
            display: none;
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
        <div class="mb-4 text-center">
            <h2 class="text-primary">Daftar Pesanan</h2>
            <p class="text-secondary">Memantau pesanan obat yang berstatus PENDING</p>
        </div>

        <!-- Pembungkus Tabel dengan Styling yang Ditingkatkan -->
        <div class="table-wrapper">
            <table class="table table-striped table-hover">
                <thead class="table-header">
                    <tr>
                        <th scope="col">ID PESANAN</th>
                        <th scope="col">NAMA PELANGGAN</th>
                        <th scope="col">LIST BARANG</th>
                        <th scope="col">ALAMAT</th>
                        <th scope="col">NO TELEPON</th>
                        <th scope="col">TIPE PEMBAYARAN</th>
                        <th scope="col">BUKTI TRANSFER</th>
                        <th scope="col">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php foreach ($result as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['ID_Pesanan']) ?></td>
                                <td><?= htmlspecialchars($row['Nama_Pelanggan']) ?></td>
                                <td><?= htmlspecialchars($row['Rincian_Obat']) ?></td>
                                <td><?= htmlspecialchars($row['Alamat']) ?></td>
                                <td><?= htmlspecialchars($row['No_Telepon']) ?></td>
                                <td><?= htmlspecialchars($row['Tipe_Pembayaran']) ?></td>
                                <td>
                                    <?php if (!empty($row['Bukti_Transfer'])): ?>
                                        <a href="data:image/jpeg;base64,<?= base64_encode($row['Bukti_Transfer']) ?>" target="_blank">Lihat Bukti</a>
                                    <?php else: ?>
                                        Tidak Ada
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <!-- Tombol Tampilkan Struk -->
                                    <button
                                        type="button"
                                        class="btn btn-info btn-sm"
                                        onclick='tampilkanStruk(<?= json_encode([
                                                                    'ID_Pesanan'      => $row['ID_Pesanan'],
                                                                    'Nama_Pelanggan'  => $row['Nama_Pelanggan'],
                                                                    'Alamat'          => $row['Alamat'],
                                                                    'Detail_Obat'     => $row['Detail_Obat'],
                                                                    'Tipe_Pembayaran' => $row['Tipe_Pembayaran'],
                                                                    'Tanggal_Pemesanan' => $row['Tanggal_Pemesanan']
                                                                ]) ?>)'>
                                        Tampilkan Struk
                                    </button>

                                    <!-- Tombol Konfirmasi Pesanan -->
                                    <form action="penjualan.action.php" method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="process">
                                        <input type="hidden" name="Id_pesanan" value="<?= htmlspecialchars($row['ID_Pesanan']) ?>">
                                        <button type="submit" class="btn btn-success btn-sm">TERKIRIM</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="no-data">Tidak ada pesanan yang ditemukan.</td>
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
                <h4 class="fw-bold">Struk Pesanan</h4>
                <span class="close-button text-muted" onclick="tutupModal()">
                    <svg width="24" height="24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </span>
            </div>

            <!-- Isi Struk -->
            <div class="mt-4" id="struk-container">
                <p><strong>ID Pesanan:</strong> <span id="strukId"></span></p>
                <p><strong>Nama Pelanggan:</strong> <span id="strukPelanggan"></span></p>
                <p><strong>Alamat:</strong> <span id="strukAlamat"></span></p>
                <p><strong>Tanggal Pemesanan:</strong> <span id="strukTanggalPemesanan"></span></p>
                <p><strong>Tipe Pembayaran:</strong> <span id="strukTipePembayaran"></span></p>

                <h5>Detail Obat</h5>
                <div class="table-responsive border rounded">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Barang</th>
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
                    <p><strong>Biaya Pengiriman:</strong> Rp. 10.000</p>
                    <p><strong>Total Bayar:</strong> <span id="strukTotalBayar"></span></p>
                </div>
            </div>

            <!-- Tombol Simpan Struk -->
            <div class="text-end mt-4">
                <button class="btn btn-primary" onclick="simpanStruk()">Simpan Struk</button>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS (opsional untuk komponen lain) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function tampilkanStruk(data) {
            document.getElementById("strukId").innerText = data.ID_Pesanan;
            document.getElementById("strukPelanggan").innerText = data.Nama_Pelanggan;
            document.getElementById("strukAlamat").innerText = data.Alamat;
            document.getElementById("strukTipePembayaran").innerText = data.Tipe_Pembayaran;
            document.getElementById("strukTanggalPemesanan").innerText = data.Tanggal_Pemesanan;

            const items = data.Detail_Obat ? data.Detail_Obat.split(", ") : [];
            const tbody = document.getElementById("strukItems");
            tbody.innerHTML = "";

            let totalHarga = 0;
            items.forEach((item) => {
                const [nama, jumlah, harga] = item.split(";");
                const subtotal = parseInt(jumlah) * parseInt(harga);
                totalHarga += subtotal;

                const row = `
        <tr>
            <td>${nama}</td>
            <td>${jumlah}</td>
            <td>Rp. ${parseInt(harga).toLocaleString('id-ID')}</td>
            <td>Rp. ${subtotal.toLocaleString('id-ID')}</td>
        </tr>`;
                tbody.innerHTML += row;
            });

            const biayaPengiriman = 10000;
            document.getElementById("strukTotalHarga").innerText = `Rp. ${totalHarga.toLocaleString('id-ID')}`;
            document.getElementById("strukTotalBayar").innerText = `Rp. ${(totalHarga + biayaPengiriman).toLocaleString('id-ID')}`;

            document.getElementById("modalRincian").style.display = 'block';
        }


        function tutupModal() {
            document.getElementById("modalRincian").style.display = 'none';
        }

        // Menutup modal jika klik di luar area modalInner
        document.getElementById("modalRincian").addEventListener("click", function(e) {
            if (e.target.id === "modalRincian") {
                tutupModal();
            }
        });

        // Fungsi menyimpan struk jadi PDF mirip history_belanja
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
            doc.save('struk-pesanan.pdf');
        }
    </script>

</body>

</html>
<?php
$mysqli->close();
?>