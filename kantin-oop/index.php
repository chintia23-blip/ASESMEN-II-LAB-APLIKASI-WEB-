
<?php
session_start();

// ==========================================
// 1. CLASS INDUK
// ==========================================
class Menu {
    protected $id;
    protected $nama;
    protected $harga;

    public function __construct($id, $nama, $harga) {
        $this->id = $id;
        $this->nama = $nama;
        $this->harga = $harga;
    }

    public function getId() {
        return $this->id;
    }

    public function getNama() {
        return $this->nama;
    }

    public function getHarga() {
        return $this->harga;
    }

    public function displayInfo() {
        return $this->nama;
    }
}

// ==========================================
// 2. CLASS TURUNAN
// ==========================================
class Makanan extends Menu {
    private $levelPedas;

    public function __construct($id, $nama, $harga, $levelPedas) {
        parent::__construct($id, $nama, $harga);
        $this->levelPedas = $levelPedas;
    }

    public function displayInfo() {
        return "🍔 [MAKANAN] ID: {$this->id} — 
        <strong>{$this->nama}</strong> 
        (Level Pedas: {$this->levelPedas}) — Rp " .
        number_format($this->harga, 0, ',', '.');
    }
}

class Minuman extends Menu {
    private $ukuran;

    public function __construct($id, $nama, $harga, $ukuran) {
        parent::__construct($id, $nama, $harga);
        $this->ukuran = $ukuran;
    }

    public function displayInfo() {
        return "🥤 [MINUMAN] ID: {$this->id} — 
        <strong>{$this->nama}</strong> 
        (Ukuran: {$this->ukuran}) — Rp " .
        number_format($this->harga, 0, ',', '.');
    }
}

// ==========================================
// 3. DATA MENU
// ==========================================
$daftarMenu = [
    "M001" => new Makanan("M001", "Nasi Ayam Geprek", 18000, "Level 3"),
    "M002" => new Makanan("M002", "Mie Instan Telor", 12000, "Level 1"),
    "B001" => new Minuman("B001", "Es Teh Manis", 5000, "Jumbo"),
    "B002" => new Minuman("B002", "Jus Alpukat", 10000, "Regular")
];

// ==========================================
// 4. FITUR SESSION
// ==========================================
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

// Simpan nama pelanggan
if (isset($_POST['simpan_nama'])) {
    $_SESSION['nama_pelanggan'] = trim($_POST['nama']);
}

// Tambahkan pesanan
if (isset($_POST['tambah'])) {
    $id = $_POST['menu_id'];

    if (isset($daftarMenu[$id])) {
        if (isset($_SESSION['keranjang'][$id])) {
            $_SESSION['keranjang'][$id]++;
        } else {
            $_SESSION['keranjang'][$id] = 1;
        }
    }
}

// Kosongkan keranjang
if (isset($_POST['kosongkan'])) {
    $_SESSION['keranjang'] = [];
}

// ==========================================
// 5. HITUNG TOTAL PESANAN
// ==========================================

// ==========================================
// 5. FITUR UPLOAD GAMBAR
// ==========================================

$pesanUpload = "";

if (isset($_POST['upload_gambar'])) {

    $file = $_FILES['gambar'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $pesanUpload = "Gagal mengupload gambar.";
    } elseif ($file['size'] > 3 * 1024 * 1024) {
        $pesanUpload = "Ukuran gambar maksimal 3 MB.";
    } else {

        $info = getimagesize($file['tmp_name']);

        $formatGambar = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp'
        ];

        if ($info !== false &&
            isset($formatGambar[$info['mime']])) {

            $namaBaru = bin2hex(random_bytes(8)) . "." .
                $formatGambar[$info['mime']];

            $tujuan = "images/" . $namaBaru;

            if (move_uploaded_file($file['tmp_name'], $tujuan)) {
                $_SESSION['gambar_terakhir'] = $tujuan;
                $pesanUpload = "Gambar berhasil diupload!";
            } else {
                $pesanUpload = "Gagal menyimpan gambar.";
            }

        } else {
            $pesanUpload = "Format gambar harus JPG, PNG, atau WEBP.";
        }
    }
}

// ==========================================
// 6. FITUR UPLOAD DOKUMEN PDF
// ==========================================

if (isset($_POST['upload_file'])) {

    $file = $_FILES['dokumen'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $pesanUpload = "Gagal mengupload dokumen.";
    } elseif ($file['size'] > 5 * 1024 * 1024) {
        $pesanUpload = "Ukuran dokumen maksimal 5 MB.";
    } else {

        $ekstensi = strtolower(
            pathinfo($file['name'], PATHINFO_EXTENSION)
        );

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        if ($ekstensi === 'pdf' && $mime === 'application/pdf') {

            $namaBaru = bin2hex(random_bytes(8)) . ".pdf";
            $tujuan = "files/" . $namaBaru;

            if (move_uploaded_file($file['tmp_name'], $tujuan)) {
                $_SESSION['dokumen_terakhir'] = $tujuan;
                $pesanUpload = "Dokumen PDF berhasil diupload!";
            } else {
                $pesanUpload = "Gagal menyimpan dokumen.";
            }

        } else {
            $pesanUpload = "File harus berupa dokumen PDF.";
        }
    }
}

