<header class="sticky py-5">
  <nav class="w-9/12 flex flex-row mx-auto items-center">
    <div class="flex items-center basis-1/4">
      <a href="home.php" class="flex items-center">
        <img src="image/logo.png" class="h-8 mr-2" alt="logo" />
        <span class="text-2xl font-semibold text-cyan-600">Bailu Pharmacy</span>
      </a>
    </div>
    <div class="basis-1/4 flex items-center justify-start mr-2">
      <input
        type="text"
        placeholder="Search..."
        class="px-4 py-2 border rounded-lg text-sm border-cyan-600 w-full focus:outline-none focus:ring focus:ring-cyan-300" />
    </div>
    <div class="basis-1/4 flex items-center justify-start">
      <a href="home.php" class="mx-4 font-semibold text-cyan-600 hover:text-cyan-700"><span>HOME</span></a>
      <a href="shop.php" class="mx-4 font-semibold text-cyan-600 hover:text-cyan-700"><span>SHOP</span></a>
      <a href="#" class="mx-4 font-semibold text-cyan-600 hover:text-cyan-700"><span>ABOUT</span></a>
    </div>
    <div class="basis-1/4 flex justify-end items-center">
      <?php
      session_start();
      if (isset($_SESSION['login']) && $_SESSION['login'] === true && isset($_SESSION['username'])) {
        echo '<span class="px-4 py-2 text-cyan-600 font-semibold rounded-lg">' . htmlspecialchars($_SESSION['username']) . '</span>';
        echo '<a href="logout.php" class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 ml-4">LOGOUT</a>';
      } else {
        echo '<a href="login.php" class="px-4 py-2 bg-cyan-600 text-white rounded-lg font-semibold hover:bg-cyan-700">LOGIN</a>';
      }
      ?>
    </div>
  </nav>
</header>