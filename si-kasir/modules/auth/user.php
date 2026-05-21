<?php
require_once '../../config/koneksi.php';
require_once '../../includes/session.php';

requireAdmin();

$pageTitle = 'Manajemen User';
$conn = getConnection();

if (isset($_GET['success'])) {
    $success = $_GET['success'];
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    if ($id == getUserId()) {
        $error = 'Tidak dapat menghapus akun sendiri!';
    } else {
        $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM t_penjualan WHERE id_user = ?");
        $checkStmt->bind_param("i", $id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $checkData = $checkResult->fetch_assoc();

        if ($checkData['count'] > 0) {
            $error = 'User tidak dapat dihapus karena sudah memiliki riwayat transaksi!';
        } else {
            $deleteStmt = $conn->prepare("DELETE FROM m_user WHERE id_user = ?");
            $deleteStmt->bind_param("i", $id);
            if ($deleteStmt->execute()) {
                $success = 'User berhasil dihapus!';
            } else {
                $error = 'Gagal menghapus user!';
            }
            $deleteStmt->close();
        }
        $checkStmt->close();
    }
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$query = "SELECT * FROM m_user WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND LOWER(username) LIKE LOWER(?)";
    $params[] = "%$search%";
    $types .= "s";
}

$query .= " ORDER BY id_user ASC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
$totalResults = count($users);
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
                        <input type="text" name="search" placeholder="⌕ Cari username..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary">⌕ Cari</button>
                        <?php if (!empty($search)): ?>
                            <a href="user.php" class="btn btn-secondary">⟳ Reset</a>
                        <?php endif; ?>
                    </form>
                    <?php if (!empty($search)): ?>
                        <p style="margin-top: 10px; color: #7878a0; font-size: 14px;">
                            Menampilkan <?php echo $totalResults; ?> hasil
                            untuk "<strong><?php echo htmlspecialchars($search); ?></strong>"
                        </p>
                    <?php endif; ?>
                </div>
                <a href="user_tambah.php" class="btn btn-success">+ Tambah User</a>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $row): ?>
                                <tr>
                                    <td><?php echo $row['id_user']; ?></td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td>
                                        <?php if ($row['role'] === 'Admin'): ?>
                                            <span class="badge badge-primary">Admin</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">Kasir</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="action-buttons">
                                        <a href="user_edit.php?id=<?php echo $row['id_user']; ?>" class="btn btn-sm">✎ Edit</a>
                                        <a href="?delete=<?php echo $row['id_user']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus user <?php echo htmlspecialchars($row['username']); ?>?')">⌫ Hapus</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 40px;">
                                    <div style="color: #7878a0; font-size: 16px;">
                                        ◎ Tidak ada data user
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
