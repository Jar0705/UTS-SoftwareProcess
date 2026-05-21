<?php
require_once '../../config/koneksi.php';
require_once '../../includes/session.php';

requireAdmin();

$pageTitle = 'Edit Produk';
$conn = getConnection();

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

// Get product data
$stmt = $conn->prepare("SELECT * FROM m_produk WHERE id_produk = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit;
}

$produk = $result->fetch_assoc();
$stmt->close();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = trim($_POST['nama_produk'] ?? '');
    $harga_jual = trim($_POST['harga_jual'] ?? '');
    $stok_baru = intval($_POST['stok'] ?? 0);
    $keterangan = trim($_POST['keterangan'] ?? '');
    
    // Validasi
    if (empty($nama_produk)) {
        $error = 'Nama produk tidak boleh kosong!';
    } elseif (empty($harga_jual) || !is_numeric($harga_jual) || $harga_jual < 0) {
        $error = 'Harga harus berupa angka positif!';
    } elseif ($stok_baru < 0) {
        $error = 'Stok tidak boleh negatif!';
    } else {
        try {
            $conn->begin_transaction();
            
            // Update produk
            $updateStmt = $conn->prepare("UPDATE m_produk SET nama_produk = ?, harga_jual = ?, stok = ? WHERE id_produk = ?");
            $updateStmt->bind_param("sdii", $nama_produk, $harga_jual, $stok_baru, $id);
            $updateStmt->execute();
            $updateStmt->close();
            
            // Log stok jika ada perubahan
            $stok_lama = $produk['stok'];
            if ($stok_baru != $stok_lama) {
                $selisih = $stok_baru - $stok_lama;
                $tipe = $selisih > 0 ? 'Masuk' : 'Keluar';
                $jumlah = abs($selisih);
                $ket = empty($keterangan) ? 'Penyesuaian Stok Manual' : $keterangan;
                
                $logStmt = $conn->prepare("INSERT INTO t_log_stok (id_produk, Jumlah, Tipe, Keterangan) VALUES (?, ?, ?, ?)");
                $logStmt->bind_param("iiss", $id, $jumlah, $tipe, $ket);
                $logStmt->execute();
                $logStmt->close();
            }
            
            $conn->commit();
            
            header('Location: index.php?success=Produk berhasil diupdate!');
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Gagal mengupdate produk: ' . $e->getMessage();
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - SI-KASIR</title>
    <link rel="stylesheet" href="/assets/css/sidebar.css">
    <link rel="stylesheet" href="/assets/css/produk.css">
</head>
<body>
    <?php include '../../includes/sidebar.php'; ?>

    <main class="main-content">
        <?php include '../../includes/topbar.php'; ?>

        <div class="content-area">
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="form-container">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="nama_produk">Nama Produk *</label>
                        <input type="text" id="nama_produk" name="nama_produk" required value="<?php echo htmlspecialchars($produk['nama_produk']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="harga_jual">Harga Jual (Rp) *</label>
                        <input type="number" id="harga_jual" name="harga_jual" step="0.01" min="0" required value="<?php echo $produk['harga_jual']; ?>">
                    </div>

                    <div class="form-group">
                        <label for="stok">Stok *</label>
                        <input type="number" id="stok" name="stok" min="0" required value="<?php echo $produk['stok']; ?>">
                        <small>Stok saat ini: <?php echo $produk['stok']; ?></small>
                    </div>

                    <div class="form-group">
                        <label for="keterangan">Keterangan Perubahan Stok (Opsional)</label>
                        <textarea id="keterangan" name="keterangan" rows="3" placeholder="Contoh: Stock Opname, Penyesuaian, dll"></textarea>
                    </div>

                    <div class="form-actions">
                        <a href="index.php" class="btn">Batal</a>
                        <button type="submit" class="btn btn-success">⤻ Update</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
