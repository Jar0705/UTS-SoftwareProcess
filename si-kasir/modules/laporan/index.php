<?php
require_once '../../config/koneksi.php';
require_once '../../includes/session.php';

requireAdmin();

$pageTitle = 'Laporan & Analisis';
$conn = getConnection();

// Get date filter
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - SI-KASIR</title>
    <link rel="stylesheet" href="/assets/css/sidebar.css">
    <link rel="stylesheet" href="/assets/css/laporan.css">
</head>
<body>
    <?php include '../../includes/sidebar.php'; ?>

    <main class="main-content">
        <?php include '../../includes/topbar.php'; ?>

        <div class="content-area">
            <!-- Menu Tabs -->
            <div class="tabs">
                <a href="?tab=penjualan&tanggal=<?php echo $tanggal; ?>" class="tab <?php echo (!isset($_GET['tab']) || $_GET['tab'] === 'penjualan') ? 'active' : ''; ?>">
                    ▤ Laporan Penjualan
                </a>
                <a href="?tab=bestseller" class="tab <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'bestseller') ? 'active' : ''; ?>">
                    ★ Best Seller
                </a>
                <a href="?tab=mutasi" class="tab <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'mutasi') ? 'active' : ''; ?>">
                    ⊞ Mutasi Stok
                </a>
            </div>

            <?php
            $tab = $_GET['tab'] ?? 'penjualan';
            
            if ($tab === 'penjualan') {
                include 'laporan_penjualan.php';
            } elseif ($tab === 'bestseller') {
                include 'laporan_bestseller.php';
            } elseif ($tab === 'mutasi') {
                include 'laporan_mutasi.php';
            }
            ?>
        </div>
    </main>
</body>
</html>
<?php $conn->close(); ?>
