<?php
require_once 'config.php';


if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$success_message = "";
$error_message = "";
$user_id = $_SESSION['user_id'];


if(isset($_POST['iade_et'])) {
    $odunc_id = $_POST['odunc_id'];
    
    try {
        
        $stmt = $db->prepare("SELECT ok.*, k.kitap_adi FROM odunc_kitaplar ok 
                            JOIN kitaplar k ON ok.kitap_id = k.id 
                            WHERE ok.id = ? AND ok.kullanici_id = ? AND ok.durum = 'odunc_alindi'");
        $stmt->execute([$odunc_id, $user_id]);
        $odunc = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($odunc) {
            
            $stmt = $db->prepare("UPDATE odunc_kitaplar SET durum = 'iade_edildi', iade_tarihi = NOW() WHERE id = ?");
            $stmt->execute([$odunc_id]);
            
            
            $stmt = $db->prepare("UPDATE kitaplar SET stok_sayisi = stok_sayisi + 1 WHERE id = ?");
            $stmt->execute([$odunc['kitap_id']]);
            
            $success_message = "'" . $odunc['kitap_adi'] . "' kitabı başarıyla iade edildi!";
        } else {
            $error_message = "İade edilecek kitap bulunamadı!";
        }
    } catch(PDOException $e) {
        $error_message = "Bir hata oluştu!";
    }
}


try {
    $stmt = $db->prepare("
        SELECT ok.id as odunc_id, ok.odunc_tarihi, ok.durum,
            k.id as kitap_id, k.kitap_adi, k.yazar, k.kategori, k.aciklama
        FROM odunc_kitaplar ok 
        JOIN kitaplar k ON ok.kitap_id = k.id 
        WHERE ok.kullanici_id = ? 
        ORDER BY ok.odunc_tarihi DESC
    ");
    $stmt->execute([$user_id]);
    $odunc_kitaplar = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error_message = "Kitaplar yüklenirken hata oluştu!";
    $odunc_kitaplar = [];
}


$aktif_kitaplar = [];
$iade_edilmis_kitaplar = [];

foreach($odunc_kitaplar as $kitap) {
    if($kitap['durum'] == 'odunc_alindi') {
        $aktif_kitaplar[] = $kitap;
    } else {
        $iade_edilmis_kitaplar[] = $kitap;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ödünç Aldığım Kitaplar - Kütüphane Sistemi</title>


    <link rel="stylesheet" href="style.css">

    <style>
    .tabs {
        margin-bottom: 30px;
    }

    .tab-buttons {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .tab-button {
        padding: 12px 25px;
        background-color: #95a5a6;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
    }

    .tab-button.active {
        background-color: #3498db;
    }

    .tab-button:hover {
        background-color: #2980b9;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .books-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
    }

    .book-item {
        background-color: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .book-title {
        color: #2c3e50;
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .book-author {
        color: #666;
        margin-bottom: 10px;
    }

    .book-category {
        background-color: #3498db;
        color: white;
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 12px;
        display: inline-block;
        margin-bottom: 10px;
    }

    .book-date {
        color: #666;
        font-size: 14px;
        margin-bottom: 15px;
    }

    .book-status {
        margin-bottom: 15px;
    }

    .status-active {
        color: #e67e22;
        font-weight: bold;
    }

    .status-returned {
        color: #27ae60;
        font-weight: bold;
    }

    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background-color: white;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .stat-number {
        font-size: 30px;
        font-weight: bold;
        color: #3498db;
    }

    .stat-label {
        color: #666;
        margin-top: 5px;
    }
    </style>
</head>

<body>

    <div class="navbar">
        <div class="container">
            <h1>📚 Kütüphane Takip Sistemi</h1>
            <div>
                <span>Hoş geldin, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                <a href="index.php">Ana Sayfa</a>
                <a href="books.php">Kitaplar</a>
                <?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                <a href="add_book.php">Kitap Ekle</a>
                <?php endif; ?>
                <a href="logout.php">Çıkış</a>
            </div>
        </div>
    </div>


    <div class="container">
        <div class="main-content">
            <h2>📋 Ödünç Aldığım Kitaplar</h2>

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


            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($aktif_kitaplar); ?></div>
                    <div class="stat-label">Şu Anda Elimde</div>
                </div>

                <div class="stat-card">
                    <div class="stat-number"><?php echo count($iade_edilmis_kitaplar); ?></div>
                    <div class="stat-label">İade Edilmiş</div>
                </div>

                <div class="stat-card">
                    <div class="stat-number"><?php echo count($odunc_kitaplar); ?></div>
                    <div class="stat-label">Toplam Ödünç</div>
                </div>
            </div>


            <div class="tabs">
                <div class="tab-buttons">
                    <button class="tab-button active" onclick="showTab('aktif')">
                        📚 Şu Anda Elimde (<?php echo count($aktif_kitaplar); ?>)
                    </button>
                    <button class="tab-button" onclick="showTab('gecmis')">
                        📜 İade Edilmiş (<?php echo count($iade_edilmis_kitaplar); ?>)
                    </button>
                </div>


                <div id="aktif" class="tab-content active">
                    <?php if(count($aktif_kitaplar) > 0): ?>
                    <div class="books-list">
                        <?php foreach($aktif_kitaplar as $kitap): ?>
                        <div class="book-item">
                            <div class="book-title">
                                <?php echo htmlspecialchars($kitap['kitap_adi']); ?>
                            </div>

                            <div class="book-author">
                                👤 <strong>Yazar:</strong> <?php echo htmlspecialchars($kitap['yazar']); ?>
                            </div>

                            <div class="book-category">
                                <?php echo htmlspecialchars($kitap['kategori']); ?>
                            </div>

                            <div class="book-date">
                                📅 <strong>Ödünç Alım Tarihi:</strong>
                                <?php echo date('d.m.Y H:i', strtotime($kitap['odunc_tarihi'])); ?>
                            </div>

                            <div class="book-status">
                                📖 <span class="status-active">Elimde</span>
                            </div>

                            <?php if(!empty($kitap['aciklama'])): ?>
                            <div style="color: #666; font-size: 14px; margin-bottom: 15px;">
                                <?php echo htmlspecialchars(substr($kitap['aciklama'], 0, 100)); ?>
                                <?php if(strlen($kitap['aciklama']) > 100): ?>...<?php endif; ?>
                            </div>
                            <?php endif; ?>

                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="odunc_id" value="<?php echo $kitap['odunc_id']; ?>">
                                <button type="submit" name="iade_et" class="btn btn-primary" onclick="return confirm('Bu kitabı iade etmek istediğinizden emin misiniz?')">
                                    📤 İade Et
                                </button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div style="text-align: center; padding: 50px; background-color: white; border-radius: 10px;">
                        <h3>📚 Şu Anda Ödünç Alınmış Kitap Yok</h3>
                        <p>Henüz hiç kitap ödünç almamışsınız.</p>
                        <a href="books.php" class="btn btn-primary">Kitaplara Gözat</a>
                    </div>
                    <?php endif; ?>
                </div>


                <div id="gecmis" class="tab-content">
                    <?php if(count($iade_edilmis_kitaplar) > 0): ?>
                    <div class="books-list">
                        <?php foreach($iade_edilmis_kitaplar as $kitap): ?>
                        <div class="book-item">
                            <div class="book-title">
                                <?php echo htmlspecialchars($kitap['kitap_adi']); ?>
                            </div>

                            <div class="book-author">
                                👤 <strong>Yazar:</strong> <?php echo htmlspecialchars($kitap['yazar']); ?>
                            </div>

                            <div class="book-category">
                                <?php echo htmlspecialchars($kitap['kategori']); ?>
                            </div>

                            <div class="book-date">
                                📅 <strong>Ödünç Alım:</strong>
                                <?php echo date('d.m.Y', strtotime($kitap['odunc_tarihi'])); ?>
                            </div>

                            <div class="book-status">
                                ✅ <span class="status-returned">İade Edildi</span>
                            </div>

                            <div style="color: #666; font-size: 14px;">
                                Bu kitabı daha önce okumuştunuz.
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div style="text-align: center; padding: 50px; background-color: white; border-radius: 10px;">
                        <h3>📜 İade Edilmiş Kitap Yok</h3>
                        <p>Henüz hiç kitap iade etmemişsiniz.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>


    <div class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Kütüphane Takip Sistemi</p>
        </div>
    </div>


    <script>
    function showTab(tabName) {

        var tabContents = document.getElementsByClassName('tab-content');
        for (var i = 0; i < tabContents.length; i++) {
            tabContents[i].classList.remove('active');
        }


        var tabButtons = document.getElementsByClassName('tab-button');
        for (var i = 0; i < tabButtons.length; i++) {
            tabButtons[i].classList.remove('active');
        }


        document.getElementById(tabName).classList.add('active');
        event.target.classList.add('active');
    }


    function iadeEt(oduncId, kitapAdi) {
        if (confirm('Bu kitabı iade etmek istediğinizden emin misiniz?')) {

            var form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = '<input type="hidden" name="odunc_id" value="' + oduncId + '">' +
                '<input type="hidden" name="iade_et" value="1">';
            document.body.appendChild(form);
            form.submit();
        }
    }


    document.addEventListener('DOMContentLoaded', function() {
        showTab('aktif');
    });
    </script>
</body>

</html>