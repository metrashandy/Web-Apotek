<?php
// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "apotek");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Inisialisasi variabel
$Id_pembelian = isset($_GET['Id_pembelian']) ? $_GET['Id_pembelian'] : null;
$dataPembelian = null;
$dataDetail = [];

// Jika `Id_pembelian` diberikan, ambil data untuk update
if ($Id_pembelian) {
    $queryPembelian = "SELECT * FROM tb_pembelian WHERE Id_pembelian = ?";
    $stmt = $conn->prepare($queryPembelian);
    $stmt->bind_param("i", $Id_pembelian);
    $stmt->execute();
    $resultPembelian = $stmt->get_result();
    $dataPembelian = $resultPembelian->fetch_assoc();

    $queryDetail = "SELECT * FROM tb_pembelian_detail WHERE Id_pembelian = ?";
    $stmt = $conn->prepare($queryDetail);
    $stmt->bind_param("i", $Id_pembelian);
    $stmt->execute();
    $resultDetail = $stmt->get_result();
    while ($row = $resultDetail->fetch_assoc()) {
        $dataDetail[] = $row;
    }
}

// Query untuk mendapatkan data obat dan suplier
$queryObat = "SELECT Id_Obat, Nama_Obat FROM tb_obat";
$resultObat = $conn->query($queryObat);

$querySuplier = "SELECT Id_suplier, Nama_suplier FROM tb_suplier";
$resultSuplier = $conn->query($querySuplier);

$queryJenis = "SELECT Id_jenis, nama_jenis, bentuk_obat FROM tb_jenis_obat";
$resultJenis = $conn->query($queryJenis);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Transaksi Pembelian</title>
    <style>
        #modalTambahObat {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
            border-radius: 10px;
        }

        #modalOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
    </style>
    <script>
        // Menambahkan baris input obat
        function tambahBaris(data = null) {
            let table = document.getElementById("tabelObat");
            let row = table.insertRow(-1);

            row.innerHTML = `
                <td>
                    <select name="Id_obat[]" class="selectObat" onchange="checkTambahObat(this)" required>
                        <option value="">-- Pilih Obat --</option>
                        <?php
                        $resultObat->data_seek(0); // Reset pointer untuk mengulang data
                        while ($row = $resultObat->fetch_assoc()) { ?>
                            <option value="<?php echo $row['Id_Obat']; ?>" 
                                ${data && data.Id_obat == "<?php echo $row['Id_Obat']; ?>" ? "selected" : ""}>
                                <?php echo $row['Nama_Obat']; ?>
                            </option>
                        <?php } ?>
                        <option value="tambah">+ Tambah Obat Baru</option>
                    </select>
                </td>
                <td><input type="date" name="tanggal_kadarluarsa[]" value="${data ? data.tanggal_kadarluarsa : ''}" required></td>
                <td><input type="text" name="jumlah_item[]" value="${data ? data.jumlah_item : ''}" min="1" oninput="updateTotal()" required></td>
                <td><input type="text" name="harga_satuan[]" value="${data ? data.harga_satuan : ''}" min="1" oninput="updateTotal()" required></td>
                <td><button type="button" onclick="hapusBaris(this)">Hapus</button></td>
            `;
        }

        // Menghapus baris input obat
        function hapusBaris(button) {
            let row = button.parentElement.parentElement;
            row.remove();
            updateTotal();
        }

        // Menghitung total stok dan harga
        function updateTotal() {
            let jumlahItems = document.getElementsByName('jumlah_item[]');
            let hargaSatuans = document.getElementsByName('harga_satuan[]');

            let totalItem = 0;
            let totalHarga = 0;

            for (let i = 0; i < jumlahItems.length; i++) {
                let jumlah = parseInt(jumlahItems[i].value) || 0;
                let harga = parseInt(hargaSatuans[i].value) || 0;

                totalItem += jumlah;
                totalHarga += jumlah * harga;
            }

            document.getElementById('total_item').value = totalItem;
            document.getElementById('total_harga').value = totalHarga;
        }

        // Memunculkan modal tambah obat
        function checkTambahObat(select) {
            if (select.value === "tambah") {
                document.getElementById("modalTambahObat").style.display = "block";
                document.getElementById("modalOverlay").style.display = "block";
                select.value = ""; // Reset nilai dropdown
            }
        }

        function simpanObatBaru(formId) {
            const form = document.getElementById(formId);
            const formData = new FormData(form);

            fetch('obat.action.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    alert('Obat berhasil ditambahkan!');
                    tutupModal();
                    location.reload(); // Memuat ulang halaman untuk menampilkan data terbaru
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menyimpan data.');
                });
        }



        // Simpan transaksi
        function simpanTransaksi(event) {
            event.preventDefault();
            const form = document.querySelector('form[action="pembelian.action.php"]');
            const formData = new FormData(form);

            fetch('pembelian.action.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    alert('Transaksi berhasil disimpan!');
                    window.location.href = "index.php?page=pembelian";
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menyimpan data.');
                });
        }

        function hitungKembalian() {
            const totalHarga = parseInt(document.getElementById("total_harga").value) || 0;
            const totalBayar = parseInt(document.getElementById("total_bayar").value) || 0;

            let kembalian = totalBayar - totalHarga;
            document.getElementById("kembalian").value = kembalian >= 0 ? kembalian : 0;
        }

        function validasiForm() {
            const totalHarga = parseInt(document.getElementById("total_harga").value) || 0;
            const totalBayar = parseInt(document.getElementById("total_bayar").value) || 0;

            console.log(`Total Harga: ${totalHarga}, Total Bayar: ${totalBayar}`); // Debugging

            if (totalBayar < totalHarga) {
                alert("Total bayar tidak boleh kurang dari total harga.");
                return false; // Mencegah pengiriman form
            }
            return true;
        }



        // Menutup modal tambah obat
        function tutupModal() {
            document.getElementById("modalTambahObat").style.display = "none";
            document.getElementById("modalOverlay").style.display = "none";
        }
    </script>
