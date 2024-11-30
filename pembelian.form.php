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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Transaksi Pembelian</title>
    <script>
        // Fungsi untuk menambah baris input obat
        function tambahBaris(data = null) {
            let table = document.getElementById("tabelObat");
            let row = table.insertRow(-1);

            row.innerHTML = `
                <td>
                    <select name="Id_obat[]" class="selectObat" required>
                        <option value="">-- Pilih Obat --</option>
                        <?php
                        $resultObat->data_seek(0); // Reset pointer untuk mengulang data
                        while ($row = $resultObat->fetch_assoc()) { ?>
                            <option value="<?php echo $row['Id_Obat']; ?>" 
                                ${data && data.Id_obat == "<?php echo $row['Id_Obat']; ?>" ? "selected" : ""}>
                                <?php echo $row['Nama_Obat']; ?>
                            </option>
                        <?php } ?>
                    </select>
                    <button type="button" onclick="tampilkanModal()">Tambah Obat Baru</button>
                </td>
                <td><input type="date" name="tanggal_kadarluarsa[]" value="${data ? data.tanggal_kadarluarsa : ''}" required></td>
                <td><input type="number" name="jumlah_item[]" value="${data ? data.jumlah_item : ''}" min="1" required></td>
                <td><input type="number" name="harga_satuan[]" value="${data ? data.harga_satuan : ''}" min="1" required></td>
                <td><button type="button" onclick="hapusBaris(this)">Hapus</button></td>
            `;
        }

        // Fungsi untuk menghapus baris input obat
        function hapusBaris(button) {
            let row = button.parentElement.parentElement;
            row.remove();
        }

        // Fungsi untuk menampilkan modal tambah obat baru
        function tampilkanModal() {
            document.getElementById("modalTambahObat").style.display = "block";
            document.getElementById("modalOverlay").style.display = "block";
        }

        // Fungsi untuk menyembunyikan modal tambah obat baru
        function sembunyikanModal() {
            document.getElementById("modalTambahObat").style.display = "none";
            document.getElementById("modalOverlay").style.display = "none";
        }

        // Fungsi untuk menambah obat ke daftar tanpa reload
        function tambahObatKeDaftar(obat) {
            let selectList = document.querySelectorAll(".selectObat");
            selectList.forEach(select => {
                let option = document.createElement("option");
                option.value = obat.Id_Obat;
                option.text = obat.Nama_Obat;
                select.appendChild(option);
            });
        }

        // Fungsi untuk menyimpan obat baru (AJAX)
        function simpanObatBaru() {
            let nama = document.getElementById("Nama_Obat").value;
            let stok = document.getElementById("Stok_obat").value;
            let harga = document.getElementById("Harga_satuan").value;
            let jenis = document.getElementById("Id_jenis").value;

            if (nama && stok && harga && jenis) {
                fetch('proses_tambah_obat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ Nama_Obat: nama, Stok_obat: stok, Harga_satuan: harga, Id_jenis: jenis }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        tambahObatKeDaftar(data.obat);
                        sembunyikanModal();
                    } else {
                        alert("Gagal menambahkan obat baru.");
                    }
                });
            } else {
                alert("Semua field harus diisi!");
            }
        }
    </script>
    <style>
        #modalTambahObat, #modalOverlay {
            display: none;
        }

        #modalOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        #modalTambahObat {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            padding: 20px;
            border: 1px solid #ccc;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <h1>Form Transaksi Pembelian</h1>
    <form action="proses_pembelian.php" method="POST">
        <label for="tanggal_pembelian">Tanggal Pembelian:</label>
        <input type="date" name="tanggal_pembelian" required><br><br>

        <label for="Id_suplier">Supplier:</label>
        <select name="Id_suplier" required>
            <?php while ($row = $resultSuplier->fetch_assoc()) { ?>
                <option value="<?php echo $row['Id_suplier']; ?>">
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
                <!-- Baris obat akan ditambahkan -->
            </tbody>
        </table>
        <br>
        <button type="button" onclick="tambahBaris()">Tambah Obat</button><br><br>

        <button type="submit">Simpan Transaksi</button>
    </form>

    <!-- Modal Tambah Obat Baru -->
    <div id="modalOverlay" onclick="sembunyikanModal()"></div>
    <div id="modalTambahObat">
        <h3>Tambah Obat Baru</h3>
        <label for="Nama_Obat">Nama Obat:</label>
        <input type="text" id="Nama_Obat" required><br><br>

        <label for="Stok_obat">Stok Obat:</label>
        <input type="number" id="Stok_obat" min="1" required><br><br>

        <label for="Harga_satuan">Harga Satuan:</label>
        <input type="number" id="Harga_satuan" min="1" required><br><br>

        <label for="Id_jenis">Jenis Obat:</label>
        <input type="number" id="Id_jenis" required><br><br>

        <button type="button" onclick="simpanObatBaru()">Simpan Obat</button>
        <button type="button" onclick="sembunyikanModal()">Batal</button>
    </div>
</body>
</html>
