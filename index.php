<?php
require "session.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <style>
        body {
            background-color: #f2f2f2;
        }

        .sidebar {
            background-color: #362b2b;
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
            color: white;
            text-decoration: none;
        }

        .sidebar ul li button {
            background: none;
            border: none;
            cursor: pointer;
        }

        .sidebar ul li a:hover,
        .sidebar ul li button:hover {
            color: #00bfff;
        }

        .content {
            padding: 20px;
        }

        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-2 sidebar">
                <ul>
                    <li><a href="index.php?page=dashboard">Dashboard</a></li>
                    <li>
                        <button class="btn-toggle" onclick="toggleMenu('master-collapse')">
                            Master Data <span>&#9660;</span>
                        </button>
                        <div class="collapse" id="master-collapse">
                            <ul class="ps-3">
                                <li><a href="index.php?page=barangkadarluarsa">Barang Kadarluarsa</a></li>
                                <li><a href="index.php?page=barangAkanKadarluarsa">Barang Akan Kadarluarsa</a></li>
                                <li><a href="index.php?page=obat">Barang</a></li>
                                <li><a href="index.php?page=pegawai">Pegawai</a></li>
                                <li><a href="index.php?page=suplier">Suplier</a></li>
                                <li><a href="index.php?page=pelanggan">Pelanggan</a></li>
                            </ul>
                        </div>
                    </li>
                    <li>
                        <button class="btn-toggle" onclick="toggleMenu('orders-collapse')">
                            Orders <span>&#9660;</span>
                        </button>
                        <div class="collapse" id="orders-collapse">
                            <ul class="ps-3">
                                <li><a href="index.php?page=pembelian">Pembelian</a></li>
                                <li><a href="index.php?page=penjualan">Penjualan</a></li>
                            </ul>
                        </div>
                    </li>
                    <li>
                        <button class="btn-toggle" onclick="toggleMenu('account-collapse')">
                            <div class="user-info d-flex align-items-center">
                                <img src="gambar/user2.png" alt="User">
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
                <?php
                if (isset($_GET['page'])) {
                    $target = $_GET['page'] . ".php";
                    if (file_exists($target)) {
                        include $target;
                    } else {
                        echo "<p class='text-danger'>Halaman tidak ditemukan!</p>";
                    }
                } else {
                    echo "<h1>Selamat datang di Admin Panel!</h1>";
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