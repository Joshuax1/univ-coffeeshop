<?php
require 'auth_check.php';
require 'db_connect.php';

// Hanya pembeli yang bisa akses halaman ini
if ($current_role !== 'pembeli') {
    exit("Akses ditolak. Halaman ini hanya untuk pembeli.");
}

// Ambil transaksi pembeli saat ini
$stmt = $pdo->prepare('
    SELECT t.transaction_id, t.transaction_date, t.total_amount, t.payment_method, 
           COUNT(td.detail_id) as item_count
    FROM transactions t
    LEFT JOIN transaction_details td ON t.transaction_id = td.transaction_id
    WHERE t.user_id = ?
    GROUP BY t.transaction_id
    ORDER BY t.transaction_date DESC
');
$stmt->execute([$current_user_id]);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .order-list {
            margin-top: 15px;
        }
        .order-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 12px;
            background: white;
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .order-id {
            font-weight: bold;
            color: #2f3e56;
        }
        .order-date {
            font-size: 0.9em;
            color: #6c757d;
        }
        .order-amount {
            font-size: 1.1em;
            color: #8b5cf6;
            font-weight: bold;
        }
        .order-details {
            font-size: 0.9em;
            color: #6c757d;
            margin-top: 8px;
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
    </style>
</head>
<body class="dashboard-body">
    <div class="dashboard-wrapper">
        <div class="header-row">
            <div class="header">Riwayat Pesanan</div>
            <p><a href="dashboard.php" class="logout-link">Kembali</a></p>
        </div>

        <div class="order-list">
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <div class="order-id">Pesanan #<?= $order['transaction_id'] ?></div>
                                <div class="order-date"><?= date('d M Y H:i', strtotime($order['transaction_date'])) ?></div>
                            </div>
                            <div class="order-amount">Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></div>
                        </div>
                        <div class="order-details">
                            <strong><?= $order['item_count'] ?></strong> item(s) • Metode: <strong><?= htmlspecialchars($order['payment_method'] ?? 'N/A') ?></strong>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <p style="font-size: 1.1em; margin-bottom: 10px;">📭 Belum ada pesanan</p>
                    <p>Mulai berbelanja sekarang untuk melihat riwayat pesanan Anda.</p>
                    <a href="product_catalog.php" class="main-button" style="display: inline-block; margin-top: 15px; text-decoration: none; padding: 10px 20px; background-color: #8b5cf6; color: white; border-radius: 4px;">Lihat Katalog</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
