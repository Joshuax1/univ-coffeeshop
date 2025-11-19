<?php
require 'auth_check.php';
require 'db_connect.php';

// Hanya pembeli yang bisa akses halaman ini
if ($current_role !== 'pembeli') {
    exit("Akses ditolak. Halaman ini hanya untuk pembeli.");
}

// Inisialisasi keranjang jika belum ada
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: product_catalog.php');
    exit;
}

$error = '';
$success = false;

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = trim($_POST['payment_method'] ?? 'cash');
    
    if (!in_array($payment_method, ['cash', 'card', 'e-wallet'])) {
        $error = "Metode pembayaran tidak valid.";
    } else {
        try {
            // Hitung total
            $product_ids = array_keys($_SESSION['cart']);
            $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
            $stmt = $pdo->prepare("SELECT product_id, price FROM products WHERE product_id IN ($placeholders)");
            $stmt->execute($product_ids);
            $products_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $products_data = [];
            foreach ($products_list as $p) {
                $products_data[$p['product_id']] = $p['price'];
            }
            
            $total_amount = 0;
            foreach ($_SESSION['cart'] as $pid => $qty) {
                if (isset($products_data[$pid])) {
                    $total_amount += $products_data[$pid] * $qty;
                }
            }
            
            // Insert transaksi
            $stmt = $pdo->prepare("INSERT INTO transactions (user_id, transaction_date, total_amount, payment_method) VALUES (?, NOW(), ?, ?)");
            $stmt->execute([$current_user_id, $total_amount, $payment_method]);
            $transaction_id = $pdo->lastInsertId();
            
            // Insert detail transaksi
            $stmt = $pdo->prepare("INSERT INTO transaction_details (transaction_id, product_id, quantity, price_at_sale) VALUES (?, ?, ?, ?)");
            foreach ($_SESSION['cart'] as $pid => $qty) {
                if (isset($products_data[$pid])) {
                    $stmt->execute([$transaction_id, $pid, $qty, $products_data[$pid]]);
                }
            }
            
            // Clear keranjang
            unset($_SESSION['cart']);
            $success = true;
            
        } catch (PDOException $e) {
            $error = "Gagal memproses pesanan: " . $e->getMessage();
        }
    }
}

// Ambil data keranjang jika belum checkout
$cart_items = [];
$cart_total = 0;
if (!$success && !empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $stmt = $pdo->prepare("SELECT product_id, product_name, price FROM products WHERE product_id IN ($placeholders)");
    $stmt->execute($product_ids);
    $products_data = $stmt->fetchAll();
    
    foreach ($products_data as $product) {
        $pid = $product['product_id'];
        $qty = $_SESSION['cart'][$pid];
        $subtotal = $product['price'] * $qty;
        $cart_items[] = [
            'id' => $pid,
            'name' => $product['product_name'],
            'price' => $product['price'],
            'quantity' => $qty,
            'subtotal' => $subtotal
        ];
        $cart_total += $subtotal;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .checkout-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .checkout-section {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .checkout-section h3 {
            margin: 0 0 15px 0;
            color: #2f3e56;
            font-size: 1.1em;
            border-bottom: 2px solid #8b5cf6;
            padding-bottom: 10px;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .item-info {
            flex: 1;
        }
        .item-name {
            font-weight: bold;
            color: #2f3e56;
            margin-bottom: 5px;
        }
        .item-qty {
            font-size: 0.9em;
            color: #6c757d;
        }
        .item-price {
            text-align: right;
            font-weight: bold;
            color: #8b5cf6;
        }
        .order-summary {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 0.95em;
        }
        .summary-row.total {
            font-size: 1.2em;
            font-weight: bold;
            color: #2f3e56;
            margin-top: 10px;
        }
        .payment-method {
            margin-bottom: 15px;
        }
        .payment-method label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #2f3e56;
        }
        .payment-options {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .payment-option {
            flex: 1;
            min-width: 150px;
        }
        .payment-option input[type="radio"] {
            margin-right: 8px;
        }
        .payment-option label {
            display: inline;
            margin: 0;
            font-weight: normal;
            cursor: pointer;
        }
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .btn-checkout {
            flex: 1;
            background-color: #8b5cf6;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1em;
            transition: background-color 0.2s;
        }
        .btn-checkout:hover {
            background-color: #7c3aed;
        }
        .btn-back {
            flex: 1;
            background-color: #d1d5db;
            color: #2f3e56;
            border: none;
            padding: 12px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1em;
            text-decoration: none;
            text-align: center;
            transition: background-color 0.2s;
        }
        .btn-back:hover {
            background-color: #9ca3af;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
        }
        .success-message h2 {
            margin: 0 0 10px 0;
            color: #155724;
        }
        .success-message p {
            margin: 5px 0;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="dashboard-body">
    <div class="dashboard-wrapper">
        <div class="header-row">
            <div class="header">Checkout</div>
            <p><a href="product_catalog.php" class="logout-link">Kembali</a></p>
        </div>

        <?php if ($success): ?>
            <div class="success-message">
                <h2>✅ Pesanan Berhasil Dibuat!</h2>
                <p>Terima kasih telah berbelanja di toko kami.</p>
                <p style="margin-top: 15px;">
                    <a href="order_history.php" class="btn-checkout" style="display: inline-block; padding: 10px 20px; text-decoration: none;">Lihat Riwayat Pesanan</a>
                </p>
            </div>
        <?php else: ?>
            <?php if (!empty($error)): ?>
                <div class="error-message">❌ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="checkout-container">
                <div class="checkout-section">
                    <h3>Ringkasan Pesanan</h3>
                    <?php foreach ($cart_items as $item): ?>
                        <div class="order-item">
                            <div class="item-info">
                                <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                                <div class="item-qty"><?= $item['quantity'] ?> x Rp <?= number_format($item['price'], 0, ',', '.') ?></div>
                            </div>
                            <div class="item-price">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></div>
                        </div>
                    <?php endforeach; ?>
                    <div class="order-summary">
                        <div class="summary-row total">
                            <span>Total:</span>
                            <span>Rp <?= number_format($cart_total, 0, ',', '.') ?></span>
                        </div>
                    </div>
                </div>

                <form method="POST" class="checkout-section">
                    <div class="payment-method">
                        <label>Pilih Metode Pembayaran:</label>
                        <div class="payment-options">
                            <div class="payment-option">
                                <input type="radio" id="cash" name="payment_method" value="cash" checked>
                                <label for="cash">💵 Tunai</label>
                            </div>
                            <div class="payment-option">
                                <input type="radio" id="card" name="payment_method" value="card">
                                <label for="card">💳 Kartu</label>
                            </div>
                            <div class="payment-option">
                                <input type="radio" id="ewallet" name="payment_method" value="e-wallet">
                                <label for="ewallet">📱 E-Wallet</label>
                            </div>
                        </div>
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn-checkout">Konfirmasi Pesanan</button>
                        <a href="product_catalog.php" class="btn-back">Kembali</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
