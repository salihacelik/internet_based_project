<?php
require_once 'config.php';

// Giriş kontrolü
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Admin kontrolü
if(!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}

$success_message = "";
$error_message = "";

// Form gönderildiğinde
if($_POST) {
    $kitap_adi = trim($_POST['kitap_adi']);
    $yazar = trim($_POST['yazar']);
    $kategori = trim($_POST['kategori']);
    $stok_sayisi = intval($_POST['stok_sayisi']);
    $aciklama = trim($_POST['aciklama']);
    
    // Boş alan kontrolü
    if(empty($kitap_adi) || empty($yazar) || empty($kategori)) {
        $error_message = "Kitap adı, yazar ve kategori alanları boş bırakılamaz!";
    }
    // Stok kontrolü
    elseif($stok_sayisi < 1) {
        $error_message = "Stok sayısı en az 1 olmalı!";
    }
    // Kitap adı uzunluk kontrolü
    elseif(strlen($kitap_adi) < 2) {
        $error_message = "Kitap adı en az 2 karakter olmalı!";
    }
    // Yazar adı uzunluk kontrolü
    elseif(strlen($yazar) < 2) {
        $error_message = "Yazar adı en az 2 karakter olmalı!";
    }
    else {
        try {
            // Aynı kitap var mı kontrol et
            $stmt = $db->prepare("SELECT id FROM kitaplar WHERE kitap_adi = ? AND yazar = ?");
            $stmt->execute([$kitap_adi, $yazar]);
            
            if($stmt->fetch()) {
                $error_message = "Bu kitap zaten sistemde kayıtlı!";
            } else {
                // Yeni kitabı ekle
                $stmt = $db->prepare("INSERT INTO kitaplar (kitap_adi, yazar, kategori, stok_sayisi, aciklama, ekleme_tarihi) VALUES (?, ?, ?, ?, ?, NOW())");
                
                if($stmt->execute([$kitap_adi, $yazar, $kategori, $stok_sayisi, $aciklama])) {
                    $success_message = "'" . $kitap_adi . "' kitabı başarıyla eklendi!";
                    // Form verilerini temizle
                    $_POST = array();
                } else {
                    $error_message = "Kitap eklenirken bir hata oluştu!";
                }
            }
            
        } catch(PDOException $e) {
            $error_message = "Veritabanı hatası oluştu!";
        }
    }
}

// Mevcut kategorileri çek (dropdown için)
try {
    $stmt = $db->prepare("SELECT DISTINCT kategori FROM kitaplar ORDER BY kategori");
    $stmt->execute();
    $kategoriler = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $kategoriler = [];
}

