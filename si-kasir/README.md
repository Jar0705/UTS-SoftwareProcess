# SI-KASIR - Sistem Informasi Kasir Terintegrasi

Sistem Informasi Kasir untuk Toko Swalayan "Maju Jaya"

## 🚀 Quick Start

### Menggunakan Docker (Recommended)

**PENTING: Jika database masih kosong atau search tidak jalan, restart dengan database baru!**

**Cara Termudah - Double-click:**
```
restart-fresh.bat
```

**Atau via Terminal:**
```bash
cd si-kasir
docker-compose down -v
docker-compose up -d
```

**Tunggu 30-60 detik** sampai container selesai dibuat.

**Akses Aplikasi:**
- Web App: http://localhost:8080
- phpMyAdmin: http://localhost:8081

**Login:**
- Admin: `admin` / `admin123`
- Kasir: `kasir1` / `kasir123`

**Database sudah berisi 15 produk sample siap pakai!**

---

## Fitur Utama

### 🎨 **Dashboard Profesional**
- ✅ Sidebar collapsible dengan animasi smooth
- ✅ Warna soft & profesional (abu-abu gelap)
- ✅ Real-time statistics cards
- ✅ Notification bell untuk stok kritis
- ✅ User avatar dengan role badge
- ✅ Quick actions & recent transactions

### 1. **Modul Manajemen Pengguna (Autentikasi)**
- ✅ **CREATE**: Pendaftaran user baru oleh Admin
- ✅ **READ**: Proses Login dengan verifikasi password hash
- ✅ **UPDATE**: Perubahan password otomatis saat login pertama
- ✅ **DELETE**: Menghapus akun kasir (dengan validasi)
- ✅ Session Management dengan role-based access
- ✅ Validasi: Username/Password wajib diisi, bruteforce prevention

### 2. **Modul Master Produk (Inventory Control)**
- ✅ **CREATE**: Menambah barang baru + auto-create log stok awal
- ✅ **READ**: Menampilkan daftar stok dengan filter stok kritis (< 5)
- ✅ **UPDATE**: Mengubah harga jual dan stock opname dengan logging
- ✅ **DELETE**: Restricted delete (tidak bisa hapus jika ada di transaksi)
- ✅ Validasi: Harga & stok tidak boleh negatif, nama produk wajib diisi
- ✅ Audit Trail: Setiap perubahan stok tercatat di t_log_stok

### 3. **Modul Transaksi Penjualan**
- ✅ Multi-item transaction dengan keranjang belanja
- ✅ Automatic stock reduction saat transaksi
- ✅ Database Transaction (Atomicity) dengan rollback
- ✅ Validasi: Stock availability, uang bayar cukup
- ✅ Generate nomor nota otomatis (PJN + tanggal + urutan)
- ✅ Digital receipt dengan print function
- ✅ Logging mutasi stok keluar otomatis

### 4. **Modul Laporan & Analisis**
- ✅ **Laporan Penjualan Harian**: Filter by tanggal, summary cards
- ✅ **Best Seller**: Top 10 produk berdasarkan total qty terjual
- ✅ **Laporan Mutasi Stok**: Riwayat masuk/keluar dengan filter
- ✅ Detail transaksi per nota
- ✅ INNER JOIN untuk relasi data

## Validasi Lengkap

| Fitur | Jenis Validasi | Pesan Kesalahan |
|-------|----------------|-----------------|
| Login | Empty Field | "Username dan Password wajib diisi!" |
| Login | Wrong Credentials | "Username atau Password salah!" |
| Input Produk | Tipe Data | "Harga harus berupa angka positif!" |
| Input Produk | Empty Field | "Data produk tidak lengkap, semua kolom wajib diisi!" |
| Input Produk | Negative Value | "Harga dan Stok tidak boleh bernilai negatif" |
| Transaksi | Stock Availability | "Stok [Nama Barang] tidak mencukupi untuk transaksi ini." |
| Transaksi | Financial Logic | "Uang bayar kurang dari total tagihan." |
| Delete Produk | Restricted Delete | "Produk tidak dapat dihapus karena sudah pernah ada dalam transaksi!" |

## Teknologi

- PHP 8.2
- MySQL 8.0
- Apache Web Server
- Docker & Docker Compose

---

## Struktur Database

- `m_user` - Master data pengguna
- `m_produk` - Master data produk
- `t_penjualan` - Header transaksi penjualan
- `t_penjualan_detail` - Detail item transaksi
- `t_log_stok` - Log mutasi stok

---

## Struktur Folder

```
si-kasir/
├── config/              # Konfigurasi database
├── includes/            # File include (session, header, footer)
├── modules/             # Modul aplikasi
│   ├── auth/           # Autentikasi (login, logout)
│   ├── produk/         # Master produk
│   ├── transaksi/      # Transaksi penjualan
│   └── laporan/        # Laporan & analisis
├── assets/              # Asset statis
│   ├── css/            # Stylesheet
│   └── js/             # JavaScript
├── database/            # SQL scripts
├── Dockerfile           # Docker configuration
├── docker-compose.yml   # Docker Compose configuration
└── index.php           # Dashboard utama
```

---

## 📝 Notes

- Untuk reset database: `docker-compose down -v && docker-compose up -d`
- Semua fitur sudah sesuai dengan dokumen spesifikasi
- Database sudah berisi 15 produk sample untuk demo

---

## Author

Muhammad Maulana Rachman

## License

Educational Purpose Only
