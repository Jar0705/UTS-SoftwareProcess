<?php
require_once '../../config/koneksi.php';
require_once '../../includes/session.php';

requireAdmin();

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$conn = getConnection();

// Get transaction header
$stmt = $conn->prepare("SELECT p.*, u.username FROM t_penjualan p INNER JOIN m_user u ON p.id_user = u.id_user WHERE p.id_penjualan = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit;
}

$transaksi = $result->fetch_assoc();
$stmt->close();

// Get transaction details
$detailStmt = $conn->prepare("SELECT d.*, m.nama_produk FROM t_penjualan_detail d INNER JOIN m_produk m ON d.id_produk = m.id_produk WHERE d.id_penjualan = ?");
$detailStmt->bind_param("i", $id);
$detailStmt->execute();
$details = $detailStmt->get_result();
$detailStmt->close();

$pageTitle = 'Detail Transaksi';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - SI-KASIR</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/transaksi.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-content">
                <h1>📄 Detail Transaksi</h1>
                <a href="index.php?tab=penjualan" class="btn">◀ Kembali</a>
            </div>
        </header>

        <div class="nota-container">
            <div class="nota-header">
                <h1>TOKO MAJU JAYA</h1>
                <p>Detail Transaksi Penjualan</p>
            </div>

            <div class="nota-divider"></div>

            <div class="nota-info">
                <table>
                    <tr>
                        <td>No. Nota</td>
                        <td>: <?php echo $transaksi['nomor_nota']; ?></td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td>: <?php echo date('d/m/Y H:i:s', strtotime($transaksi['tgl_transaksi'])); ?> WIB</td>
                    </tr>
                    <tr>
                        <td>Kasir</td>
                        <td>: <?php echo htmlspecialchars($transaksi['username']); ?></td>
                    </tr>
                </table>
            </div>

            <div class="nota-divider"></div>

            <div class="nota-items">
                <table>
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Qty</th>
                            <th>Harga</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($detail = $details->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($detail['nama_produk']); ?></td>
                                <td><?php echo $detail['Qty']; ?></td>
                                <td>Rp <?php echo number_format($detail['Subtotal'] / $detail['Qty'], 0, ',', '.'); ?></td>
                                <td>Rp <?php echo number_format($detail['Subtotal'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="nota-divider"></div>

            <div class="nota-total">
                <table>
                    <tr>
                        <td><strong>TOTAL</strong></td>
                        <td><strong>Rp <?php echo number_format($transaksi['total_bayar'], 0, ',', '.'); ?></strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>
