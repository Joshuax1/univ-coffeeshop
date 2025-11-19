<?php
require 'auth_check.php';
require 'db_connect.php';

// Hanya pembeli yang bisa akses halaman ini
if ($current_role !== 'pembeli') {
    exit("Akses ditolak. Halaman ini hanya untuk pembeli.");
}

// Inisialisasi keranjang di session jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle tambah ke keranjang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $product_id = (int)$_POST['product_id'];
        $quantity = (int)($_POST['quantity'] ?? 1);
        
        if ($quantity > 0) {
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id] += $quantity;
            } else {
                $_SESSION['cart'][$product_id] = $quantity;
            }
        }
    } elseif ($_POST['action'] === 'remove') {
        $product_id = (int)$_POST['product_id'];
        unset($_SESSION['cart'][$product_id]);
    } elseif ($_POST['action'] === 'update') {
        $product_id = (int)$_POST['product_id'];
        $quantity = (int)($_POST['quantity'] ?? 1);
        
        if ($quantity > 0) {
            $_SESSION['cart'][$product_id] = $quantity;
        } else {
            unset($_SESSION['cart'][$product_id]);
        }
    } elseif ($_POST['action'] === 'checkout') {
        if (!empty($_SESSION['cart'])) {
            // Redirect ke checkout page
            header('Location: checkout.php');
            exit;
        }
    }
}

// Ambil semua produk
$products = $pdo->query('SELECT product_id, product_name, price, category FROM products ORDER BY product_name')->fetchAll();

// Hitung total keranjang
$cart_total = 0;
$cart_items = [];
if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $stmt = $pdo->prepare("SELECT product_id, product_name, price FROM products WHERE product_id IN ($placeholders)");
    $stmt->execute($product_ids);
    $cart_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($cart_products as $product) {
        $pid = $product['product_id'];
        $qty = $_SESSION['cart'][$pid];
        $price = $product['price'];
        $cart_items[$pid] = [
            'name' => $product['product_name'],
            'quantity' => $qty,
            'price' => $price,
            'subtotal' => $qty * $price
        ];
        $cart_total += $qty * $price;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Produk</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .catalog-container {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 20px;
            margin-top: 15px;
        }
        .catalog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 15px;
        }
        .product-item {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            background: white;
            text-align: center;
            transition: box-shadow 0.2s;
        }
        .product-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .product-item .name {
            font-weight: bold;
            margin-bottom: 8px;
            color: #2f3e56;
            font-size: 0.95em;
        }
        .product-item .category {
            font-size: 0.8em;
            color: #6c757d;
            margin-bottom: 10px;
        }
        .product-item .price {
            font-size: 1.1em;
            color: #8b5cf6;
            font-weight: bold;
            margin-bottom: 12px;
        }
        .product-item form {
            display: flex;
            gap: 5px;
            align-items: center;
        }
        .product-item input[type="number"] {
            width: 50px;
            padding: 5px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 0.9em;
        }
        .product-item .btn-add {
            background-color: #8b5cf6;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85em;
            flex: 1;
            transition: background-color 0.2s;
        }
        .product-item .btn-add:hover {
            background-color: #7c3aed;
        }
        .search-box {
            margin-bottom: 20px;
        }
        .search-box input {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 1em;
        }
        .cart-sidebar {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        .cart-sidebar h3 {
            margin: 0 0 15px 0;
            color: #2f3e56;
            font-size: 1.1em;
        }
        .cart-item {
            border-bottom: 1px solid #e5e7eb;
            padding: 10px 0;
            margin-bottom: 10px;
            font-size: 0.9em;
        }
        .cart-item:last-child {
            border-bottom: none;
        }
        .cart-item-name {
            font-weight: bold;
            color: #2f3e56;
            margin-bottom: 5px;
        }
        .cart-item-qty {
            color: #6c757d;
            margin-bottom: 5px;
        }
        .cart-item-price {
            color: #8b5cf6;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .cart-item-remove {
            background: #f3f4f6;
            color: #dc2626;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8em;
        }
        .cart-item-remove:hover {
            background: #e5e7eb;
        }
        .cart-total {
            border-top: 2px solid #e5e7eb;
            padding-top: 12px;
            margin-top: 12px;
            font-weight: bold;
            color: #2f3e56;
            margin-bottom: 15px;
        }
        .cart-empty {
            text-align: center;
            color: #6c757d;
            padding: 20px 0;
        }
        .btn-checkout {
            width: 100%;
            background-color: #8b5cf6;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.2s;
        }
        .btn-checkout:hover {
            background-color: #7c3aed;
        }
        .btn-checkout:disabled {
            background-color: #d1d5db;
            cursor: not-allowed;
        }
        @media (max-width: 768px) {
            .catalog-container {
                grid-template-columns: 1fr;
            }
            .cart-sidebar {
                position: static;
            }
        }
    </style>
</head>
<body class="dashboard-body">
    <div class="dashboard-wrapper">
        <div class="header-row">
            <div class="header">Katalog Produk</div>
            <p><a href="dashboard.php" class="logout-link">Kembali</a></p>
        </div>

        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Cari produk..." onkeyup="filterProducts()">
        </div>

        <div class="catalog-container">
            <div class="catalog-grid" id="productGrid">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <div class="product-item" data-name="<?= strtolower(htmlspecialchars($product['product_name'])) ?>" data-category="<?= strtolower(htmlspecialchars($product['category'] ?? '')) ?>">
                            <div class="name"><?= htmlspecialchars($product['product_name']) ?></div>
                            <div class="category"><?= htmlspecialchars($product['category'] ?? 'Umum') ?></div>
                            <div class="price">Rp <?= number_format($product['price'], 0, ',', '.') ?></div>
                            <form method="POST" style="margin-top: 10px;">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                                <input type="number" name="quantity" value="1" min="1" max="100">
                                <button type="submit" class="btn-add">Tambah</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="grid-column: 1/-1; text-align: center; color: #6c757d;">Belum ada produk tersedia.</p>
                <?php endif; ?>
            </div>

            <div class="cart-sidebar">
                <h3>🛒 Keranjang</h3>
                <?php if (empty($cart_items)): ?>
                    <div class="cart-empty">Keranjang kosong</div>
                <?php else: ?>
                    <?php foreach ($cart_items as $pid => $item): ?>
                        <div class="cart-item">
                            <div class="cart-item-name"><?= htmlspecialchars($item['name']) ?></div>
                            <div class="cart-item-qty">Qty: <?= $item['quantity'] ?></div>
                            <div class="cart-item-price">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></div>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?= $pid ?>">
                                <button type="submit" class="cart-item-remove">Hapus</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                    <div class="cart-total">Total: Rp <?= number_format($cart_total, 0, ',', '.') ?></div>
                    <form method="POST">
                        <input type="hidden" name="action" value="checkout">
                        <button type="submit" class="btn-checkout">Checkout</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function filterProducts() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            const productItems = document.querySelectorAll('.product-item');
            
            productItems.forEach(item => {
                const name = item.getAttribute('data-name');
                const category = item.getAttribute('data-category');
                
                if (name.includes(searchInput) || category.includes(searchInput)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
