<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// session
session_start();
require 'db_connect.php'; 

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT user_id, username, password, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        if (password_verify($password, $user['password'])) { 
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
            
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Email atau Kata Sandi salah.";
        }
    } else {
        $error = "Email atau Kata Sandi salah.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Sistem</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
    <div class="container">
        <h2>Masuk</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Kata Sandi" required>
            
            <a href="forgot_password.php" class="link-text">Lupa Sandi?</a>
            
            <button type="submit" class="main-button">Lanjutkan</button>
        </form>
        
        <div class="divider">
            <span style="background: white; padding: 0 10px;">atau</span>
        </div>

        <a href="#" class="google-login">Login with Google</a>
        
        <div class="divider">
             Belum punya akun? <a href="register.php" style="color:#4a5568;">Daftar</a>
        </div>
    </div>
</body>
</html>