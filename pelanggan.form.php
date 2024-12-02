<?php

$mysqli = new mysqli("localhost", "root", "", "apotek");

$action=$_GET['action'];
$currentid = "";
$nama = "";
$alamat = "";

if ($action == "add") {
  $result = $mysqli->query("SELECT MAX(Id_pelanggan) AS currentid FROM tb_pelanggan;");

  if (!$result) {
    die("Error: " . $mysqli->error);
  }

  $row = $result->fetch_assoc(); //khusus single result
  $max_number = (int)$row['currentid'];
  $new_number = $max_number + 1;

  $currentid = $new_number; //tambahkan dengan 1 untuk id baru
  $nama = "";
  $telepon = "";
  $email = "";
  $alamat = "";
  
} else if ($action == "edit") {
  $currentid = $_GET['Id_pelanggan'];
  $query = "SELECT * FROM tb_pelanggan WHERE Id_pelanggan = '" . $currentid . "';";

  //print_r($query);
  $result = $mysqli->query($query);
  $row = $result->fetch_assoc(); //khusus single result

  //print_r($row);
  $nama = $row['username'];
  $email = $row['email'];
  $alamat = $row['alamat'];
  $telepon = $row['no_tlp'];
  
  
  
  
}

?>
<div class="col" style="padding-top: 20px;">
  <form action="pelanggan.action.php" method="POST">
    <div class="mb-3">
      <label for="id" class="form-label">ID PELANGAN</label>
      <input type="text" class="form-control" name="Id_pelanggan" id="NIP" placeholder="NIP" value="<?php echo $currentid; ?>">
    </div>
    <div class="mb-3">
      <label for="nama" class="form-label">USERNAME</label>
      <input type="text" class="form-control" name="username" id="nama" placeholder="Tuliskan nama di sini" value="<?php echo $nama; ?>">
    </div>
    <div class="mb-3">
      <label for="nama" class="form-label">EMAIL</label>
      <input type="email" class="form-control" name="Alamat" id="nama" placeholder="Tuliskan email di sini" value="<?php echo $alamat; ?>">
    </div>
    <div class="mb-3">
      <label for="nama" class="form-label">NO TELEPON</label>
      <input type="text" class="form-control" name="no_tlp" id="nama" placeholder="Tuliskan no telepon di sini" value="<?php echo $email; ?>">
    </div>
    <div class="mb-3">
      <label for="nama" class="form-label">ALAMAT</label>
      <input type="text" class="form-control" name="alamat" id="nama" placeholder="Tuliskan alamat di sini" value="<?php echo $telepon; ?>">
    </div>
    <input type="hidden" name="action" value="<?php echo $action; ?>">
    <div class="d-grid gap-2 p-2 d-md-flex justify-content-md-end">
      <button class="btn btn-primary" type="submit">Simpan</button>
    </div>
  </form>
</div>