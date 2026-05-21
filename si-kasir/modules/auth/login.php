<?php
require_once '../../config/koneksi.php';
require_once '../../includes/session.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ../../index.php');
    exit;
}

$error = '';
$ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $conn_check = getConnection();
    $conn_check->query("CREATE TABLE IF NOT EXISTS t_login_attempts (
        id_attempt INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        username VARCHAR(50) NOT NULL,
        attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_ip_time (ip_address, attempted_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $stmt = $conn_check->prepare("SELECT COUNT(*) as count FROM t_login_attempts WHERE ip_address = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
    $stmt->bind_param("s", $ip_address);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $attempts = $row ? $row['count'] : 0;
    $stmt->close();
    $conn_check->close();

    if ($attempts >= 5) {
        $error = 'Terlalu banyak percobaan login! Silakan coba lagi dalam 5 menit.';
    }

    if (empty($error)) {
        if (empty($username) || empty($password)) {
            $error = 'Username dan Password wajib diisi!';
        } else {
            try {
            $conn = getConnection();
            $stmt = $conn->prepare("SELECT id_user, username, password, role FROM m_user WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Debug mode - remove this in production
            $debug = false; // Set to true for debugging

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Check if password is hashed or plain text
                $isValidPassword = false;
                
                // Try plain text comparison first
                if ($password === $user['password']) {
                    $isValidPassword = true;
                    
                    // Auto-hash the password for security
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = $conn->prepare("UPDATE m_user SET password = ? WHERE id_user = ?");
                    $updateStmt->bind_param("si", $hashedPassword, $user['id_user']);
                    $updateStmt->execute();
                    $updateStmt->close();
                } else {
                    // Try password_verify for hashed passwords
                    $isValidPassword = password_verify($password, $user['password']);
                }
                
                if ($isValidPassword) {
                    $_SESSION['id_user'] = $user['id_user'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];

                    $clearStmt = $conn->prepare("DELETE FROM t_login_attempts WHERE ip_address = ?");
                    $clearStmt->bind_param("s", $ip_address);
                    $clearStmt->execute();
                    $clearStmt->close();

                    header('Location: ../../index.php');
                    exit;
                } else {
                    $error = 'Username atau Password salah!';
                    if ($debug) {
                        $error .= " (Debug: Password tidak cocok)";
                    }
                    $logStmt = $conn->prepare("INSERT INTO t_login_attempts (ip_address, username) VALUES (?, ?)");
                    $logStmt->bind_param("ss", $ip_address, $username);
                    $logStmt->execute();
                    $logStmt->close();
                }
            } else {
                $error = 'Username atau Password salah!';
                if ($debug) {
                    $error .= " (Debug: User tidak ditemukan)";
                }
                $logStmt = $conn->prepare("INSERT INTO t_login_attempts (ip_address, username) VALUES (?, ?)");
                $logStmt->bind_param("ss", $ip_address, $username);
                $logStmt->execute();
                $logStmt->close();
            }
            
            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan sistem. Silakan coba lagi.';
            // For debugging: $error = 'Error: ' . $e->getMessage();
            if (isset($conn) && $conn) {
                $logStmt = $conn->prepare("INSERT INTO t_login_attempts (ip_address, username) VALUES (?, ?)");
                $logStmt->bind_param("ss", $ip_address, $username);
                $logStmt->execute();
                $logStmt->close();
            }
        }
    }
}
}

$pageTitle = 'Login';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - SI-KASIR</title>
    <link rel="stylesheet" href="../../assets/css/login.css">
</head>
<body>
    <div class="login-container">
        <h1>SI-KASIR</h1>
        <h2>Sistem Informasi Kasir Terintegrasi</h2>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Masukkan username" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password" required>
            </div>
            
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
