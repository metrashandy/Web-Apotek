<?php
session_start();

$host   = 'localhost';
$user   = 'root';
$pass   = '';
$dbname = 'apotek';

$koneksi = new mysqli($host, $user, $pass, $dbname);
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Filter dan Sorting
$where = "";
$order = " ORDER BY nama_obat ASC";

if (!empty($_GET['kategori'])) {
    $kategori = $koneksi->real_escape_string($_GET['kategori']);
    $where .= " AND tb_jenis_obat.nama_jenis = '$kategori'";
}
if (!empty($_GET['bentuk'])) {
    $bentuk = $koneksi->real_escape_string($_GET['bentuk']);
    $where .= " AND tb_jenis_obat.bentuk_obat = '$bentuk'";
}
if (!empty($_GET['urutkan'])) {
    $urutkan = $_GET['urutkan'];
    if ($urutkan === 'Z-A') {
        $order = " ORDER BY nama_obat DESC";
    }
}

$query = "SELECT tb_obat.id_obat, tb_obat.nama_obat, tb_obat.stok_obat, tb_obat.harga_satuan, tb_obat.foto_obat, tb_jenis_obat.nama_jenis, tb_jenis_obat.bentuk_obat 
          FROM tb_obat 
          JOIN tb_jenis_obat ON tb_obat.id_jenis = tb_jenis_obat.id_jenis 
          WHERE 1=1 $where $order";

