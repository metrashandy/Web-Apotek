<!doctype html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home - Bailu Pharmacy</title>
  <link href="src/output.css" rel="stylesheet">
</head>
<style>
  #popup table,
  #popup th,
  #popup td {
    border: 1px solid #ccc;
    padding: 8px;
    text-align: center;
  }

  #popup button {
    margin-top: 10px;
    padding: 10px 20px;
    background: #28a745;
    color: #fff;
    border: none;
    cursor: pointer;
  }

  #popup button:hover {
    background: #218838;
  }
</style>
<script>
  let cart = []; // Array untuk menyimpan pesanan sementara

  function showPopup() {
    document.getElementById('popup').style.display = 'block';
    document.getElementById('popup-overlay').style.display = 'block';
    renderCart();
  }

  function hidePopup() {
    document.getElementById('popup').style.display = 'none';
    document.getElementById('popup-overlay').style.display = 'none';
  }

  function addToCart(idObat) {
    fetch(`get_obat.php?id=${idObat}`)
      .then(response => response.json())
      .then(data => {
        if (!data) {
          alert('Obat tidak ditemukan!');
          return;
        }

        const existingItem = cart.find(item => item.id === idObat);
        if (existingItem) {
          existingItem.jumlah++;
        } else {
          cart.push({
            id: idObat,
            nama: data.Nama_Obat,
            harga: data.Harga_satuan,
            jumlah: 1,
          });
        }

        // Tampilkan popup setelah data ditambahkan
        renderCart();
        showPopup();
      })
      .catch(error => console.error('Error fetching data:', error));
  }


  function removeFromCart(index) {
    cart.splice(index, 1);
    renderCart();
  }

  function updateQuantity(index, jumlah) {
    if (jumlah > 0) {
      cart[index].jumlah = jumlah;
    }
    renderCart();
  }

  function renderCart() {
    const tbody = document.querySelector('#cart-table tbody');
    const totalHargaEl = document.getElementById('total-harga');
    tbody.innerHTML = '';
    let totalHarga = 0;

    cart.forEach((item, index) => {
      const row = document.createElement('tr');
      row.innerHTML = `
            <td>${item.nama}</td>
            <td>
                <input type="number" value="${item.jumlah}" min="1" onchange="updateQuantity(${index}, this.value)">
            </td>
            <td>Rp ${item.harga}</td>
            <td>Rp ${item.harga * item.jumlah}</td>
            <td><button onclick="removeFromCart(${index})">Hapus</button></td>
        `;
      tbody.appendChild(row);
      totalHarga += item.harga * item.jumlah;
    });

    totalHargaEl.textContent = totalHarga;
  }

  function submitOrder() {
  if (cart.length === 0) {
    alert('Keranjang kosong!');
    return;
  }

  // Membuat FormData untuk mengirim data dalam bentuk form
  const formData = new FormData();
  
  // Menambahkan data obat dan jumlah item ke dalam FormData
  cart.forEach((item, index) => {
    formData.append('Id_Obat[]', item.id);
    formData.append('jumlah_item[]', item.jumlah);
  });

  // Mengirim data menggunakan metode POST
  fetch('pesanan.action.php', {
    method: 'POST',
    body: formData,
  })
  .then(response => {
    if (response.ok) {
      alert('Pesanan berhasil dikonfirmasi!');
      cart = [];
      renderCart();
      hidePopup();
    } else {
      alert('Gagal mengkonfirmasi pesanan.');
    }
  })
  .catch(error => console.error('Error submitting order:', error));
}

</script>


