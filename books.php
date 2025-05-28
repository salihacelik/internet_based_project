<?php
require_once 'config.php';


if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$success_message = "";
$error_message = "";


$is_ajax = isset($_POST['ajax']) && $_POST['ajax'] == '1';


if(isset($_POST['odunc_al'])) {
    $kitap_id = $_POST['kitap_id'];
    $user_id = $_SESSION['user_id'];
    
    try {
    
        $stmt = $db->prepare("SELECT stok_sayisi, kitap_adi FROM kitaplar WHERE id = ?");
        $stmt->execute([$kitap_id]);
        $kitap = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($kitap && $kitap['stok_sayisi'] > 0) {
            
            $stmt = $db->prepare("SELECT id FROM odunc_kitaplar WHERE kullanici_id = ? AND kitap_id = ? AND durum = 'odunc_alindi'");
            $stmt->execute([$user_id, $kitap_id]);
            
            if($stmt->fetch()) {
                $error_message = "Bu kitabı zaten ödünç almışsınız!";
            } else {
                
                $stmt = $db->prepare("INSERT INTO odunc_kitaplar (kullanici_id, kitap_id, durum) VALUES (?, ?, 'odunc_alindi')");
                $stmt->execute([$user_id, $kitap_id]);
                
                
                $stmt = $db->prepare("UPDATE kitaplar SET stok_sayisi = stok_sayisi - 1 WHERE id = ?");
                $stmt->execute([$kitap_id]);
                
                $success_message = "'" . $kitap['kitap_adi'] . "' kitabı başarıyla ödünç alındı!";
            }
        } else {
            $error_message = "Bu kitap stokta bulunmuyor!";
        }
    } catch(PDOException $e) {
        $error_message = "Bir hata oluştu!";
    }
}


$arama = isset($_GET['arama']) ? trim($_GET['arama']) : '';
$kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';


if($is_ajax && isset($_POST['kategori'])) {
    $kategori = $_POST['kategori'];
    $arama = isset($_POST['arama']) ? trim($_POST['arama']) : '';
}


$sql = "SELECT * FROM kitaplar WHERE 1=1";
$params = [];

if(!empty($arama)) {
    $sql .= " AND (kitap_adi LIKE ? OR yazar LIKE ?)";
    $params[] = "%$arama%";
    $params[] = "%$arama%";
}

if(!empty($kategori)) {
    $sql .= " AND kategori = ?";
    $params[] = $kategori;
}

$sql .= " ORDER BY kitap_adi";


$stmt = $db->prepare($sql);
$stmt->execute($params);
$kitaplar = $stmt->fetchAll(PDO::FETCH_ASSOC);


$stmt = $db->prepare("SELECT DISTINCT kategori FROM kitaplar ORDER BY kategori");
$stmt->execute();
$kategoriler = $stmt->fetchAll(PDO::FETCH_ASSOC);


