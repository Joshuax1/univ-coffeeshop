<?php
require 'auth_check.php';
require 'db_connect.php';

// Hanya admin dan kasir yang bisa akses halaman ini
if ($current_role !== 'admin' && $current_role !== 'kasir') {
    exit("Akses ditolak. Halaman ini hanya untuk admin dan kasir.");
}

// Ambil ID transaksi dari URL
$transaction_id = (int)($_GET['id'] ?? 0);

if ($transaction_id <= 0) {
    header('Location: transaction_monitoring.php');
    exit;
}

// Ambil data transaksi
$stmt = $pdo->prepare('
    SELECT t.transaction_id, t.user_id, u.username, u.email, t.transaction_date, 
           t.total_amount, t.payment_method
    FROM transactions t
    LEFT JOIN users u ON t.user_id = u.user_id
    WHERE t.transaction_id = ?
');
$stmt->execute([$transaction_id]);
$transaction = $stmt->fetch();

if (!$transaction) {
    header('Location: transaction_monitoring.php');
    exit;
}

// Ambil detail transaksi
$stmt = $pdo->prepare('
    SELECT td.detail_id, td.product_id, p.product_name, td.quantity, 
           td.price_at_sale, (td.quantity * td.price_at_sale) as subtotal
    FROM transaction_details td
    LEFT JOIN products p ON td.product_id = p.product_id
    WHERE td.transaction_id = ?
    ORDER BY td.detail_id ASC
');
$stmt->execute([$transaction_id]);
$details = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Transaksi</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .detail-container {
            max-width: 700px;
            margin: 0 auto;
        }
        .detail-section {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .detail-section h3 {
            margin: 0 0 15px 0;
            color: #2f3e56;
            font-size: 1.1em;
            border-bottom: 2px solid #8b5cf6;
            padding-bottom: 10px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #6c757d;
        }
        .info-value {
            color: #2f3e56;
            text-align: right;
        }
        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .item-row:last-child {
            border-bottom: none;
        }
        .item-name {
            flex: 1;
            font-weight: bold;
            color: #2f3e56;
        }
        .item-qty {
            color: #6c757d;
            margin: 0 15px;
            min-width: 60px;
            text-align: center;
        }
        .item-price {
            color: #6c757d;
            margin: 0 15px;
            min-width: 100px;
            text-align: right;
        }
        .item-subtotal {
            font-weight: bold;
            color: #8b5cf6;
            min-width: 120px;
            text-align: right;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 1.05em;
        }
        .summary-row.total {
            font-size: 1.3em;
            font-weight: bold;
            color: #2f3e56;
            border-top: 2px solid #e5e7eb;
            padding-top: 15px;
            margin-top: 10px;
        }
        .payment-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.9em;
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
        .btn-back {
            display: inline-block;
            padding: 10px 20px;
            background-color: #d1d5db;
            color: #2f3e56;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            margin-top: 15px;
        }
        .btn-back:hover {
            background-color: #9ca3af;
        }
    </style>
</head>
<body class="dashboard-body">
    <div class="dashboard-wrapper">
        <div class="header-row">
            <div class="header">Detail Transaksi #<?= $transaction['transaction_id'] ?></div>
            <p><a href="transaction_monitoring.php" class="logout-link">Kembali</a></p>
        </div>

        <div class="detail-container">
            <!-- Informasi Transaksi -->
            <div class="detail-section">
                <h3>Informasi Transaksi</h3>
                <div class="info-row">
                    <span class="info-label">ID Transaksi</span>
                    <span class="info-value">#<?= $transaction['transaction_id'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tanggal & Waktu</span>
                    <span class="info-value"><?= date('d M Y H:i:s', strtotime($transaction['transaction_date'])) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Metode Pembayaran</span>
                    <span class="info-value">
                        <?php 
                        $payment_class = 'payment-cash';
                        $payment_text = $transaction['payment_method'];
                        if ($transaction['payment_method'] === 'card') {
                            $payment_class = 'payment-card';
                            $payment_text = '💳 Kartu';
                        } elseif ($transaction['payment_method'] === 'e-wallet') {
                            $payment_class = 'payment-ewallet';
                            $payment_text = '📱 E-Wallet';
                        } else {
                            $payment_text = '💵 Tunai';
                        }
                        ?>
                        <span class="payment-badge <?= $payment_class ?>"><?= $payment_text ?></span>
                    </span>
                </div>
            </div>

            <!-- Informasi Pembeli -->
            <div class="detail-section">
                <h3>Informasi Pembeli</h3>
                <div class="info-row">
                    <span class="info-label">Nama Pengguna</span>
                    <span class="info-value"><?= htmlspecialchars($transaction['username'] ?? 'Unknown') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value"><?= htmlspecialchars($transaction['email'] ?? '-') ?></span>
                </div>
            </div>

            <!-- Detail Produk -->
            <div class="detail-section">
                <h3>Detail Produk</h3>
                <div style="margin-bottom: 15px;">
                    <div class="item-row" style="font-weight: bold; color: #6c757d; border-bottom: 2px solid #e5e7eb;">
                        <div class="item-name">Produk</div>
                        <div class="item-qty">Qty</div>
                        <div class="item-price">Harga</div>
                        <div class="item-subtotal">Subtotal</div>
                    </div>
                    <?php foreach ($details as $detail): ?>
                        <div class="item-row">
                            <div class="item-name"><?= htmlspecialchars($detail['product_name'] ?? 'Unknown') ?></div>
                            <div class="item-qty"><?= $detail['quantity'] ?></div>
                            <div class="item-price">Rp <?= number_format($detail['price_at_sale'], 0, ',', '.') ?></div>
                            <div class="item-subtotal">Rp <?= number_format($detail['subtotal'], 0, ',', '.') ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>Rp <?= number_format($transaction['total_amount'], 0, ',', '.') ?></span>
                </div>
                <div class="summary-row">
                    <span>Pajak (0%)</span>
                    <span>Rp 0</span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span>Rp <?= number_format($transaction['total_amount'], 0, ',', '.') ?></span>
                </div>
            </div>

            <a href="transaction_monitoring.php" class="btn-back">← Kembali ke Monitoring</a>
        </div>
    </div>
</body>
</html>
