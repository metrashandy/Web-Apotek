<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Koneksi ke database
$host   = 'localhost';
$user   = 'root';
$pass   = '';
$dbname = 'apotek';

$koneksi = new mysqli($host, $user, $pass, $dbname);
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Ambil data pelanggan dari database
$id_pelanggan = $_SESSION['id_pelanggan'];
$query = "SELECT * FROM tb_pelanggan WHERE id_pelanggan = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("s", $id_pelanggan);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $no_tlp = trim($_POST['no_tlp']);
    $alamat = trim($_POST['alamat']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $success_message = '';
    $error_message = '';

    // Validasi input dasar
    if (empty($email) || empty($no_tlp) || empty($alamat)) {
        $error_message = "Semua field kecuali password harus diisi.";
    } else {
        // Jika password baru diisi, proses perubahan password
        if (!empty($new_password) || !empty($confirm_password)) {
            if (empty($current_password)) {
                $error_message = "Harap masukkan password saat ini untuk mengubah password.";
            } elseif ($new_password !== $confirm_password) {
                $error_message = "Password baru dan konfirmasi password tidak cocok!";
            } else {
                // Verifikasi password saat ini
                $verify_query = "SELECT password FROM tb_pelanggan WHERE id_pelanggan = ?";
                $verify_stmt = $koneksi->prepare($verify_query);
                $verify_stmt->bind_param("s", $id_pelanggan);
                $verify_stmt->execute();
                $verify_result = $verify_stmt->get_result();
                $user = $verify_result->fetch_assoc();

                if ($user && password_verify($current_password, $user['password'])) {
                    // Hash password baru
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_query = "UPDATE tb_pelanggan SET email = ?, no_tlp = ?, alamat = ?, password = ? WHERE id_pelanggan = ?";
                    $update_stmt = $koneksi->prepare($update_query);
                    $update_stmt->bind_param("sssss", $email, $no_tlp, $alamat, $hashed_password, $id_pelanggan);

                    if ($update_stmt->execute()) {
                        $success_message = "Profil dan password berhasil diperbarui!";
                        // Refresh data user
                        $stmt->execute();
                        $user_data = $stmt->get_result()->fetch_assoc();
                    } else {
                        $error_message = "Gagal memperbarui profil: " . $koneksi->error;
                    }
                } else {
                    $error_message = "Password saat ini salah.";
                }
            }
        } else {
            // Update tanpa mengubah password
            $update_query = "UPDATE tb_pelanggan SET email = ?, no_tlp = ?, alamat = ? WHERE id_pelanggan = ?";
            $update_stmt = $koneksi->prepare($update_query);
            $update_stmt->bind_param("ssss", $email, $no_tlp, $alamat, $id_pelanggan);

            if ($update_stmt->execute()) {
                $success_message = "Profil berhasil diperbarui!";
                // Refresh data user
                $stmt->execute();
                $user_data = $stmt->get_result()->fetch_assoc();
            } else {
                $error_message = "Gagal memperbarui profil: " . $koneksi->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Bailu Pharmacy</title>
    <link href="src/output.css" rel="stylesheet">
    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</head>

<script src="cart.js"></script>

<body class="bg-gray-50">
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
                <button onclick="showPopup()" class="mx-4 font-semibold text-cyan-600 hover:text-cyan-700 flex items-center">
                    <img src="image/icon-shop.png" alt="cart" class="h-5 w-5 mr-1" />
                    <span id="cart-count">0</span>
                </button>
            </div>
            <div class="basis-1/4 flex justify-end items-center">
                <?php
                // Tampilkan ikon user dan nama jika sudah login
                if (isset($_SESSION['login']) && $_SESSION['login'] === true && !empty($_SESSION['username'])) {
                    echo '<span class="px-4 py-2 text-cyan-600 font-semibold rounded-lg mr-2">'
                        . htmlspecialchars($_SESSION['username']) . '</span>';
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

    <!-- Pop-up Cart -->
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
                                <!-- Cart items -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Modal footer -->
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

    <!-- Main Content -->
    <main class="w-9/12 mx-auto py-8">
        <div class="bg-white rounded-lg shadow-md p-6 max-w-4xl mx-auto">
            <!-- Tab Navigation -->
            <div class="flex border-b border-gray-200 mb-6">
                <button class="px-6 py-3 border-b-2 border-cyan-600 text-cyan-600 font-semibold">
                    Profil Saya
                </button>
                <a href="history_belanja.php" class="px-6 py-3 text-gray-500 hover:text-gray-700 font-semibold">
                    Riwayat Pesanan
                </a>
            </div>

            <?php if (!empty($success_message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $success_message; ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $error_message; ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <!-- Grid layout for inputs -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Username and Email side by side -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" value="<?php echo htmlspecialchars($user_data['username']); ?>"
                            class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm cursor-not-allowed focus:border-cyan-500 focus:ring-cyan-500"
                            readonly>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="email"
                            value="<?php echo htmlspecialchars($user_data['email']); ?>"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500" required>
                    </div>

                    <!-- Phone and Address side by side -->
                    <div>
                        <label for="no_tlp" class="block text-sm font-medium text-gray-700">No. Telepon</label>
                        <input type="text" name="no_tlp" id="no_tlp"
                            value="<?php echo htmlspecialchars($user_data['no_tlp']); ?>"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500" required>
                    </div>

                    <div>
                        <label for="alamat" class="block text-sm font-medium text-gray-700">Alamat</label>
                        <textarea name="alamat" id="alamat" rows="1"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500" required><?php echo htmlspecialchars($user_data['alamat']); ?></textarea>
                    </div>
                </div>

                <!-- Password Section -->
                <div class="border-t pt-6 mt-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Ubah Password</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Current Password -->
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700">Password Saat Ini</label>
                            <div class="relative">
                                <input type="password" name="current_password" id="current_password"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500">
                                <button type="button"
                                    onclick="togglePassword('current_password')"
                                    class="absolute inset-y-0 right-0 px-3 flex items-center text-sm text-gray-600 hover:text-gray-800">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- New Password -->
                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700">Password Baru</label>
                            <div class="relative">
                                <input type="password" name="new_password" id="new_password"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500">
                                <button type="button"
                                    onclick="togglePassword('new_password')"
                                    class="absolute inset-y-0 right-0 px-3 flex items-center text-sm text-gray-600 hover:text-gray-800">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                            <div class="relative">
                                <input type="password" name="confirm_password" id="confirm_password"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500">
                                <button type="button"
                                    onclick="togglePassword('confirm_password')"
                                    class="absolute inset-y-0 right-0 px-3 flex items-center text-sm text-gray-600 hover:text-gray-800">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">Kosongkan jika tidak ingin mengubah password</p>
                </div>

                <!-- Submit dan Logout -->
                <div class="flex justify-between items-center pt-6 border-t">
                    <a href="logout.php" class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700">
                        LOGOUT
                    </a>
                    <button type="submit"
                        class="inline-flex justify-center rounded-md border border-transparent bg-cyan-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>

</html>