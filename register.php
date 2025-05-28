<?php

require_once 'config.php';


if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error_message = "";
$success_message = "";


if($_POST) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = trim($_POST['email']);
    

    if(empty($username) || empty($password) || empty($confirm_password) || empty($email)) {
        $error_message = "Tüm alanlar doldurulmalı!";
    }

    elseif(strlen($password) < 6) {
        $error_message = "Şifre en az 6 karakter olmalı!";
    }

    elseif($password !== $confirm_password) {
        $error_message = "Şifreler eşleşmiyor!";
    }

    elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Geçerli bir email adresi giriniz!";
    }

    elseif(strlen($username) < 3) {
        $error_message = "Kullanıcı adı en az 3 karakter olmalı!";
    }
    else {
        try {

            

            $stmt = $db->prepare("SELECT id FROM kullanicilar WHERE kullanici_adi = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if($stmt->fetch()) {
                $error_message = "Bu kullanıcı adı veya email adresi zaten kullanılıyor!";
            } else {

                $password_hash = sha1($password);
                $stmt = $db->prepare("INSERT INTO kullanicilar (kullanici_adi, sifre, email, admin_mi, kayit_tarihi) VALUES (?, ?, ?, 0, NOW())");
                
                if($stmt->execute([$username, $password_hash, $email])) {
                    $success_message = "Kayıt başarıyla tamamlandı! Şimdi giriş yapabilirsiniz.";

                    $_POST = array();
                } else {
                    $error_message = "Kayıt sırasında bir hata oluştu!";
                }
            }
            
        } catch(PDOException $e) {
            $error_message = "Veritabanı bağlantı hatası: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol - Kütüphane Sistemi</title>


    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="navbar">
        <div class="container">
            <h1>📚 Kütüphane Takip Sistemi</h1>
            <div>
                <a href="index.php">Ana Sayfa</a>
                <a href="login.php">Giriş Yap</a>
            </div>
        </div>
    </div>


    <div class="container">
        <div class="form-container">
            <h2 style="text-align: center; margin-bottom: 30px; color: #2c3e50;">
                📝 Kayıt Ol
            </h2>

            <?php if($error_message): ?>
            <div class="error-message">
                ❌ <?php echo htmlspecialchars($error_message); ?>
            </div>
            <?php endif; ?>

            <?php if($success_message): ?>
            <div class="success-message">
                ✅ <?php echo htmlspecialchars($success_message); ?>
            </div>
            <?php endif; ?>

            <form method="POST" id="registerForm">
                <div class="form-group">
                    <label for="username">Kullanıcı Adı:</label>
                    <input type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required minlength="3">
                    <small style="color: #666;">En az 3 karakter olmalı</small>
                </div>

                <div class="form-group">
                    <label for="email">Email Adresi:</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Şifre:</label>
                    <input type="password" id="password" name="password" required minlength="6">
                    <small style="color: #666;">En az 6 karakter olmalı</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Şifre Tekrar:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px;">
                        📝 Kayıt Ol
                    </button>
                </div>
            </form>

            <div style="text-align: center; margin-top: 20px;">
                <p>Zaten hesabınız var mı?
                    <a href="login.php" style="color: #3498db; text-decoration: none;">Giriş Yap</a>
                </p>
            </div>
        </div>
    </div>


    <div class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Kütüphane Takip Sistemi</p>
        </div>
    </div>


    <script>
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        var username = document.getElementById('username').value.trim();
        var email = document.getElementById('email').value.trim();
        var password = document.getElementById('password').value;
        var confirmPassword = document.getElementById('confirm_password').value;


        if (username.length < 3) {
            alert('Kullanıcı adı en az 3 karakter olmalı!');
            e.preventDefault();
            return false;
        }


        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('Geçerli bir email adresi giriniz!');
            e.preventDefault();
            return false;
        }


        if (password.length < 6) {
            alert('Şifre en az 6 karakter olmalı!');
            e.preventDefault();
            return false;
        }


        if (password !== confirmPassword) {
            alert('Şifreler eşleşmiyor!');
            e.preventDefault();
            return false;
        }

        return true;
    });


    document.getElementById('confirm_password').addEventListener('keyup', function() {
        var password = document.getElementById('password').value;
        var confirmPassword = this.value;

        if (confirmPassword !== '' && password !== confirmPassword) {
            this.style.borderColor = '#e74c3c';
        } else {
            this.style.borderColor = '#ddd';
        }
    });
    </script>
</body>

</html>