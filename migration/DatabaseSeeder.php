<?php
// migration/DatabaseSeeder.php

// 1. Panggil koneksi database
require '../db_connect.php'; 

// PENTING: Aktifkan Error Reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>POS COFFEESHOP DATA SEEDER</h1>";

// --- Data yang akan di-seed ---

// Kata sandi default untuk Admin dan Kasir (harus di-hash)
$password_admin = 'admin12345'; // GANTI DENGAN PASSWORD YANG AMAN
$password_kasir = 'kasir123';
$hashed_admin_pass = password_hash($password_admin, PASSWORD_BCRYPT);
$hashed_kasir_pass = password_hash($password_kasir, PASSWORD_BCRYPT);

$hashed_pembeli_pass = password_hash('pembeli123', PASSWORD_BCRYPT);

$users_to_seed = [
    [
        'username' => 'superadmin', 
        'email' => 'superadmin@pos.com', 
        'password' => $hashed_admin_pass, 
        'role' => 'admin'
    ],
    [
        'username' => 'kasir_utama', 
        'email' => 'kasir@pos.com', 
        'password' => $hashed_kasir_pass, 
        'role' => 'kasir'
    ],
    [
        'username' => 'pembeli1', 
        'email' => 'pembeli1@pos.com', 
        'password' => $hashed_pembeli_pass, 
        'role' => 'pembeli'
    ],
    [
        'username' => 'pembeli2', 
        'email' => 'pembeli2@pos.com', 
        'password' => $hashed_pembeli_pass, 
        'role' => 'pembeli'
    ],
];

$products_to_seed = [
    ['product_name' => 'Espresso', 'price' => 15000.00, 'category' => 'Coffee'],
    ['product_name' => 'Cappuccino', 'price' => 25000.00, 'category' => 'Coffee'],
    ['product_name' => 'Croissant', 'price' => 18000.00, 'category' => 'Food'],
];

$materials_to_seed = [
    ['material_name' => 'Biji Kopi Arabika', 'stock' => 50, 'unit' => 'kg'],
    ['material_name' => 'Susu Cair', 'stock' => 100, 'unit' => 'liter'],
    ['material_name' => 'Gula', 'stock' => 75, 'unit' => 'kg'],
];

// Supplier dan Pelanggan (baru)
$suppliers_to_seed = [
    ['supplier_name' => 'Sigma Corp', 'contact_person' => 'Budi', 'phone' => '081234567890', 'address' => 'Jl. Melati 02'],
    ['supplier_name' => 'Dela Bakery', 'contact_person' => 'Dela', 'phone' => '087654321098', 'address' => 'Jln Mawar 09'],
    ['supplier_name' => 'Westfood', 'contact_person' => 'Wes', 'phone' => '085612345678', 'address' => 'Jl. Perbatasan 10'],
];

$customers_to_seed = [
    ['customer_name' => 'Angela', 'phone' => '081234567888', 'address' => ''],
    ['customer_name' => 'Tari', 'phone' => '081256789900', 'address' => ''],
    ['customer_name' => 'Joshua', 'phone' => '081234567990', 'address' => ''],
    ['customer_name' => 'Uma', 'phone' => '081234567700', 'address' => ''],
];

// --- Fungsi Penyuntikan Data ---

function seed_table($pdo, $table_name, $data, $columns) {
    if (empty($data)) return 0;

    $placeholders = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
    $sql = "INSERT INTO `{$table_name}` (" . implode(', ', $columns) . ") VALUES {$placeholders}";
    $count = 0;

    try {
        $stmt = $pdo->prepare($sql);
        
        foreach ($data as $row) {
            $values = array_values($row);
            $stmt->execute($values);
            $count++;
        }
        echo "<p style='color: green;'>✅ Berhasil menyuntikkan {$count} data ke tabel `{$table_name}`.</p>";
        return $count;

    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Gagal menyuntikkan data ke `{$table_name}`: " . $e->getMessage() . "</p>";
        // PENTING: Untuk user, kita abaikan error DUMP/data lama, karena data harus UNIQUE
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
             echo "<p style='color: orange;'>⚠️ Data sudah ada (Unique key violation), seeding diabaikan.</p>";
        }
        return 0;
    }
}

