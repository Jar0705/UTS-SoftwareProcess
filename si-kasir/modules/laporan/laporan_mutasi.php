<?php
// Laporan Mutasi Stok
$filter_produk = $_GET['produk'] ?? '';
$filter_tipe = $_GET['tipe'] ?? '';

$query = "SELECT l.*, m.nama_produk 
          FROM t_log_stok l
          INNER JOIN m_produk m ON l.id_produk = m.id_produk
          WHERE 1=1";

$params = [];
$types = "";

if (!empty($filter_produk)) {
    $query .= " AND m.nama_produk LIKE ?";
    $params[] = "%$filter_produk%";
    $types .= "s";
}

if (!empty($filter_tipe)) {
    $query .= " AND l.Tipe = ?";
    $params[] = $filter_tipe;
    $types .= "s";
}

$query .= " ORDER BY l.waktu_log DESC LIMIT 100";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get all products for filter
$products = $conn->query("SELECT DISTINCT nama_produk FROM m_produk ORDER BY nama_produk");
?>

<div class="report-section">
    <div class="report-header">
        <h2>Laporan Mutasi Stok</h2>
        <p>Riwayat pergerakan stok barang (masuk & keluar)</p>
    </div>

    <div class="filter-section">
        <form method="GET" class="filter-form">
            <input type="hidden" name="tab" value="mutasi">
            <input type="text" name="produk" placeholder="Cari produk..." value="<?php echo htmlspecialchars($filter_produk); ?>">
            <select name="tipe">
                <option value="">Semua Tipe</option>
                <option value="Masuk" <?php echo $filter_tipe === 'Masuk' ? 'selected' : ''; ?>>Masuk</option>
                <option value="Keluar" <?php echo $filter_tipe === 'Keluar' ? 'selected' : ''; ?>>Keluar</option>
            </select>
            <button type="submit" class="btn">⌕ Filter</button>
            <a href="?tab=mutasi" class="btn">⟳ Reset</a>
        </form>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Produk</th>
                    <th>Tipe</th>
                    <th>Jumlah</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['waktu_log'])); ?> WIB</td>
                            <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                            <td>
                                <?php if ($row['Tipe'] === 'Masuk'): ?>
                                    <span class="badge badge-success">↓ Masuk</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">↑ Keluar</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $row['Jumlah']; ?></td>
                            <td><?php echo htmlspecialchars($row['Keterangan'] ?? '-'); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">Tidak ada data mutasi stok</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $stmt->close(); ?>
