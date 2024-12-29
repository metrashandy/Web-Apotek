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

$query        = "SELECT id_obat, nama_obat, stok_obat, harga_satuan, foto_obat ,id_jenis FROM tb_obat LIMIT 12";
$result       = $koneksi->query($query);
$all_products = [];
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $all_products[] = $row;
  }
}
?>

<!doctype html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home - Bailu Pharmacy</title>
  <link href="src/output.css" rel="stylesheet">
</head>
<style>
  /* Gaya untuk overlay pop-up */
  #payment-popup,
  #bank-popup {
    position: fixed;
    inset: 0;
    z-index: 50;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.5);
    /* Latar belakang semi-transparan */
  }

  /* Kontainer pop-up */
  #payment-popup>div,
  #bank-popup>div {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 20px;
    width: 400px;
    /* Lebar kontainer */
  }

  /* Judul dan tombol */
  #payment-popup h3,
  #bank-popup h3 {
    font-size: 1.25rem;
    font-weight: bold;
    color: #333;
    margin-bottom: 1rem;
  }

  #payment-popup button,
  #bank-popup button {
    display: block;
    width: 100%;
    padding: 10px;
    font-size: 1rem;
    font-weight: bold;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.3s ease;
  }

  #payment-popup button:hover,
  #bank-popup button:hover {
    opacity: 0.9;
  }

  #payment-popup .bg-green-500,
  #bank-popup .bg-green-500 {
    background: #38a169;
  }

  #payment-popup .bg-blue-500,
  #bank-popup .bg-blue-500 {
    background: #3182ce;
  }

  #payment-popup .bg-gray-300,
  #bank-popup .bg-gray-300 {
    background: #e2e8f0;
    color: #4a5568;
  }
