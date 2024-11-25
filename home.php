<?php
session_start();
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Beranda - AyoMain</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  <style>
    footer {
      background-color: #362b2b;
      color: white;
      padding: 20px 0;
      text-align: center;
    }

    .footer-logo {
      max-width: 100px;
      margin-top: 10px;
    }

    .navbar-nav {
      font-size: 13px;
    }

    .card-title {
      text-align: center;
    }

    .card-text {
      text-align: center;
      color: #008000;
    }

    .card img {
      margin-top: 10px;
      height: 250px;
      width: 100%;
    }

    .overlay-text {
      position: absolute;
      top: 0;
      left: 50%;
      transform: translate(-50%, 0);
      color: #333;
      white-space: nowrap;
    }

    .overlay-text.large {
      font-size: 5rem;
      text-align: center;
    }

    .overlay-text.small {
      font-size: 3rem;
      text-align: center;
    }

    .button {
      display: inline-block;
      padding: 7px 20px;
      background-color: red;
      color: white;
      text-decoration: none;
      border-radius: 5px;
    }
  </style>
</head>

<body style="background-color: #f2f2f2;">

  <!-- buat navbar -->
  <section class="bg-white navbar-light shadoe-sm">
    <div class="container">
      <nav class="navbar navbar-expand-lg bg-white navbar-light shadoe-sm">
        <div class="container-fluid">
          <img src="gambar/ayomain-logo-png.png" alt="" width="50" height="50">
          <a class="navbar-brand me-4">AyoMain</a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
              <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="home.php">BERANDA</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="syarat&ketentuan.php">SYARAT & KETENTUAN</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="produk.php">PRODUK</a>
              </li>
            </ul>
            <?php
            if (isset($_SESSION['username'])) {
            ?>
              <div class="dropdown">
                <button class="ms-3 button dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <img src="gambar/user-logo.png" alt="user" width="25px" height="25px">
                  <?php echo $_SESSION['username']; ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-white">
                  <li><a class="dropdown-item" href="#">Settings</a></li>
                  <li><a class="dropdown-item" href="logout.php">Sign out</a></li>
                </ul>
              </div>
            <?php } else {
            ?><a href="login.php" class="ms-3 button"><img src="gambar/user-logo.png" alt="user" width="25px" height="25px">
              <?php echo "Log in";
            }
              ?>
              </a>
          </div>
        </div>
      </nav>
    </div>
  </section>

  <!-- buat gambar home -->
  <section class="position-relative">
    <img src="gambar/main-game.jpeg" alt="" class="img-fluid w-100" style="object-fit: cover;">
    <div class="overlay-text small" style="color: white; text-shadow: 2px 2px 10px black;">
      <p>RENTAL AYO MAIN</p>
    </div>
    <div class="overlay-text large mt-5 fw-bold" style="color: red; text-shadow: 2px 2px 3px black;">
      <p>PETUALANGAN MENANTI KITA</p>
    </div>
  </section>


  <!-- buat tampilan produk -->
  <section class="mt-5" style="background-color: #f2f2f2;">
    <h1 style="text-align: center;"><strong style="color: white; text-shadow: 2px 2px 4px black;">PRODUK UNGGULAN</strong></h1>
    <div class="container">
      <div class="row">
        <div class="col-4">
          <div class="card">
            <img src="gambar/ps5.png" class="card-img-top" alt="PS 5">
            <div class="card-body d-flex flex-column align-items-center">
              <h5 class="card-title"><br> PlayStation 5</h5>
              <p class="card-text">Rp. 170,000/Hari</p>
              <a href="#" class="btn btn-dark mt-auto bg-black form-control">ORDER</a>
            </div>
          </div>
        </div>
        <div class="col-4">
          <div class="card">
            <img src="gambar/xboxseriesS.png" class="card-img-top" alt="PS 5">
            <div class="card-body d-flex flex-column align-items-center">
              <h5 class="card-title"><br> Xbox series S</h5>
              <p class="card-text">Rp. 170,000/Hari</p>
              <a href="#" class="btn btn-dark mt-auto bg-black form-control">ORDER</a>
            </div>
          </div>
        </div>
        <div class="col-4">
          <div class="card">
            <img src="gambar/nintendoswitch.png" class="card-img-top" alt="PS 5">
            <div class="card-body d-flex flex-column align-items-center">
              <h5 class="card-title"><br> Nintendo Switch</h5>
              <p class="card-text">Rp. 80,000/Hari</p>
              <a href="#" class="btn btn-dark mt-auto bg-black form-control">ORDER</a>
            </div>
          </div>
        </div>
      </div>
      <div class="text-center mt-3">
        <a href="produk.php" class="btn btn-dark mt-auto bg-danger"><img src="gambar/pencarian-logo.png" style="height: 27px; width: 27px;" alt="detail"> Lihat Semua Produk</a>
      </div>
    </div>
  </section>

  <!-- buat footer -->
  <section class="mt-5">
    <footer>
      <div class="container">
        <div class="row">
          <div class="col-lg-4">
            <!-- Logo di kiri -->
            <img src="gambar/ayomain-logo-png.png" alt="AyoMain Logo" class="footer-logo">
          </div>
          <div class="col-lg-4">
            <!-- Tentang Kami -->
            <h5>Tentang Kami</h5>
            <p>AyoMain melayani jasa rental atau penyewaan konsol, aksesoris, dan game dengan syarat mudah dan harga yang bersahabat.</p>
          </div>
          <div class="col-lg-4">
            <!-- Informasi Kontak -->
            <h5>Informasi Kontak</h5>
            <p>
              <img src="gambar/lokasi-logo.png" alt="Lokasi Icon" width="20" height="20"> Alamat: Jl Jimbaran No.60<br>
              <img src="gambar/telpon-logo.png" alt="Telepon Icon" width="20" height="20"> Telpon/WA: +62 123 456 7769<br>
              <img src="gambar/mail-logo.png" alt="Email Icon" width="20" height="20"> Email: info@rentalayomain.com
            </p>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-12">
            <!-- Social Media Icons dengan link -->
            <a href="https://www.instagram.com/rentalayomain/" target="_blank"><img src="gambar/Instagram-logo.png" alt="Instagram Icon" width="30" height="30"></a>
            <a href="https://www.facebook.com/rentalayomain/" target="_blank"><img src="gambar/Facebook-logo.png" alt="Facebook Icon" width="30" height="30"></a>
          </div>
        </div>
      </div>
    </footer>
  </section>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>

</html>