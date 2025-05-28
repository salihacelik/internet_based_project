<?php
session_start();

// Kullanıcı adını al (mesaj için)
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Kullanıcı';

// Tüm session verilerini temizle
$_SESSION = array();

// Session cookie'sini de sil
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Session'ı tamamen sonlandır
session_destroy();
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Çıkış Yapıldı - Kütüphane Sistemi</title>

    <!-- CSS dosyası -->
    <link rel="stylesheet" href="style.css">

    <style>
    .logout-container {
        max-width: 600px;
        margin: 50px auto;
        background-color: white;
        padding: 40px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .logout-icon {
        font-size: 60px;
        color: #27ae60;
        margin-bottom: 20px;
    }

    .countdown {
        font-size: 18px;
        color: #27ae60;
        font-weight: bold;
        margin: 20px 0;
    }
    </style>
</head>

<body>
    <!-- Üst Menü -->
    <div class="navbar">
        <div class="container">
            <h1>📚 Kütüphane Takip Sistemi</h1>
            <div>
                <a href="index.php">Ana Sayfa</a>
                <a href="login.php">Giriş Yap</a>
                <a href="register.php">Kayıt Ol</a>
            </div>
        </div>
    </div>

    <!-- Ana İçerik -->
    <div class="container">
        <div class="logout-container">
            <div class="logout-icon">
                ✅
            </div>

            <h2 style="color: #2c3e50; margin-bottom: 20px;">
                Başarıyla Çıkış Yapıldı!
            </h2>

            <p style="font-size: 18px; color: #666; margin-bottom: 20px;">
                Hoşça kal <strong><?php echo htmlspecialchars($username); ?></strong>!
            </p>

            <p style="color: #666; margin-bottom: 30px;">
                Kütüphane sisteminden güvenli bir şekilde çıkış yaptınız.
                Oturumunuz sonlandırıldı.
            </p>

            <div class="success-message" style="margin-bottom: 30px;">
                🔒 Tüm oturum verileriniz temizlendi.
            </div>

            <div class="countdown" id="countdown-text">
                <span id="countdown">5</span> saniye içinde ana sayfaya yönlendirileceksiniz...
            </div>

            <div style="margin-top: 30px;">
                <a href="index.php" class="btn btn-primary" style="margin-right: 10px;">
                    🏠 Ana Sayfaya Git
                </a>
                <a href="login.php" class="btn btn-secondary">
                    🔐 Tekrar Giriş Yap
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Kütüphane Takip Sistemi</p>
        </div>
    </div>

    <!-- Basit JavaScript -->
    <script>
    // Geri sayım
    var countdown = 5;
    var countdownElement = document.getElementById('countdown');

    // Her saniye çalışacak fonksiyon
    var timer = setInterval(function() {
        countdown--;
        countdownElement.textContent = countdown;

        // Sayaç 0'a ulaştığında ana sayfaya git
        if (countdown <= 0) {
            clearInterval(timer);
            window.location.href = 'index.php';
        }
    }, 1000);

    // Sayfa yüklendiğinde odağı yakala (klavye için)
    document.addEventListener('keydown', function(e) {
        // Enter veya Space tuşuna basıldığında ana sayfaya git
        if (e.key === 'Enter' || e.key === ' ') {
            clearInterval(timer);
            window.location.href = 'index.php';
        }
    });

    // Herhangi bir yere tıklandığında geri sayımı durdur
    document.addEventListener('click', function() {
        clearInterval(timer);
        document.getElementById('countdown-text').innerHTML = 'Geri sayım durduruldu. İstediğiniz zaman sayfaya gidebilirsiniz.';
    });
    </script>
</body>

</html>