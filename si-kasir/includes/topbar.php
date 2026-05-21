<!-- Top Bar Component -->
<div class="top-bar">
    <h1 class="page-title"><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
    <div class="top-bar-actions">
        <button class="notification-btn" title="Notifikasi" onclick="showNotifications()">
            ⚡
            <?php
            // Check for critical stock
            $conn_temp = getConnection();
            $result = $conn_temp->query("SELECT COUNT(*) as count FROM m_produk WHERE stok < 5");
            $stok_kritis = $result->fetch_assoc()['count'];
            $conn_temp->close();
            
            if ($stok_kritis > 0):
            ?>
                <span class="notification-badge"><?php echo $stok_kritis; ?></span>
            <?php endif; ?>
        </button>
        <button class="profile-btn" title="Profile" onclick="showProfile()">
            ◉
        </button>
    </div>
</div>

<script>
function showNotifications() {
    alert('Notifikasi: Ada <?php echo $stok_kritis ?? 0; ?> produk dengan stok kritis!');
}

function showProfile() {
    alert('Profile: <?php echo getUsername(); ?> (<?php echo getUserRole(); ?>)');
}
</script>
