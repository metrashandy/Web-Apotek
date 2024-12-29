let cart = []; // Array untuk menyimpan pesanan sementara

// Load cart from localStorage on page load
document.addEventListener("DOMContentLoaded", () => {
  const storedCart = localStorage.getItem("cart");
  if (storedCart) {
    cart = JSON.parse(storedCart);
    renderCart();
    updateCartCount();
  } else {
    updateCartCount();
  }
});

// Listen for changes in localStorage (optional)
window.addEventListener("storage", (event) => {
  if (event.key === "cart") {
    const updatedCart = JSON.parse(event.newValue);
    cart = updatedCart || [];
    renderCart();
    updateCartCount();
  }
});

// Function to update the cart count in the navbar
function updateCartCount() {
  const cartCountElement = document.getElementById("cart-count");
  if (cartCountElement) {
    const totalQuantity = cart.reduce((total, item) => total + item.jumlah, 0);
    cartCountElement.textContent = totalQuantity;
  }
}

function showPopup() {
  document.getElementById("popup").classList.remove("hidden");
  document.body.style.overflow = "hidden";
  renderCart();
}

function hidePopup() {
  document.getElementById("popup").classList.add("hidden");
  document.body.style.overflow = "auto";
}

function addToCart(idObat, stokObat) {
  fetch(`get_obat.php?id=${idObat}`)
    .then((response) => response.json())
    .then((data) => {
      if (!data) {
        alert("Obat tidak ditemukan!");
        return;
      }

      const existingItem = cart.find((item) => item.id === idObat);

      if (existingItem) {
        if (existingItem.jumlah + 1 > stokObat) {
          alert(`Stok tidak mencukupi! Stok tersedia: ${stokObat}`);
          return;
        }
        existingItem.jumlah++;
      } else {
        if (stokObat < 1) {
          alert("Stok obat habis!");
          return;
        }
        cart.push({
          id: idObat,
          nama: data.Nama_Obat,
          harga: data.Harga_satuan,
          jumlah: 1,
          stokTersedia: stokObat,
          foto_obat: data.foto_obat || "",
        });
      }

      saveCart();
      renderCart();
      updateCartCount();
      showPopup();
    })
    .catch((error) => console.error("Error fetching data:", error));
}

function removeFromCart(index) {
  cart.splice(index, 1);
  saveCart();
  renderCart();
  updateCartCount();
}

function updateQuantity(index, jumlah) {
  const item = cart[index];
  const newQuantity = parseInt(jumlah, 10);

  if (newQuantity <= 0) {
    alert("Jumlah minimal pesanan adalah 1");
    return;
  }

  if (newQuantity > item.stokTersedia) {
    alert(`Stok tidak mencukupi! Stok tersedia: ${item.stokTersedia}`);
    document.querySelector(`#cart-table tbody tr:nth-child(${index + 1}) input`).value = item.jumlah;
    return;
  }

  item.jumlah = newQuantity;
  saveCart();
  renderCart();
  updateCartCount();
}

