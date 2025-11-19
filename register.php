<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db_connect.php'; 

$message = '';
$error = ''; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password']; 
    
    $requested_role = $_POST['role'] ?? 'kasir';
    
    if (!in_array($requested_role, ['admin', 'kasir', 'supplier'])) {
        $requested_role = 'kasir'; 
    }

    if ($password !== $confirm_password) {
        $error = "Konfirmasi kata sandi tidak cocok.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt_check = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt_check->execute([$email]);
    
            if ($stmt_check->rowCount() > 0) {
                $error = "Email sudah terdaftar.";
            } else {
                $sql = "INSERT INTO users (email, username, password, role) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$email, $username, $hashed_password, $requested_role]); 
    
                $message = "Pendaftaran berhasil! Akun dibuat sebagai: " . strtoupper($requested_role);
            }
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan database: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Baru</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
    <div class="container">
        <h2>Daftar Akun Baru</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if (!empty($message)): ?>
            <div class="error" style="background-color:#d4edda; color:#155724; border-color:#c3e6cb;">
                <p style="text-align: center; font-size: 3em; margin-bottom: 0;">✅</p>
                <p style="text-align: center; font-weight: bold; margin-top: 5px;">Selamat Anda Berhasil Mendaftar</p>
            </div>
            <a href="login.php" class="main-button" style="display:block; text-align:center; text-decoration:none; margin-top:15px;">Masuk Sekarang</a>
        <?php endif; ?>

        <?php if (empty($message)): ?>
        <form method="POST" action="register.php">
            
            <label for="username">Nama Pengguna:</label>
            <input type="text" name="username" placeholder="Nama Pengguna" required>
            
            <label for="email">Email:</label>
            <input type="email" name="email" placeholder="Email" required>
            
            <label for="password">Kata Sandi:</label>
            <input type="password" name="password" placeholder="Buat Kata Sandi" required>
            
            <label for="confirm_password">Konfirmasi Kata Sandi:</label>
            <input type="password" name="confirm_password" placeholder="Ulangi Kata Sandi" required>
            
            <input type="hidden" name="role" value="kasir"> 

            <button type="submit" class="main-button">Selanjutnya</button>
        </form>
        
        <p class="divider">Sudah punya akun? <a href="login.php" style="color:#4a5568;">Masuk</a></p>
        <?php endif; ?>
    </div>
</body>
</html>