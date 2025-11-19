<?php
require 'auth_check.php';
require 'db_connect.php';

if ($current_role !== 'admin') {
    exit("Akses ditolak. Anda tidak memiliki izin Admin.");
}

$message = $_GET['message'] ?? '';
$action = $_GET['action'] ?? 'read';
$id = $_GET['id'] ?? null;
$user = ['user_id' => '', 'username' => '', 'email' => '', 'role' => 'kasir'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? null;
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'] ?? 'kasir';
    $password = $_POST['password'] ?? '';

    if ($user_id) {
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('UPDATE users SET username = ?, email = ?, role = ?, password = ? WHERE user_id = ?');
            $ok = $stmt->execute([$username, $email, $role, $hash, $user_id]);
        } else {
            $stmt = $pdo->prepare('UPDATE users SET username = ?, email = ?, role = ? WHERE user_id = ?');
            $ok = $stmt->execute([$username, $email, $role, $user_id]);
        }
        $message = $ok ? 'Pengguna diperbarui.' : 'Gagal memperbarui.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
        $ok = $stmt->execute([$username, $email, $hash, $role]);
        $message = $ok ? 'Pengguna ditambahkan.' : 'Gagal menambahkan.';
    }
    header('Location: user_management.php?message=' . urlencode($message));
    exit;
}

if ($action === 'delete' && $id) {
    // Opsional: cegah hapus diri sendiri
    if ((int)$id === (int)$current_user_id) {
        $message = 'Tidak dapat menghapus akun yang sedang digunakan.';
    } else {
        try {
            $stmt = $pdo->prepare('DELETE FROM users WHERE user_id = ?');
            $ok = $stmt->execute([$id]);
            $message = $ok ? 'Pengguna dihapus.' : 'Gagal menghapus.';
        } catch (PDOException $e) {
            $message = 'Gagal menghapus. Data mungkin dipakai di relasi lain.';
        }
    }
    header('Location: user_management.php?message=' . urlencode($message));
    exit;
}

if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare('SELECT user_id, username, email, role FROM users WHERE user_id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch() ?: $user;
}

$users = $pdo->query('SELECT user_id, username, email, role FROM users ORDER BY user_id DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengguna & Hak Akses</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard-body">
<div class="dashboard-wrapper">
    <div class="header-row">
        <div class="header">Pengguna & Hak Akses</div>
        <p><a href="dashboard.php" class="logout-link">Kembali</a></p>
    </div>
    <p style="color: blue;"><?php echo htmlspecialchars($message); ?></p>

    <div class="form-container">
        <h3><?php echo ($action === 'edit' ? 'Edit: ' . htmlspecialchars($user['username']) : 'Tambah Pengguna'); ?></h3>
        <form method="POST">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['user_id']); ?>">

            <label for="username">Nama Pengguna:</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

            <label for="email">Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <label for="role">Role:</label>
            <select name="role">
                <?php $roles = ['admin' => 'Admin', 'kasir' => 'Kasir', 'supplier' => 'Supplier']; ?>
                <?php foreach ($roles as $val => $label): ?>
                    <option value="<?php echo $val; ?>" <?php echo ($user['role'] === $val ? 'selected' : ''); ?>><?php echo $label; ?></option>
                <?php endforeach; ?>
            </select>
            <p style="font-size:0.9em; color:#6c757d; margin-top:5px;">Catatan: Role 'Pembeli' hanya dapat didaftar melalui halaman registrasi publik.</p>

            <label for="password">Kata Sandi <?php echo ($action === 'edit' ? '(isi jika ingin diubah)' : ''); ?>:</label>
            <input type="password" name="password" <?php echo ($action === 'edit' ? '' : 'required'); ?>>

            <button type="submit" class="form-button"><?php echo ($action === 'edit' ? 'Simpan Perubahan' : 'Tambahkan'); ?></button>
            <?php if ($action === 'edit'): ?>
                <a href="user_management.php" class="cancel-link">Batal</a>
            <?php endif; ?>
        </form>
    </div>

    <h3>Daftar Pengguna</h3>
    <div class="product-grid">
        <?php if (!empty($users)): ?>
            <?php foreach ($users as $row): ?>
                <div class="product-card">
                    <div class="name"><?php echo htmlspecialchars($row['username']); ?> (<?php echo htmlspecialchars(strtoupper($row['role'])); ?>)</div>
                    <div class="details"><?php echo htmlspecialchars($row['email']); ?></div>
                    <div class="actions">
                        <a class="edit-btn" href="?action=edit&id=<?php echo $row['user_id']; ?>">Edit</a>
                        <?php if ((int)$row['user_id'] !== (int)$current_user_id): ?>
                        <a class="delete-btn" href="?action=delete&id=<?php echo $row['user_id']; ?>" onclick="return confirm('Hapus pengguna ini?')">Hapus</a>
                        <?php endif; ?>
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