if($is_ajax) {
    ob_clean(); 
    
    if(count($kitaplar) > 0) {
        foreach($kitaplar as $kitap) {
            echo '<div class="book-card">';
            echo '<div class="book-title">' . htmlspecialchars($kitap['kitap_adi']) . '</div>';
            echo '<div class="book-author">👤 <strong>Yazar:</strong> ' . htmlspecialchars($kitap['yazar']) . '</div>';
            echo '<div class="book-category">' . htmlspecialchars($kitap['kategori']) . '</div>';
            echo '<div class="book-stock">📦 <strong>Stok:</strong> ';
            
            if($kitap['stok_sayisi'] > 0) {
                echo '<span class="stock-available">' . $kitap['stok_sayisi'] . ' adet mevcut</span>';
            } else {
                echo '<span class="stock-unavailable">Stokta yok</span>';
            }
            echo '</div>';
            
            if(!empty($kitap['aciklama'])) {
                $aciklama = strlen($kitap['aciklama']) > 100 ? substr($kitap['aciklama'], 0, 100) . '...' : $kitap['aciklama'];
                echo '<div class="book-description">' . htmlspecialchars($aciklama) . '</div>';
            }
            
            if($kitap['stok_sayisi'] > 0) {
                echo '<form method="POST" style="display: inline;">';
                echo '<input type="hidden" name="kitap_id" value="' . $kitap['id'] . '">';
                echo '<button type="submit" name="odunc_al" class="btn btn-primary" onclick="return confirm(\'Bu kitabı ödünç almak istediğinizden emin misiniz?\')">📚 Ödünç Al</button>';
                echo '</form>';
            } else {
                echo '<button class="btn btn-secondary" disabled>❌ Stokta Yok</button>';
            }
            echo '</div>';
        }
    } else {
        echo '<div style="text-align: center; padding: 50px; background-color: white; border-radius: 10px; grid-column: 1 / -1;">';
        echo '<h3>📚 Kitap Bulunamadı</h3>';
        echo '<p>Seçilen kategoride kitap bulunamadı.</p>';
        echo '</div>';
    }
    exit; 
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitaplar - Kütüphane Sistemi</title>


    <link rel="stylesheet" href="style.css">


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
    .search-form {
        background-color: white;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 30px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .search-form .form-group {
        display: inline-block;
        margin-right: 15px;
        margin-bottom: 0;
    }

    .search-form input,
    .search-form select {
        width: 200px;
        padding: 10px;
        border: 2px solid #ddd;
        border-radius: 5px;
    }

    .books-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    .book-card {
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

    .book-stock {
        margin-bottom: 15px;
    }

    .stock-available {
        color: #27ae60;
        font-weight: bold;
    }

    .stock-unavailable {
        color: #e74c3c;
        font-weight: bold;
    }

    .book-description {
        color: #666;
        font-size: 14px;
        margin-bottom: 15px;
        line-height: 1.4;
    }


    .loading {
        text-align: center;
        padding: 50px;
        font-size: 18px;
        color: #3498db;
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
                <a href="my_books.php">Ödünç Aldıklarım</a>
                <?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                <a href="add_book.php">Kitap Ekle</a>
                <?php endif; ?>
                <a href="logout.php">Çıkış</a>
            </div>
        </div>
    </div>


    <div class="container">
        <div class="main-content">
            <h2>📖 Kitaplar</h2>

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


            <div class="search-form">
                <form method="GET" id="searchForm">
                    <div class="form-group">
                        <input type="text" name="arama" id="aramaInput" placeholder="Kitap adı veya yazar ara..." value="<?php echo htmlspecialchars($arama); ?>">
                    </div>

                    <div class="form-group">

                        <select name="kategori" id="kategoriSelect">
                            <option value="">Tüm Kategoriler</option>
                            <?php foreach($kategoriler as $kat): ?>
                            <option value="<?php echo htmlspecialchars($kat['kategori']); ?>" <?php echo ($kategori == $kat['kategori']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($kat['kategori']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">🔍 Ara</button>
                    <a href="books.php" class="btn btn-secondary">🔄 Temizle</a>
                </form>
            </div>



            <div class="books-grid" id="booksContainer">
                <?php if(count($kitaplar) > 0): ?>
                <?php foreach($kitaplar as $kitap): ?>
                <div class="book-card">
                    <div class="book-title">
                        <?php echo htmlspecialchars($kitap['kitap_adi']); ?>
                    </div>

                    <div class="book-author">
                        👤 <strong>Yazar:</strong> <?php echo htmlspecialchars($kitap['yazar']); ?>
                    </div>

                    <div class="book-category">
                        <?php echo htmlspecialchars($kitap['kategori']); ?>
                    </div>

                    <div class="book-stock">
                        📦 <strong>Stok:</strong>
                        <?php if($kitap['stok_sayisi'] > 0): ?>
                        <span class="stock-available"><?php echo $kitap['stok_sayisi']; ?> adet mevcut</span>
                        <?php else: ?>
                        <span class="stock-unavailable">Stokta yok</span>
                        <?php endif; ?>
                    </div>

                    <?php if(!empty($kitap['aciklama'])): ?>
                    <div class="book-description">
                        <?php echo htmlspecialchars(substr($kitap['aciklama'], 0, 100)); ?>
                        <?php if(strlen($kitap['aciklama']) > 100): ?>...<?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <?php if($kitap['stok_sayisi'] > 0): ?>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="kitap_id" value="<?php echo $kitap['id']; ?>">
                        <button type="submit" name="odunc_al" class="btn btn-primary" onclick="return confirm('Bu kitabı ödünç almak istediğinizden emin misiniz?')">
                            📚 Ödünç Al
                        </button>
                    </form>
                    <?php else: ?>
                    <button class="btn btn-secondary" disabled>
                        ❌ Stokta Yok
                    </button>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div style="text-align: center; padding: 50px; background-color: white; border-radius: 10px;">
                    <h3>📚 Kitap Bulunamadı</h3>
                    <p>Arama kriterlerinize uygun kitap bulunamadı.</p>
                    <a href="books.php" class="btn btn-primary">Tüm Kitapları Gör</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <div class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Kütüphane Takip Sistemi</p>
        </div>
    </div>


    <script>
    $(document).ready(function() {

        $('#kategoriSelect').change(function() {
            var kategori = $(this).val();
            var arama = $('#aramaInput').val();


            $('#booksContainer').html('<div class="loading">🔄 Kitaplar yükleniyor...</div>');


            $.ajax({
                url: 'books.php',
                type: 'POST',
                data: {
                    ajax: '1',
                    kategori: kategori,
                    arama: arama
                },
                success: function(response) {

                    $('#booksContainer').html(response);
                },
                error: function() {
                    $('#booksContainer').html('<div class="error-message">❌ Bir hata oluştu! Sayfa yenilenecek...</div>');
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                }
            });
        });


        $('#aramaInput').keypress(function(e) {
            if (e.which == 13) {
                $('#searchForm').submit();
            }
        });
    });
    </script>
</body>

</html>