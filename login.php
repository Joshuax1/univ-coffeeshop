<?php
include 'db_connect.php'; 

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Email dan Kata Sandi wajib diisi.";
    } else {
        $stmt = $conn->prepare("SELECT id, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                
                header('Location: dashboard.php');
                exit;

            } else {
                $error = "Email atau Kata Sandi salah.";
            }
        } else {
            $error = "Email atau Kata Sandi salah.";
        }

        $stmt->close();
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
            
            <button type="submit" class="main-button" style="width:100%; padding:14px 20px; margin:8px 0; border-radius:4px; cursor:pointer; font-size:1em; text-align:center; box-sizing:border-box; background-color:#4a5568; color:white; border:none;">Lanjutkan</button>
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