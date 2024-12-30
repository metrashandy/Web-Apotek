<?php
session_start();
require "koneksi.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - Bailu Pharmacy</title>
    <link href="src/output.css" rel="stylesheet">
</head>

<body class="bg-gray-100">

    <div class="flex justify-center items-center min-h-screen">
        <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-md">
            <form action="" method="post">
                <div class="text-center mb-6">
                    <img src="image/logo.png" alt="AyoMain Logo" class="w-16 h-16 mx-auto">
                    <h4 class="text-2xl font-bold text-cyan-600">Sign up</h4>
                </div>
                <div class="mb-4">
                    <label for="Inputuser1" class="block text-gray-700 font-semibold mb-2">Username</label>
                    <input type="text" name="username" id="Inputuser1" placeholder="Masukkan Username"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-cyan-300" required>
                </div>
                <div class="mb-4">
                    <label for="InputEmail" class="block text-gray-700 font-semibold mb-2">Email</label>
                    <input type="email" name="email" id="InputEmail" placeholder="Masukkan Email"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-cyan-300" required>
                </div>
                <div class="mb-4">
                    <label for="InputPassword1" class="block text-gray-700 font-semibold mb-2">Password</label>
                    <input type="password" name="password" id="InputPassword1" placeholder="Masukkan Password"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-cyan-300" required>
                </div>
                <div class="mb-4">
                    <label for="InputPassword11" class="block text-gray-700 font-semibold mb-2">Konfirmasi Password</label>
                    <input type="password" name="password1" id="InputPassword11" placeholder="Masukkan Password Ulang"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-cyan-300" required>
                </div>
                <div class="mb-4">
                    <label for="InputTelepon" class="block text-gray-700 font-semibold mb-2">Nomor Telepon</label>
                    <input type="text" name="telepon" id="InputTelepon" placeholder="Masukkan Nomor Telepon"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-cyan-300"
                        required pattern="\d*" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>
                <div class="mb-4">
                    <label for="InputRumah" class="block text-gray-700 font-semibold mb-2">Alamat Rumah</label>
                    <input type="text" name="rumah" id="InputRumah" placeholder="Masukkan Alamat Rumah"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-cyan-300" required>
                </div>
                <div id="emptyPublished">

                </div>
                <button type="submit" name="tombolSignup"
                    class="w-full bg-cyan-600 text-white py-2 rounded-lg hover:bg-cyan-700 transition">
                    Sign up
                </button>
                <div class="mt-4 text-center text-sm">
                    Sudah memiliki akun? <a href="login.php" class="text-cyan-600 hover:underline">Masuk</a>
                </div>
                <hr class="my-6 border-gray-300">
                <div>
                    <button type="button"
                        class="w-full bg-gray-300 text-gray-800 py-2 rounded-lg hover:bg-gray-400 transition"
                        onclick="window.location.href='home.php'">
                        Masuk Sebagai Guest
                    </button>
                </div>
            </form>

            <!-- PHP -->
            <?php
            if (isset($_POST['tombolSignup'])) {
                $username = htmlspecialchars(trim($_POST['username']));
                $email = htmlspecialchars(trim($_POST['email']));
                $password = htmlspecialchars(trim($_POST['password']));
                $password1 = htmlspecialchars(trim($_POST['password1']));
                $telepon = htmlspecialchars(trim($_POST['telepon']));
                $rumah = htmlspecialchars(trim($_POST['rumah']));

                // Validasi field kosong
                if (empty($username) || empty($email) || empty($password) || empty($password1) || empty($telepon) || empty($rumah)) {
                    echo '<div class="text-red-500 mt-4 text-center">Semua field harus diisi!</div>';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    // Validasi format email
                    echo '<div class="text-red-500 mt-4 text-center">Format email tidak valid!</div>';
                } elseif ($password !== $password1) {
                    // Validasi kesesuaian password
                    echo '<div class="text-red-500 mt-4 text-center">Password tidak cocok!</div>';
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                    // Cek username atau email sudah terdaftar
                    $stmt = $koneksi->prepare("SELECT * FROM tb_pelanggan WHERE username = ? OR email = ?");
                    $stmt->bind_param("ss", $username, $email);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        echo '<div class="text-red-500 mt-4 text-center">Username atau Email sudah digunakan!</div>';
                    } else {
                        // Simpan data ke database
                        $sql = "INSERT INTO tb_pelanggan (username, email, no_tlp, alamat, password) VALUES (?, ?, ?, ?, ?)";
                        $stmt = $koneksi->prepare($sql);
                        $stmt->bind_param("sssss", $username, $email, $telepon, $rumah, $hashedPassword);

                        if ($stmt->execute()) {
                            echo '<div class="text-green-500 mt-4 text-center">Pendaftaran berhasil!</div>';
                        } else {
                            echo '<div class="text-red-500 mt-4 text-center">Terjadi kesalahan. Coba lagi!</div>';
                        }
                    }
                    $stmt->close();
                }
                $koneksi->close();
            }
            ?>
        </div>
    </div>

</body>

</html>