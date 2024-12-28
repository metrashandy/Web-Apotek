<?php
require "session.php";

// Menghubungkan ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "apotek";

// Menentukan halaman yang diminta (routing)
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard'; // default ke 'dashboard'

// Daftar halaman yang valid untuk setiap submenu
$masterDataPages = ['barangAkanKadarluarsa', 'barangkadarluarsa', 'obat', 'suplier', 'pegawai', 'pelanggan'];
$ordersPages = ['pembelian', 'penjualan', 'pesanan'];
$accountPages = ['settings']; // Tambahkan halaman lain jika ada
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Bailu Pharmacy</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <style>
        body {
            background-color: #EFF6FF;
            font-family: 'Open Sans', sans-serif;
            font-weight: bold;
            font-size: 20px;
            color: #0B90B1;
        }

        /* Sidebar */
        .sidebar {
            background-color: #FFFFFF;
            /* Mengganti warna latar belakang menjadi putih */
            color: #333;
            /* Mengubah warna teks menjadi lebih gelap untuk kontras */
            min-height: 100vh;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            /* Menambahkan bayangan untuk efek kedalaman */
            transition: width 0.3s;
            /* Animasi transisi saat sidebar di-toggle */
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar ul li {
            margin-bottom: 20px;
        }

        /* Gaya untuk link di sidebar tanpa submenu */
        .sidebar ul li a {
            display: flex;
            align-items: center;
            /* Menghilangkan justify-content: space-between */
            justify-content: flex-start;
            gap: 10px;
            /* Jarak antara ikon dan teks */
            font-family: 'Open Sans', sans-serif;
            font-weight: bold;
            font-size: 16px;
            color: #333;
            text-decoration: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 10px 15px;
            border-radius: 8px;
            transition: background-color 0.3s, color 0.3s;
        }

        /* Gaya untuk button di sidebar dengan submenu */
        .sidebar ul li button {
            display: flex;
            align-items: center;
            justify-content: space-between;
            /* Memisahkan konten kiri (ikon & teks) dengan chevron di kanan */
            gap: 10px;
            font-family: 'Open Sans', sans-serif;
            font-weight: bold;
            font-size: 16px;
            color: #333;
            text-decoration: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 10px 15px;
            border-radius: 8px;
            transition: background-color 0.3s, color 0.3s;
        }

        /* Efek hover pada link dan button */
        .sidebar ul li a:hover,
        .sidebar ul li button:hover {
            background-color: #F0F4FF;
            color: #0B90B1;
        }

        /* Gaya submenu */
        .sidebar .collapse ul {
            padding-left: 25px;
            /* Indentasi submenu */
        }

        /* Ikon sidebar */
        .sidebar .fas {
            width: 20px;
            /* Lebar konsisten untuk ikon */
            text-align: center;
        }

        /* Ikon chevron dropdown */
        .chevron {
            transition: transform 0.3s ease;
            /* Animasi rotasi */
        }

        /* Rotasi chevron saat submenu terbuka */
        .btn-toggle.active .chevron {
            transform: rotate(180deg);
        }

        /* Aktif link */
        .sidebar ul li a.active {
            background-color: #0B90B1;
            color: #fff;
        }

        .sidebar ul li a.active .fas {
            color: #fff;
        }
    </style>
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-white">
        <img src="image/logo.png" alt="Logo Pharmacy" width="40" height="40">
        <a class="navbar-brand" href="index.php">
            <span style="color: #2462E1; font-family: 'Open Sans', sans-serif;">Bailu</span>
            <span style="color: #2462E1; font-family: 'Open Sans', sans-serif;">Pharmacy</span>
        </a>
        <button class="btn btn-link btn-sm order-1 order-lg-0" id="sidebarToggle" href="#">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Form Pencarian -->
        <form class="search-bar mx-auto" method="get" action="">
            <div class="search-container">
                <div class="search-bar-bg"></div>
                <input class="search-input form-control" type="text" name="search" placeholder="Pencarian..." aria-label="Search" />
            </div>
        </form>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <ul>
                    <li>
                        <a href="index.php?page=dashboard" class="<?php echo ($page == 'dashboard') ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt"></i>
                            <span class="link-text">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <button class="btn-toggle <?php echo in_array($page, $masterDataPages) ? 'active' : ''; ?>" onclick="toggleMenu('master-collapse')">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-database"></i>
                                <span class="link-text">Master Data</span>
                            </div>
                            <i class="fas fa-chevron-down chevron"></i> <!-- Ikon dropdown di kanan -->
                        </button>
                        <div class="collapse <?php echo in_array($page, $masterDataPages) ? 'show' : ''; ?>" id="master-collapse">
                            <ul class="ps-3">
                                <li>
                                    <a href="index.php?page=barangAkanKadarluarsa" class="<?php echo ($page == 'barangAkanKadarluarsa') ? 'active' : ''; ?>">
                                        <i class="fas fa-boxes"></i>
                                        <span class="link-text">Barang Akan Kadaluarsa</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="index.php?page=barangkadarluarsa" class="<?php echo ($page == 'barangkadarluarsa') ? 'active' : ''; ?>">
                                        <i class="fas fa-box-open"></i>
                                        <span class="link-text">Barang Kadaluarsa</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="index.php?page=obat" class="<?php echo ($page == 'obat') ? 'active' : ''; ?>">
                                        <i class="fas fa-pills"></i>
                                        <span class="link-text">Obat</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="index.php?page=suplier" class="<?php echo ($page == 'suplier') ? 'active' : ''; ?>">
                                        <i class="fas fa-truck-loading"></i>
                                        <span class="link-text">Suplier</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="index.php?page=pegawai" class="<?php echo ($page == 'pegawai') ? 'active' : ''; ?>">
                                        <i class="fas fa-user-tie"></i>
                                        <span class="link-text">Pegawai</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="index.php?page=pelanggan" class="<?php echo ($page == 'pelanggan') ? 'active' : ''; ?>">
                                        <i class="fas fa-users"></i>
                                        <span class="link-text">Pelanggan</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li>
                        <button class="btn-toggle <?php echo in_array($page, $ordersPages) ? 'active' : ''; ?>" onclick="toggleMenu('orders-collapse')">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-shopping-cart"></i>
                                <span class="link-text">Orders</span>
                            </div>
                            <i class="fas fa-chevron-down chevron"></i> <!-- Ikon dropdown di kanan -->
                        </button>
                        <div class="collapse <?php echo in_array($page, $ordersPages) ? 'show' : ''; ?>" id="orders-collapse">
                            <ul class="ps-3">
                                <li>
                                    <a href="index.php?page=pembelian" class="<?php echo ($page == 'pembelian') ? 'active' : ''; ?>">
                                        <i class="fas fa-shopping-bag"></i>
                                        <span class="link-text">Pembelian</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="index.php?page=penjualan" class="<?php echo ($page == 'penjualan') ? 'active' : ''; ?>">
                                        <i class="fas fa-dollar-sign"></i>
                                        <span class="link-text">Penjualan</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="index.php?page=pesanan" class="<?php echo ($page == 'pesanan') ? 'active' : ''; ?>">
                                        <i class="fas fa-concierge-bell"></i>
                                        <span class="link-text">Pesanan</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li>
                        <button class="btn-toggle <?php echo in_array($page, $accountPages) ? 'active' : ''; ?>" onclick="toggleMenu('account-collapse')">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-user"></i>
                                <span class="link-text"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            </div>
                            <i class="fas fa-chevron-down chevron"></i> <!-- Ikon dropdown di kanan -->
                        </button>
                        <div class="collapse <?php echo in_array($page, $accountPages) ? 'show' : ''; ?>" id="account-collapse">
                            <ul class="ps-3">
                                <li>
                                    <a href="#" class="<?php echo ($page == 'settings') ? 'active' : ''; ?>">
                                        <i class="fas fa-cog"></i>
                                        <span class="link-text">Settings</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="logout.php">
                                        <i class="fas fa-sign-out-alt"></i>
                                        <span class="link-text">Sign out</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Content -->
            <div class="col content">
                <!-- Konten dinamis yang ditentukan oleh halaman -->
                <?php
                $page_file = $page . '.php';
                if (file_exists($page_file)) {
                    include $page_file;
                } else {
                    echo "<h1>Halaman tidak ditemukan.</h1>";
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        function toggleMenu(id) {
            const element = document.getElementById(id);
            const toggleButton = element.previousElementSibling; // Mendapatkan tombol toggle sebelum collapse

            if (element.classList.contains('show')) {
                element.classList.remove('show');
                toggleButton.classList.remove('active');
            } else {
                // Menutup semua submenu lainnya jika hanya ingin satu submenu terbuka
                const allCollapses = document.querySelectorAll('.collapse.show');
                allCollapses.forEach((collapse) => {
                    collapse.classList.remove('show');
                    const btn = collapse.previousElementSibling;
                    if (btn && btn.classList.contains('btn-toggle')) {
                        btn.classList.remove('active');
                    }
                });

                element.classList.add('show');
                toggleButton.classList.add('active');
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>

</html>

<?php

?>