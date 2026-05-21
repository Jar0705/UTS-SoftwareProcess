<?php
require_once '../../config/koneksi.php';
require_once '../../includes/session.php';

requireLogin();

$pageTitle = 'Transaksi Penjualan';
$conn = getConnection();

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$error = '';
$success = '';

// Handle Add to Cart
if (isset($_POST['add_to_cart'])) {
    $id_produk = intval($_POST['id_produk']);
    $qty = intval($_POST['qty']);
    
    // Get product info
    $stmt = $conn->prepare("SELECT * FROM m_produk WHERE id_produk = ?");
    $stmt->bind_param("i", $id_produk);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $produk = $result->fetch_assoc();
        
        // Check stock
        if ($qty > $produk['stok']) {
            $error = "Stok " . $produk['nama_produk'] . " tidak mencukupi untuk transaksi ini.";
        } else {
            // Check if product already in cart
            $found = false;
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['id_produk'] == $id_produk) {
                    $item['qty'] += $qty;
                    $item['subtotal'] = $item['qty'] * $item['harga_jual'];
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $_SESSION['cart'][] = [
                    'id_produk' => $id_produk,
                    'nama_produk' => $produk['nama_produk'],
                    'harga_jual' => $produk['harga_jual'],
                    'qty' => $qty,
                    'subtotal' => $qty * $produk['harga_jual']
                ];
            }
            
            $success = "Produk berhasil ditambahkan ke keranjang!";
        }
    }
    $stmt->close();
}

// Handle Remove from Cart
if (isset($_GET['remove'])) {
    $index = intval($_GET['remove']);
    if (isset($_SESSION['cart'][$index])) {
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array
        $success = "Item berhasil dihapus dari keranjang!";
    }
}

// Handle Clear Cart
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
    $success = "Keranjang berhasil dikosongkan!";
}

// Handle Checkout
if (isset($_POST['checkout'])) {
    $uang_bayar = floatval($_POST['uang_bayar'] ?? 0);
    
    if (empty($_SESSION['cart'])) {
        $error = "Keranjang masih kosong!";
    } else {
        // Calculate total
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['subtotal'];
        }
        
        if ($uang_bayar < $total) {
            $error = "Uang bayar kurang dari total tagihan.";
        } else {
            try {
                $conn->begin_transaction();
                
                // Generate nomor nota
                $tanggal = date('Ymd');
                $stmt = $conn->query("SELECT COUNT(*) as count FROM t_penjualan WHERE DATE(tgl_transaksi) = CURDATE()");
                $row = $stmt->fetch_assoc();
                $nomor_urut = str_pad($row['count'] + 1, 4, '0', STR_PAD_LEFT);
                $nomor_nota = "PJN" . $tanggal . $nomor_urut;
                
                // Check stock availability for all items
                foreach ($_SESSION['cart'] as $item) {
                    $checkStmt = $conn->prepare("SELECT stok FROM m_produk WHERE id_produk = ?");
                    $checkStmt->bind_param("i", $item['id_produk']);
                    $checkStmt->execute();
                    $checkResult = $checkStmt->get_result();
                    $produk = $checkResult->fetch_assoc();
                    
                    if ($produk['stok'] < $item['qty']) {
                        throw new Exception("Stok " . $item['nama_produk'] . " tidak mencukupi untuk transaksi ini.");
                    }
                    $checkStmt->close();
                }
                
                // Insert header
                $id_user = getUserId();
                $tgl_transaksi = date('Y-m-d H:i:s');
                $headerStmt = $conn->prepare("INSERT INTO t_penjualan (nomor_nota, tgl_transaksi, total_bayar, id_user) VALUES (?, ?, ?, ?)");
                $headerStmt->bind_param("ssdi", $nomor_nota, $tgl_transaksi, $total, $id_user);
                $headerStmt->execute();
                $id_penjualan = $conn->insert_id;
                $headerStmt->close();
                
                // Insert details and update stock
                foreach ($_SESSION['cart'] as $item) {
                    // Insert detail
                    $detailStmt = $conn->prepare("INSERT INTO t_penjualan_detail (id_penjualan, id_produk, Qty, Subtotal) VALUES (?, ?, ?, ?)");
                    $detailStmt->bind_param("iiid", $id_penjualan, $item['id_produk'], $item['qty'], $item['subtotal']);
                    $detailStmt->execute();
                    $detailStmt->close();
                    
                    // Update stock
                    $updateStmt = $conn->prepare("UPDATE m_produk SET stok = stok - ? WHERE id_produk = ?");
                    $updateStmt->bind_param("ii", $item['qty'], $item['id_produk']);
                    $updateStmt->execute();
                    $updateStmt->close();
                    
                    // Log stock
                    $keterangan = "Penjualan Nota #" . $nomor_nota;
                    $logStmt = $conn->prepare("INSERT INTO t_log_stok (id_produk, Jumlah, Tipe, Keterangan) VALUES (?, ?, 'Keluar', ?)");
                    $logStmt->bind_param("iis", $item['id_produk'], $item['qty'], $keterangan);
                    $logStmt->execute();
                    $logStmt->close();
                }
                
                $conn->commit();
                
                // Store transaction info for receipt
                $_SESSION['last_transaction'] = [
                    'nomor_nota' => $nomor_nota,
                    'items' => $_SESSION['cart'],
                    'total' => $total,
                    'bayar' => $uang_bayar,
                    'kembalian' => $uang_bayar - $total
                ];
                
                // Clear cart
                $_SESSION['cart'] = [];
                
                header('Location: nota.php');
                exit;
                
            } catch (Exception $e) {
                $conn->rollback();
                $error = $e->getMessage();
            }
        }
    }
}

