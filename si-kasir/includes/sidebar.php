<!-- Header Component -->
<header class="header" id="header">
    <div class="header-left">
        <a href="/index.php" class="header-brand">
            <span class="brand-icon">◆</span>
            <span class="brand-text">SI-KASIR</span>
        </a>
    </div>

    <nav class="header-nav">
        <a href="/index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' && dirname($_SERVER['PHP_SELF']) === '/' ? 'active' : ''; ?>">
            <span class="nav-icon">◈</span>
            <span class="nav-text">Dashboard</span>
        </a>
        
        <?php if (getUserRole() === 'Admin'): ?>
        <a href="/modules/produk/index.php" class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/modules/produk/') !== false ? 'active' : ''; ?>">
            <span class="nav-icon">⊞</span>
            <span class="nav-text">Produk</span>
        </a>
        <a href="/modules/auth/user.php" class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/modules/auth/user') !== false ? 'active' : ''; ?>">
            <span class="nav-icon">◎</span>
            <span class="nav-text">User</span>
        </a>
        <?php endif; ?>
        
        <?php if (getUserRole() !== 'Admin'): ?>
        <a href="/modules/transaksi/index.php" class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/modules/transaksi/') !== false ? 'active' : ''; ?>">
            <span class="nav-icon">⇄</span>
            <span class="nav-text">Transaksi</span>
        </a>
        <?php endif; ?>
        
        <?php if (getUserRole() === 'Admin'): ?>
        <a href="/modules/laporan/index.php" class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/modules/laporan/') !== false ? 'active' : ''; ?>">
            <span class="nav-icon">▣</span>
            <span class="nav-text">Laporan</span>
        </a>
        <?php endif; ?>
    </nav>

    <div class="header-right">
        <div class="header-user">
            <div class="user-avatar-mini">
                <?php echo strtoupper(substr(getUsername(), 0, 1)); ?>
            </div>
            <div class="user-info-mini">
                <span class="user-name"><?php echo htmlspecialchars(getUsername()); ?></span>
                <span class="user-role"><?php echo htmlspecialchars(getUserRole()); ?></span>
            </div>
        </div>
        <a href="/modules/auth/logout.php" class="logout-btn" title="Logout">
            <span>⏻</span>
        </a>
    </div>
</header>