// ==========================================
// 7. HITUNG TOTAL PESANAN
// ==========================================

$total = 0;

// ==========================================
// 6. TAMPILAN WEBSITE
// ==========================================
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sistem Pemesanan Kantin</title>

<style>
body {
    font-family: 'Segoe UI', sans-serif;
    background: #f4f7f6;
    margin: 40px;
    color: #333;
}

.container {
    max-width: 750px;
    margin: auto;
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.08);
}

h2 {
    color: #2c3e50;
    border-bottom: 2px solid #3498db;
    padding-bottom: 10px;
}

.menu {
    background: #f8f9fa;
    margin: 10px 0;
    padding: 15px;
    border-left: 4px solid #3498db;
    border-radius: 4px;
}

input, select {
    padding: 10px;
    margin: 7px 0;
    width: 95%;
    border: 1px solid #ddd;
    border-radius: 5px;
}

button {
    background: #3498db;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

button:hover {
    background: #217dbb;
}

.section {
    margin-top: 25px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.info {
    color: #217dbb;
    font-weight: bold;
}
</style>
</head>

<body>

<div class="container">

<h2>Daftar Menu Kantin</h2>

<!-- SESSION NAMA PELANGGAN -->
<div class="section">
    <h3>Data Pelanggan</h3>

    <form method="POST">
        <input type="text" name="nama"
        placeholder="Masukkan nama pelanggan" required>

        <button type="submit" name="simpan_nama">
            Simpan Nama
        </button>
    </form>

    <?php if (isset($_SESSION['nama_pelanggan'])): ?>
    <p class="info">
        Selamat datang,
        <?= htmlspecialchars($_SESSION['nama_pelanggan']) ?>!
    </p>

    <a href="logout.php">
        <button type="button">Logout</button>
    </a>
<?php endif; ?>
</div>

<!-- DAFTAR MENU -->
<div class="section">
    <h3>Pilihan Makanan dan Minuman</h3>

    <?php foreach ($daftarMenu as $menu): ?>
        <div class="menu">
            <?= $menu->displayInfo() ?>

            <form method="POST">
                <input type="hidden" name="menu_id"
                value="<?= htmlspecialchars($menu->getId()) ?>">

                <button type="submit" name="tambah">
                    Tambah Pesanan
                </button>
            </form>
        </div>
    <?php endforeach; ?>
</div>

<!-- KERANJANG SESSION -->
<div class="section">
    <h3>Keranjang Pesanan</h3>

    <?php if (!empty($_SESSION['keranjang'])): ?>

        <?php foreach ($_SESSION['keranjang'] as $id => $jumlah): ?>

            <?php
            $menu = $daftarMenu[$id];
            $subtotal = $menu->getHarga() * $jumlah;
            $total += $subtotal;
            ?>

            <p>
                <?= htmlspecialchars($menu->getNama()) ?>
                (<?= $jumlah ?>)
                — Rp <?= number_format($subtotal, 0, ',', '.') ?>
            </p>

        <?php endforeach; ?>

        <hr>

        <strong>
            Total: Rp <?= number_format($total, 0, ',', '.') ?>
        </strong>

        <form method="POST">
            <button type="submit" name="kosongkan">
                Kosongkan Keranjang
            </button>
        </form>

    <?php else: ?>

        <p>Belum ada pesanan.</p>

    <?php endif; ?>
</div>

<!-- ================================== -->
<!-- FITUR UPLOAD GAMBAR -->
<!-- ================================== -->

<div class="section">
    <h3>Upload Foto Makanan</h3>

    <form method="POST" enctype="multipart/form-data">

        <label>Pilih Foto Makanan:</label>

        <input type="file"
               name="gambar"
               accept=".jpg,.jpeg,.png,.webp"
               required>

        <button type="submit" name="upload_gambar">
            Upload Gambar
        </button>

    </form>

    <?php if (!empty($_SESSION['gambar_terakhir'])): ?>

        <p>Foto berhasil disimpan:</p>

        <img
            src="<?= htmlspecialchars($_SESSION['gambar_terakhir']) ?>"
            alt="Foto makanan"
            style="width:200px; border-radius:10px;"
        >

    <?php endif; ?>
</div>


<!-- ================================== -->
<!-- FITUR UPLOAD DOKUMEN -->
<!-- ================================== -->

<div class="section">
    <h3>Upload Bukti Pembayaran</h3>

    <form method="POST" enctype="multipart/form-data">

        <label>Pilih Dokumen PDF:</label>

        <input type="file"
               name="dokumen"
               accept=".pdf,application/pdf"
               required>

        <button type="submit" name="upload_file">
            Upload Dokumen
        </button>

    </form>

    <?php if (!empty($_SESSION['dokumen_terakhir'])): ?>

        <p>Dokumen berhasil disimpan:</p>

        <a href="<?= htmlspecialchars($_SESSION['dokumen_terakhir']) ?>"
           target="_blank"
           rel="noopener">
            Lihat Bukti Pembayaran PDF
        </a>

    <?php endif; ?>
</div>


<!-- PESAN HASIL UPLOAD -->

<?php if (!empty($pesanUpload)): ?>

    <div class="section">
        <strong>
            <?= htmlspecialchars($pesanUpload) ?>
        </strong>
    </div>

<?php endif; ?>

</div>
</body>
</html>
