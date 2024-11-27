<?php
session_start();
require "koneksi.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - AyoMain</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f8f9fa;
        }

        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .form-container img {
            display: block;
            margin: 0 auto 20px;
        }

        .form-container h4 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            margin-bottom: 5px;
            color: #333;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        .form-group input:focus {
            border-color: #007bff;
            outline: none;
        }

        .form-group .btn {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        .btn-primary {
            background-color: #dc3545;
            color: #fff;
        }

        .btn-primary:hover {
            background-color: #c82333;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: #fff;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .form-group p {
            text-align: center;
            margin-top: 10px;
            font-size: 14px;
        }

        .form-group p a {
            color: #dc3545;
            text-decoration: none;
        }

        .form-group p a:hover {
            text-decoration: underline;
        }

        .alert {
            margin-top: 20px;
            text-align: center;
            padding: 10px;
            border-radius: 5px;
        }

        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .line {
            border: none;
            border-top: 1px solid #ccc;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="form-container">
            <form action="" method="post">
                <div class="form-group">
                    <img src="gambar/ayomain-logo-png.png" alt="AyoMain Logo" width="50" height="50">
                    <h4>Sign up</h4>
                </div>
                <div class="form-group">
                    <label for="Inputuser1">Username</label>
                    <input type="text" name="username" id="Inputuser1" placeholder="Masukkan Username" required>
                </div>
                <div class="form-group">
                    <label for="InputPassword1">Password</label>
                    <input type="password" name="password" id="InputPassword1" placeholder="Masukkan Password" required>
                </div>
                <div class="form-group">
                    <label for="InputPassword11">Konfirmasi Password</label>
                    <input type="password" name="password1" id="InputPassword11" placeholder="Masukkan Password Ulang" required>
                </div>
                <div class="form-group">
                    <label for="InputTelepon">Nomor Telepon</label>
                    <input type="text" name="telepon" id="InputTelepon" placeholder="Masukkan Nomor Telepon" required>
                </div>
                <div class="form-group">
                    <label for="InputRumah">Alamat Rumah</label>
                    <input type="text" name="rumah" id="InputRumah" placeholder="Masukkan Alamat Rumah" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary" name="tombolSignup">Sign up</button>
                </div>
                <div class="form-group">
                    <p>Sudah memiliki akun? <a href="login.php">Masuk</a></p>
                </div>
                <hr class="line">
                <div class="form-group">
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='home.php'">
                        Masuk Sebagai Guest
                    </button>
                </div>
            </form>

            <?php
            if (isset($_POST['tombolSignup'])) {
                $username = $koneksi->real_escape_string($_POST['username']);
                $password = $koneksi->real_escape_string($_POST['password']);
                $password1 = $koneksi->real_escape_string($_POST['password1']);
                $telepon = $koneksi->real_escape_string($_POST['telepon']);
                $rumah = $koneksi->real_escape_string($_POST['rumah']);

                if ($password == $password1) {
                    $sql = "INSERT INTO tb_pelanggan (Nama_pelanggan, no_tlp, alamat, password) VALUES (?, ?, ?, ?)";
                    $stmt = $koneksi->prepare($sql);
                    $stmt->bind_param("ssis", $username, $telepon, $rumah,  $password);
                    $stmt->execute();

                    echo '<div class="alert alert-success">Data Berhasil Ditambahkan!</div>';
                } else {
                    echo '<div class="alert alert-warning">Masukkan Data Yang Benar!</div>';
                }

                $stmt->close();
                $koneksi->close();
            }
            ?>
        </div>
    </div>
</body>

</html>