// Son eklenen kitapları göster (5 adet)
try {
    $stmt = $db->prepare("SELECT * FROM kitaplar ORDER BY ekleme_tarihi DESC LIMIT 5");
    $stmt->execute();
    $son_kitaplar = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $son_kitaplar = [];
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitap Ekle - Kütüphane Sistemi</title>

    <!-- CSS dosyası -->
    <link rel="stylesheet" href="style.css">

    <style>
    .admin-header {
        background-color: #e67e22;
        color: white;
        padding: 20px 0;
        text-align: center;
        margin-bottom: 30px;
    }

    .form-row {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
    }

    .form-row .form-group {
        flex: 1;
    }

    .recent-books {
        background-color: white;
        padding: 20px;
        border-radius: 10px;
        margin-top: 30px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .book-list-item {
        padding: 10px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .book-list-item:last-child {
        border-bottom: none;
    }

    .book-info {
        flex: 1;
    }

    .book-name {
        font-weight: bold;
        color: #2c3e50;
    }

    .book-details {
        font-size: 14px;
        color: #666;
    }

    .book-stock {
        background-color: #3498db;
        color: white;
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 12px;
    }

    .quick-categories {
        display: flex;
        gap: 10px;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }

    .category-btn {
        background-color: #ecf0f1;
        border: none;
        padding: 8px 15px;
        border-radius: 20px;
        cursor: pointer;
        font-size: 14px;
        color: #2c3e50;
    }

    .category-btn:hover {
        background-color: #3498db;
        color: white;
    }
    </style>
</head>

<body>
    <!-- Üst Menü -->
    <div class="navbar">
        <div class="container">
            <h1>📚 Kütüphane Takip Sistemi</h1>
            <div>
                <span>Hoş geldin, <?php echo htmlspecialchars($_SESSION['username']); ?>! (Admin)</span>
                <a href="index.php">Ana Sayfa</a>
                <a href="books.php">Kitaplar</a>
                <a href="my_books.php">Ödünç Aldıklarım</a>
                <a href="logout.php">Çıkış</a>
            </div>
        </div>
    </div>

    <!-- Admin Header -->
    <div class="admin-header">
        <div class="container">
            <h2>⚙️ Admin Paneli - Kitap Ekleme</h2>
            <p>Kütüphane sistemine yeni kitap ekleyin</p>
        </div>
    </div>

    <!-- Ana İçerik -->
    <div class="container">
        <div class="main-content">

            <?php if($success_message): ?>
            <div class="success-message">
                ✅ <?php echo htmlspecialchars($success_message); ?>
            </div>
            <?php endif; ?>

            <?php if($error_message): ?>
            <div class="error-message">
                ❌ <?php echo htmlspecialchars($error_message); ?>
            </div>
            <?php endif; ?>

            <!-- Kitap Ekleme Formu -->
            <div class="form-container">
                <h3 style="text-align: center; margin-bottom: 30px; color: #2c3e50;">
                    📖 Yeni Kitap Ekle
                </h3>

                <form method="POST" id="addBookForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="kitap_adi">Kitap Adı:</label>
                            <input type="text" id="kitap_adi" name="kitap_adi" value="<?php echo isset($_POST['kitap_adi']) ? htmlspecialchars($_POST['kitap_adi']) : ''; ?>" required minlength="2">
                        </div>

                        <div class="form-group">
                            <label for="yazar">Yazar:</label>
                            <input type="text" id="yazar" name="yazar" value="<?php echo isset($_POST['yazar']) ? htmlspecialchars($_POST['yazar']) : ''; ?>" required minlength="2">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="kategori">Kategori:</label>
                            <!-- Hızlı kategori seçimi -->
                            <div class="quick-categories">
                                <button type="button" class="category-btn" onclick="setCategory('Roman')">Roman</button>
                                <button type="button" class="category-btn" onclick="setCategory('Bilim Kurgu')">Bilim Kurgu</button>
                                <button type="button" class="category-btn" onclick="setCategory('Tarih')">Tarih</button>
                                <button type="button" class="category-btn" onclick="setCategory('Felsefe')">Felsefe</button>
                                <button type="button" class="category-btn" onclick="setCategory('Teknoloji')">Teknoloji</button>
                            </div>
                            <input type="text" id="kategori" name="kategori" value="<?php echo isset($_POST['kategori']) ? htmlspecialchars($_POST['kategori']) : ''; ?>" required list="kategoriler">
                            <datalist id="kategoriler">
                                <?php foreach($kategoriler as $kat): ?>
                                <option value="<?php echo htmlspecialchars($kat['kategori']); ?>">
                                    <?php endforeach; ?>
                            </datalist>
                        </div>

                        <div class="form-group">
                            <label for="stok_sayisi">Stok Sayısı:</label>
                            <input type="number" id="stok_sayisi" name="stok_sayisi" value="<?php echo isset($_POST['stok_sayisi']) ? htmlspecialchars($_POST['stok_sayisi']) : '1'; ?>" required min="1" max="100">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="aciklama">Açıklama (İsteğe Bağlı):</label>
                        <textarea id="aciklama" name="aciklama" rows="4" style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 5px; resize: vertical;"><?php echo isset($_POST['aciklama']) ? htmlspecialchars($_POST['aciklama']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 18px;">
                            ➕ Kitabı Ekle
                        </button>
                    </div>
                </form>
            </div>

            <!-- Son Eklenen Kitaplar -->
            <?php if(count($son_kitaplar) > 0): ?>
            <div class="recent-books">
                <h3 style="color: #2c3e50; margin-bottom: 20px;">📚 Son Eklenen Kitaplar</h3>

                <?php foreach($son_kitaplar as $kitap): ?>
                <div class="book-list-item">
                    <div class="book-info">
                        <div class="book-name">
                            <?php echo htmlspecialchars($kitap['kitap_adi']); ?>
                        </div>
                        <div class="book-details">
                            👤 <?php echo htmlspecialchars($kitap['yazar']); ?> |
                            📂 <?php echo htmlspecialchars($kitap['kategori']); ?> |
                            📅 <?php echo date('d.m.Y', strtotime($kitap['ekleme_tarihi'])); ?>
                        </div>
                    </div>
                    <div class="book-stock">
                        <?php echo $kitap['stok_sayisi']; ?> adet
                    </div>
                </div>
                <?php endforeach; ?>

                <div style="text-align: center; margin-top: 20px;">
                    <a href="books.php" class="btn btn-secondary">Tüm Kitapları Gör</a>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Kütüphane Takip Sistemi</p>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
    // Hızlı kategori seçimi
    function setCategory(kategori) {
        document.getElementById('kategori').value = kategori;
    }

    // Form validasyonu
    document.getElementById('addBookForm').addEventListener('submit', function(e) {
        var kitapAdi = document.getElementById('kitap_adi').value.trim();
        var yazar = document.getElementById('yazar').value.trim();
        var kategori = document.getElementById('kategori').value.trim();
        var stokSayisi = parseInt(document.getElementById('stok_sayisi').value);

        // Boş alan kontrolü
        if (kitapAdi == '' || yazar == '' || kategori == '') {
            alert('Kitap adı, yazar ve kategori alanları boş bırakılamaz!');
            e.preventDefault();
            return false;
        }

        // Uzunluk kontrolü
        if (kitapAdi.length < 2) {
            alert('Kitap adı en az 2 karakter olmalı!');
            e.preventDefault();
            return false;
        }

        if (yazar.length < 2) {
            alert('Yazar adı en az 2 karakter olmalı!');
            e.preventDefault();
            return false;
        }

        // Stok kontrolü
        if (stokSayisi < 1 || stokSayisi > 100) {
            alert('Stok sayısı 1 ile 100 arasında olmalı!');
            e.preventDefault();
            return false;
        }

        return true;
    });

    // Büyük harf yapma (otomatik)
    document.getElementById('kitap_adi').addEventListener('blur', function() {
        this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1);
    });

    document.getElementById('yazar').addEventListener('blur', function() {
        this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1);
    });

    document.getElementById('kategori').addEventListener('blur', function() {
        this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1);
    });
    </script>
</body>

</html>