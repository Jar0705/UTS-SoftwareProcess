<?php
require_once '../../config/koneksi.php';
require_once '../../includes/session.php';

requireLogin();

if (!isset($_SESSION['last_transaction'])) {
    header('Location: index.php');
    exit;
}

$trans = $_SESSION['last_transaction'];
$pageTitle = 'Nota Penjualan';
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
        <div class="nota-container">
            <div class="nota-header">
                <h1>TOKO MAJU JAYA</h1>
                <p>Sistem Informasi Kasir Terintegrasi</p>
                <p>Jl. Contoh No. 123, Kota</p>
                <p>Telp: (021) 12345678</p>
            </div>

            <div class="nota-divider"></div>

            <div class="nota-info">
                <table>
                    <tr>
                        <td>No. Nota</td>
                        <td>: <?php echo $trans['nomor_nota']; ?></td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td>: <?php echo date('d/m/Y H:i:s'); ?> WIB</td>
                    </tr>
                    <tr>
                        <td>Kasir</td>
                        <td>: <?php echo htmlspecialchars(getUsername()); ?></td>
                    </tr>
                </table>
            </div>

            <div class="nota-divider"></div>

            <div class="nota-items">
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Harga</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trans['items'] as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['nama_produk']); ?></td>
                                <td><?php echo $item['qty']; ?></td>
                                <td>Rp <?php echo number_format($item['harga_jual'], 0, ',', '.'); ?></td>
                                <td>Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="nota-divider"></div>

            <div class="nota-total">
                <table>
                    <tr>
                        <td><strong>TOTAL</strong></td>
                        <td><strong>Rp <?php echo number_format($trans['total'], 0, ',', '.'); ?></strong></td>
                    </tr>
                    <tr>
                        <td>Bayar</td>
                        <td>Rp <?php echo number_format($trans['bayar'], 0, ',', '.'); ?></td>
                    </tr>
                    <tr>
                        <td>Kembalian</td>
                        <td>Rp <?php echo number_format($trans['kembalian'], 0, ',', '.'); ?></td>
                    </tr>
                </table>
            </div>

            <div class="nota-divider"></div>

            <div class="nota-footer">
                <p>Terima kasih atas kunjungan Anda!</p>
                <p>Barang yang sudah dibeli tidak dapat dikembalikan</p>
            </div>

            <div class="nota-actions">
                <button onclick="window.print()" class="btn btn-success">⎙ Cetak Nota</button>
                <a href="index.php" class="btn">◀ Transaksi Baru</a>
            </div>
        </div>
    </div>

    <style>
        @media print {
            .nota-actions {
                display: none;
            }
            body {
                background: white;
            }
            .container {
                padding: 0;
            }
        }
    </style>
</body>
</html>