// --- Pastikan Skema Tersedia ---
function ensure_schema(PDO $pdo, bool $withConstraints = false) {
    // Users
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        user_id INT(11) NOT NULL AUTO_INCREMENT,
        username VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin','kasir','supplier','pembeli') NOT NULL DEFAULT 'pembeli',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id),
        UNIQUE KEY username (username),
        UNIQUE KEY email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Products
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        product_id INT(11) NOT NULL AUTO_INCREMENT,
        product_name VARCHAR(100) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        category VARCHAR(50) DEFAULT NULL,
        PRIMARY KEY (product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Raw Materials
    $pdo->exec("CREATE TABLE IF NOT EXISTS raw_materials (
        material_id INT(11) NOT NULL AUTO_INCREMENT,
        material_name VARCHAR(100) NOT NULL,
        stock INT(11) NOT NULL DEFAULT 0,
        unit VARCHAR(20) NOT NULL,
        PRIMARY KEY (material_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Suppliers (schema from dump)
    $pdo->exec("CREATE TABLE IF NOT EXISTS suppliers (
        supplier_id INT(11) NOT NULL AUTO_INCREMENT,
        supplier_name VARCHAR(100) NOT NULL,
        contact_person VARCHAR(100) DEFAULT NULL,
        phone VARCHAR(20) DEFAULT NULL,
        address TEXT DEFAULT NULL,
        PRIMARY KEY (supplier_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Customers
    $pdo->exec("CREATE TABLE IF NOT EXISTS customers (
        customer_id INT(11) NOT NULL AUTO_INCREMENT,
        customer_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) DEFAULT NULL,
        address TEXT DEFAULT NULL,
        PRIMARY KEY (customer_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Transactions
    $pdo->exec("CREATE TABLE IF NOT EXISTS transactions (
        transaction_id INT(11) NOT NULL AUTO_INCREMENT,
        user_id INT(11) DEFAULT NULL,
        transaction_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        total_amount DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50) DEFAULT NULL,
        PRIMARY KEY (transaction_id),
        KEY user_id (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Transaction Details
    $pdo->exec("CREATE TABLE IF NOT EXISTS transaction_details (
        detail_id INT(11) NOT NULL AUTO_INCREMENT,
        transaction_id INT(11) NOT NULL,
        product_id INT(11) NOT NULL,
        quantity INT(11) NOT NULL,
        price_at_sale DECIMAL(10,2) NOT NULL,
        PRIMARY KEY (detail_id),
        KEY transaction_id (transaction_id),
        KEY product_id (product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Purchase Orders
    $pdo->exec("CREATE TABLE IF NOT EXISTS purchase_orders (
        po_id INT(11) NOT NULL AUTO_INCREMENT,
        supplier_id INT(11) NOT NULL,
        user_id INT(11) DEFAULT NULL,
        po_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        total_cost DECIMAL(10,2) NOT NULL,
        PRIMARY KEY (po_id),
        KEY supplier_id (supplier_id),
        KEY user_id (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Purchase Details
    $pdo->exec("CREATE TABLE IF NOT EXISTS purchase_details (
        detail_id INT(11) NOT NULL AUTO_INCREMENT,
        po_id INT(11) NOT NULL,
        material_id INT(11) NOT NULL,
        quantity INT(11) NOT NULL,
        unit_price DECIMAL(10,2) NOT NULL,
        PRIMARY KEY (detail_id),
        KEY po_id (po_id),
        KEY material_id (material_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    if ($withConstraints) {
        // Add foreign keys
        $pdo->exec("ALTER TABLE transactions
            ADD CONSTRAINT transactions_ibfk_1 FOREIGN KEY (user_id) REFERENCES users(user_id)");

        $pdo->exec("ALTER TABLE transaction_details
            ADD CONSTRAINT transaction_details_ibfk_1 FOREIGN KEY (transaction_id) REFERENCES transactions(transaction_id),
            ADD CONSTRAINT transaction_details_ibfk_2 FOREIGN KEY (product_id) REFERENCES products(product_id)");

        $pdo->exec("ALTER TABLE purchase_orders
            ADD CONSTRAINT purchase_orders_ibfk_1 FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id),
            ADD CONSTRAINT purchase_orders_ibfk_2 FOREIGN KEY (user_id) REFERENCES users(user_id)");

        $pdo->exec("ALTER TABLE purchase_details
            ADD CONSTRAINT purchase_details_ibfk_1 FOREIGN KEY (po_id) REFERENCES purchase_orders(po_id),
            ADD CONSTRAINT purchase_details_ibfk_2 FOREIGN KEY (material_id) REFERENCES raw_materials(material_id)");
    }
}

// --- EKSEKUSI SEEDING (ALWAYS FRESH) ---

try {
    // Drop dengan urutan aman (anak -> induk)
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    $pdo->exec("DROP TABLE IF EXISTS purchase_details");
    $pdo->exec("DROP TABLE IF EXISTS transaction_details");
    $pdo->exec("DROP TABLE IF EXISTS purchase_orders");
    $pdo->exec("DROP TABLE IF EXISTS transactions");
    $pdo->exec("DROP TABLE IF EXISTS customers");
    $pdo->exec("DROP TABLE IF EXISTS suppliers");
    $pdo->exec("DROP TABLE IF EXISTS raw_materials");
    $pdo->exec("DROP TABLE IF EXISTS products");
    $pdo->exec("DROP TABLE IF EXISTS users");
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");

    // Buat ulang tabel dengan FK
    ensure_schema($pdo, true);

    // 1. Seeding Users
    $user_columns = ['username', 'email', 'password', 'role'];
    echo "<h2>Seeding Users</h2>";
    seed_table($pdo, 'users', $users_to_seed, $user_columns);

    // 2. Seeding Products
    $product_columns = ['product_name', 'price', 'category'];
    echo "<h2>Seeding Products</h2>";
    seed_table($pdo, 'products', $products_to_seed, $product_columns);

    // 3. Seeding Raw Materials
    $material_columns = ['material_name', 'stock', 'unit'];
    echo "<h2>Seeding Raw Materials</h2>";
    seed_table($pdo, 'raw_materials', $materials_to_seed, $material_columns);

    // 4. Seeding Suppliers (baru)
    $supplier_columns = ['supplier_name', 'contact_person', 'phone', 'address'];
    echo "<h2>Seeding Suppliers</h2>";
    seed_table($pdo, 'suppliers', $suppliers_to_seed, $supplier_columns);

    // 5. Seeding Customers (baru)
    $customer_columns = ['customer_name', 'phone', 'address'];
    echo "<h2>Seeding Customers</h2>";
    seed_table($pdo, 'customers', $customers_to_seed, $customer_columns);

    // 6. Seeding Transactions (dummy) - asumsi ID pengguna=1,2; produk=1..3
    $transactions_to_seed = [
        ['user_id' => 1, 'transaction_date' => date('Y-m-d H:i:s'), 'total_amount' => 45000.00, 'payment_method' => 'cash'],
        ['user_id' => 2, 'transaction_date' => date('Y-m-d H:i:s', strtotime('-1 day')), 'total_amount' => 25000.00, 'payment_method' => 'e-wallet'],
    ];
    $txn_columns = ['user_id','transaction_date','total_amount','payment_method'];
    echo "<h2>Seeding Transactions</h2>";
    seed_table($pdo, 'transactions', $transactions_to_seed, $txn_columns);

    // Ambil ID transaksi yang baru dibuat
    $txns = $pdo->query("SELECT transaction_id FROM transactions ORDER BY transaction_id ASC")->fetchAll();
    $t1 = $txns[0]['transaction_id'] ?? 1;
    $t2 = $txns[1]['transaction_id'] ?? ($t1 + 1);

    $transaction_details_to_seed = [
        ['transaction_id' => $t1, 'product_id' => 1, 'quantity' => 1, 'price_at_sale' => 15000.00],
        ['transaction_id' => $t1, 'product_id' => 2, 'quantity' => 1, 'price_at_sale' => 25000.00],
        ['transaction_id' => $t2, 'product_id' => 3, 'quantity' => 1, 'price_at_sale' => 18000.00],
    ];
    $txn_detail_columns = ['transaction_id','product_id','quantity','price_at_sale'];
    echo "<h2>Seeding Transaction Details</h2>";
    seed_table($pdo, 'transaction_details', $transaction_details_to_seed, $txn_detail_columns);

    // 7. Seeding Purchase Orders
    $purchase_orders_to_seed = [
        ['supplier_id' => 1, 'user_id' => 1, 'po_date' => date('Y-m-d H:i:s'), 'total_cost' => 200000.00],
    ];
    $po_columns = ['supplier_id','user_id','po_date','total_cost'];
    echo "<h2>Seeding Purchase Orders</h2>";
    seed_table($pdo, 'purchase_orders', $purchase_orders_to_seed, $po_columns);

    $po_id = (int)$pdo->query("SELECT po_id FROM purchase_orders ORDER BY po_id DESC LIMIT 1")->fetchColumn();
    $purchase_details_to_seed = [
        ['po_id' => $po_id, 'material_id' => 1, 'quantity' => 10, 'unit_price' => 15000.00],
        ['po_id' => $po_id, 'material_id' => 2, 'quantity' => 20, 'unit_price' => 12000.00],
    ];
    $po_detail_columns = ['po_id','material_id','quantity','unit_price'];
    echo "<h2>Seeding Purchase Details</h2>";
    seed_table($pdo, 'purchase_details', $purchase_details_to_seed, $po_detail_columns);

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Kesalahan Umum: " . $e->getMessage() . "</p>";
}

echo "<hr><p>Proses Seeding Selesai. Password Admin: **{$password_admin}** | Kasir: **{$password_kasir}**</p>";
?>