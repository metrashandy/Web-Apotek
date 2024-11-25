<?php
session_start();
require "koneksi.php";
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
            $username = htmlspecialchars($_POST['username']);
            $password = ($_POST['password']);

            $query = mysqli_query($koneksi, "SELECT * FROM tb_pegawai WHERE username='$username'");
            $hitungData = mysqli_num_rows($query);
            $data = mysqli_fetch_array($query);

            if ($hitungData > 0) {
                if ($password == $data['passwd']) {
                    $_SESSION['username'] = $data['Nama_pegawai'];
                    $_SESSION['login'] = true;
                    $_SESSION['admin'] = true;
                    header('location: admin.php');
                } else {
        ?>
                    <div class="alert alert-warning">Password Salah!</div>
                <?php
                }
            } else {
                $query = mysqli_query($koneksi, "SELECT * FROM tb_pelanggan WHERE NAMA_PELANGGAN='$username'");
                $hitungData = mysqli_num_rows($query);
                $data = mysqli_fetch_array($query);

                if ($hitungData > 0) {
                    if (password_verify($password, $data['PASSWORD'])) {
                        $_SESSION['username'] = $data['NAMA_PELANGGAN'];
                        $_SESSION['login'] = true;
                        header('location: home.php');
                    } else {
                ?>
                        <div class="alert alert-warning">Password Salah!</div>
                    <?php
                    }
                } else {
                    ?>
                    <div class="alert alert-warning">Akun Tidak Tersedia!</div>
        <?php
                }
            }
        }
        ?>
    </section>
</body>

</html>
