<?php
// Laporan Best Seller
$query = "SELECT 
            m.id_produk,
            m.nama_produk,
            m.harga_jual,
            SUM(d.Qty) as total_terjual,
            SUM(d.Subtotal) as total_pendapatan
          FROM t_penjualan_detail d
          INNER JOIN m_produk m ON d.id_produk = m.id_produk
          INNER JOIN t_penjualan p ON d.id_penjualan = p.id_penjualan
          GROUP BY m.id_produk, m.nama_produk, m.harga_jual
          ORDER BY total_terjual DESC
          LIMIT 10";

$result = $conn->query($query);
?>

<div class="report-section">
    <div class="report-header">
        <h2>Produk Best Seller</h2>
        <p>Top 10 produk paling laku berdasarkan total quantity terjual</p>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Ranking</th>
                    <th>Nama Produk</th>
                    <th>Harga</th>
                    <th>Total Terjual</th>
                    <th>Total Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php 
                    $rank = 1;
                    while ($row = $result->fetch_assoc()): 
                    ?>
                        <tr>
                            <td>
                                <?php if ($rank === 1): ?>
                                    ◆
                                <?php elseif ($rank === 2): ?>
                                    ◈
                                <?php elseif ($rank === 3): ?>
                                    ◇
                                <?php else: ?>
                                    <?php echo $rank; ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                            <td>Rp <?php echo number_format($row['harga_jual'], 0, ',', '.'); ?></td>
                            <td><?php echo $row['total_terjual']; ?> pcs</td>
                            <td>Rp <?php echo number_format($row['total_pendapatan'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php 
                    $rank++;
                    endwhile; 
                    ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">Belum ada data penjualan</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
