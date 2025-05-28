<?php

require_once 'config.php';


if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error_message = "";


if($_POST) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    
    if(empty($username) || empty($password)) {
        $error_message = "Kullanıcı adı ve şifre boş bırakılamaz!";
    } else {
        try {
            
            $stmt = $db->prepare("SELECT id, kullanici_adi, sifre, admin_mi FROM kullanicilar WHERE kullanici_adi = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            
            if($user && sha1($password) === $user['sifre']) {
                // Session'ları ayarla
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['kullanici_adi'];
                $_SESSION['is_admin'] = $user['admin_mi'];
                
                
                header("Location: index.php");
                exit();
            } else {
                $error_message = "Kullanıcı adı veya şifre hatalı!";
            }
            
        } catch(PDOException $e) {
            $error_message = "Veritabanı bağlantı hatası!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - Kütüphane Sistemi</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="navbar">
        <div class="container">
            <h1>📚 Kütüphane Takip Sistemi</h1>
            <div>
                <a href="index.php">Ana Sayfa</a>
                <a href="register.php">Kayıt Ol</a>
            </div>
        </div>
    </div>


    <div class="container">
        <div class="form-container">
            <h2 style="text-align: center; margin-bottom: 30px; color: #2c3e50;">
                🔐 Giriş Yap
            </h2>

            <?php if($error_message): ?>
            <div class="error-message">
                ❌ <?php echo htmlspecialchars($error_message); ?>
            </div>
            <?php endif; ?>

            <form method="POST" id="loginForm">
                <div class="form-group">
                    <label for="username">Kullanıcı Adı:</label>
                    <input type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Şifre:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px;">
                        🚪 Giriş Yap
                    </button>
                </div>
            </form>

            <div style="text-align: center; margin-top: 20px;">
                <p>Hesabınız yok mu?
                    <a href="register.php" style="color: #3498db; text-decoration: none;">Kayıt Ol</a>
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
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        var username = document.getElementById('username').value.trim();
        var password = document.getElementById('password').value;

        if (username == '' || password == '') {
            alert('Kullanıcı adı ve şifre boş bırakılamaz!');
            e.preventDefault();
            return false;
        }

        if (username.length < 3) {
            alert('Kullanıcı adı en az 3 karakter olmalı!');
            e.preventDefault();
            return false;
        }
    });
    </script>
</body>

</html>