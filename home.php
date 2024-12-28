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

<script>
  let cart = []; // Array untuk menyimpan pesanan sementara

  // Function to update the cart count in the navbar
  function updateCartCount() {
    const cartCountElement = document.getElementById('cart-count');
    if (cartCountElement) {
      // Calculate total quantity
      const totalQuantity = cart.reduce((total, item) => total + item.jumlah, 0);
      cartCountElement.textContent = totalQuantity;
    }
  }

  function showPopup() {
    document.getElementById('popup').style.display = 'block';
    document.getElementById('popup-overlay').style.display = 'block';
    renderCart();
  }

  function hidePopup() {
    document.getElementById('popup').style.display = 'none';
    document.getElementById('popup-overlay').style.display = 'none';
  }

  function addToCart(idObat, stokObat) {
    fetch(`get_obat.php?id=${idObat}`)
      .then(response => response.json())
      .then(data => {
        if (!data) {
          alert('Obat tidak ditemukan!');
          return;
        }

        const existingItem = cart.find(item => item.id === idObat);

        if (existingItem) {
          if (existingItem.jumlah + 1 > stokObat) {
            alert(`Stok tidak mencukupi! Stok tersedia: ${stokObat}`);
            return;
          }
          existingItem.jumlah++;
        } else {
          if (stokObat < 1) {
            alert('Stok obat habis!');
            return;
          }
          cart.push({
            id: idObat,
            nama: data.Nama_Obat,
            harga: data.Harga_satuan,
            jumlah: 1,
            stokTersedia: stokObat,
            gambar: data.gambar || 'default.png'
          });
        }
        renderCart();
        updateCartCount();
        showPopup();
      })
      .catch(error => console.error('Error fetching data:', error));
  }

  function removeFromCart(index) {
    cart.splice(index, 1);
    renderCart();
    updateCartCount(); // Update cart count after removing
  }

  function updateQuantity(index, jumlah) {
    const item = cart[index];
    const newQuantity = parseInt(jumlah, 10);

    // Check if the new quantity is valid
    if (newQuantity <= 0) {
      alert('Jumlah minimal pesanan adalah 1');
      return;
    }

    // Check if the new quantity exceeds available stock
    if (newQuantity > item.stokTersedia) {
      alert(`Stok tidak mencukupi! Stok tersedia: ${item.stokTersedia}`);
      // Reset the input to previous valid value
      document.querySelector(`#cart-table tbody tr:nth-child(${index + 1}) input`).value = item.jumlah;
      return;
    }

    item.jumlah = newQuantity;
    renderCart();
    updateCartCount();
  }

  function renderCart() {
    const tbody = document.getElementById('cart-items');
    const totalHargaEl = document.getElementById('total-harga');
    tbody.innerHTML = '';
    let totalHarga = 0;

    if (cart.length === 0) {
      tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-10 text-center">
                    <p class="text-gray-500 text-lg">Keranjang belanja kosong</p>
                    <p class="text-gray-400 text-sm mt-1">Silakan tambahkan produk ke keranjang</p>
                </td>
            </tr>
        `;
      totalHargaEl.textContent = '0';
      return;
    }

    cart.forEach((item, index) => {
      const row = document.createElement('tr');
      row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <img src="image/products/${item.gambar || 'default.png'}" 
                     alt="${item.nama}" 
                     class="h-16 w-16 object-cover rounded-lg">
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">${item.nama}</div>
                <div class="text-sm text-gray-500">Stok: ${item.stokTersedia}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <input type="number" 
                       class="w-20 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-cyan-500 focus:border-cyan-500"
                       value="${item.jumlah}" 
                       min="1" 
                       max="${item.stokTersedia}"
                       onchange="updateQuantity(${index}, this.value)">
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                Rp ${numberFormat(item.harga)}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                Rp ${numberFormat(item.harga * item.jumlah)}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button onclick="removeFromCart(${index})" 
                        class="text-red-600 hover:text-red-900 bg-red-100 hover:bg-red-200 px-3 py-1 rounded-md transition-colors">
                    Hapus
                </button>
            </td>
        `;
      tbody.appendChild(row);
      totalHarga += item.harga * item.jumlah;
    });

    totalHargaEl.textContent = numberFormat(totalHarga);
  }

  function numberFormat(number) {
    return new Intl.NumberFormat('id-ID').format(number);
  }

  function showPopup() {
    document.getElementById('popup').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    renderCart();
  }

  function hidePopup() {
    document.getElementById('popup').classList.add('hidden');
    document.body.style.overflow = 'auto';
  }

  function submitOrder() {
    if (cart.length === 0) {
      alert('Keranjang kosong!');
      return;
    }

    const formData = new FormData();
    cart.forEach((item) => {
      formData.append('Id_Obat[]', item.id);
      formData.append('jumlah_item[]', item.jumlah);
    });

    fetch('pesanan.action.php', {
        method: 'POST',
        body: formData
      })
      .then(response => {
        if (response.ok) {
          alert('Pesanan berhasil dikonfirmasi!');
          cart = [];
          renderCart();
          updateCartCount(); // Reset cart count after successful order
          hidePopup();
        } else {
          alert('Gagal mengkonfirmasi pesanan.');
        }
      })
      .catch(error => console.error('Error submitting order:', error));
  }

  // Initialize cart count on page load
  document.addEventListener('DOMContentLoaded', updateCartCount);
