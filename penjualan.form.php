<?php
// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "apotek");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Inisialisasi variabel
$Id_penjualan = isset($_GET['Id_penjualan']) ? $_GET['Id_penjualan'] : null;
$dataPenjualan = null;
$dataDetail = [];

// Jika `Id_penjualan` diberikan, ambil data untuk update
if ($Id_penjualan) {
    $queryPenjualan = "SELECT * FROM tb_penjualan WHERE Id_penjualan = ?";
    $stmt = $conn->prepare($queryPenjualan);
    $stmt->bind_param("i", $Id_penjualan);
    $stmt->execute();
    $resultPenjualan = $stmt->get_result();
    $dataPenjualan = $resultPenjualan->fetch_assoc();

    $queryDetail = "SELECT * FROM tb_penjualan_detail WHERE Id_penjualan = ?";
    $stmt = $conn->prepare($queryDetail);
    $stmt->bind_param("i", $Id_penjualan);
    $stmt->execute();
    $resultDetail = $stmt->get_result();
    while ($row = $resultDetail->fetch_assoc()) {
        $dataDetail[] = $row;
    }
}

// Query untuk mendapatkan data pelanggan dan obat
$queryPelanggan = "SELECT Id_pelanggan, Nama_pelanggan FROM tb_pelanggan";
$resultPelanggan = $conn->query($queryPelanggan);

$queryObat = "SELECT Id_Obat, Nama_Obat, Harga_Satuan FROM tb_obat";
$resultObat = $conn->query($queryObat);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Transaksi Penjualan</title>
    <script>
        // Fungsi untuk menambah baris baru
        function tambahBaris(data = null) {
            const table = document.getElementById("tabelObat");
            const row = table.insertRow(-1);

            row.innerHTML = `
                <td>
                    <select name="Id_obat[]" class="selectObat" onchange="updateHarga(this)" required>
                        <option value="">-- Pilih Obat --</option>
                        <?php
                        $resultObat->data_seek(0); // Reset pointer untuk mengulang data
                        while ($row = $resultObat->fetch_assoc()) {
                            echo '<option value="' . $row['Id_Obat'] . '" data-harga="' . $row['Harga_Satuan'] . '">' . $row['Nama_Obat'] . '</option>';
                        }
                        ?>
                    </select>
                </td>
                <td><input type="text" name="jumlah_item[]" value="${data ? data.jumlah_item : 1}" min="1" oninput="updateTotal()" required></td>
                <td><input type="text" name="harga_satuan[]" value="${data ? data.harga_satuan : ''}" min="1" oninput="updateTotal()" required></td>
                <td><button type="button" onclick="hapusBaris(this)">Hapus</button></td>
            `;

            if (data) {
                const select = row.querySelector("select");
                select.value = data.Id_obat;
                updateHarga(select);
            }
        }

        // Fungsi untuk menghapus baris
        function hapusBaris(button) {
            const row = button.parentElement.parentElement;
            row.remove();
            updateTotal();
        }

        // Fungsi untuk memperbarui harga berdasarkan pilihan obat
        function updateHarga(select) {
            const harga = select.options[select.selectedIndex].getAttribute("data-harga");
            const hargaInput = select.parentElement.nextElementSibling.nextElementSibling.querySelector("input");
            hargaInput.value = harga || '';
            updateTotal();
        }

        // Fungsi untuk menghitung total item dan total harga
        function updateTotal() {
            const jumlahItems = document.getElementsByName('jumlah_item[]');
            const hargaSatuans = document.getElementsByName('harga_satuan[]');

            let totalItem = 0;
            let totalHarga = 0;

            for (let i = 0; i < jumlahItems.length; i++) {
                const jumlah = parseInt(jumlahItems[i].value) || 0;
                const harga = parseInt(hargaSatuans[i].value) || 0;

                totalItem += jumlah;
                totalHarga += jumlah * harga;
            }

            // Perbarui input total item dan harga total
            document.getElementById('jumlah_item').value = totalItem;
            document.getElementById('harga_total').value = totalHarga;
        }

        function simpanTransaksi(event) {
            event.preventDefault();
            const form = document.querySelector('form[action="penjualan.action.php"]');
            const formData = new FormData(form);

            fetch('penjualan.action.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    alert('Transaksi berhasil disimpan!');
                    window.location.href = "index.php?page=penjualan";
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menyimpan data.');
                });
        }
    </script>
</head>

<body>
    <h1>Form Transaksi Penjualan</h1>
    <form action="penjualan.action.php" method="POST">
        <input type="hidden" name="action" value="<?php echo $Id_penjualan ? 'edit' : 'add'; ?>">
        <input type="hidden" name="Id_penjualan" value="<?php echo $dataPenjualan['Id_penjualan'] ?? ''; ?>">

        <label for="Tanggal_penjualan">Tanggal Penjualan:</label>
        <input type="date" name="Tanggal_penjualan" value="<?php echo $dataPenjualan['Tanggal_penjualan'] ?? ''; ?>" required><br><br>

        <label for="Id_pelanggan">Pelanggan:</label>
        <select name="Id_pelanggan" required>
            <?php while ($row = $resultPelanggan->fetch_assoc()) { ?>
                <option value="<?php echo $row['Id_pelanggan']; ?>"
                    <?php echo ($dataPenjualan['Id_pelanggan'] ?? '') == $row['Id_pelanggan'] ? 'selected' : ''; ?>>
                    <?php echo $row['Nama_pelanggan']; ?>
                </option>
            <?php } ?>
        </select><br><br>

        <table id="tabelObat" border="1">
            <thead>
                <tr>
                    <th>Obat</th>
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

        <label for="jumlah_item">Total Item:</label>
        <input type="number" id="jumlah_item" name="Total_item" value="<?php echo $dataPenjualan['Total_item'] ?? 0; ?>" readonly><br><br>

        <label for="harga_total">Total Harga:</label>
        <input type="number" id="harga_total" name="harga_total" value="<?php echo $dataPenjualan['harga_total'] ?? 0; ?>" readonly><br><br>

        <button type="button" onclick="simpanTransaksi(event)">Simpan Transaksi</button>
    </form>
</body>

</html>