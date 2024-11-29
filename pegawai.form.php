<?php

$mysqli = new mysqli("localhost", "root", "", "apotek");

$action=$_GET['action'];
$currentid = "";
$nama = "";
$alamat = "";

if ($action == "add") {
  $result = $mysqli->query("SELECT MAX(Id_pegawai) AS currentid FROM tb_pegawai;");

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
  $nip = "";
  $passwd = "";
} else if ($action == "edit") {
  $currentid = $_GET['Id_pegawai'];
  $query = "SELECT * FROM tb_pegawai WHERE Id_pegawai = '" . $currentid . "';";

  //print_r($query);
  $result = $mysqli->query($query);
  $row = $result->fetch_assoc(); //khusus single result

  //print_r($row);
  $nama = $row['Nama_pegawai'];
  $telepon = $row['No_tlp'];
  $email = $row['email'];
  $nip = $row['Nip'];
  $passwd = $row['passwd'];
  
}

?>
<div class="col" style="padding-top: 20px;">
  <form action="pegawai.action.php" method="POST">
    <div class="mb-3">
      <label for="id" class="form-label">ID_PEGAWAI</label>
      <input type="text" class="form-control" name="Id_pegawai" id="NIP" placeholder="NIP" value="<?php echo $currentid; ?>">
    </div>
    <div class="mb-3">
      <label for="nama" class="form-label">NAMA</label>
      <input type="text" class="form-control" name="Nama_pegawai" id="nama" placeholder="Tuliskan nama di sini" value="<?php echo $nama; ?>">
    </div>
    <div class="mb-3">
      <label for="nama" class="form-label">TELEPON</label>
      <input type="text" class="form-control" name="No_tlp" id="nama" placeholder="Tuliskan no_telepon di sini" value="<?php echo $telepon; ?>">
    </div>
    <div class="mb-3">
      <label for="nama" class="form-label">EMAIL</label>
      <input type="email" class="form-control" name="email" id="nama" placeholder="Tuliskan email di sini" value="<?php echo $email; ?>">
    </div>
    <div class="mb-3">
      <label for="nama" class="form-label">NIP</label>
      <input type="text" class="form-control" name="Nip" id="nama" placeholder="Tuliskan nip di sini" value="<?php echo $nip; ?>">
    </div>
    <div class="mb-3">
      <label for="nama" class="form-label">PASSWORD</label>
      <input type="text" class="form-control" name="passwd" id="nama" placeholder="Tuliskan password akun di sini" value="<?php echo $passwd; ?>">
    </div>
    <input type="hidden" name="action" value="<?php echo $action; ?>">
    <div class="d-grid gap-2 p-2 d-md-flex justify-content-md-end">
      <button class="btn btn-primary" type="submit">Simpan</button>
    </div>
  </form>
</div>