</script>

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
          <div class="flex justify-between items-center w-full">
            <div class="text-lg font-semibold text-gray-900">
              Total: Rp <span id="total-harga" class="text-cyan-600">0</span>
            </div>
            <div class="flex space-x-3">
              <button type="button" onclick="hidePopup()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 sm:mt-0 sm:w-auto sm:text-sm">
                Lanjut Belanja
              </button>
              <button type="button" onclick="submitOrder()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-cyan-600 text-base font-medium text-white hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 sm:ml-3 sm:w-auto sm:text-sm">
                Konfirmasi Pesanan
              </button>
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
          class="flex flex-col items-center bg-white p-4 rounded-lg shadow-md transition 
                 transform hover:scale-105 hover:shadow-lg hover:outline hover:outline-2 hover:outline-cyan-300">
          <img src="image/icon-medicine.png" alt="Obat" class="w-20 h-20 mb-2" />
          <p class="text-lg font-semibold text-gray-700">Obat</p>
        </div>
        <!-- Suplemen -->
        <div
          class="flex flex-col items-center bg-white p-4 rounded-lg shadow-md transition
                 transform hover:scale-105 hover:shadow-lg hover:outline hover:outline-2 hover:outline-cyan-300">
          <img src="image/icon-supplement.png" alt="Suplemen" class="w-20 h-20 mb-2" />
          <p class="text-lg font-semibold text-gray-700">Suplemen</p>
        </div>
        <!-- Vitamin -->
        <div
          class="flex flex-col items-center bg-white p-4 rounded-lg shadow-md transition 
                 transform hover:scale-105 hover:shadow-lg hover:outline hover:outline-2 hover:outline-cyan-300">
          <img src="image/icon-vitamin.png" alt="Vitamin" class="w-20 h-20 mb-2" />
          <p class="text-lg font-semibold text-gray-700">Vitamin</p>
        </div>
        <!-- Produk Bayi -->
        <div
          class="flex flex-col items-center bg-white p-4 rounded-lg shadow-md transition 
                 transform hover:scale-105 hover:shadow-lg hover:outline hover:outline-2 hover:outline-cyan-300">
          <img src="image/icon-baby-product.png" alt="Produk Bayi" class="w-20 h-20 mb-2" />
          <p class="text-lg font-semibold text-gray-700">Produk Bayi</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Produk Terlaris -->
  <section class="bg-blue-50 py-20">
    <div class="container mx-auto">
      <h2 class="text-center text-4xl font-bold text-gray-800 mb-12">Produk Kami</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php foreach ($all_products as $product): ?>
          <div class="bg-white rounded-lg shadow-md p-4 flex flex-col items-center">
            <!-- Menampilkan gambar obat langsung dari database -->
            <?php
            // Jika foto_obat ada, tampilkan dalam bentuk gambar, jika tidak, tampilkan gambar default
            if (!empty($product['foto_obat'])) {
              $image_data = $product['foto_obat']; // Data gambar BLOB
              $image_base64 = base64_encode($image_data); // Encode gambar ke format base64
              $image_src = 'data:image/jpeg;base64,' . $image_base64; // Set format gambar
            } else {
              // Gambar default jika foto_obat kosong
              $image_src = 'image/products/default.png';
            }
            ?>
            <img
              src="<?php echo $image_src; ?>"
              alt="<?php echo htmlspecialchars($product['nama_obat']); ?>"
              class="h-32 w-32 object-cover mb-4 rounded-md">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">
              <?php echo htmlspecialchars($product['nama_obat']); ?>
            </h3>
            <p class="text-gray-500 mb-2">Stok: <?php echo htmlspecialchars($product['stok_obat']); ?></p>
            <p class="text-gray-800 font-bold mb-4">Rp <?php echo number_format($product['harga_satuan'], 0, ',', '.'); ?></p>
            <button
              onclick="addToCart(<?php echo $product['id_obat']; ?>, <?php echo $product['stok_obat']; ?>)"
              class="bg-cyan-600 text-white px-4 py-2 rounded-md hover:bg-cyan-700 transition">
              Tambah ke Keranjang
            </button>
          </div>
        <?php endforeach; ?>
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