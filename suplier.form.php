<?php

$mysqli = new mysqli("localhost", "root", "", "apotek");

$action=$_GET['action'];
$currentid = "";
$nama = "";
$alamat = "";

if ($action == "add") {
  $result = $mysqli->query("SELECT MAX(Id_suplier) AS currentid FROM tb_suplier;");

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
  $currentid = $_GET['Id_suplier'];
  $query = "SELECT * FROM tb_suplier WHERE Id_suplier = '" . $currentid . "';";

  //print_r($query);
  $result = $mysqli->query($query);
  $row = $result->fetch_assoc(); //khusus single result

  //print_r($row);
  $nama = $row['Nama_suplier'];
  $alamat = $row['Alamat'];
  $email = $row['email'];
  $telepon = $row['no_tlp'];
  
  
  
  
}

?>
<div class="col" style="padding-top: 20px;">
  <form action="suplier.action.php" method="POST">
    <div class="mb-3">
      <label for="id" class="form-label">ID SUPLIER</label>
      <input type="text" class="form-control" name="Id_suplier" id="NIP" placeholder="NIP" value="<?php echo $currentid; ?>">
    </div>
    <div class="mb-3">
      <label for="nama" class="form-label">NAMA SUPLIER</label>
      <input type="text" class="form-control" name="Nama_suplier" id="nama" placeholder="Tuliskan nama di sini" value="<?php echo $nama; ?>">
    </div>
    <div class="mb-3">
      <label for="nama" class="form-label">ALAMAT</label>
      <input type="text" class="form-control" name="Alamat" id="nama" placeholder="Tuliskan no_telepon di sini" value="<?php echo $alamat; ?>">
    </div>
    <div class="mb-3">
      <label for="nama" class="form-label">EMAIL</label>
      <input type="email" class="form-control" name="email" id="nama" placeholder="Tuliskan email di sini" value="<?php echo $email; ?>">
    </div>
    <div class="mb-3">
      <label for="nama" class="form-label">NO TELEPON</label>
      <input type="text" class="form-control" name="no_tlp" id="nama" placeholder="Tuliskan nip di sini" value="<?php echo $telepon; ?>">
    </div>
    <input type="hidden" name="action" value="<?php echo $action; ?>">
    <div class="d-grid gap-2 p-2 d-md-flex justify-content-md-end">
      <button class="btn btn-primary" type="submit">Simpan</button>
    </div>
  </form>
</div>