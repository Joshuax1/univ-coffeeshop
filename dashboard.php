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
        <?php if ($current_role === 'pembeli'): ?>
            <p class="subtle" style="margin-top:-6px;">Selamat datang di toko kami</p>
        <?php else: ?>
            <p class="subtle" style="margin-top:-6px;">Ringkasan operasional hari ini</p>
        <?php endif; ?>
        
        <?php if ($current_role === 'admin'): ?>
            <h2 style="margin: 10px 0 8px; font-size:1.1em; color:#6c757d; font-weight:normal;">Menu Manajemen</h2>
            <ul class="menu-list">
                <li><a href="product_management.php">Data Produk</a></li>
                <li><a href="raw_material_management.php">Data Bahan Baku</a></li> 
                <li><a href="supplier_management.php">Data Supplier</a></li>
                <li><a href="customer_management.php">Data Pelanggan</a></li> 
                <li><a href="user_management.php">Pengguna & Hak Akses</a></li>
                <li><a href="reports.php">Laporan</a></li>
            </ul>
        <?php endif; ?>
        
        <?php if ($current_role !== 'pembeli'): ?>
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
                <p style="text-align:left; font-size:0.9em; color:#6c757d; margin-bottom:10px;">Frame</p>
                <svg width="100%" height="120" viewBox="0 0 300 120" style="background:#f9fafb; border-radius:6px;">
                  <!-- Grid lines -->
                  <line x1="30" y1="100" x2="280" y2="100" stroke="#e5e7eb" stroke-width="1"/>
                  <!-- Chart line -->
                  <polyline points="30,80 60,60 90,70 120,40 150,50 180,30 210,45 240,35 270,50" fill="none" stroke="#8b5cf6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <!-- X-axis labels -->
                  <text x="30" y="115" font-size="10" fill="#9ca3af" text-anchor="middle">S</text>
                  <text x="60" y="115" font-size="10" fill="#9ca3af" text-anchor="middle">S</text>
                  <text x="90" y="115" font-size="10" fill="#9ca3af" text-anchor="middle">R</text>
                  <text x="120" y="115" font-size="10" fill="#9ca3af" text-anchor="middle">K</text>
                  <text x="150" y="115" font-size="10" fill="#9ca3af" text-anchor="middle">J</text>
                  <text x="180" y="115" font-size="10" fill="#9ca3af" text-anchor="middle">S</text>
                  <text x="210" y="115" font-size="10" fill="#9ca3af" text-anchor="middle">M</text>
                </svg>
            </div>
        <?php endif; ?>
        
        <h2 style="margin: 10px 0 8px; font-size:1.1em; color:#6c757d; font-weight:normal;">Akses Cepat</h2>
        <ul class="menu-list">
            <?php if ($current_role === 'kasir' || $current_role === 'admin'): ?>
                <li><a href="cashier_transaction.php">Transaksi Kasir</a></li>
                <li><a href="transaction_monitoring.php">Monitoring Transaksi</a></li>
            <?php endif; ?>
            <?php if ($current_role === 'pembeli'): ?>
                <li><a href="product_catalog.php">Lihat Katalog Produk</a></li>
                <li><a href="order_history.php">Riwayat Pesanan</a></li>
            <?php endif; ?>
        </ul>
    </div>
</body>
</html>