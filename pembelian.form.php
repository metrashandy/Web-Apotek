<?php
require_once 'check_admin.php';
checkAdmin();

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

// Query untuk mendapatkan data obat, suplier, dan jenis obat
$queryObat = "SELECT Id_Obat, Nama_Obat FROM tb_obat";
$resultObat = $conn->query($queryObat);

$querySuplier = "SELECT Id_suplier, Nama_suplier FROM tb_suplier";
$resultSuplier = $conn->query($querySuplier);

$queryJenis = "SELECT Id_jenis, nama_jenis, bentuk_obat FROM tb_jenis_obat";
$resultJenis = $conn->query($queryJenis);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Transaksi Pembelian</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Custom CSS untuk Penyesuaian -->
    <style>
        body {
            background-color: #f8f9fa;
            /* Warna latar belakang netral */
        }

        .form-wrapper {
            background-color: #ffffff;
            /* Latar belakang putih untuk konten */
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            /* Bayangan untuk kedalaman */
            max-width: 900px;
            margin: auto;
        }

        .form-header {
            margin-bottom: 20px;
            text-align: center;
        }

        #modalTambahObat {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1050;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
            border-radius: 10px;
            width: 100%;
            max-width: 500px;
        }

        #modalOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1040;
        }

        .btn-remove {
            color: #dc3545;
            border: none;
            background: none;
            cursor: pointer;
        }

        .btn-remove:hover {
            color: #a71d2a;
        }
    </style>
</head>

