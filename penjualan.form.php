<?php
require_once 'check_admin.php';
checkAdmin();

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
$queryPelanggan = "SELECT Id_pelanggan, username FROM tb_pelanggan";
$resultPelanggan = $conn->query($queryPelanggan);

$queryObat = "SELECT Id_Obat, Nama_Obat, Harga_Satuan FROM tb_obat";
$resultObat = $conn->query($queryObat);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Form Transaksi Penjualan</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Custom CSS (mirip pembelian.form.php) -->
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-wrapper {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            max-width: 900px;
            margin: auto;
        }
        .form-header {
            margin-bottom: 20px;
            text-align: center;
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
<div class="container my-5">
    <div class="form-wrapper">
        <!-- Judul Halaman -->
        <div class="form-header">
            <h2 class="<?php echo ($Id_penjualan) ? "text-primary" : "text-success"; ?>">
                <?php echo ($Id_penjualan) ? "Edit Transaksi Penjualan" : "Tambah Transaksi Penjualan"; ?>
            </h2>
        </div>

        <form action="penjualan.action.php" method="POST" id="formPenjualan" onsubmit="return validasiForm()" class="needs-validation" novalidate>
            <input type="hidden" name="action" value="<?php echo $Id_penjualan ? 'edit' : 'add'; ?>">
            <input type="hidden" name="Id_penjualan" value="<?php echo htmlspecialchars($dataPenjualan['Id_penjualan'] ?? ''); ?>">

            <div class="mb-3">
                <label for="Tanggal_penjualan" class="form-label">Tanggal Penjualan:</label>
                <input type="date" name="Tanggal_penjualan" id="Tanggal_penjualan" class="form-control" 
                       value="<?php echo htmlspecialchars($dataPenjualan['Tanggal_penjualan'] ?? ''); ?>" required>
            </div>

            <div class="mb-3">
                <label for="Id_pelanggan" class="form-label">Pelanggan:</label>
                <select name="Id_pelanggan" id="Id_pelanggan" class="form-select" required>
                    <option value="">-- Pilih Pelanggan --</option>
                    <?php while ($row = $resultPelanggan->fetch_assoc()) { ?>
                        <option value="<?php echo $row['Id_pelanggan']; ?>"
                            <?php echo (($dataPenjualan['Id_pelanggan'] ?? '') == $row['Id_pelanggan']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($row['username']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="table-responsive">
                <table id="tabelObat" class="table table-bordered">
                    <thead class="table-light">
                    <tr>
                        <th>Obat</th>
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
                    <label for="jumlah_item" class="form-label">Total Item:</label>
                    <input type="number" id="jumlah_item" name="Total_item"
                           class="form-control" value="<?php echo htmlspecialchars($dataPenjualan['Total_item'] ?? 0); ?>" readonly>
                </div>
                <div class="col-md-4">
                    <label for="harga_total" class="form-label">Total Harga:</label>
                    <input type="number" id="harga_total" name="harga_total"
                           class="form-control" value="<?php echo htmlspecialchars($dataPenjualan['harga_total'] ?? 0); ?>" readonly>
                </div>
                <div class="col-md-4">
                    <label for="total_bayar" class="form-label">Total Bayar:</label>
                    <input type="number" id="total_bayar" name="Total_bayar"
                           class="form-control"
                           value="<?php echo htmlspecialchars($dataPenjualan['Total_bayar'] ?? 0); ?>"
                           min="0" oninput="hitungKembalian()" required>
                </div>
            </div>

            <div class="row g-3 mt-3">
                <div class="col-md-4">
                    <label for="kembalian" class="form-label">Kembalian:</label>
                    <input type="number" id="kembalian" name="Kembalian"
                           class="form-control" value="<?php echo htmlspecialchars($dataPenjualan['Kembalian'] ?? 0); ?>" readonly>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-save-fill"></i> Simpan Transaksi
                </button>
                <a href="index.php?page=penjualan" class="btn btn-secondary">
                    <i class="bi bi-arrow-left-circle-fill"></i> Kembali
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Bootstrap 5 JS dan Dependensi -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Fungsi untuk menambah baris baru
    function tambahBaris(data = null) {
        const tableBody = document.getElementById("tabelObat").getElementsByTagName("tbody")[0];
        const row = tableBody.insertRow();

        // Kolom Obat
        const cellObat = row.insertCell(0);
        let selectObat = document.createElement("select");
        selectObat.name = "Id_obat[]";
        selectObat.className = "form-select";
        selectObat.required = true;
        selectObat.onchange = function() {
            updateHarga(this);
        };

        let defaultOption = document.createElement("option");
        defaultOption.value = "";
        defaultOption.text = "-- Pilih Obat --";
        selectObat.appendChild(defaultOption);

        <?php
        // Kembalikan pointer data resultObat ke awal
        $resultObat->data_seek(0);
        while ($rowObat = $resultObat->fetch_assoc()) {
                // Menggunakan 'var' untuk menghindari konflik deklarasi
                echo "var option = document.createElement('option');";
                echo "option.value = '{$rowObat['Id_Obat']}';";
                echo "option.text = '{$rowObat['Nama_Obat']}';";
                echo "selectObat.appendChild(option);";
        }
        ?>

        cellObat.appendChild(selectObat);

        // Kolom Jumlah Item
        const cellJumlah = row.insertCell(1);
        let inputJumlah = document.createElement("input");
        inputJumlah.type = "number";
        inputJumlah.name = "jumlah_item[]";
        inputJumlah.className = "form-control";
        inputJumlah.min = "1";
        inputJumlah.required = true;
        inputJumlah.oninput = updateTotal;
        cellJumlah.appendChild(inputJumlah);

        // Kolom Harga Satuan
        const cellHarga = row.insertCell(2);
        let inputHarga = document.createElement("input");
        inputHarga.type = "number";
        inputHarga.name = "harga_satuan[]";
        inputHarga.className = "form-control";
        inputHarga.min = "1";
        inputHarga.required = true;
        inputHarga.oninput = updateTotal;
        cellHarga.appendChild(inputHarga);

        // Kolom Aksi
        const cellAksi = row.insertCell(3);
        let btnHapus = document.createElement("button");
        btnHapus.type = "button";
        btnHapus.className = "btn-remove";
        btnHapus.innerHTML = "<i class='bi bi-trash-fill'></i> Hapus";
        btnHapus.onclick = function() {
            hapusBaris(this);
        };
        cellAksi.appendChild(btnHapus);

        // Jika data ada (untuk edit), set nilai input
        if (data) {
            selectObat.value = data.Id_obat;
            inputJumlah.value = data.jumlah_item;
            inputHarga.value = data.harga_satuan;
        }

        updateTotal();
    }

    // Fungsi untuk memperbarui harga berdasarkan pilihan obat
    function updateHarga(select) {
        const harga = select.options[select.selectedIndex].getAttribute("data-harga");
        const inputHarga = select.parentElement.parentElement.cells[2].querySelector("input");
        inputHarga.value = harga || "";
        updateTotal();
    }

    // Fungsi untuk hapus baris
    function hapusBaris(button) {
        const row = button.parentElement.parentElement;
        row.remove();
        updateTotal();
    }

    // Fungsi untuk menghitung total item dan total harga
    function updateTotal() {
        const jumlahItems = document.getElementsByName("jumlah_item[]");
        const hargaSatuans = document.getElementsByName("harga_satuan[]");
        let totalItem = 0;
        let totalHarga = 0;

        for (let i = 0; i < jumlahItems.length; i++) {
            const jml = parseInt(jumlahItems[i].value) || 0;
            const hrg = parseInt(hargaSatuans[i].value) || 0;
            totalItem += jml;
            totalHarga += jml * hrg;
        }

        document.getElementById("jumlah_item").value = totalItem;
        document.getElementById("harga_total").value = totalHarga;
        hitungKembalian();
    }

    // Fungsi untuk menghitung kembalian
    function hitungKembalian() {
        const totalHarga = parseInt(document.getElementById("harga_total").value) || 0;
        const totalBayar = parseInt(document.getElementById("total_bayar").value) || 0;
        let kembalian = totalBayar - totalHarga;
        document.getElementById("kembalian").value = (kembalian >= 0) ? kembalian : 0;
    }

    // Validasi form
    function validasiForm() {
        const totalHarga = parseInt(document.getElementById("harga_total").value) || 0;
        const totalBayar = parseInt(document.getElementById("total_bayar").value) || 0;
        if (totalBayar < totalHarga) {
            alert("Total bayar tidak boleh kurang dari total harga.");
            return false;
        }
        return true;
    }

    // Menambahkan baris secara otomatis jika ada data detail (edit)
    window.addEventListener("DOMContentLoaded", () => {
        <?php
        if ($dataDetail) {
            foreach ($dataDetail as $detail) {
                echo "tambahBaris(" . json_encode($detail) . ");";
            }
        } else {
            echo "tambahBaris();";
        }
        ?>

        // Contoh validasi form Bootstrap
        (function() {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms)
                .forEach(function(form) {
                    form.addEventListener('submit', function(event) {
                        if (!form.checkValidity()) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
        })();
    });
</script>
</body>
</html>

<?php
// Tutup koneksi
$conn->close();
?>