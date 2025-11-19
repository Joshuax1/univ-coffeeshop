<?php
require 'auth_check.php';
require 'db_connect.php';

if ($current_role !== 'admin') {
    exit("Akses ditolak. Anda tidak memiliki izin Admin.");
}

$message = $_GET['message'] ?? '';
$action = $_GET['action'] ?? 'read';
$id = $_GET['id'] ?? null;
$customer = ['customer_id' => '', 'customer_name' => '', 'phone' => '', 'address' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'] ?? null;
    $name = trim($_POST['customer_name']);
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($customer_id) {
        $stmt = $pdo->prepare('UPDATE customers SET customer_name = ?, phone = ?, address = ? WHERE customer_id = ?');
        $ok = $stmt->execute([$name, $phone, $address, $customer_id]);
        $message = $ok ? 'Pelanggan diperbarui.' : 'Gagal memperbarui.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO customers (customer_name, phone, address) VALUES (?, ?, ?)');
        $ok = $stmt->execute([$name, $phone, $address]);
        $message = $ok ? 'Pelanggan ditambahkan.' : 'Gagal menambahkan.';
    }
    header('Location: customer_management.php?message=' . urlencode($message));
    exit;
}

if ($action === 'delete' && $id) {
    try {
        $stmt = $pdo->prepare('DELETE FROM customers WHERE customer_id = ?');
        $ok = $stmt->execute([$id]);
        $message = $ok ? 'Pelanggan dihapus.' : 'Gagal menghapus.';
    } catch (PDOException $e) {
        $message = 'Gagal menghapus. Data mungkin dipakai di relasi lain.';
    }
    header('Location: customer_management.php?message=' . urlencode($message));
    exit;
}

if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare('SELECT * FROM customers WHERE customer_id = ?');
    $stmt->execute([$id]);
    $customer = $stmt->fetch() ?: $customer;
}

$customers = $pdo->query('SELECT * FROM customers ORDER BY customer_id DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pelanggan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard-body">
<div class="dashboard-wrapper">
    <div class="header-row">
        <div class="header">Data Pelanggan</div>
        <p><a href="dashboard.php" class="logout-link">Kembali</a></p>
    </div>
    <input class="search-input" type="text" placeholder="Cari pelanggan" oninput="filterCust(this.value)">
    <p style="color: blue;"><?php echo htmlspecialchars($message); ?></p>

    <div class="form-container">
        <h3><?php echo ($action === 'edit' ? 'Edit: ' . htmlspecialchars($customer['customer_name']) : 'Tambah Pelanggan'); ?></h3>
        <form method="POST">
            <input type="hidden" name="customer_id" value="<?php echo htmlspecialchars($customer['customer_id']); ?>">

            <label for="customer_name">Nama:</label>
            <input type="text" name="customer_name" value="<?php echo htmlspecialchars($customer['customer_name']); ?>" required>

            <label for="phone">Telepon:</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($customer['phone']); ?>">

            <label for="address">Alamat:</label>
            <input type="text" name="address" value="<?php echo htmlspecialchars($customer['address']); ?>">

            <button type="submit" class="form-button"><?php echo ($action === 'edit' ? 'Simpan Perubahan' : 'Tambahkan'); ?></button>
            <?php if ($action === 'edit'): ?>
                <a href="customer_management.php" class="cancel-link">Batal</a>
            <?php endif; ?>
        </form>
    </div>

    <h3>Daftar Pelanggan</h3>
    <div id="customers-list" class="product-grid">
        <?php if (!empty($customers)): ?>
            <?php foreach ($customers as $row): ?>
                <div class="product-card">
                    <div class="name"><?php echo htmlspecialchars($row['customer_name']); ?></div>
                    <div class="details"><?php echo htmlspecialchars($row['phone'] ?: '-'); ?> | <?php echo htmlspecialchars($row['address'] ?: '-'); ?></div>
                    <div class="actions">
                        <a class="edit-btn" href="?action=edit&id=<?php echo $row['customer_id']; ?>">Edit</a>
                        <a class="delete-btn" href="?action=delete&id=<?php echo $row['customer_id']; ?>" onclick="return confirm('Hapus pelanggan ini?')">Hapus</a>
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
<script>
function filterCust(q){
  q = (q||'').toLowerCase();
  document.querySelectorAll('#customers-list .product-card').forEach(function(el){
    var name = el.querySelector('.name').innerText.toLowerCase();
    el.style.display = name.indexOf(q) !== -1 ? '' : 'none';
  });
}
</script>
