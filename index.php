<?php
session_start();
require "koneksi.php";
if ($_SESSION['role'] == "user") {
    header('location: home.php');
  }
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - AyoMain</title>
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

    section:not(:first-child) {
      margin-bottom: 30px;
    }

    .card img {
      margin-top: 10px;
      height: 250px;
      width: 100%;
    }
  </style>
</head>

<body style="background-color: #f2f2f2;">
  <div class="row">
    <!-- kolom pertama -->
    <div class="col-2">
      <?php include 'sidebar.php'; ?>
    </div>
    <!-- kolom kedua -->
    <div class="col" style="padding-top: 20px;">
      <?php
      if (isset($_GET['page'])) {
        $target = $_GET['page'] . ".php";
        include $target;
      }

      ?>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>

</html>