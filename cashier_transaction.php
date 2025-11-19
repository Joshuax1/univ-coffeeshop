<?php
require 'auth_check.php'; 
require 'db_connect.php'; 

if ($current_role !== 'admin' && $current_role !== 'kasir') {
    exit("Akses ditolak.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Kasir</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body class="dashboard-body">
    <div class="dashboard-wrapper">
        <div class="header">Transaksi Kasir</div>
        
        <div class="form-container">
            <h3>Input Penjualan Cepat</h3>
            <form method="POST" action="process_transaction.php"> 
                
                <label for="product_name">Nama Produk:</label>
                <input type="text" name="product_name" placeholder="Pilih Produk"> 
                
                <label for="quantity">Jumlah:</label>
                <input type="number" name="quantity" min="1" value="1" required>
                
                <label for="payment_method">Metode Pembayaran:</label>
                <select name="payment_method" style="width: 100%; padding: 10px; margin-bottom: 15px;">
                    <option value="cash">Tunai (Cash)</option>
                    <option value="card">Kartu</option>
                    <option value="e-wallet">E-Wallet</option>
                </select>

                <button type="button" class="form-button" style="background-color: #4a5568; margin-bottom: 15px;">Tambahkan Item</button>
                
                <h3>Rincian Total</h3>
                <p>Total Akhir: Rp 0.00</p>
                <p>Uang Diterima: <input type="number" placeholder="Masukkan Jumlah Uang" style="margin: 5px 0;"></p>
                
                <button type="submit" class="main-button" style="background-color: #28a745;">Selesaikan Transaksi</button>
                <a href="dashboard.php" class="cancel-link" style="text-align: center; display: block; margin-top: 10px;">Kembali</a>
            </form>
        </div>
        
    </div>
</body>
</html>