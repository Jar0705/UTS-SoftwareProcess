-- Database: db_majujaya
CREATE DATABASE IF NOT EXISTS db_majujaya;
USE db_majujaya;

-- Table: m_user
CREATE TABLE IF NOT EXISTS m_user (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Kasir') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: m_produk
CREATE TABLE IF NOT EXISTS m_produk (
    id_produk INT AUTO_INCREMENT PRIMARY KEY,
    nama_produk VARCHAR(100) NOT NULL,
    harga_jual DECIMAL(10,2) NOT NULL,
    stok INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: t_penjualan
CREATE TABLE IF NOT EXISTS t_penjualan (
    id_penjualan INT AUTO_INCREMENT PRIMARY KEY,
    nomor_nota VARCHAR(20) UNIQUE NOT NULL,
    tgl_transaksi DATETIME NOT NULL,
    total_bayar DECIMAL(12,2) NOT NULL,
    id_user INT NOT NULL,
    FOREIGN KEY (id_user) REFERENCES m_user(id_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: t_penjualan_detail
CREATE TABLE IF NOT EXISTS t_penjualan_detail (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_penjualan INT NOT NULL,
    id_produk INT NOT NULL,
    Qty INT NOT NULL,
    Subtotal DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (id_penjualan) REFERENCES t_penjualan(id_penjualan),
    FOREIGN KEY (id_produk) REFERENCES m_produk(id_produk)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: t_log_stok
CREATE TABLE IF NOT EXISTS t_log_stok (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_produk INT NOT NULL,
    Jumlah INT NOT NULL,
    Tipe ENUM('Masuk', 'Keluar') NOT NULL,
    Keterangan VARCHAR(255),
    waktu_log TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_produk) REFERENCES m_produk(id_produk)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: t_login_attempts
CREATE TABLE IF NOT EXISTS t_login_attempts (
    id_attempt INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    username VARCHAR(50) NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_time (ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (password: admin123)
-- Temporary: using plain text, will be hashed on first login
-- Or you can run: php database/generate_password.php to get proper hash
INSERT INTO m_user (username, password, role) VALUES 
('admin', 'admin123', 'Admin'),
('kasir1', 'kasir123', 'Kasir');

-- Insert sample products
INSERT INTO m_produk (nama_produk, harga_jual, stok) VALUES 
('Indomie Goreng', 3500.00, 100),
('Aqua 600ml', 3000.00, 50),
('Teh Botol Sosro', 4000.00, 75),
('Mie Sedaap Goreng', 3500.00, 80),
('Coca Cola 330ml', 5000.00, 60),
('Chitato Rasa Sapi Panggang', 12000.00, 40),
('Oreo Vanilla', 8500.00, 35),
('Susu Ultra Milk 250ml', 6000.00, 45),
('Beng Beng', 2500.00, 120),
('Silverqueen Chunky Bar', 15000.00, 25),
('Kopi Kapal Api', 2000.00, 150),
('Gula Pasir 1kg', 15000.00, 30),
('Minyak Goreng Bimoli 1L', 18000.00, 20),
('Telur Ayam 1kg', 28000.00, 15),
('Beras Premium 5kg', 65000.00, 10);

-- Insert initial stock log
INSERT INTO t_log_stok (id_produk, Jumlah, Tipe, Keterangan) VALUES 
(1, 100, 'Masuk', 'Stok Awal'),
(2, 50, 'Masuk', 'Stok Awal'),
(3, 75, 'Masuk', 'Stok Awal'),
(4, 80, 'Masuk', 'Stok Awal'),
(5, 60, 'Masuk', 'Stok Awal'),
(6, 40, 'Masuk', 'Stok Awal'),
(7, 35, 'Masuk', 'Stok Awal'),
(8, 45, 'Masuk', 'Stok Awal'),
(9, 120, 'Masuk', 'Stok Awal'),
(10, 25, 'Masuk', 'Stok Awal'),
(11, 150, 'Masuk', 'Stok Awal'),
(12, 30, 'Masuk', 'Stok Awal'),
(13, 20, 'Masuk', 'Stok Awal'),
(14, 15, 'Masuk', 'Stok Awal'),
(15, 10, 'Masuk', 'Stok Awal');
