<?php
require "session.php";

// Menghubungkan ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "apotek";

// Menentukan halaman yang diminta (routing)
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard'; // default ke 'dashboard'

// Daftar halaman yang valid

?>
<!DOCTYPE html>
<html lang="en">

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

        .sidebar {
            background-color: #A1D6E2;
            color: white;
            min-height: 100vh;
            padding: 20px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin-bottom: 15px;
        }

        .sidebar ul li a,
        .sidebar ul li button {
            font-family: 'Open Sans', sans-serif;
            font-weight: bold;
            font-size: 15px;
            color: #0B90B1;
            text-decoration: none;
            background: none;
            border: none;
            cursor: pointer;
        }

        .sidebar ul li a:hover,
        .sidebar ul li button:hover {
            color: #0B90B1;
        }

        .content {
            padding: 20px;
        }

        .navbar .search-bar {
            width: 100%;
            display: flex;
            justify-content: center;
            position: relative;
        }

        .search-container {
            width: 40%;
            position: relative;
        }

        .search-input {
            width: 100%;
            height: 40px;
            padding: 0 15px 0 40px;
            font-size: 0.9em;
            border-radius: 25px;
            outline: none;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border: none;
        }

        .search-bar-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 15px;
            /* background-color: #007BFF; */
            /* Menghapus warna latar belakang biru */
            border-radius: 25px 25px 0 0;
            /* Sudut melengkung di bagian atas */
        }

        .search-icon {
            position: absolute;
            top: 50%;
            left: 10px;
            transform: translateY(-50%);
            font-size: 1.2em;
            color: #4A90E2;
            z-index: 3;
        }

        .search-input:focus {
            border-color: #00A1D1;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        .navbar-brand span {
            font-size: 1.5em;
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
        <button class="btn btn-link btn-sm order-1 order-lg-0" id="sidebarToggle" href="#"><i class="fas fa-bars"></i></button>

        <!-- Form Pencarian -->
        <form class="search-bar mx-auto" method="get" action="">
            <div class="search-container">
                <div class="search-bar-bg"></div>
                <i class="fas fa-search search-icon"></i>
                <input class="search-input form-control" type="text" name="search" placeholder="Search for..." aria-label="Search" />
            </div>
        </form>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-2 sidebar">
                <ul>
                    <li><a href="index.php?page=dashboard">Dashboard</a></li>
                    <li>
                        <button class="btn-toggle" onclick="toggleMenu('master-collapse')">
                            Master Data
                        </button>
                        <div class="collapse" id="master-collapse">
                            <ul class="ps-3">
                                <li><a href="index.php?page=barangAkanKadarluarsa">Obat</a></li>
                                <li><a href="index.php?page=barangkadarluarsa">Obat</a></li>
                                <li><a href="index.php?page=obat">Obat</a></li>
                                <li><a href="index.php?page=suplier">Suplier</a></li>
                                <li><a href="index.php?page=pegawai">Pegawai</a></li>
                                <li><a href="index.php?page=pelanggan">Pelanggan</a></li>
                            </ul>
                        </div>
                    </li>
                    <li>
                        <button class="btn-toggle" onclick="toggleMenu('orders-collapse')">
                            Orders
                        </button>
                        <div class="collapse" id="orders-collapse">
                            <ul class="ps-3">
                                <li><a href="index.php?page=pembelian">Pembelian</a></li>
                                <li><a href="index.php?page=penjualan">Penjualan</a></li>
                                <li><a href="index.php?page=">Obat</a></li>
                            </ul>
                        </div>
                    </li>
                    <li>
                        <button class="btn-toggle" onclick="toggleMenu('account-collapse')">
                            <div class="user-info d-flex align-items-center">
                                <span><?php echo $_SESSION['username']; ?></span>
                            </div>
                        </button>
                        <div class="collapse" id="account-collapse">
                            <ul class="ps-3">
                                <li><a href="#">Settings</a></li>
                                <li><a href="logout.php">Sign out</a></li>
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
            if (element.classList.contains('show')) {
                element.classList.remove('show');
            } else {
                element.classList.add('show');
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>

</html>

<?php

?>