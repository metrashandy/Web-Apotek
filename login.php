<?php
session_start();
require "koneksi.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bailu Pharmacy</title>
    <link href="src/output.css" rel="stylesheet">
</head>

<body class="bg-gray-100">

    <section class="flex justify-center items-center min-h-screen">
        <form action="" method="post" class="bg-white shadow-lg rounded-lg p-8 w-full max-w-md">
            <div class="text-center mb-6">
                <img src="image/logo.png" alt="Logo Bailu Pharmacy" class="w-16 h-16 mx-auto">
                <h2 class="text-2xl font-bold text-cyan-600">Login</h2>
            </div>
            <div class="mb-4">
                <label for="Inputuser1" class="block text-gray-700 font-semibold mb-2">Username</label>
                <input type="text" name="username" id="Inputuser1" placeholder="Masukkan Username"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-cyan-300">
            </div>
            <div class="mb-6">
                <label for="InputPassword1" class="block text-gray-700 font-semibold mb-2">Password</label>
                <input type="password" name="password" id="InputPassword1" placeholder="Masukkan Password"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-cyan-300">
            </div>
            <div id="emptyPublished">

            </div>
            <button type="submit" name="tombolLogin"
                class="w-full bg-cyan-600 text-white py-2 rounded-lg hover:bg-cyan-700 transition">
                Sign in
            </button>
            <div class="mt-4 text-center text-sm">
                Belum memiliki akun? <a href="signup.php" class="text-cyan-600 hover:underline">Daftar</a>
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
        if (isset($_POST['tombolLogin'])) {
            $username = htmlspecialchars(trim($_POST['username']));
            $password = trim($_POST['password']);
            $hash = password_hash($password, PASSWORD_DEFAULT);

            if (empty($username) || empty($password)) {
        ?><script>
                    let empty = '<div class="text-red-500 mt-4 text-center">Username dan Password harus diisi!</div>';
                    emptyPublished.innerHTML += empty;
                </script>
            <?php
            } else {

                // Cek sebagai admin
                $stmt = $koneksi->prepare("SELECT * FROM tb_pegawai WHERE Nip = ?");
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();
                $data = $result->fetch_assoc();

                if ($data && password_verify($password, $hash)) {
                    $_SESSION['username'] = $data['Nama_pegawai'];
                    $_SESSION['login'] = true;
                    $_SESSION['role'] = 'admin';
                    header('Location: index.php');
                    exit();
                }

                // Cek sebagai user
                $stmt = $koneksi->prepare("SELECT * FROM tb_pelanggan WHERE username = ?");
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();
                $data = $result->fetch_assoc();

                if ($data && password_verify($password, $hash)) {
                    $_SESSION['username'] = $data['username'];
                    $_SESSION['login'] = true;
                    $_SESSION['role'] = 'user';
                    header('Location: home.php');
                    exit();
                }

                // Jika gagal login
            ?><script>
                    let error = '<div class="text-red-500 mt-4 text-center">Username atau Password salah!</div>';
                    emptyPublished.innerHTML += error;
                </script>
        <?php
                $stmt->close();
            }
        }
        ?>
    </section>

</body>

</html>