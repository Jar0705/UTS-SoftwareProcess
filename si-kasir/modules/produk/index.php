<?php
require_once '../../config/koneksi.php';
require_once '../../includes/session.php';

requireAdmin();

$pageTitle = 'Master Produk';
$conn = getConnection();

// Handle success message
if (isset($_GET['success'])) {
    $success = $_GET['success'];
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Check if product is used in transactions
    $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM t_penjualan_detail WHERE id_produk = ?");
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $checkData = $checkResult->fetch_assoc();
    
    if ($checkData['count'] > 0) {
        $error = "Produk tidak dapat dihapus karena sudah pernah ada dalam transaksi!";
    } else {
        $deleteStmt = $conn->prepare("DELETE FROM m_produk WHERE id_produk = ?");
        $deleteStmt->bind_param("i", $id);
        if ($deleteStmt->execute()) {
            $success = "Produk berhasil dihapus!";
        } else {
            $error = "Gagal menghapus produk!";
        }
        $deleteStmt->close();
    }
    $checkStmt->close();
}

// Get search and filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter = $_GET['filter'] ?? 'all';

// Build query
$query = "SELECT * FROM m_produk WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND LOWER(nama_produk) LIKE LOWER(?)";
    $params[] = "%$search%";
    $types .= "s";
}

if ($filter === 'kritis') {
    $query .= " AND stok < 5";
}

$query .= " ORDER BY id_produk ASC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Store results in array to avoid pointer issues
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
$totalResults = count($products);
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
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="toolbar">
                <div class="search-filter">
                    <form method="GET" action="" class="search-form">
                        <input type="text" name="search" placeholder="⌕ Cari produk..." value="<?php echo htmlspecialchars($search); ?>">
                        <select name="filter" onchange="this.form.submit()">
                            <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>Semua Produk</option>
                            <option value="kritis" <?php echo $filter === 'kritis' ? 'selected' : ''; ?>>Stok Kritis (&lt; 5)</option>
                        </select>
                        <button type="submit" class="btn btn-primary">⌕ Cari</button>
                        <?php if (!empty($search) || $filter !== 'all'): ?>
                            <a href="index.php" class="btn btn-secondary">⟳ Reset</a>
                        <?php endif; ?>
                    </form>
                    <?php if (!empty($search) || $filter !== 'all'): ?>
                        <p style="margin-top: 10px; color: #7878a0; font-size: 14px;">
                            Menampilkan <?php echo $totalResults; ?> hasil
                            <?php if (!empty($search)): ?>
                                untuk "<strong><?php echo htmlspecialchars($search); ?></strong>"
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                </div>
                <a href="tambah.php" class="btn btn-success">+ Tambah Produk</a>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Produk</th>
                            <th>Harga Jual</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($products) > 0): ?>
                            <?php foreach ($products as $row): ?>
                                <tr>
                                    <td><?php echo $row['id_produk']; ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                                    <td>Rp <?php echo number_format($row['harga_jual'], 0, ',', '.'); ?></td>
                                    <td><?php echo $row['stok']; ?></td>
                                    <td>
                                        <?php if ($row['stok'] < 5): ?>
                                            <span class="badge badge-danger">Stok Kritis</span>
                                        <?php elseif ($row['stok'] < 20): ?>
                                            <span class="badge badge-warning">Stok Rendah</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">Stok Aman</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="action-buttons">
                                        <a href="edit.php?id=<?php echo $row['id_produk']; ?>" class="btn btn-sm">✎ Edit</a>
                                        <a href="?delete=<?php echo $row['id_produk']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus produk ini?')">⌫ Hapus</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px;">
                                    <div style="color: #7878a0; font-size: 16px;">
                                        ⊡ Tidak ada data produk
                                        <?php if (!empty($search)): ?>
                                            <br><small>untuk pencarian "<?php echo htmlspecialchars($search); ?>"</small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
