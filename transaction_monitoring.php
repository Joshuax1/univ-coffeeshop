<?php
require 'auth_check.php';
require 'db_connect.php';

// Hanya admin dan kasir yang bisa akses halaman ini
if ($current_role !== 'admin' && $current_role !== 'kasir') {
    exit("Akses ditolak. Halaman ini hanya untuk admin dan kasir.");
}

// Filter berdasarkan tanggal jika ada
$date_filter = '';
$date_param = '';
if (!empty($_GET['date'])) {
    $date_filter = " AND DATE(t.transaction_date) = ?";
    $date_param = $_GET['date'];
}

// Ambil semua transaksi dengan detail
$query = '
    SELECT t.transaction_id, t.user_id, u.username, t.transaction_date, t.total_amount, 
           t.payment_method, COUNT(td.detail_id) as item_count
    FROM transactions t
    LEFT JOIN users u ON t.user_id = u.user_id
    LEFT JOIN transaction_details td ON t.transaction_id = td.transaction_id
    WHERE 1=1' . $date_filter . '
    GROUP BY t.transaction_id
    ORDER BY t.transaction_date DESC
';

$stmt = $pdo->prepare($query);
if (!empty($date_param)) {
    $stmt->execute([$date_param]);
} else {
    $stmt->execute();
}
$transactions = $stmt->fetchAll();

// Hitung statistik
$total_transactions = count($transactions);
$total_revenue = 0;
foreach ($transactions as $txn) {
    $total_revenue += $txn['total_amount'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Transaksi</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .monitoring-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .filter-section {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .filter-section input {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 0.95em;
        }
        .filter-section button {
            padding: 8px 16px;
            background-color: #8b5cf6;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .filter-section button:hover {
            background-color: #7c3aed;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        .stat-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        .stat-label {
            font-size: 0.9em;
            color: #6c757d;
            margin-bottom: 8px;
        }
        .stat-value {
            font-size: 1.8em;
            font-weight: bold;
            color: #8b5cf6;
        }
        .transaction-list {
            margin-top: 20px;
        }
        .transaction-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .transaction-info {
            flex: 1;
            min-width: 250px;
        }
        .transaction-id {
            font-weight: bold;
            color: #2f3e56;
            margin-bottom: 5px;
        }
        .transaction-user {
            font-size: 0.9em;
            color: #6c757d;
            margin-bottom: 3px;
        }
        .transaction-date {
            font-size: 0.85em;
            color: #9ca3af;
        }
        .transaction-details {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }
        .detail-item {
            text-align: right;
        }
        .detail-label {
            font-size: 0.85em;
            color: #6c757d;
        }
        .detail-value {
            font-weight: bold;
            color: #2f3e56;
            font-size: 1.05em;
        }
        .payment-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
        }
        .payment-cash {
            background-color: #d1fae5;
            color: #065f46;
        }
        .payment-card {
            background-color: #dbeafe;
            color: #0c4a6e;
        }
        .payment-ewallet {
            background-color: #fce7f3;
            color: #831843;
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
            background: white;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        .view-details-btn {
            padding: 6px 12px;
            background-color: #8b5cf6;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            text-decoration: none;
            display: inline-block;
        }
        .view-details-btn:hover {
            background-color: #7c3aed;
        }
    </style>
</head>
<body class="dashboard-body">
    <div class="dashboard-wrapper">
        <div class="header-row">
            <div class="header">Monitoring Transaksi</div>
            <p><a href="dashboard.php" class="logout-link">Kembali</a></p>
        </div>

        <div class="monitoring-header">
            <div class="filter-section">
                <form method="GET" style="display: flex; gap: 10px; align-items: center;">
                    <label for="date" style="margin: 0; font-weight: bold;">Filter Tanggal:</label>
                    <input type="date" id="date" name="date" value="<?= htmlspecialchars($_GET['date'] ?? '') ?>">
                    <button type="submit">Cari</button>
                    <?php if (!empty($_GET['date'])): ?>
                        <a href="transaction_monitoring.php" style="padding: 8px 16px; background-color: #d1d5db; color: #2f3e56; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; text-decoration: none; display: inline-block;">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Transaksi</div>
                <div class="stat-value"><?= $total_transactions ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Pendapatan</div>
                <div class="stat-value">Rp <?= number_format($total_revenue, 0, ',', '.') ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Rata-rata Transaksi</div>
                <div class="stat-value">Rp <?= $total_transactions > 0 ? number_format($total_revenue / $total_transactions, 0, ',', '.') : '0' ?></div>
            </div>
        </div>

        <div class="transaction-list">
            <?php if (!empty($transactions)): ?>
                <?php foreach ($transactions as $txn): ?>
                    <div class="transaction-card">
                        <div class="transaction-info">
                            <div class="transaction-id">Transaksi #<?= $txn['transaction_id'] ?></div>
                            <div class="transaction-user">👤 <?= htmlspecialchars($txn['username'] ?? 'Unknown') ?></div>
                            <div class="transaction-date">📅 <?= date('d M Y H:i:s', strtotime($txn['transaction_date'])) ?></div>
                        </div>
                        <div class="transaction-details">
                            <div class="detail-item">
                                <div class="detail-label">Item</div>
                                <div class="detail-value"><?= $txn['item_count'] ?> produk</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Metode</div>
                                <div>
                                    <?php 
                                    $payment_class = 'payment-cash';
                                    $payment_text = $txn['payment_method'];
                                    if ($txn['payment_method'] === 'card') {
                                        $payment_class = 'payment-card';
                                        $payment_text = '💳 Kartu';
                                    } elseif ($txn['payment_method'] === 'e-wallet') {
                                        $payment_class = 'payment-ewallet';
                                        $payment_text = '📱 E-Wallet';
                                    } else {
                                        $payment_text = '💵 Tunai';
                                    }
                                    ?>
                                    <span class="payment-badge <?= $payment_class ?>"><?= $payment_text ?></span>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Total</div>
                                <div class="detail-value">Rp <?= number_format($txn['total_amount'], 0, ',', '.') ?></div>
                            </div>
                            <a href="transaction_details.php?id=<?= $txn['transaction_id'] ?>" class="view-details-btn">Lihat Detail</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <p style="font-size: 1.1em; margin-bottom: 10px;">📭 Tidak ada transaksi</p>
                    <p>Belum ada transaksi yang tercatat untuk periode ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
