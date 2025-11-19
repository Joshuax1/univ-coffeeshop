<?php
require 'auth_check.php';
require 'db_connect.php';

if ($current_role !== 'admin') {
    exit("Akses ditolak. Anda tidak memiliki izin Admin.");
}

$message = $_GET['message'] ?? '';
$action = $_GET['action'] ?? 'read';
$id = $_GET['id'] ?? null;
$product_data = ['product_name' => '', 'price' => '', 'category' => '', 'product_id' => ''];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['product_name']);
    $price = $_POST['price'];
    $category = trim($_POST['category']);
    $product_id = $_POST['product_id'] ?? null;

    if ($product_id) {
        $sql = "UPDATE products SET product_name = ?, price = ?, category = ? WHERE product_id = ?";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute([$name, $price, $category, $product_id]);
        $message = $success ? "Produk berhasil diperbarui." : "Gagal memperbarui produk.";
    } else {
        $sql = "INSERT INTO products (product_name, price, category) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute([$name, $price, $category]);
        $message = $success ? "Produk baru berhasil ditambahkan." : "Gagal menambahkan produk.";
    }
    header('Location: product_management.php?message=' . urlencode($message));
    exit;
}

if ($action === 'delete' && $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
        $success = $stmt->execute([$id]);
        $message = $success ? "Produk berhasil dihapus." : "Gagal menghapus produk.";
    } catch (PDOException $e) {
        $message = "Gagal menghapus. Data produk ini mungkin digunakan dalam transaksi lain.";
    }
    header('Location: product_management.php?message=' . urlencode($message));
    exit;
}

if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$id]);
    $product_data = $stmt->fetch();
    if (!$product_data) {
        header('Location: product_management.php?message=' . urlencode('Data tidak ditemukan.'));
        exit;
    }
}

$products = $pdo->query("SELECT * FROM products ORDER BY product_id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Produk</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body class="dashboard-body">
    <div class="header">Data Produk</div>
    <p style="color: blue;"><?= htmlspecialchars($message) ?></p>

    <div class="form-container">
        <h3><?= ($action === 'edit' ? 'Edit Produk: ' . htmlspecialchars($product_data['product_name']) : 'Tambah Produk Baru') ?></h3>
        <form method="POST">
            <input type="hidden" name="product_id" value="<?= htmlspecialchars($product_data['product_id']) ?>">
            
            <label for="product_name">Nama Produk:</label>
            <input type="text" name="product_name" value="<?= htmlspecialchars($product_data['product_name']) ?>" required>
            
            <label for="price">Harga Jual (Rp.):</label>
            <input type="number" step="1" name="price" value="<?= htmlspecialchars($product_data['price']) ?>" required>
            
            <label for="category">Kategori:</label>
            <input type="text" name="category" value="<?= htmlspecialchars($product_data['category']) ?>">

            <button type="submit" class="form-button"><?= ($action === 'edit' ? 'Simpan Perubahan' : 'Tambahkan Produk') ?></button>
            <?php if ($action === 'edit'): ?>
                <a href="product_management.php" class="cancel-link">Batal</a>
            <?php endif; ?>
        </form>
    </div>

    <h3>Daftar Produk</h3>
    <div class="product-grid">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="name"><?= htmlspecialchars($product['product_name']) ?></div>
                    <div class="details">Harga: Rp<?= number_format($product['price'], 0, ',', '.') ?> | Kategori: <?= htmlspecialchars($product['category'] ?? '-') ?></div>
                    <div class="actions">
                        <a href="?action=edit&id=<?= $product['product_id'] ?>" class="edit-btn">Edit</a>
                        <a href="?action=delete&id=<?= $product['product_id'] ?>" class="delete-btn" 
                            onclick="return confirm('Yakin ingin menghapus produk <?= htmlspecialchars($product['product_name']) ?>?')">Hapus</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Belum ada data produk.</p>
        <?php endif; ?>
    </div>
</body>
</html>