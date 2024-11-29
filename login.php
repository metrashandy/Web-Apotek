<?php
session_start();
require "koneksi.php";
if ($_SESSION['admin'] == false) {
    header('location: home.php');
  }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AyoMain</title>
    <link rel="stylesheet" href="login.css">
</head>

<body>
    <section class="form-container">
        <form action="" method="post">
            <div class="form-group">
                <div class="text-center">
                    <img src="gambar/ayomain-logo-png.png" alt="" width="50" height="50">
                </div>
                <h4>Login</h4>
                <label for="Inputuser1">Username</label>
                <input type="text" class="form-control" name="username" id="Inputuser1" placeholder="Masukkan Username">
            </div>
            <div class="form-group">
                <label for="InputPassword1">Password</label>
                <input type="password" class="form-control" name="password" id="InputPassword1" placeholder="Masukkan Password">
            </div>
            <button type="submit" class="btn" name="tombolLogin">Sign in</button>

            <div class="form-group">
                <p class="text-center">Belum memiliki akun? <a href="signup.php">Daftar</a></p>
            </div>

            <hr class="line">

            <div class="form-group">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='home.php'">Masuk Sebagai Guest</button>
            </div>
        </form>

        <!-- PHP LOGIN -->
        <?php

        if (isset($_POST['tombolLogin'])) {
            $username = htmlspecialchars(trim($_POST['username']));
            $password = trim($_POST['password']);

            // Cek login sebagai admin
            $stmt = $koneksi->prepare("SELECT * FROM tb_pegawai WHERE Nip = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();

            if ($data) {
                if ($password === $data['passwd']) { // Perbandingan password biasa
                    $_SESSION['username'] = $data['Nama_pegawai'];
                    $_SESSION['login'] = true;
                    $_SESSION['role'] = 'admin'; // Tentukan role
                    header('Location: admin.php');
                    exit();
                } else {
                    echo '<div class="alert alert-warning">Password Salah!</div>';
                }
            } else {
                // Cek login sebagai user
                $stmt = $koneksi->prepare("SELECT * FROM tb_pelanggan WHERE username = ?");
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();
                $data = $result->fetch_assoc();

                if ($data) {
                    if ($password === $data['password']) { // Perbandingan password biasa
                        $_SESSION['username'] = $data['Nama_pelanggan'];
                        $_SESSION['login'] = true;
                        $_SESSION['role'] = 'user'; // Tentukan role
                        header('Location: home.php');
                        exit();
                    } else {
                        echo '<div class="alert alert-warning">Password Salah!</div>';
                    }
                } else {
                    echo '<div class="alert alert-warning">Akun Tidak Tersedia!</div>';
                }
            }

            $stmt->close();
        }
        ?>

    </section>
</body>

</html>