<?php
require 'auth_check.php'; 

$laba_hari_ini = 5000000; 
$current_role = $_SESSION['role']; 

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard-body">
    
    <div class="dashboard-wrapper"> 
        
        <div class="header-row">
            <div class="header">Selamat Datang, <?= htmlspecialchars($current_username) ?></div>
            <p><a href="logout.php" class="logout-link">Keluar / Logout</a></p>
        </div>
        
        <?php if ($current_role === 'admin'): ?>
            <h2 style="margin-top: 0;">Menu Manajemen</h2>
            <ul class="menu-list">
                <li><a href="product_management.php">Data Produk</a></li>
                <li><a href="raw_material_management.php">Data Bahan Baku</a></li> 
                <li><a href="supplier_management.php">Data Supplier</a></li>
                <li><a href="customer_management.php">Data Pelanggan</a></li> 
                <li><a href="user_management.php">Pengguna & Hak Akses</a></li>
                <li><a href="reports.php">Laporan</a></li>
            </ul>
            <hr>
        <?php endif; ?>
        <div class="status-card">
            <h3>Laba Hari Ini</h3>
            <p>Rp <?= number_format($laba_hari_ini, 0, ',', '.') ?></p>
        </div>

        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Latte</div>
                <div class="value">Rp 1.000.000</div>
            </div>
            <div class="summary-item">
                <div class="label">Espresso</div>
                <div class="value">Rp 500.000</div>
            </div>
            <div class="summary-item">
                <div class="label">Total Penjualan</div>
                <div class="value">Rp 1.500.000</div>
            </div>
            <div class="summary-item">
                <div class="label">Produk Terjual</div>
                <div class="value">150 unit</div>
            </div>
        </div>
        
        <div class="frame-section">
            <p>Grafik Penjualan Bulanan (Frame)</p>
            <span style="font-size: 3em;">📈</span>
        </div>

        <h2>Akses Cepat</h2>
        <ul class="menu-list">
            <?php if ($current_role === 'kasir' || $current_role === 'admin'): ?>
                <li><a href="cashier_transaction.php">Transaksi Kasir</a></li> 
            <?php endif; ?>
        </ul>
        </div>
</body>
</html>