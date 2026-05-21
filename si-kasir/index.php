<?php
require_once 'config/koneksi.php';
require_once 'includes/session.php';

requireLogin();

$pageTitle = 'Dashboard';
$conn = getConnection();

// Get statistics
$stats = [
    'total_produk' => 0,
    'stok_kritis' => 0,
    'transaksi_hari_ini' => 0,
    'pendapatan_hari_ini' => 0
];

// Total produk
$result = $conn->query("SELECT COUNT(*) as count FROM m_produk");
$stats['total_produk'] = $result->fetch_assoc()['count'];

// Stok kritis
$result = $conn->query("SELECT COUNT(*) as count FROM m_produk WHERE stok < 5");
$stats['stok_kritis'] = $result->fetch_assoc()['count'];

// Transaksi hari ini
$result = $conn->query("SELECT COUNT(*) as count, COALESCE(SUM(total_bayar), 0) as total FROM t_penjualan WHERE DATE(tgl_transaksi) = CURDATE()");
$row = $result->fetch_assoc();
$stats['transaksi_hari_ini'] = $row['count'];
$stats['pendapatan_hari_ini'] = $row['total'];

// Recent transactions
$recent_transactions = $conn->query("SELECT p.*, u.username FROM t_penjualan p INNER JOIN m_user u ON p.id_user = u.id_user ORDER BY p.tgl_transaksi DESC LIMIT 5");

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - SI-KASIR</title>
    <link rel="stylesheet" href="/assets/css/sidebar.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h1 class="page-title">Dashboard</h1>
            <div class="top-bar-actions">
                <button class="notification-btn" title="Notifikasi">
                    ⚡
                    <?php if ($stats['stok_kritis'] > 0): ?>
                        <span class="notification-badge"><?php echo $stats['stok_kritis']; ?></span>
                    <?php endif; ?>
                </button>
                <button class="profile-btn" title="Profile">
                    ◉
                </button>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Welcome Message -->
            <div style="background: linear-gradient(135deg, rgba(0,240,255,0.08) 0%, rgba(184,0,255,0.08) 100%); color: #00f0ff; padding: 30px; border-radius: 4px; margin-bottom: 30px; border: 1px solid rgba(0,240,255,0.2); box-shadow: 0 0 20px rgba(0,240,255,0.1);">
                <h2 style="font-size: 28px; margin-bottom: 10px; text-shadow: 0 0 10px rgba(0,240,255,0.3);">Selamat Datang, <?php echo htmlspecialchars(getUsername()); ?>! 👋</h2>
                <p style="opacity: 0.8; font-size: 16px; color: #7878a0;">Sistem Informasi Kasir Terintegrasi - Toko Maju Jaya</p>
            </div>

            <!-- Statistics Cards -->
            <div class="dashboard-grid">
                <div class="stat-card primary">
                    <div class="stat-header">
                        <span class="stat-title">Total Produk</span>
                        <div class="stat-icon">⊞</div>
                    </div>
                    <div class="stat-value"><?php echo $stats['total_produk']; ?></div>
                    <div class="stat-label">Produk terdaftar</div>
                </div>

                <div class="stat-card <?php echo $stats['stok_kritis'] > 0 ? 'danger' : 'success'; ?>">
                    <div class="stat-header">
                        <span class="stat-title">Stok Kritis</span>
                        <div class="stat-icon">⚡</div>
                    </div>
                    <div class="stat-value"><?php echo $stats['stok_kritis']; ?></div>
                    <div class="stat-label">Produk stok < 5</div>
                </div>

                <div class="stat-card success">
                    <div class="stat-header">
                        <span class="stat-title">Transaksi Hari Ini</span>
                        <div class="stat-icon">▤</div>
                    </div>
                    <div class="stat-value"><?php echo $stats['transaksi_hari_ini']; ?></div>
                    <div class="stat-label">Total transaksi</div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-header">
                        <span class="stat-title">Pendapatan Hari Ini</span>
                        <div class="stat-icon">⟐</div>
                    </div>
                    <div class="stat-value">Rp <?php echo number_format($stats['pendapatan_hari_ini'], 0, ',', '.'); ?></div>
                    <div class="stat-label">Total penjualan</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h3>▸ Aksi Cepat</h3>
                <div class="action-buttons">
                    <?php if (getUserRole() === 'Admin'): ?>
                    <a href="/modules/produk/tambah.php" class="action-btn">
                        <span class="icon">⊕</span>
                        <span>Tambah Produk</span>
                    </a>
                    <a href="/modules/laporan/index.php" class="action-btn">
                        <span class="icon">▣</span>
                        <span>Lihat Laporan</span>
                    </a>
                    <a href="/modules/produk/index.php?filter=kritis" class="action-btn">
                        <span class="icon">⚡</span>
                        <span>Cek Stok Kritis</span>
                    </a>
                    <?php else: ?>
                    <a href="/modules/transaksi/index.php" class="action-btn">
                        <span class="icon">⇄</span>
                        <span>Transaksi Baru</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Transactions -->
            <?php if ($recent_transactions->num_rows > 0): ?>
            <div style="background: #1a1a2e; padding: 25px; border-radius: 4px; border: 1px solid rgba(0,240,255,0.15);">
                <h3 style="color: #00f0ff; margin-bottom: 20px; font-size: 16px; text-transform: uppercase; letter-spacing: 1px;">▣ Transaksi Terbaru</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: rgba(0,240,255,0.05);">
                            <th style="padding: 12px; text-align: left; color: #00f0ff; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">No. Nota</th>
                            <th style="padding: 12px; text-align: left; color: #00f0ff; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Tanggal</th>
                            <th style="padding: 12px; text-align: left; color: #00f0ff; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Kasir</th>
                            <th style="padding: 12px; text-align: right; color: #00f0ff; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($trans = $recent_transactions->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid rgba(0,240,255,0.1);">
                            <td style="padding: 12px; color: #c8c8d8; font-family: 'Courier New', monospace;"><?php echo $trans['nomor_nota']; ?></td>
                            <td style="padding: 12px; color: #7878a0;"><?php echo date('d/m/Y H:i', strtotime($trans['tgl_transaksi'])); ?> WIB</td>
                            <td style="padding: 12px; color: #7878a0;"><?php echo htmlspecialchars($trans['username']); ?></td>
                            <td style="padding: 12px; text-align: right; color: #00f0ff; font-weight: 600; font-family: 'Courier New', monospace;">Rp <?php echo number_format($trans['total_bayar'], 0, ',', '.'); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