// Get all products
$products = $conn->query("SELECT * FROM m_produk WHERE stok > 0 ORDER BY nama_produk ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - SI-KASIR</title>
    <link rel="stylesheet" href="/assets/css/sidebar.css">
    <link rel="stylesheet" href="/assets/css/transaksi.css">
</head>
<body>
    <?php include '../../includes/sidebar.php'; ?>

    <main class="main-content">
        <?php include '../../includes/topbar.php'; ?>

        <div class="content-area">
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="transaction-layout">
                <!-- Product Selection -->
                <div class="product-section">
                    <h3>⊞ Pilih Produk</h3>
                    <form method="POST" class="add-product-form">
                        <div class="form-group">
                            <label>Produk</label>
                            <select name="id_produk" required>
                                <option value="">-- Pilih Produk --</option>
                                <?php while ($prod = $products->fetch_assoc()): ?>
                                    <option value="<?php echo $prod['id_produk']; ?>">
                                        <?php echo htmlspecialchars($prod['nama_produk']); ?> - Rp <?php echo number_format($prod['harga_jual'], 0, ',', '.'); ?> (Stok: <?php echo $prod['stok']; ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Jumlah</label>
                            <input type="number" name="qty" min="1" value="1" required>
                        </div>
                        
                        <button type="submit" name="add_to_cart" class="btn btn-success btn-block">+ Tambah ke Keranjang</button>
                    </form>
                </div>

                <!-- Cart -->
                <div class="cart-section">
                    <div class="cart-header">
                        <h3>⇄ Keranjang Belanja</h3>
                        <?php if (!empty($_SESSION['cart'])): ?>
                            <a href="?clear=1" class="btn btn-sm btn-danger" onclick="return confirm('Kosongkan keranjang?')">⌫ Kosongkan</a>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($_SESSION['cart'])): ?>
                        <div class="empty-cart">
                            <div style="font-size: 64px; margin-bottom: 20px;">⇄</div>
                            <p>Keranjang masih kosong</p>
                        </div>
                    <?php else: ?>
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Harga</th>
                                    <th>Qty</th>
                                    <th>Subtotal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total = 0;
                                foreach ($_SESSION['cart'] as $index => $item): 
                                    $total += $item['subtotal'];
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['nama_produk']); ?></td>
                                        <td>Rp <?php echo number_format($item['harga_jual'], 0, ',', '.'); ?></td>
                                        <td><?php echo $item['qty']; ?></td>
                                        <td>Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                                        <td>
                                            <a href="?remove=<?php echo $index; ?>" class="btn btn-sm btn-danger">⌫</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="total-row">
                                    <td colspan="3"><strong>TOTAL</strong></td>
                                    <td colspan="2"><strong>Rp <?php echo number_format($total, 0, ',', '.'); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>

                        <!-- Checkout Form -->
                        <form method="POST" class="checkout-form">
                            <div class="form-group">
                                <label>⟐ Uang Bayar (Rp)</label>
                                <input type="number" name="uang_bayar" min="<?php echo $total; ?>" step="1000" required placeholder="Masukkan jumlah uang">
                            </div>
                            <button type="submit" name="checkout" class="btn btn-success btn-block">⤻ Selesaikan Transaksi</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
<?php $conn->close(); ?>
