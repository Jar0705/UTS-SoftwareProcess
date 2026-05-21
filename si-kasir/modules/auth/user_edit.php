<?php
require_once '../../config/koneksi.php';
require_once '../../includes/session.php';

requireAdmin();

$pageTitle = 'Edit User';
$conn = getConnection();

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: user.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM m_user WHERE id_user = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: user.php');
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'Kasir';

    if (empty($username)) {
        $error = 'Username tidak boleh kosong!';
    } elseif (strlen($username) < 3) {
        $error = 'Username minimal 3 karakter!';
    } elseif (!in_array($role, ['Admin', 'Kasir'])) {
        $error = 'Role tidak valid!';
    } elseif (!empty($password) && strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif (!empty($password) && $password !== $confirm_password) {
        $error = 'Konfirmasi password tidak cocok!';
    } else {
        $checkStmt = $conn->prepare("SELECT id_user FROM m_user WHERE username = ? AND id_user != ?");
        $checkStmt->bind_param("si", $username, $id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $error = 'Username sudah digunakan!';
        } else {
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare("UPDATE m_user SET username = ?, password = ?, role = ? WHERE id_user = ?");
                $updateStmt->bind_param("sssi", $username, $hashedPassword, $role, $id);
            } else {
                $updateStmt = $conn->prepare("UPDATE m_user SET username = ?, role = ? WHERE id_user = ?");
                $updateStmt->bind_param("ssi", $username, $role, $id);
            }

            if ($updateStmt->execute()) {
                header('Location: user.php?success=User ' . urlencode($username) . ' berhasil diupdate!');
                exit;
            } else {
                $error = 'Gagal mengupdate user: ' . $conn->error;
            }
            $updateStmt->close();
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
                        <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($user['username']); ?>" minlength="3">
                    </div>

                    <div class="form-group">
                        <label for="password">Password Baru</label>
                        <input type="password" id="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah password" minlength="6">
                        <small>Minimal 6 karakter. Biarkan kosong jika tidak ingin mengubah password.</small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi Password Baru</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password baru">
                    </div>

                    <div class="form-group">
                        <label for="role">Role *</label>
                        <select id="role" name="role" required>
                            <option value="Kasir" <?php echo $user['role'] === 'Kasir' ? 'selected' : ''; ?>>Kasir</option>
                            <option value="Admin" <?php echo $user['role'] === 'Admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <a href="user.php" class="btn">Batal</a>
                        <button type="submit" class="btn btn-success">⤻ Update</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