$result = $koneksi->query($query);
$all_products = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $all_products[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - Bailu Pharmacy</title>
    <link href="src/output.css" rel="stylesheet">
</head>

<script src="cart.js"></script>

<body>
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
                <input
                    type="text"
                    placeholder="Search..."
                    class="px-4 py-2 border rounded-lg text-sm border-cyan-600 w-full focus:outline-none focus:ring focus:ring-cyan-300" />
            </div>
            <div class="basis-1/4 flex items-center justify-start">
                <a href="home.php" class="mx-4 font-semibold text-cyan-600 hover:text-cyan-700">
                    <span>HOME</span>
                </a>
                <a href="shop.php" class="mx-4 font-semibold text-cyan-600 hover:text-cyan-700">
                    <span>SHOP</span>
                </a>
                <a href="#" class="mx-4 font-semibold text-cyan-600 hover:text-cyan-700">
                    <span>ABOUT</span>
                </a>
                <!-- Updated Cart Button with Dynamic Count -->
                <button onclick="showPopup()" class="mx-4 font-semibold text-cyan-600 hover:text-cyan-700 flex items-center">
                    <img src="image/icon-shop.png" alt="cart" class="h-5 w-5 mr-1" />
                    <span id="cart-count">0</span> <!-- Updated Span -->
                </button>
            </div>
            <div class="basis-1/4 flex justify-end items-center">
                <?php
                // Tampilkan ikon user dan nama jika sudah login, atau tombol login jika belum
                if (isset($_SESSION['login']) && $_SESSION['login'] === true && !empty($_SESSION['username'])) {
                    // Tampilkan nama user
                    echo '<span class="px-4 py-2 text-cyan-600 font-semibold rounded-lg mr-2">'
                        . htmlspecialchars($_SESSION['username']) . '</span>';
                    // Tampilkan ikon user yang dapat diklik
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

    <!-- Pop-up Cart dengan Tailwind -->
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
                                <!-- Cart items will be inserted here by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <div class="flex flex-col space-y-4 w-full">
                        <!-- Total Harga -->
                        <div class="text-lg font-semibold text-gray-900">
                            Total Harga: Rp <span id="total-harga" class="text-cyan-600">0</span>
                        </div>

                        <!-- Biaya Kirim -->
                        <div class="text-lg font-semibold text-gray-900">
                            Biaya Kirim: Rp <span id="biaya-kirim" class="text-cyan-600">0</span>
                        </div>

                        <!-- Total Biaya -->
                        <div class="text-lg font-semibold text-gray-900">
                            Total Biaya: Rp <span id="total-biaya" class="text-cyan-600">0</span>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="flex justify-between items-center w-full">
                            <div class="flex space-x-3">
                                <button type="button" onclick="hidePopup()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 sm:mt-0 sm:w-auto sm:text-sm">
                                    Lanjut Belanja
                                </button>
                                <button type="button" onclick="choosePaymentType()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-cyan-600 text-base font-medium text-white hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 sm:ml-3 sm:w-auto sm:text-sm">
                                    Konfirmasi Pesanan
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Dropdown -->
    <main>
        <div class="w-full md:w-9/12 mx-auto">
            <div class="flex flex-wrap gap-4 mt-8 mb-4 justify-center">
                <!-- Dropdown Kategori -->
                <select id="kategori" class="border px-4 py-2 rounded shadow-sm focus:outline-none focus:ring focus:ring-cyan-300">
                    <option value="">Semua Kategori</option>
                    <option value="Obat">Obat</option>
                    <option value="Suplemen">Suplemen</option>
                    <option value="Vitamin">Vitamin</option>
                    <option value="Produk Bayi">Produk Bayi</option>
                </select>

                <!-- Dropdown Bentuk Obat -->
                <select id="bentuk" class="border px-4 py-2 rounded shadow-sm focus:outline-none focus:ring focus:ring-cyan-300">
                    <option value="">Semua Bentuk</option>
                    <option value="Tablet">Tablet</option>
                    <option value="Sirup">Sirup</option>
                    <option value="kapsul">Kapsul</option>
                    <option value="bubuk">Bubuk</option>
                    <option value="makanan">Makanan</option>
                </select>

                <!-- Dropdown Urutkan -->
                <select id="urutkan" class="border px-4 py-2 rounded shadow-sm focus:outline-none focus:ring focus:ring-cyan-300">
                    <option value="A-Z">Judul (A-Z)</option>
                    <option value="Z-A">Judul (Z-A)</option>
                </select>

                <button onclick="applyFilters()" class="bg-cyan-600 text-white px-4 py-2 rounded shadow hover:bg-cyan-700 transition">
                    Filter
                </button>
            </div>

            <section class="py-10 bg-gray-50">
                <div class="w-full md:w-9/12 mx-auto">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <?php
                        if (!empty($all_products)) {
                            foreach ($all_products as $prod) {
                                if (!empty($prod['foto_obat'])) {
                                    $image_data = $prod['foto_obat'];
                                    $image_base64 = base64_encode($image_data);
                                    $image_src = 'data:image/jpeg;base64,' . $image_base64;
                                } else {
                                    $image_src = 'image/products/default.png';
                                }
                        ?>
                                <div class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition transform hover:-translate-y-1">
                                    <!-- Gambar produk -->
                                    <img src="<?php echo $image_src; ?>" alt="<?php echo htmlspecialchars($prod['nama_obat']); ?>" class="w-full h-32 object-cover rounded-md mb-4" />
                                    <!-- Nama Obat -->
                                    <h3 class="text-lg font-semibold text-gray-700 mb-2"><?php echo htmlspecialchars($prod['nama_obat']); ?></h3>
                                    <!-- Harga -->
                                    <p class="text-cyan-600 font-semibold mb-2">Rp <?php echo number_format($prod['harga_satuan'], 0, ',', '.'); ?></p>
                                    <!-- Jumlah Stok -->
                                    <p class="text-sm text-gray-500 mb-4">Stok Tersedia: <span class="text-gray-700"><?php echo $prod['stok_obat']; ?></span></p>
                                    <!-- Tombol Pesan Sekarang -->
                                    <button class="w-full py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition <?php echo $prod['stok_obat'] <= 0 ? 'opacity-50 cursor-not-allowed' : ''; ?>" onclick="addToCart(<?php echo (int)$prod['id_obat']; ?>, <?php echo (int)$prod['stok_obat']; ?>)" <?php echo $prod['stok_obat'] <= 0 ? 'disabled' : ''; ?>>
                                        <?php echo $prod['stok_obat'] <= 0 ? 'Stok Habis' : 'Pesan Sekarang'; ?>
                                    </button>
                                </div>
                        <?php
                            }
                        } else {
                            echo '<p class="text-center text-gray-500">Tidak ada produk di database.</p>';
                        }
                        ?>
                    </div>
                </div>
            </section>
        </div>

        <script>
            function toggleDropdown1() {
                let dropdown1 = document.getElementById('dropdown1');
                let dropdown2 = document.getElementById('dropdown2');
                let dropdown3 = document.getElementById('dropdown3');

                if (dropdown1.classList.contains('hidden')) {
                    dropdown1.classList.remove('hidden');
                    dropdown2.classList.add('hidden');
                    dropdown3.classList.add('hidden');
                } else {
                    dropdown1.classList.add('hidden');
                }
            }

            function toggleDropdown2() {
                let dropdown1 = document.getElementById('dropdown1');
                let dropdown2 = document.getElementById('dropdown2');
                let dropdown3 = document.getElementById('dropdown3');

                if (dropdown2.classList.contains('hidden')) {
                    dropdown2.classList.remove('hidden');
                    dropdown1.classList.add('hidden');
                    dropdown3.classList.add('hidden');
                } else {
                    dropdown2.classList.add('hidden');
                }
            }

            function toggleDropdown3() {
                let dropdown1 = document.getElementById('dropdown1');
                let dropdown2 = document.getElementById('dropdown2');
                let dropdown3 = document.getElementById('dropdown3');

                if (dropdown3.classList.contains('hidden')) {
                    dropdown3.classList.remove('hidden');
                    dropdown1.classList.add('hidden');
                    dropdown2.classList.add('hidden');
                } else {
                    dropdown3.classList.add('hidden');
                }
            }

            function applyFilters() {
                const kategori = document.getElementById('kategori').value;
                const bentuk = document.getElementById('bentuk').value;
                const urutkan = document.getElementById('urutkan').value;

                let params = new URLSearchParams();
                if (kategori) params.append('kategori', kategori);
                if (bentuk) params.append('bentuk', bentuk);
                if (urutkan) params.append('urutkan', urutkan);

                window.location.href = '?' + params.toString();
            }
        </script>
        </div>
    </main>
</body>

</html>