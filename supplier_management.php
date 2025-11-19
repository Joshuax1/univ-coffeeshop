<?php
require 'auth_check.php';
require 'db_connect.php';

if ($current_role !== 'admin') {
    exit("Akses ditolak. Anda tidak memiliki izin Admin.");
}

$message = $_GET['message'] ?? '';
$action = $_GET['action'] ?? 'read';
$id = $_GET['id'] ?? null;
$supplier = ['supplier_id' => '', 'supplier_name' => '', 'contact' => '', 'address' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplier_id = $_POST['supplier_id'] ?? null;
    $name = trim($_POST['supplier_name']);
    $contact = trim($_POST['contact'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($supplier_id) {
        $stmt = $pdo->prepare('UPDATE suppliers SET supplier_name = ?, contact = ?, address = ? WHERE supplier_id = ?');
        $ok = $stmt->execute([$name, $contact, $address, $supplier_id]);
        $message = $ok ? 'Supplier diperbarui.' : 'Gagal memperbarui.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO suppliers (supplier_name, contact, address) VALUES (?, ?, ?)');
        $ok = $stmt->execute([$name, $contact, $address]);
        $message = $ok ? 'Supplier ditambahkan.' : 'Gagal menambahkan.';
    }
    header('Location: supplier_management.php?message=' . urlencode($message));
    exit;
}

if ($action === 'delete' && $id) {
    try {
        $stmt = $pdo->prepare('DELETE FROM suppliers WHERE supplier_id = ?');
        $ok = $stmt->execute([$id]);
        $message = $ok ? 'Supplier dihapus.' : 'Gagal menghapus.';
    } catch (PDOException $e) {
        $message = 'Gagal menghapus. Data mungkin dipakai di relasi lain.';
    }
    header('Location: supplier_management.php?message=' . urlencode($message));
    exit;
}

if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare('SELECT * FROM suppliers WHERE supplier_id = ?');
    $stmt->execute([$id]);
    $supplier = $stmt->fetch() ?: $supplier;
}

$suppliers = $pdo->query('SELECT * FROM suppliers ORDER BY supplier_id DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Supplier</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard-body">
<div class="dashboard-wrapper">
    <div class="header-row">
        <div class="header">Data Supplier</div>
        <p><a href="dashboard.php" class="logout-link">Kembali</a></p>
    </div>
    <p style="color: blue;"><?php echo htmlspecialchars($message); ?></p>

    <div class="form-container">
        <h3><?php echo ($action === 'edit' ? 'Edit: ' . htmlspecialchars($supplier['supplier_name']) : 'Tambah Supplier'); ?></h3>
        <form method="POST">
            <input type="hidden" name="supplier_id" value="<?php echo htmlspecialchars($supplier['supplier_id']); ?>">

            <label for="supplier_name">Nama Supplier:</label>
            <input type="text" name="supplier_name" value="<?php echo htmlspecialchars($supplier['supplier_name']); ?>" required>

            <label for="contact">Kontak:</label>
            <input type="text" name="contact" value="<?php echo htmlspecialchars($supplier['contact']); ?>">

            <label for="address">Alamat:</label>
            <input type="text" name="address" value="<?php echo htmlspecialchars($supplier['address']); ?>">

            <button type="submit" class="form-button"><?php echo ($action === 'edit' ? 'Simpan Perubahan' : 'Tambahkan'); ?></button>
            <?php if ($action === 'edit'): ?>
                <a href="supplier_management.php" class="cancel-link">Batal</a>
            <?php endif; ?>
        </form>
    </div>

    <h3>Daftar Supplier</h3>
    <div class="product-grid">
        <?php if (!empty($suppliers)): ?>
            <?php foreach ($suppliers as $row): ?>
                <div class="product-card">
                    <div class="name"><?php echo htmlspecialchars($row['supplier_name']); ?></div>
                    <div class="details"><?php echo htmlspecialchars($row['contact'] ?: '-'); ?> | <?php echo htmlspecialchars($row['address'] ?: '-'); ?></div>
                    <div class="actions">
                        <a class="edit-btn" href="?action=edit&id=<?php echo $row['supplier_id']; ?>">Edit</a>
                        <a class="delete-btn" href="?action=delete&id=<?php echo $row['supplier_id']; ?>" onclick="return confirm('Hapus supplier ini?')">Hapus</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Belum ada data.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
