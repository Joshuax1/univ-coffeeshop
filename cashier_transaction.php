<?php
require 'auth_check.php'; 
require 'db_connect.php'; 

if ($current_role !== 'admin' && $current_role !== 'kasir') {
    exit("Akses ditolak.");
}

$success_message = '';
$error_message = '';

// Ambil pesan dari session jika ada
if (isset($_SESSION['transaction_success'])) {
    $success_message = $_SESSION['transaction_success'];
    unset($_SESSION['transaction_success']);
}

if (isset($_SESSION['transaction_error'])) {
    $error_message = $_SESSION['transaction_error'];
    unset($_SESSION['transaction_error']);
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
        <div class="header-row">
            <div class="header">Transaksi Kasir</div>
            <p><a href="dashboard.php" class="logout-link">Kembali</a></p>
        </div>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success" style="background-color:#d4edda; color:#155724; border:1px solid #c3e6cb; padding:12px; border-radius:4px; margin-bottom:15px;">
                ✅ <?= htmlspecialchars($success_message) ?>
            </div>
            <script>
                setTimeout(function() {
                    document.querySelector('.alert-success').style.display = 'none';
                }, 3000);
            </script>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error" style="background-color:#f8d7da; color:#721c24; border:1px solid #f5c6cb; padding:12px; border-radius:4px; margin-bottom:15px;">
                ❌ <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
            <h3>Input Penjualan Cepat</h3>
            <form method="POST" action="process_transaction.php"> 
                
                <label for="product_name">Pilih produk:</label>
                <input type="text" name="product_name" placeholder="Daftar Produk" class="search-input"> 
                
                <label for="quantity">Jumlah:</label>
                <input type="number" name="quantity" min="1" value="1" required>
                
                <label for="payment_method">Metode Pembayaran:</label>
                <select name="payment_method" style="width: 100%; padding: 10px; margin-bottom: 15px;">
                    <option value="cash">Tunai (Cash)</option>
                    <option value="card">Kartu</option>
                    <option value="e-wallet">E-Wallet</option>
                </select>

                <button type="button" class="btn btn-primary" style="width:100%; margin-bottom: 12px;">Tambahkan Item</button>
                
                <h3>Rincian Total</h3>
                <p>Total Akhir: Rp 0.00</p>
                <p>Uang Diterima: <input type="number" placeholder="Masukkan Jumlah Uang" style="margin: 5px 0; width:100%; padding:10px;"></p>
                
                <button type="submit" class="btn btn-primary" style="width:100%;">Selanjutnya</button>
                <a href="dashboard.php" class="btn btn-secondary" style="width:100%; display:block; text-align:center; margin-top:8px; text-decoration:none;">Kembali</a>
            </form>
        </div>
        
    </div>
</body>
</html>