<body>
  <!-- Navbar -->
  <?php include "navbar.php"; ?>
  <!-- Pop-Up -->
  <div id="popup" style="display: none; position: fixed; top: 10%; left: 10%; width: 80%; background: #fff; border: 1px solid #ccc; padding: 20px; z-index: 1000;">
    <h2>Detail Pesanan</h2>
    <table id="cart-table" style="width: 100%; border-collapse: collapse;">
      <thead>
        <tr>
          <th>Nama Obat</th>
          <th>Jumlah</th>
          <th>Harga Satuan</th>
          <th>Total</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
    <p><strong>Total Harga:</strong> Rp <span id="total-harga">0</span></p>
    <button onclick="hidePopup()">Tutup</button>
    <button onclick="submitOrder()">Konfirmasi Pesanan</button>
  </div>

  <!-- Overlay untuk Background Gelap -->
  <div id="popup-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 999;" onclick="hidePopup()"></div>


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
        <button onclick=" showPopup()">Konfirmasi Pesanan</button>
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
        <div class="flex flex-col items-center bg-white p-4 rounded-lg shadow-md transition transform hover:scale-105 hover:shadow-lg hover:outline hover:outline-2 hover:outline-cyan-300">
          <img src="image/icon-medicine.png" alt="Obat" class="w-20 h-20 mb-2" />
          <p class="text-lg font-semibold text-gray-700">Obat</p>
        </div>
        <!-- Suplemen -->
        <div class="flex flex-col items-center bg-white p-4 rounded-lg shadow-md transition transform hover:scale-105 hover:shadow-lg hover:outline hover:outline-2 hover:outline-cyan-300">
          <img src="image/icon-supplement.png" alt="Suplemen" class="w-20 h-20 mb-2" />
          <p class="text-lg font-semibold text-gray-700">Suplemen</p>
        </div>
        <!-- Vitamin -->
        <div class="flex flex-col items-center bg-white p-4 rounded-lg shadow-md transition transform hover:scale-105 hover:shadow-lg hover:outline hover:outline-2 hover:outline-cyan-300">
          <img src="image/icon-vitamin.png" alt="Vitamin" class="w-20 h-20 mb-2" />
          <p class="text-lg font-semibold text-gray-700">Vitamin</p>
        </div>
        <!-- Produk Bayi -->
        <div class="flex flex-col items-center bg-white p-4 rounded-lg shadow-md transition transform hover:scale-105 hover:shadow-lg hover:outline hover:outline-2 hover:outline-cyan-300">
          <img src="image/icon-baby-product.png" alt="Produk Bayi" class="w-20 h-20 mb-2" />
          <p class="text-lg font-semibold text-gray-700">Produk Bayi</p>
        </div>
      </div>
    </div>
  </section>

  <section class="py-10 bg-gray-50">
    <div class="w-9/12 mx-auto">
      <h2 class="text-3xl font-bold text-cyan-600 text-center mb-8 ">Produk Terlaris</h2>
      <div class="grid grid-cols-4 gap-6">
        <!-- Produk 1 -->
        <div class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition">
          <img src="image/obat-antimo.jpg" alt="Produk 1" class="w-full h-32 object-contain mb-4" />
          <h3 class="text-lg font-semibold text-gray-700 mb-2">ANTIMO HERBAL 15 ML BOX 10 SACHET</h3>
          <p class="text-cyan-600 font-semibold mb-2">Rp 10.000</p>
          <!-- Form Pemesanan -->
          <button onclick="addToCart(1)">Pesan Sekarang</button>


        </div>

        <!-- Produk 2 -->
        <div class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition">
          <img src="image/obat-panadol.png" alt="Produk 2" class="w-full h-32 object-contain mb-4" />
          <h3 class="text-lg font-semibold text-gray-700 mb-2">PANADOL STRIP ISI 10 KAPLET</h3>
          <p class="text-cyan-600 font-semibold mb-2">Rp 12.000</p>
          <button onclick="addToCart(2)">Pesan Sekarang</button>

        </div>
        <!-- Produk 3 -->
        <div class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition">
          <img src="image/VITACIMIN-500-MG-20-TABLET.png" alt="Produk 3" class="w-full h-32 object-contain mb-4" />
          <h3 class="text-lg font-semibold text-gray-700 mb-2">VITACIMIN 500 MG 20 TABLET</h3>
          <p class="text-cyan-600 font-semibold mb-2">Rp 23.000</p>
          <button class="w-full py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition">Beli Sekarang</button>
        </div>
        <!-- Produk 4 -->
        <div class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition">
          <img src="image/CROWN-KANTONG-ASI-PACK-30-PCS.png" alt="Produk 4" class="w-full h-32 object-contain mb-4" />
          <h3 class="text-lg font-semibold text-gray-700 mb-2">CROWN KANTONG ASI PACK 30 PCS</h3>
          <p class="text-cyan-600 font-semibold mb-2">Rp 85.000</p>
          <button class="w-full py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition">Beli Sekarang</button>
        </div>
        <!-- Produk 1 -->
        <div class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition">
          <img src="image/obat-antimo.jpg" alt="Produk 1" class="w-full h-32 object-contain mb-4" />
          <h3 class="text-lg font-semibold text-gray-700 mb-2">ANTIMO HERBAL 15 ML BOX 10 SACHET</h3>
          <p class="text-cyan-600 font-semibold mb-2">Rp 10.000</p>
          <button class="w-full py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition">Beli Sekarang</button>
        </div>
        <!-- Produk 2 -->
        <div class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition">
          <img src="image/obat-panadol.png" alt="Produk 2" class="w-full h-32 object-contain mb-4" />
          <h3 class="text-lg font-semibold text-gray-700 mb-2">PANADOL STRIP ISI 10 KAPLET</h3>
          <p class="text-cyan-600 font-semibold mb-2">Rp 12.000</p>
          <button class="w-full py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition">Beli Sekarang</button>
        </div>
        <!-- Produk 3 -->
        <div class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition">
          <img src="image/VITACIMIN-500-MG-20-TABLET.png" alt="Produk 3" class="w-full h-32 object-contain mb-4" />
          <h3 class="text-lg font-semibold text-gray-700 mb-2">VITACIMIN 500 MG 20 TABLET</h3>
          <p class="text-cyan-600 font-semibold mb-2">Rp 23.000</p>
          <button class="w-full py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition">Beli Sekarang</button>
        </div>
        <!-- Produk 4 -->
        <div class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition">
          <img src="image/CROWN-KANTONG-ASI-PACK-30-PCS.png" alt="Produk 4" class="w-full h-32 object-contain mb-4" />
          <h3 class="text-lg font-semibold text-gray-700 mb-2">CROWN KANTONG ASI PACK 30 PCS</h3>
          <p class="text-cyan-600 font-semibold mb-2">Rp 85.000</p>
          <button class="w-full py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition">Beli Sekarang</button>
        </div>
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