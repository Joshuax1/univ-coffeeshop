<?php
require 'auth_check.php';
require 'db_connect.php';

if ($current_role !== 'admin') {
    exit("Akses ditolak. Anda tidak memiliki izin Admin.");
}

function safeCount($pdo, $sql) {
    try {
        return (int)$pdo->query($sql)->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}

$cnt_users = safeCount($pdo, "SELECT COUNT(*) FROM users");
$cnt_products = safeCount($pdo, "SELECT COUNT(*) FROM products");
$cnt_materials = safeCount($pdo, "SELECT COUNT(*) FROM raw_materials");
$cnt_suppliers = safeCount($pdo, "SELECT COUNT(*) FROM suppliers");
$cnt_customers = safeCount($pdo, "SELECT COUNT(*) FROM customers");

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .report-table { width:100%; border-collapse: collapse; background:#fff; border-radius:8px; overflow:hidden; }
        .report-table th, .report-table td { padding:12px 14px; border-bottom:1px solid #eee; text-align: left; }
        .report-table thead th { background:#2f3e56; color:#fff; }
        .stat-grid { display:grid; grid-template-columns: repeat(2, 1fr); gap:10px; margin-bottom:20px; }
        .stat-card { background:#fff; padding:12px; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,.1); }
        .stat-card .label{ color:#6c757d; font-size:.9em; }
        .stat-card .value{ font-size:1.4em; font-weight:bold; }
    </style>
</head>
<body class="dashboard-body">
<div class="dashboard-wrapper">
    <div class="header-row">
        <div class="header">Laporan</div>
        <p><a href="dashboard.php" class="logout-link">Kembali</a></p>
    </div>

    <div class="stat-grid">
        <div class="stat-card"><div class="label">Total Produk</div><div class="value"><?php echo number_format($cnt_products,0,',','.'); ?></div></div>
        <div class="stat-card"><div class="label">Total Bahan Baku</div><div class="value"><?php echo number_format($cnt_materials,0,',','.'); ?></div></div>
        <div class="stat-card"><div class="label">Total Supplier</div><div class="value"><?php echo number_format($cnt_suppliers,0,',','.'); ?></div></div>
        <div class="stat-card"><div class="label">Total Pelanggan</div><div class="value"><?php echo number_format($cnt_customers,0,',','.'); ?></div></div>
    </div>

    <h3>Ringkasan Laporan</h3>
    <table class="report-table">
        <thead>
            <tr>
                <th>Jenis laporan</th>
                <th>Isi</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Laporan Penjualan</td>
                <td>Per hari/bulan, revenue dan laba. (Belum tersedia: menunggu implementasi transaksi)</td>
            </tr>
            <tr>
                <td>Laporan Pembelian</td>
                <td>Riwayat pembelian bahan baku. (Belum tersedia)</td>
            </tr>
            <tr>
                <td>Laporan Stok</td>
                <td>Stok awal, masuk, keluar dan akhir. (Tersedia daftar bahan baku; pergerakan stok belum tersedia)</td>
            </tr>
            <tr>
                <td>Laba Rugi</td>
                <td>Analisis keuntungan dan HPP. (Belum tersedia)</td>
            </tr>
        </tbody>
    </table>

    <p style="margin-top:10px; color:#6c757d;">Catatan: Modul transaksi dan pembelian diperlukan untuk menghasilkan laporan yang akurat.</p>
</div>
</body>
</html>
