@echo off
echo ========================================
echo SI-KASIR - Fresh Restart (Reset Semua)
echo ========================================
echo.
echo Menghentikan container...
docker-compose down -v
echo.
echo Memulai container dengan database baru...
docker-compose up -d
echo.
echo ========================================
echo SELESAI!
echo ========================================
echo.
echo Database FRESH - Semua transaksi dihapus!
echo - 15 produk sample (stok penuh)
echo - 2 user (admin, kasir1)
echo - 0 transaksi (bersih untuk demo)
echo.
echo Akses aplikasi di: http://localhost:8080
echo Login: admin / admin123
echo.
echo SIAP UNTUK DEMO BESOK!
echo.
pause
