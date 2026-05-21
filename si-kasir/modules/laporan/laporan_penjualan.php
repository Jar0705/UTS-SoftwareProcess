<?php
// Laporan Penjualan Harian
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');

$query = "SELECT p.*, u.username 
          FROM t_penjualan p 
          INNER JOIN m_user u ON p.id_user = u.id_user 
          WHERE DATE(p.tgl_transaksi) = ? 
          ORDER BY p.tgl_transaksi DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $tanggal);
$stmt->execute();
$result = $stmt->get_result();

// Calculate total
$total_penjualan = 0;
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
    $total_penjualan += $row['total_bayar'];
}
$stmt->close();
?>

<div class="report-section">
    <div class="report-header">
        <h2>Laporan Penjualan Harian</h2>
        <form method="GET" class="date-filter">
            <input type="hidden" name="tab" value="penjualan">
            <input type="date" name="tanggal" value="<?php echo $tanggal; ?>">
            <button type="submit" class="btn">⌕ Filter</button>
        </form>
    </div>

    <div class="summary-cards">
        <div class="summary-card">
            <div class="summary-icon">📅</div>
            <div class="summary-info">
                <h3>Tanggal</h3>
                <p><?php 
                    $bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    $tgl = date('d', strtotime($tanggal));
                    $bln = $bulan[(int)date('m', strtotime($tanggal))];
                    $thn = date('Y', strtotime($tanggal));
                    echo "$tgl $bln $thn";
                ?></p>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon">▤</div>
            <div class="summary-info">
                <h3>Total Transaksi</h3>
                <p><?php echo count($data); ?> transaksi</p>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon">⟐</div>
            <div class="summary-info">
                <h3>Total Penjualan</h3>
                <p>Rp <?php echo number_format($total_penjualan, 0, ',', '.'); ?></p>
            </div>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No. Nota</th>
                    <th>Tanggal</th>
                    <th>Kasir</th>
                    <th>Total</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($data) > 0): ?>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <td><?php echo $row['nomor_nota']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['tgl_transaksi'])); ?> WIB</td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td>Rp <?php echo number_format($row['total_bayar'], 0, ',', '.'); ?></td>
                            <td>
                                <a href="detail_transaksi.php?id=<?php echo $row['id_penjualan']; ?>" class="btn btn-sm">◎ Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">Tidak ada transaksi pada tanggal ini</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
