<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - Bailu Pharmacy</title>
    <link href="src/output.css" rel="stylesheet">
</head>

<body>
    <!-- Navbar -->
    <?php include "navbar.php"; ?>

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