<?php
require "session.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="admin.css">
</head>

<body>
    <div class="main-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <ul>
                <li>
                    <a href="index.php?page=dasboard">Dashboard</a>
                </li>
                <li>
                    <button class="btn-toggle" onclick="toggleMenu('master-collapse')">
                        Master Data <span>&#9660;</span>
                    </button>
                    <div class="collapse" id="master-collapse">
                        <ul class="btn-toggle-nav">
                            <li><a href="index.php?page=Pelanggan">Pelanggan</a></li>
                            <li><a href="index.php?page=barang">Barang</a></li>
                            <li><a href="index.php?page=pegawai">Pegawai</a></li>
                        </ul>
                    </div>
                </li>
                <li>
                    <button class="btn-toggle" onclick="toggleMenu('orders-collapse')">
                        Orders <span>&#9660;</span>
                    </button>
                    <div class="collapse" id="orders-collapse">
                        <ul class="btn-toggle-nav">
                            <li><a href="index.php?page=booking">Booking</a></li>
                            <li><a href="index.php?page=pesanan">Penyewaan</a></li>
                        </ul>
                    </div>
                </li>
                <li>
                    <button class="btn-toggle" onclick="toggleMenu('account-collapse')">
                        <div class="user-info">
                            <img src="gambar/user2.png" alt="User">
                            <span><?php echo $_SESSION['username']; ?></span>
                        </div>
                    </button>
                    <div class="collapse" id="account-collapse">
                        <ul class="btn-toggle-nav">
                            <li><a href="#">Settings</a></li>
                            <li><a href="logout.php">Sign out</a></li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Content -->
        <div class="content">
            <?php
            if (isset($_GET['page'])) {
                $target = $_GET['page'] . ".php";
                if (file_exists($target)) {
                    include $target;
                } else {
                    echo "<p>Halaman tidak ditemukan!</p>";
                }
            } else {
                echo "<h1>Selamat datang di Admin Panel!</h1>";
            }
            ?>
        </div>
    </div>

    <script>
        function toggleMenu(id) {
            const element = document.getElementById(id);
            const button = element.previousElementSibling;
            if (element.style.display === "block") {
                element.style.display = "none";
                button.classList.remove('active');
            } else {
                element.style.display = "block";
                button.classList.add('active');
            }
        }
    </script>
</body>

</html>