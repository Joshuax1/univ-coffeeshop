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

// --- EKSEKUSI SEEDING ---

try {
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

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Kesalahan Umum: " . $e->getMessage() . "</p>";
}

echo "<hr><p>Proses Seeding Selesai. Password Admin: **{$password_admin}** | Kasir: **{$password_kasir}**</p>";
?>