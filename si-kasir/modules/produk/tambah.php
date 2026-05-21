<?php
require_once '../../config/koneksi.php';
require_once '../../includes/session.php';

requireAdmin();

$pageTitle = 'Tambah Produk';
$conn = getConnection();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = trim($_POST['nama_produk'] ?? '');
    $harga_jual = trim($_POST['harga_jual'] ?? '');
    $stok = trim($_POST['stok'] ?? '');
    
    // Validasi
    if (empty($nama_produk)) {
        $error = 'Nama produk tidak boleh kosong!';
    } elseif (empty($harga_jual) || !is_numeric($harga_jual) || $harga_jual < 0) {
        $error = 'Harga harus berupa angka positif!';
    } elseif (empty($stok) || !is_numeric($stok) || $stok < 0) {
        $error = 'Stok harus berupa angka positif!';
    } else {
        try {
            $conn->begin_transaction();
            
            // Insert produk
            $stmt = $conn->prepare("INSERT INTO m_produk (nama_produk, harga_jual, stok) VALUES (?, ?, ?)");
            $stmt->bind_param("sdi", $nama_produk, $harga_jual, $stok);
            $stmt->execute();
            $id_produk = $conn->insert_id;
            $stmt->close();
            
            // Insert log stok awal
            $logStmt = $conn->prepare("INSERT INTO t_log_stok (id_produk, Jumlah, Tipe, Keterangan) VALUES (?, ?, 'Masuk', 'Stok Awal')");
            $logStmt->bind_param("ii", $id_produk, $stok);
            $logStmt->execute();
            $logStmt->close();
            
            $conn->commit();
            
            header('Location: index.php?success=Produk berhasil ditambahkan!');
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Gagal menambahkan produk: ' . $e->getMessage();
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
                        <input type="text" id="nama_produk" name="nama_produk" required value="<?php echo htmlspecialchars($_POST['nama_produk'] ?? ''); ?>" placeholder="Masukkan nama produk">
                    </div>

                    <div class="form-group">
                        <label for="harga_jual">Harga Jual (Rp) *</label>
                        <input type="number" id="harga_jual" name="harga_jual" step="0.01" min="0" required value="<?php echo htmlspecialchars($_POST['harga_jual'] ?? ''); ?>" placeholder="0">
                    </div>

                    <div class="form-group">
                        <label for="stok">Stok Awal *</label>
                        <input type="number" id="stok" name="stok" min="0" required value="<?php echo htmlspecialchars($_POST['stok'] ?? ''); ?>" placeholder="0">
                    </div>

                    <div class="form-actions">
                        <a href="index.php" class="btn">Batal</a>
                        <button type="submit" class="btn btn-success">⤻ Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
