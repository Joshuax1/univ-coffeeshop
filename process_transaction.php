<?php
require 'auth_check.php';
require 'db_connect.php';

if ($current_role !== 'admin' && $current_role !== 'kasir') {
    exit("Akses ditolak.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cashier_transaction.php');
    exit;
}

try {
    $product_name = trim($_POST['product_name'] ?? '');
    $quantity = (int)($_POST['quantity'] ?? 1);
    $payment_method = trim($_POST['payment_method'] ?? 'cash');

    if (empty($product_name) || $quantity <= 0) {
        throw new Exception("Data produk atau jumlah tidak valid.");
    }

    // Cari produk berdasarkan nama
    $stmt = $pdo->prepare("SELECT product_id, price FROM products WHERE product_name = ? LIMIT 1");
    $stmt->execute([$product_name]);
    $product = $stmt->fetch();

    if (!$product) {
        throw new Exception("Produk tidak ditemukan.");
    }

    $product_id = $product['product_id'];
    $price = $product['price'];
    $total_amount = $price * $quantity;

    // Insert transaksi
    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, transaction_date, total_amount, payment_method) VALUES (?, NOW(), ?, ?)");
    $stmt->execute([$current_user_id, $total_amount, $payment_method]);
    $transaction_id = $pdo->lastInsertId();

    // Insert detail transaksi
    $stmt = $pdo->prepare("INSERT INTO transaction_details (transaction_id, product_id, quantity, price_at_sale) VALUES (?, ?, ?, ?)");
    $stmt->execute([$transaction_id, $product_id, $quantity, $price]);

    // Set pesan sukses di session
    $_SESSION['transaction_success'] = "Transaksi berhasil! Total: Rp " . number_format($total_amount, 0, ',', '.');
    
    // Redirect kembali ke transaksi tanpa query parameter
    header('Location: cashier_transaction.php');
    exit;

} catch (Exception $e) {
    // Set pesan error di session
    $_SESSION['transaction_error'] = $e->getMessage();
    
    // Redirect kembali ke transaksi tanpa query parameter
    header('Location: cashier_transaction.php');
    exit;
}
?>
