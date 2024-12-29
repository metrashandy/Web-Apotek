<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Belanja - Bailu Pharmacy</title>
    <link href="src/output.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>

<body class="bg-gray-50">
    <?php
    session_start();

    // Validasi login dan role user
    if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'user') {
        die("Anda harus login terlebih dahulu.");
    }

    // Ambil ID pelanggan dari sesi
    $id_pelanggan = $_SESSION['id_pelanggan'];

    $mysqli = new mysqli("localhost", "root", "", "apotek");
    if ($mysqli->connect_error) {
        die("Koneksi gagal: " . $mysqli->connect_error);
    }

    // Query untuk riwayat pesanan pelanggan
    $result = $mysqli->query("
        SELECT 
            p.Id_pesanan AS ID_Pesanan,
            p.tanggal_pemesanan AS Tanggal_Pemesanan,
            GROUP_CONCAT(o.Nama_Obat SEPARATOR ', ') AS List_Barang,
            p.Harga_total AS Total_Harga,
            p.`Tipe Pembayaran` AS Tipe_Pembayaran,
            p.status AS Status_Pesanan,
            GROUP_CONCAT(CONCAT(o.Nama_Obat, ';', pd.jumlah_item, ';', pd.harga_satuan) SEPARATOR ', ') AS Detail_Obat
        FROM 
            tb_pesanan p
        JOIN 
            tb_pesanan_detail pd ON p.Id_pesanan = pd.Id_pesanan
        JOIN 
            tb_obat o ON pd.Id_obat = o.Id_Obat
        WHERE 
            p.Id_pelanggan = '$id_pelanggan'
        GROUP BY 
            p.Id_pesanan, p.tanggal_pemesanan, p.Harga_total, p.`Tipe Pembayaran`, p.status
    ");
    ?>

    <!-- Navbar -->
    <header class="sticky py-5">
        <nav class="w-9/12 flex flex-row mx-auto items-center">
            <div class="flex items-center basis-1/4">
                <a href="home.php" class="flex items-center">
                    <img src="image/logo.png" class="h-8 mr-2" alt="logo" />
                    <span class="text-2xl font-semibold text-cyan-600">Bailu Pharmacy</span>
                </a>
            </div>
            <div class="basis-1/4 flex items-center justify-start mr-2">
                <form action="shop.php" method="GET" class="w-full">
                    <input
                        type="text"
                        name="search"
                        placeholder="Search..."
                        class="px-4 py-2 border rounded-lg text-sm border-cyan-600 w-full focus:outline-none focus:ring focus:ring-cyan-300"
                        required />
                </form>
            </div>
            <div class="basis-1/4 flex items-center justify-start">
                <a href="home.php" class="mx-4 font-semibold text-cyan-600 hover:text-cyan-700">
                    <span>HOME</span>
                </a>
                <a href="shop.php" class="mx-4 font-semibold text-cyan-600 hover:text-cyan-700">
                    <span>SHOP</span>
                </a>
                <button onclick="showPopup()" class="mx-4 font-semibold text-cyan-600 hover:text-cyan-700 flex items-center">
                    <img src="image/icon-shop.png" alt="cart" class="h-5 w-5 mr-1" />
                    <span id="cart-count">0</span>
                </button>
            </div>
            <div class="basis-1/4 flex justify-end items-center">
                <?php
                // Tampilkan ikon user dan nama jika sudah login
                if (isset($_SESSION['login']) && $_SESSION['login'] === true && !empty($_SESSION['username'])) {
                    echo '<span class="px-4 py-2 text-cyan-600 font-semibold rounded-lg mr=2">'
                        . htmlspecialchars($_SESSION['username']) . '</span>';
                    echo '<a href="profile.php" class="flex items-center">';
                    echo '  <img src="image/icon-user.png" alt="User" class="h-6 w-6" />';
                    echo '</a>';
                } else {
                    // Jika belum login, tampilkan tombol login
                    echo '<a href="login.php" class="px-4 py-2 bg-cyan-600 text-white rounded-lg font-semibold hover:bg-cyan-700">LOGIN</a>';
                }
                ?>
            </div>
        </nav>
    </header>

    <!-- Pop-up Cart -->
    <div id="popup" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Background overlay -->
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
            <div id="popup-overlay" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="hidePopup()"></div>

            <!-- Modal panel -->
            <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">

                <!-- Modal header -->
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                        <h3 class="text-2xl font-bold text-gray-900" id="modal-title">
                            Keranjang Belanja
                        </h3>
                        <button type="button" onclick="hidePopup()" class="text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Cart content -->
                    <div class="mt-4">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Gambar
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Produk
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Jumlah
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Harga Satuan
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Total
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="cart-items">
                                <!-- Cart items akan diisi oleh cart.js -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="bg-gray-50 px-6 py-4 sm:px-6">
                    <div class="flex flex-col space-y-4">
                        <!-- Biaya Kirim -->
                        <div class="flex items-center">
                            <span class="text-md font-medium text-gray-700 mr-2">Biaya Kirim:</span>
                            <span class="text-md font-semibold text-cyan-600"><span id="biaya-kirim">0</span></span>
                        </div>
                        <!-- Pricing Details -->
                        <div class="flex items-center">
                            <span class="text-md font-medium text-gray-700 mr-2">Total Harga:</span>
                            <span class="text-md font-semibold text-cyan-600"><span id="total-harga">0</span></span>
                        </div>
                        <!-- Total Harga with Action Buttons -->
                        <div class="flex items-center justify-between">
                            <!-- Total Biaya -->
                            <div class="flex items-center border-t border-gray-200 pt-2">
                                <span class="text-xl font-bold text-gray-800 mr-2">Total Biaya:</span>
                                <span class="text-xl font-bold text-cyan-600"><span id="total-biaya">0</span></span>
                            </div>
                            <!-- Action Buttons -->
                            <div class="flex space-x-3">
                                <button type="button" onclick="hidePopup()" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100">
                                    Lanjut Belanja
                                </button>
                                <button type="button" onclick="choosePaymentType()" class="px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700">
                                    Konfirmasi Pesanan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="w-9/12 mx-auto py-8">
        <div class="bg-white rounded-lg shadow-md p-6 max-w-4xl mx-auto">
            <!-- Tab Navigation -->
            <div class="flex border-b border-gray-200 mb-6">
                <a href="profile.php" class="px-6 py-3 text-gray-500 hover:text-gray-700 font-semibold">
                    Profil Saya
                </a>
                <button class="px-6 py-3 border-b-2 border-cyan-600 text-cyan-600 font-semibold">
                    Riwayat Pesanan
                </button>
            </div>

            <!-- Order History Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Pesanan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">List Barang</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pembayaran</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($result as $row): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    #<?= htmlspecialchars($row['ID_Pesanan']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($row['Tanggal_Pemesanan']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <div class="max-w-xs truncate">
                                        <?= htmlspecialchars($row['List_Barang']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    // Tentukan style status
                                    $statusClass = match ($row['Status_Pesanan']) {
                                        'Selesai' => 'bg-green-100 text-green-800',
                                        'Diproses' => 'bg-yellow-100 text-yellow-800',
                                        'Dibatalkan' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                    ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClass ?>">
                                        <?= htmlspecialchars($row['Status_Pesanan']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($row['Tipe_Pembayaran']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <button
                                        onclick="tampilkanRincian(<?= htmlspecialchars(json_encode($row)) ?>)"
                                        class="text-cyan-600 hover:text-cyan-900 font-medium">
                                        Lihat Detail
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Rincian -->
    <div id="modalRincian" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-8 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
            <!-- Header -->
            <div class="flex justify-between items-center pb-4 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900">Detail Pesanan</h3>
                <button onclick="tutupModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Simpan Struk -->
            <div class="mt-6" id="struk-container">
                <!-- Info Pesanan -->
                <div class="grid grid-cols-2 gap-8 mb-6">
                    <div class="space-y-1">
                        <p class="text-sm text-gray-600">ID Pesanan</p>
                        <p class="font-medium text-gray-900" id="rincianId"></p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm text-gray-600">Tanggal Pemesanan</p>
                        <p class="font-medium text-gray-900" id="rincianTanggal"></p>
                    </div>
                </div>

                <!-- Tabel Rincian -->
                <div class="mt-6 border rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-2/5">
                                    Nama Barang
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-1/5">
                                    Jumlah
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-1/5">
                                    Harga Satuan
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-1/5">
                                    Subtotal
                                </th>
                            </tr>
                        </thead>
                        <tbody id="rincianItems" class="bg-white divide-y divide-gray-200">
                            <!-- Baris isi pesanan -->
                        </tbody>
                    </table>
                </div>

                <!-- Ringkasan Biaya -->
                <div class="mt-6 py-5 rounded-lg space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Total Harga Barang</span>
                        <span class="font-medium text-gray-900">Rp. <span id="rincianTotalHarga"></span></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Biaya Pengiriman</span>
                        <span class="font-medium text-gray-900">Rp. 10.000</span>
                    </div>
                    <div class="flex justify-between text-base font-medium pt-3 border-t border-gray-200">
                        <span class="text-gray-900">Total Biaya</span>
                        <span class="text-gray-900">Rp. <span id="rincianTotalBiaya"></span></span>
                    </div>
                </div>

                <!-- Info Tambahan -->
                <div class="mt-6 grid grid-cols-2 gap-8">
                    <div class="space-y-1 mb-5">
                        <p class="text-sm text-gray-600">Tipe Pembayaran</p>
                        <p class="font-medium text-gray-900" id="rincianTipePembayaran"></p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm text-gray-600">Status Pesanan</p>
                        <p class="font-medium" id="rincianStatus"></p>
                    </div>
                </div>
            </div>

            <!-- Tombol Simpan Struk -->
            <div class="flex justify-end mt-4">
                <button
                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-green-700"
                    onclick="simpanStruk()">
                    Simpan Struk
                </button>
            </div>
        </div>
    </div>

    <!-- Script Bagian Akhir -->
    <script src="cart.js"></script>
    <script>
        function tampilkanRincian(data) {
            document.getElementById("rincianId").innerText = '#' + data.ID_Pesanan;
            document.getElementById("rincianTanggal").innerText = data.Tanggal_Pemesanan;

            const items = data.Detail_Obat.split(", ");
            const tbody = document.getElementById("rincianItems");
            tbody.innerHTML = "";

            let totalHarga = 0;

            items.forEach((item) => {
                const [nama, jumlah, harga] = item.split(";");
                const subtotal = jumlah * harga;
                totalHarga += subtotal;

                const row = `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900 whitespace-normal">
                            ${nama}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 text-center">
                            ${jumlah}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 text-right">
                            Rp. ${parseInt(harga).toLocaleString('id-ID')}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 text-right">
                            Rp. ${subtotal.toLocaleString('id-ID')}
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });

            const biayaPengiriman = 10000;
            document.getElementById("rincianTotalHarga").innerText = totalHarga.toLocaleString('id-ID');
            document.getElementById("rincianTotalBiaya").innerText = (totalHarga + biayaPengiriman).toLocaleString('id-ID');
            document.getElementById("rincianTipePembayaran").innerText = data.Tipe_Pembayaran;

            const statusElem = document.getElementById("rincianStatus");
            const statusText = data.Status_Pesanan;
            let statusClass = '';

            switch (statusText) {
                case 'Selesai':
                    statusClass = 'text-green-600 bg-green-100 px-3 py-1 rounded-full';
                    break;
                case 'Diproses':
                    statusClass = 'text-yellow-600 bg-yellow-100 px-3 py-1 rounded-full';
                    break;
                case 'Dibatalkan':
                    statusClass = 'text-red-600 bg-red-100 px-3 py-1 rounded-full';
                    break;
                default:
                    statusClass = 'text-gray-600 bg-gray-100 px-3 py-1 rounded-full';
            }

            statusElem.className = `font-medium ${statusClass}`;
            statusElem.innerText = statusText;

            document.getElementById("modalRincian").classList.remove("hidden");
        }

        function tutupModal() {
            document.getElementById("modalRincian").classList.add("hidden");
        }

        document.getElementById("modalRincian").addEventListener("click", function(e) {
            if (e.target === this) {
                tutupModal();
            }
        });

        // Fungsi menyimpan bagian struk
        async function simpanStruk() {
            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF("p", "pt", "a4");
            const strukElement = document.getElementById('struk-container');

            await html2canvas(strukElement).then((canvas) => {
                const imageData = canvas.toDataURL('image/png');
                const pageWidth = doc.internal.pageSize.getWidth();
                const pageHeight = doc.internal.pageSize.getHeight();

                const imgProps = doc.getImageProperties(imageData);
                const pdfWidth = pageWidth;
                const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

                // Simpan ke PDF
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