</head>

<body>
    <div id="modalOverlay" onclick="tutupModal()"></div>
    <div id="modalTambahObat">
        <h2>Tambah Obat Baru</h2>
        <form id="formTambahObat" onsubmit="event.preventDefault(); simpanObatBaru('formTambahObat');" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">

            <label for="namaObatBaru">Nama Obat:</label>
            <input type="text" name="Nama_Obat" id="Nama_Obat" required><br><br>

            <input type="hidden" name="Stok_Obat" value="0"> <!-- Stok otomatis diatur ke 0 -->

            <label for="hargaObatBaru">Harga Satuan:</label>
            <input type="text" name="Harga_satuan" id="Harga_satuan" min="1" required><br><br>

            <label for="Id_jenis">Jenis Obat:</label>
            <select name="Id_jenis" required>
                <?php while ($row = $resultJenis->fetch_assoc()) { ?>
                    <option value="<?php echo $row['Id_jenis']; ?>">
                        <?php echo $row['nama_jenis'] . " - " . $row['bentuk_obat']; ?>
                    </option>
                <?php } ?>
            </select><br><br>

            <label for="foto_obat">Gambar Obat (Max 1 MB):</label>
            <input type="file" name="foto_obat" id="foto_obat" accept="image/*" required><br><br>

            <button type="button" onclick="tutupModal()">Batal</button>
            <button type="submit">Simpan Data</button>
        </form>
    </div>



    <h1>Form Transaksi Pembelian</h1>
    <form action="pembelian.action.php" method="POST" id="formTransaksi" onsubmit="return validasiForm()">


        <input type="hidden" name="action" value="<?php echo $Id_pembelian ? 'edit' : 'add'; ?>">
        <input type="hidden" name="Id_pembelian" value="<?php echo $dataPembelian['Id_pembelian'] ?? ''; ?>">

        <label for="tanggal_pembelian">Tanggal Pembelian:</label>
        <input type="date" name="tanggal_pembelian" value="<?php echo $dataPembelian['tanggal_pembelian'] ?? ''; ?>" required><br><br>

        <label for="Id_suplier">Supplier:</label>
        <select name="Id_suplier" required>
            <?php while ($row = $resultSuplier->fetch_assoc()) { ?>
                <option value="<?php echo $row['Id_suplier']; ?>"
                    <?php echo ($dataPembelian['Id_suplier'] ?? '') == $row['Id_suplier'] ? 'selected' : ''; ?>>
                    <?php echo $row['Nama_suplier']; ?>
                </option>
            <?php } ?>
        </select><br><br>

        <table id="tabelObat" border="1">
            <thead>
                <tr>
                    <th>Obat</th>
                    <th>Tanggal Kadaluarsa</th>
                    <th>Jumlah Item</th>
                    <th>Harga Satuan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($dataDetail) {
                    foreach ($dataDetail as $detail) {
                        echo "<script>tambahBaris(" . json_encode($detail) . ");</script>";
                    }
                } else {
                    echo "<script>tambahBaris();</script>";
                }
                ?>
            </tbody>
        </table>
        <br>
        <button type="button" onclick="tambahBaris()">Tambah Obat</button><br><br>

        <label for="total_item">Total Item:</label>
        <input type="number" name="total_item" id="total_item" value="<?php echo $dataPembelian['total_item'] ?? 0; ?>" readonly><br><br>

        <label for="total_harga">Total Harga:</label>
        <input type="number" name="total_harga" id="total_harga" value="<?php echo $dataPembelian['total_harga'] ?? 0; ?>" readonly><br><br>

        <label for="total_bayar">Total Bayar:</label>
        <input type="number" name="total_bayar" id="total_bayar"
            value="<?php echo $dataPembelian['Total_bayar'] ?? 0; ?>"
            oninput="hitungKembalian()" required><br><br>


        <label for="kembalian">Kembalian:</label>
        <input type="number" name="kembalian" id="kembalian"
            value="<?php echo $dataPembelian['kembalian'] ?? 0; ?>" readonly><br><br>

        <button type="submit">Simpan Transaksi</button>
    </form>
</body>

</html>