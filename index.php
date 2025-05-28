<?php
session_start();


$loggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kütüphane Takip Sistemi</title>


    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="navbar">
        <div class="container">
            <h1>📚 Kütüphane Takip Sistemi</h1>

            <?php if($loggedIn): ?>
            <div>
                <span>Hoş geldin, <?php echo $_SESSION['username']; ?>!</span>
                <a href="books.php">Kitaplar</a>
                <a href="my_books.php">Ödünç Aldıklarım</a>
                <?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                <a href="add_book.php">Kitap Ekle</a>
                <?php endif; ?>
                <a href="logout.php">Çıkış</a>
            </div>
            <?php endif; ?>
        </div>
    </div>


    <div class="container">
        <div class="main-content">

            <?php if(!$loggedIn): ?>

            <div class="hero">
                <h1>Kütüphane Takip Sistemi</h1>
                <p>Kitapları kolayca ödünç alın, takip edin ve iade edin!</p>

                <div>
                    <a href="login.php" class="btn btn-primary">Giriş Yap</a>
                    <a href="register.php" class="btn btn-secondary">Kayıt Ol</a>
                </div>
            </div>


            <div class="features">
                <div class="feature-card">
                    <h3>📖 Kitap Ödünç Alma</h3>
                    <p>İstediğiniz kitabı kolayca ödünç alın ve okuma keyfinize başlayın.</p>
                </div>

                <div class="feature-card">
                    <h3>📋 Kitap Takibi</h3>
                    <p>Ödünç aldığınız kitapları takip edin ve zamanında iade edin.</p>
                </div>

                <div class="feature-card">
                    <h3>🔍 Kolay Arama</h3>
                    <p>Geniş kitap koleksiyonumuzda aradığınız kitabı hızlıca bulun.</p>
                </div>
            </div>

            <?php else: ?>

            <div class="dashboard">
                <div class="welcome-box">
                    <h2>Hoş Geldiniz, <?php echo $_SESSION['username']; ?>!</h2>
                    <p>Kütüphane sisteminde neler yapmak istersiniz?</p>
                </div>

                <div class="dashboard-cards">
                    <div class="dashboard-card">
                        <h3>📖 Kitapları Görüntüle</h3>
                        <p>Kütüphanemizdeki tüm kitapları inceleyin.</p>
                        <a href="books.php" class="btn btn-primary">Kitaplara Git</a>
                    </div>

                    <div class="dashboard-card">
                        <h3>📋 Ödünç Aldığım Kitaplar</h3>
                        <p>Şu anda sizde bulunan kitapları görün.</p>
                        <a href="my_books.php" class="btn btn-primary">Kitaplarımı Gör</a>
                    </div>

                    <?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                    <div class="dashboard-card">
                        <h3>⚙️ Yönetim Paneli</h3>
                        <p>Yeni kitap ekleyin ve sistemi yönetin.</p>
                        <a href="add_book.php" class="btn btn-primary">Kitap Ekle</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>


    <div class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Kütüphane Takip Sistemi | Tüm hakları saklıdır</p>
            <p><strong>Üniversite Projesi</strong></p>
        </div>
    </div>
</body>

</html>