</style>
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

        <!-- Updated Modal footer -->
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

  <section class="bg-blue-50 py-20">
    <div class="w-10/12 mx-auto flex flex-col md:flex-row items-center">
      <!-- Teks Hero -->
      <div class="md:basis-1/2 text-center md:text-left">
        <h1 class="text-4xl font-bold text-cyan-600 mb-4">
          Selamat Datang di <span class="text-blue-600">Bailu Pharmacy</span>
        </h1>
        <p class="text-lg text-gray-600 mb-6">
          Kami menyediakan obat-obatan terbaik yang Anda butuhkan. Temukan produk kesehatan favorit Anda dengan mudah!
        </p>
        <a href="#shop" class="px-6 py-3 bg-cyan-600 text-white rounded-lg font-semibold hover:bg-cyan-700">
          Belanja Sekarang
        </a>
      </div>
      <!-- Gambar Hero -->
      <div class="md:basis-1/2 mt-8 md:mt-0">
        <img src="image/icon-hero.png" alt="Icon Hero" class="w-full max-w-md mx-auto md:ml-auto" />
      </div>
    </div>
  </section>

  <section class="py-10">
    <div class="w-9/12 mx-auto">
      <h2 class="text-3xl font-bold text-cyan-600 text-center mb-8">Kategori Produk</h2>
      <div class="grid grid-cols-4 gap-6">
        <!-- Obat -->
        <div
          onclick="window.location.href='shop.php?kategori=obat'"
          class="flex flex-col items-center bg-white p-4 rounded-lg shadow-md transition 
               transform hover:scale-105 hover:shadow-lg hover:outline hover:outline-2 hover:outline-cyan-300 cursor-pointer">
          <img src="image/icon-medicine.png" alt="Obat" class="w-20 h-20 mb-2" />
          <p class="text-lg font-semibold text-gray-700">Obat</p>
        </div>
        <!-- Suplemen -->
        <div
          onclick="window.location.href='shop.php?kategori=suplemen'"
          class="flex flex-col items-center bg-white p-4 rounded-lg shadow-md transition
               transform hover:scale-105 hover:shadow-lg hover:outline hover:outline-2 hover:outline-cyan-300 cursor-pointer">
          <img src="image/icon-supplement.png" alt="Suplemen" class="w-20 h-20 mb-2" />
          <p class="text-lg font-semibold text-gray-700">Suplemen</p>
        </div>
        <!-- Vitamin -->
        <div
          onclick="window.location.href='shop.php?kategori=vitamin'"
          class="flex flex-col items-center bg-white p-4 rounded-lg shadow-md transition 
               transform hover:scale-105 hover:shadow-lg hover:outline hover:outline-2 hover:outline-cyan-300 cursor-pointer">
          <img src="image/icon-vitamin.png" alt="Vitamin" class="w-20 h-20 mb-2" />
          <p class="text-lg font-semibold text-gray-700">Vitamin</p>
        </div>
        <!-- Produk Bayi -->
        <div
          onclick="window.location.href='shop.php?kategori=Produk Bayi'"
          class="flex flex-col items-center bg-white p-4 rounded-lg shadow-md transition 
               transform hover:scale-105 hover:shadow-lg hover:outline hover:outline-2 hover:outline-cyan-300 cursor-pointer">
          <img src="image/icon-baby-product.png" alt="Produk Bayi" class="w-20 h-20 mb-2" />
          <p class="text-lg font-semibold text-gray-700">Produk Bayi</p>
        </div>
      </div>
    </div>
  </section>


  <!-- Produk Terlaris -->
  <section class="py-10 bg-gray-50">
    <div class="w-9/12 mx-auto">
      <h2 class="text-3xl font-bold text-cyan-600 text-center mb-8">Produk Terlaris</h2>
      <div class="grid grid-cols-4 gap-6">
        <?php
        if (!empty($all_products)) {
          foreach ($all_products as $prod) {
            // Jika foto_obat ada, tampilkan dalam bentuk gambar, jika tidak, tampilkan gambar default
            if (!empty($prod['foto_obat'])) {
              $image_data = $prod['foto_obat']; // Data gambar BLOB
              $image_base64 = base64_encode($image_data); // Encode gambar ke format base64
              $image_src = 'data:image/jpeg;base64,' . $image_base64; // Set format gambar
            } else {
              // Gambar default jika foto_obat kosong
              $image_src = 'image/products/default.png';
            }
        ?>
            <div class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition">
              <!-- Gambar produk -->
              <img
                src="<?php echo $image_src; ?>"
                alt="<?php echo htmlspecialchars($prod['nama_obat']); ?>"
                class="w-full h-32 object-contain mb-4" />
              <!-- Nama Obat -->
              <h3 class="text-lg font-semibold text-gray-700 mb-2">
                <?php echo htmlspecialchars($prod['nama_obat']); ?>
              </h3>
              <!-- Harga -->
              <p class="text-cyan-600 font-semibold mb-2">
                Rp <?php echo number_format($prod['harga_satuan'], 0, ',', '.'); ?>
              </p>
              <!-- Jumlah Stok -->
              <p class="text-sm text-gray-500 mb-4">
                Stok Tersedia:
                <span class="text-gray-700"><?php echo $prod['stok_obat']; ?></span>
              </p>
              <!-- Tombol Pesan Sekarang -->
              <button
                class="w-full py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition
              <?php echo $prod['stok_obat'] <= 0 ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                onclick="addToCart(<?php echo (int)$prod['id_obat']; ?>, <?php echo (int)$prod['stok_obat']; ?>)"
                <?php echo $prod['stok_obat'] <= 0 ? 'disabled' : ''; ?>>
                <?php echo $prod['stok_obat'] <= 0 ? 'Stok Habis' : 'Pesan Sekarang'; ?>
              </button>
            </div>
        <?php
          }
        } else {
          echo '<p>Tidak ada produk di database.</p>';
        }
        ?>
      </div>
    </div>
  </section>



  <section class="py-10">
    <div class="w-9/12 mx-auto">
      <h2 class="text-3xl font-bold text-cyan-600 text-center mb-8">Testimoni Pelanggan</h2>
      <div class="grid grid-cols-3 gap-6">
        <!-- Testimoni 1 -->
        <div class="bg-white p-6 rounded-lg shadow-md flex flex-col items-center text-center">
          <img src="image/testimoni-1.jpg" alt="Pelanggan 1" class="w-20 h-20 rounded-full mb-4 object-cover" />
          <h3 class="text-lg font-semibold text-gray-700 mb-2">Rahma A.</h3>
          <p class="text-sm text-gray-600 italic">
            "Pelayanan apotek ini sangat cepat, dan produk yang saya beli sampai dalam kondisi sangat baik."
          </p>
        </div>
        <!-- Testimoni 2 -->
        <div class="bg-white p-6 rounded-lg shadow-md flex flex-col items-center text-center">
          <img src="image/testimoni-2.jpg" alt="Pelanggan 2" class="w-20 h-20 rounded-full mb-4 object-cover" />
          <h3 class="text-lg font-semibold text-gray-700 mb-2">Andi R.</h3>
          <p class="text-sm text-gray-600 italic">
            "Harga vitamin di sini sangat terjangkau, dan kualitasnya sangat bagus. Terima kasih Bailu Pharmacy!"
          </p>
        </div>
        <!-- Testimoni 3 -->
        <div class="bg-white p-6 rounded-lg shadow-md flex flex-col items-center text-center">
          <img src="image/testimoni-3.jpg" alt="Pelanggan 3" class="w-20 h-20 rounded-full mb-4 object-cover" />
          <h3 class="text-lg font-semibold text-gray-700 mb-2">Lina P.</h3>
          <p class="text-sm text-gray-600 italic">
            "Sangat puas dengan produk bayi yang saya beli di sini."
          </p>
        </div>
      </div>
    </div>
  </section>

  <section class="bg-cyan-600 py-10">
    <div class="w-9/12 mx-auto text-center text-white">
      <h2 class="text-3xl font-bold mb-4">Temukan Produk Terbaik untuk Kebutuhan Anda!</h2>
      <p class="text-lg mb-6">
        Jelajahi berbagai produk kesehatan, suplemen, dan vitamin di toko kami. Klik tombol di bawah untuk melihat lebih banyak!
      </p>
      <a href="shop.php" class="px-6 py-3 bg-white text-cyan-600 rounded-lg font-semibold hover:bg-gray-100">
        Cari Lebih Banyak Produk!
      </a>
    </div>
  </section>

</body>

</html>