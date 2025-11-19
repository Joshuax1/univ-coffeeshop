<?php
require 'auth_check.php';
require 'db_connect.php';

if ($current_role !== 'admin') {
    exit("Akses ditolak. Anda tidak memiliki izin Admin.");
}

$message = $_GET['message'] ?? '';
$action = $_GET['action'] ?? 'read';
$id = $_GET['id'] ?? null;
$material = ['material_id' => '', 'material_name' => '', 'stock' => '', 'unit' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $material_id = $_POST['material_id'] ?? null;
    $name = trim($_POST['material_name']);
    $stock = (int)($_POST['stock'] ?? 0);
    $unit = trim($_POST['unit'] ?? '');

    if ($material_id) {
        $stmt = $pdo->prepare('UPDATE raw_materials SET material_name = ?, stock = ?, unit = ? WHERE material_id = ?');
        $ok = $stmt->execute([$name, $stock, $unit, $material_id]);
        $message = $ok ? 'Bahan baku diperbarui.' : 'Gagal memperbarui.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO raw_materials (material_name, stock, unit) VALUES (?, ?, ?)');
        $ok = $stmt->execute([$name, $stock, $unit]);
        $message = $ok ? 'Bahan baku ditambahkan.' : 'Gagal menambahkan.';
    }
    header('Location: raw_material_management.php?message=' . urlencode($message));
    exit;
}

if ($action === 'delete' && $id) {
    try {
        $stmt = $pdo->prepare('DELETE FROM raw_materials WHERE material_id = ?');
        $ok = $stmt->execute([$id]);
        $message = $ok ? 'Bahan baku dihapus.' : 'Gagal menghapus.';
    } catch (PDOException $e) {
        $message = 'Gagal menghapus. Data mungkin dipakai di transaksi lain.';
    }
    header('Location: raw_material_management.php?message=' . urlencode($message));
    exit;
}

if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare('SELECT * FROM raw_materials WHERE material_id = ?');
    $stmt->execute([$id]);
    $material = $stmt->fetch() ?: $material;
}

$materials = $pdo->query('SELECT * FROM raw_materials ORDER BY material_id DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Bahan Baku</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard-body">
<div class="dashboard-wrapper">
    <div class="header-row">
        <div class="header">Data bahan baku</div>
        <p><a href="dashboard.php" class="logout-link">Kembali</a></p>
    </div>
    <input class="search-input" type="text" placeholder="Cari bahan baku" oninput="filterList(this.value)">
    <div class="segmented">
        <div class="segment active">Sergerti</div>
        <div class="segment">Kategori</div>
    </div>
    <p style="color: blue;"><?php echo htmlspecialchars($message); ?></p>

    <div class="form-container">
        <h3><?php echo ($action === 'edit' ? 'Edit: ' . htmlspecialchars($material['material_name']) : 'Tambah Bahan Baku'); ?></h3>
        <form method="POST">
            <input type="hidden" name="material_id" value="<?php echo htmlspecialchars($material['material_id']); ?>">

            <label for="material_name">Nama:</label>
            <input type="text" name="material_name" value="<?php echo htmlspecialchars($material['material_name']); ?>" required>

            <label for="stock">Stok:</label>
            <input type="number" name="stock" step="1" value="<?php echo htmlspecialchars($material['stock']); ?>" required>

            <label for="unit">Satuan:</label>
            <input type="text" name="unit" value="<?php echo htmlspecialchars($material['unit']); ?>" required>

            <button type="submit" class="form-button"><?php echo ($action === 'edit' ? 'Simpan Perubahan' : 'Tambahkan'); ?></button>
            <?php if ($action === 'edit'): ?>
                <a href="raw_material_management.php" class="cancel-link">Batal</a>
            <?php endif; ?>
        </form>
    </div>

    <h3>Daftar Bahan Baku</h3>
    <div id="materials-list" class="product-grid">
        <?php if (!empty($materials)): ?>
            <?php foreach ($materials as $row): ?>
                <div class="product-card">
                    <div class="name">
                        <?php
                          $stock = (int)($row['stock'] ?? 0);
                          $dotClass = $stock <= 10 ? 'dot-red' : ($stock <= 30 ? 'dot-yellow' : 'dot-green');
                        ?>
                        <span class="dot <?php echo $dotClass; ?>"></span>
                        <?php echo htmlspecialchars($row['material_name']); ?>
                    </div>
                    <div class="details">Stok: <?php echo number_format((float)$row['stock'], 0, ',', '.'); ?> <?php echo htmlspecialchars($row['unit']); ?></div>
                    <div class="actions">
                        <a class="edit-btn" href="?action=edit&id=<?php echo $row['material_id']; ?>">Edit</a>
                        <a class="delete-btn" href="?action=delete&id=<?php echo $row['material_id']; ?>" onclick="return confirm('Hapus bahan baku ini?')">Hapus</a>
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
function filterList(q){
  q = (q||'').toLowerCase();
  document.querySelectorAll('#materials-list .product-card').forEach(function(el){
    var name = el.querySelector('.name').innerText.toLowerCase();
    el.style.display = name.indexOf(q) !== -1 ? '' : 'none';
  });
}
</script>
