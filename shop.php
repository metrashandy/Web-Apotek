<?php
session_start();
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

    <!-- Dropdown -->
    <main>
        <div class="w-8/12 mx-auto">
            <div class="flex flex-row gap-20 mt-8 mb-2">
                <div class="relative select-none flex-1" id="dropdownButton1">
                    <div onclick="toggleDropdown1()" class="border-solid border-cyan-400 border-2 px-5 py-2 rounded cursor-pointer flex justify-between w-full shadow-sm">
                        KATEGORI
                        <img src="image/arrowdown.svg" alt="" width="10">
                    </div>
                    <div class="rounded border-2 border-cyan-400 absolute top-10 w-full shadow-md hidden z-10 bg-white font-semibold" id="dropdown1">
                        <div class="cursor-pointer hover:bg-cyan-500 p-4">OBAT</div>
                        <div class="cursor-pointer hover:bg-cyan-500 p-4">SUPLEMEN</div>
                        <div class="cursor-pointer hover:bg-cyan-500 p-4">VITAMIN</div>
                        <div class="cursor-pointer hover:bg-cyan-500 p-4">PRODUK BAYI</div>
                    </div>
                </div>

                <div class="relative select-none flex-1" id="dropdownButton2">
                    <div onclick="toggleDropdown2()" class="border-solid border-cyan-400 border-2 px-5 py-2 rounded cursor-pointer flex justify-between w-full shadow-sm">
                        BENTUK OBAT
                        <img src="image/arrowdown.svg" alt="" width="10">
                    </div>
                    <div class="rounded border-2 border-cyan-400 absolute top-10 w-full shadow-md hidden z-10 bg-white font-semibold" id="dropdown2">
                        <div class="cursor-pointer hover:bg-cyan-500 p-4">SIRUP</div>
                        <div class="cursor-pointer hover:bg-cyan-500 p-4">TABLET</div>
                    </div>
                </div>

                <div class="relative select-none flex-1" id="dropdownButton3">
                    <div onclick="toggleDropdown3()" class="border-solid border-cyan-400 border-2 px-5 py-2 rounded cursor-pointer flex justify-between w-full shadow-sm">
                        MENGURUTKAN
                        <img src="image/arrowdown.svg" alt="" width="10">
                    </div>
                    <div class="rounded border-2 border-cyan-400 absolute top-10 w-full shadow-md hidden z-10 bg-white font-semibold" id="dropdown3">
                        <div class="cursor-pointer hover:bg-cyan-500 p-4">JUDUL (A-Z)</div>
                        <div class="cursor-pointer hover:bg-cyan-500 p-4">JUDUL (Z-A)</div>
                    </div>
                </div>
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
            </script>
        </div>
    </main>
</body>

</html>