function renderCart() {
  const tbody = document.getElementById("cart-items");
  const totalHargaEl = document.getElementById("total-harga");
  tbody.innerHTML = "";
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
    totalHargaEl.textContent = "0";
    return;
  }

  cart.forEach((item, index) => {
    let imageSrc = "image/products/default.png";
    if (item.foto_obat && item.foto_obat.trim() !== "") {
      imageSrc = `data:image/jpeg;base64,${item.foto_obat}`;
    }

    const row = document.createElement("tr");
    row.innerHTML = `
        <td class="px-6 py-4 whitespace-nowrap">
          <img
            src="${imageSrc}"
            alt="${item.nama}"
            class="h-16 w-16 object-cover rounded-lg"
          >
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <div class="text-sm font-medium text-gray-900">${item.nama}</div>
          <div class="text-sm text-gray-500">Stok: ${item.stokTersedia}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <input
            type="number"
            class="w-20 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-cyan-500 focus:border-cyan-500"
            value="${item.jumlah}"
            min="1"
            max="${item.stokTersedia}"
            onchange="updateQuantity(${index}, this.value)"
          >
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
          Rp ${numberFormat(item.harga)}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
          Rp ${numberFormat(item.harga * item.jumlah)}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
          <button
            onclick="removeFromCart(${index})"
            class="text-red-600 hover:text-red-900 bg-red-100 hover:bg-red-200 px-3 py-1 rounded-md transition-colors"
          >
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
  return new Intl.NumberFormat("id-ID").format(number);
}

function choosePaymentType() {
  const paymentPopupHTML = `
    <div id="payment-popup" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-50">
      <div class="bg-white rounded-lg shadow-lg p-6 w-96">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Pilih Tipe Pembayaran</h3>
        <div class="space-y-4">
          <button onclick="processCOD()" class="w-full px-4 py-2 bg-green-500 text-white font-medium rounded hover:bg-green-600">
            Bayar di Tempat (COD)
          </button>
          <button onclick="showBankTransferOptions()" class="w-full px-4 py-2 bg-blue-500 text-white font-medium rounded hover:bg-blue-600">
            Transfer Bank
          </button>
        </div>
      </div>
    </div>
  `;
  document.body.insertAdjacentHTML("beforeend", paymentPopupHTML);
}

function processCOD() {
  submitOrder("COD", null);
  closePopups();
}

function showBankTransferOptions() {
  const bankPopupHTML = `
    <div id="bank-popup" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-50">
      <div class="bg-white rounded-lg shadow-lg p-6 w-96">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Transfer Bank</h3>
        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Bank</label>
        <select id="bank-select" class="block w-full border-gray-300 rounded-md mb-4" onchange="updateBankDetails()">
          <option value="Mandiri">Bank Mandiri</option>
          <option value="BNI">Bank BNI</option>
          <option value="BCA">Bank BCA</option>
          <option value="BRI">Bank BRI</option>
        </select>
        <p id="bank-details" class="text-sm text-gray-500 mb-4">
          Nomor Rekening: <strong>1234567890</strong> (Mandiri)
        </p>
        <label class="block text-sm font-medium text-gray-700 mb-2">Upload Bukti Transfer</label>
        <input type="file" id="proof-of-transfer" accept=".jpg,.jpeg,.png,.pdf" class="block w-full border-gray-300 rounded-md mb-4">
        <div class="text-sm text-red-500 mb-2 hidden" id="file-size-error">Ukuran file tidak boleh lebih dari 1 MB.</div>
        <div class="flex justify-end space-x-3">
          <button onclick="closePopups()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
            Batal
          </button>
          <button onclick="processBankTransfer()" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            Konfirmasi
          </button>
        </div>
      </div>
    </div>
  `;
  closePopups();
  document.body.insertAdjacentHTML("beforeend", bankPopupHTML);
  updateBankDetails(); // Set default bank details
}

function closePopups() {
  document.querySelectorAll("#payment-popup, #bank-popup").forEach((popup) => popup.remove());
}

function processBankTransfer() {
  const proofInput = document.getElementById("proof-of-transfer");
  const bankSelect = document.getElementById("bank-select");
  const proofFile = proofInput.files[0];
  const selectedBank = bankSelect.value;

  if (!proofFile) {
    alert("Silakan upload bukti transfer.");
    return;
  }

  if (!selectedBank) {
    alert("Silakan pilih bank.");
    return;
  }

  const formData = new FormData();
  formData.append("proof_of_transfer", proofFile);
  formData.append("selected_bank", selectedBank);

  submitOrder(`Transfer Bank ${selectedBank}`, formData);
  closePopups();
}

function submitOrder(paymentType, additionalData) {
  if (cart.length === 0) {
    alert("Keranjang kosong!");
    return;
  }

  const formData = new FormData();
  cart.forEach((item) => {
    formData.append("Id_Obat[]", item.id);
    formData.append("jumlah_item[]", item.jumlah);
  });
  formData.append("payment_type", paymentType);

  if (additionalData) {
    for (const [key, value] of additionalData.entries()) {
      formData.append(key, value);
    }
  }

  fetch("pesanan.action.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      if (response.ok) {
        alert("Pesanan berhasil dikonfirmasi!");
        cart = [];
        saveCart();
        renderCart();
        updateCartCount();
      } else {
        alert("Gagal mengkonfirmasi pesanan.");
      }
    })
    .catch((error) => console.error("Error submitting order:", error));
}

function saveCart() {
  localStorage.setItem("cart", JSON.stringify(cart));
}