<body>
    <!-- Modal Overlay -->
    <div id="modalOverlay" onclick="tutupModal()"></div>

    <!-- Modal Tambah Obat -->
    <div id="modalTambahObat" class="shadow">
        <h2 class="text-center mb-4">Tambah Obat Baru</h2>
        <form id="formTambahObat" onsubmit="event.preventDefault(); simpanObatBaru('formTambahObat');" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">

            <div class="mb-3">
                <label for="Nama_Obat" class="form-label">Nama Obat:</label>
                <input type="text" name="Nama_Obat" id="Nama_Obat" class="form-control" required>
            </div>

            <input type="hidden" name="Stok_Obat" value="0"> <!-- Stok otomatis diatur ke 0 -->

            <div class="mb-3">
                <label for="Harga_satuan" class="form-label">Harga Satuan:</label>
                <input type="number" name="Harga_satuan" id="Harga_satuan" class="form-control" min="1" required>
            </div>

            <div class="mb-3">
                <label for="Id_jenis" class="form-label">Jenis Obat:</label>
                <select name="Id_jenis" class="form-select" required>
                    <option value="">-- Pilih Jenis Obat --</option>
                    <?php while ($row = $resultJenis->fetch_assoc()) { ?>
                        <option value="<?php echo $row['Id_jenis']; ?>">
                            <?php echo $row['nama_jenis'] . " - " . $row['bentuk_obat']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="foto_obat" class="form-label">Gambar Obat (Max 1 MB):</label>
                <input type="file" name="foto_obat" id="foto_obat" class="form-control" accept="image/*" required>
            </div>

            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-secondary me-2" onclick="tutupModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Data</button>
            </div>
        </form>
    </div>

    <div class="container my-5">
        <div class="form-wrapper">
            <!-- Judul Halaman -->
            <div class="form-header">
                <h2 class="<?php echo ($Id_pembelian) ? "text-primary" : "text-success"; ?>">
                    <?php echo ($Id_pembelian) ? "Edit Transaksi Pembelian" : "Tambah Transaksi Pembelian"; ?>
                </h2>
            </div>

            <!-- Formulir Transaksi Pembelian -->
            <form action="pembelian.action.php" method="POST" id="formTransaksi" onsubmit="return validasiForm()" class="needs-validation" novalidate>

                <input type="hidden" name="action" value="<?php echo $Id_pembelian ? 'edit' : 'add'; ?>">
                <input type="hidden" name="Id_pembelian" value="<?php echo htmlspecialchars($dataPembelian['Id_pembelian'] ?? ''); ?>">

                <div class="mb-3">
                    <label for="tanggal_pembelian" class="form-label">Tanggal Pembelian:</label>
                    <input type="date" name="tanggal_pembelian" id="tanggal_pembelian" class="form-control" value="<?php echo htmlspecialchars($dataPembelian['tanggal_pembelian'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="Id_suplier" class="form-label">Supplier:</label>
                    <select name="Id_suplier" id="Id_suplier" class="form-select" required>
                        <option value="">-- Pilih Supplier --</option>
                        <?php while ($row = $resultSuplier->fetch_assoc()) { ?>
                            <option value="<?php echo $row['Id_suplier']; ?>"
                                <?php echo (($dataPembelian['Id_suplier'] ?? '') == $row['Id_suplier']) ? 'selected' : ''; ?>>
                                <?php echo $row['Nama_suplier']; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="table-responsive">
                    <table id="tabelObat" class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Obat</th>
                                <th>Tanggal Kadaluarsa</th>
                                <th>Jumlah Item</th>
                                <th>Harga Satuan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Baris Obat Akan Ditambahkan Secara Dinamis -->
                        </tbody>
                    </table>
                </div>

                <button type="button" class="btn btn-secondary mb-3" onclick="tambahBaris()">
                    <i class="bi bi-plus-circle-fill"></i> Tambah Obat
                </button>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="total_item" class="form-label">Total Item:</label>
                        <input type="number" name="total_item" id="total_item" class="form-control" value="<?php echo htmlspecialchars($dataPembelian['total_item'] ?? 0); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label for="total_harga" class="form-label">Total Harga:</label>
                        <input type="number" name="total_harga" id="total_harga" class="form-control" value="<?php echo htmlspecialchars($dataPembelian['total_harga'] ?? 0); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label for="total_bayar" class="form-label">Total Bayar:</label>
                        <input type="number" name="total_bayar" id="total_bayar" class="form-control" value="<?php echo htmlspecialchars($dataPembelian['Total_bayar'] ?? 0); ?>" oninput="hitungKembalian()" required>
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-4">
                        <label for="kembalian" class="form-label">Kembalian:</label>
                        <input type="number" name="kembalian" id="kembalian" class="form-control" value="<?php echo htmlspecialchars($dataPembelian['kembalian'] ?? 0); ?>" readonly>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-save-fill"></i> Simpan Transaksi
                    </button>
                    <a href="index.php?page=pembelian" class="btn btn-secondary">
                        <i class="bi bi-arrow-left-circle-fill"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Menambahkan baris input obat
        function tambahBaris(data = null) {
            let table = document.getElementById("tabelObat").getElementsByTagName('tbody')[0];
            let row = table.insertRow();

            // Kolom Obat
            let cellObat = row.insertCell(0);
            let selectObat = document.createElement("select");
            selectObat.name = "Id_obat[]";
            selectObat.className = "form-select";
            selectObat.required = true;
            selectObat.onchange = function() {
                checkTambahObat(this)
            };

            let defaultOption = document.createElement("option");
            defaultOption.value = "";
            defaultOption.text = "-- Pilih Obat --";
            selectObat.appendChild(defaultOption);

            <?php
            // Reset pointer untuk mengulang data obat
            $resultObat->data_seek(0);
            while ($rowObat = $resultObat->fetch_assoc()) {
                // Menggunakan 'var' untuk menghindari konflik deklarasi
                echo "var option = document.createElement('option');";
                echo "option.value = '{$rowObat['Id_Obat']}';";
                echo "option.text = '{$rowObat['Nama_Obat']}';";
                echo "selectObat.appendChild(option);";
            }
            ?>
            // Option untuk menambah obat baru
            let tambahOption = document.createElement("option");
            tambahOption.value = "tambah";
            tambahOption.text = "+ Tambah Obat Baru";
            selectObat.appendChild(tambahOption);

            if (data && data.Id_obat) {
                selectObat.value = data.Id_obat;
            }

            cellObat.appendChild(selectObat);

            // Kolom Tanggal Kadaluarsa
            let cellKadaluarsa = row.insertCell(1);
            let inputKadaluarsa = document.createElement("input");
            inputKadaluarsa.type = "date";
            inputKadaluarsa.name = "tanggal_kadarluarsa[]";
            inputKadaluarsa.className = "form-control";
            inputKadaluarsa.required = true;
            if (data && data.tanggal_kadarluarsa) {
                inputKadaluarsa.value = data.tanggal_kadarluarsa;
            }
            cellKadaluarsa.appendChild(inputKadaluarsa);

            // Kolom Jumlah Item
            let cellJumlah = row.insertCell(2);
            let inputJumlah = document.createElement("input");
            inputJumlah.type = "number";
            inputJumlah.name = "jumlah_item[]";
            inputJumlah.className = "form-control";
            inputJumlah.min = "1";
            inputJumlah.required = true;
            inputJumlah.oninput = updateTotal;
            if (data && data.jumlah_item) {
                inputJumlah.value = data.jumlah_item;
            }
            cellJumlah.appendChild(inputJumlah);

            // Kolom Harga Satuan
            let cellHarga = row.insertCell(3);
            let inputHarga = document.createElement("input");
            inputHarga.type = "number";
            inputHarga.name = "harga_satuan[]";
            inputHarga.className = "form-control";
            inputHarga.min = "1";
            inputHarga.required = true;
            inputHarga.oninput = updateTotal;
            if (data && data.harga_satuan) {
                inputHarga.value = data.harga_satuan;
            }
            cellHarga.appendChild(inputHarga);

            // Kolom Aksi
            let cellAksi = row.insertCell(4);
            let btnHapus = document.createElement("button");
            btnHapus.type = "button";
            btnHapus.className = "btn-remove";
            btnHapus.innerHTML = "<i class='bi bi-trash-fill'></i> Hapus";
            btnHapus.onclick = function() {
                hapusBaris(this)
            };
            cellAksi.appendChild(btnHapus);

            updateTotal();
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
            const form = document.getElementById('formTransaksi');
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

        // Tambahkan baris saat halaman dimuat
        window.onload = function() {
            <?php
            if ($dataDetail) {
                foreach ($dataDetail as $detail) {
                    echo "tambahBaris(" . json_encode($detail) . ");";
                }
            } else {
                echo "tambahBaris();";
            }
            ?>
        };

        // Contoh validasi form Bootstrap
        (function() {
            'use strict'

            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            var forms = document.querySelectorAll('.needs-validation')

            // Loop over them and prevent submission
            Array.prototype.slice.call(forms)
                .forEach(function(form) {
                    form.addEventListener('submit', function(event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }

                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
    <!-- Bootstrap 5 JS dan Dependensi -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>