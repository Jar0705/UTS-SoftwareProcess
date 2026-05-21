<?php
require_once '../../config/koneksi.php';
require_once '../../includes/session.php';

requireAdmin();

$pageTitle = 'Tambah User';
$conn = getConnection();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'Kasir';

    if (empty($username)) {
        $error = 'Username tidak boleh kosong!';
    } elseif (strlen($username) < 3) {
        $error = 'Username minimal 3 karakter!';
    } elseif (empty($password)) {
        $error = 'Password tidak boleh kosong!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif ($password !== $confirm_password) {
        $error = 'Konfirmasi password tidak cocok!';
    } elseif (!in_array($role, ['Admin', 'Kasir'])) {
        $error = 'Role tidak valid!';
    } else {
        $checkStmt = $conn->prepare("SELECT id_user FROM m_user WHERE username = ?");
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $error = 'Username sudah digunakan!';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO m_user (username, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashedPassword, $role);

            if ($stmt->execute()) {
                header('Location: user.php?success=User ' . urlencode($username) . ' berhasil ditambahkan!');
                exit;
            } else {
                $error = 'Gagal menambahkan user: ' . $conn->error;
            }
            $stmt->close();
        }
        $checkStmt->close();
    }
}

$conn->close();
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
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="form-container">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" placeholder="Masukkan username" minlength="3">
                    </div>

                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" required placeholder="Minimal 6 karakter" minlength="6">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required placeholder="Ulangi password">
                    </div>

                    <div class="form-group">
                        <label for="role">Role *</label>
                        <select id="role" name="role" required>
                            <option value="Kasir" <?php echo ($_POST['role'] ?? 'Kasir') === 'Kasir' ? 'selected' : ''; ?>>Kasir</option>
                            <option value="Admin" <?php echo ($_POST['role'] ?? '') === 'Admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <a href="user.php" class="btn">Batal</a>
                        <button type="submit" class="btn btn-success">⤻ Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
