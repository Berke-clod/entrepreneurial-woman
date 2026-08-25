<?php
// 1. OTURUM VE VERİTABANI GÜVENLİĞİ
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include("baglan.php"); // Mutlaka baglan.php içinde session_start() olduğundan emin ol

// Kullanıcı Giriş Kontrolü (Ana sayfadaki giriş buraya da yansır)
$is_logged_in = isset($_SESSION['user_id']) || isset($_SESSION['user']);
$user_display = "Giriş Yap";

if($is_logged_in) {
    // Eğer session'da isim varsa onu, yoksa veritabanından çekip gösterelim
    $user_display = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : "Hesabım";
}

// URL Parametreleri
$arama_kelimesi = isset($_GET['q']) ? mysqli_real_escape_string($baglanti, $_GET['q']) : "";
$sepet_sayisi = isset($_SESSION['sepet']) ? count($_SESSION['sepet']) : 0;
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $arama_kelimesi; ?> Sonuçları | hepsiKADIN</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    
    <style>
        /* --- FULL PAZARYERİ CSS MOTORU --- */
        :root {
            --hb-orange: #FF6000;
            --hb-blue: #005bd3;
            --text-dark: #333;
            --text-gray: #666;
            --bg-light: #f4f6f8;
            --white: #ffffff;
            --border: #e2e8f0;
            --shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-light); color: var(--text-dark); overflow-x: hidden; }
        a { text-decoration: none; color: inherit; }

        /* HEADER - FULL ENTEGRASYON */
        header { background: var(--white); padding: 18px 0; border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 2000; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        
        /* DÜZELTME: Genişlik %100 yapıldı ve max-width verildi */
        .header-container { width: 100%; max-width: 1380px; margin: auto; display: flex; align-items: center; justify-content: space-between; gap: 40px; padding: 0 20px; }
        
        .logo { font-size: 36px; font-weight: 800; color: var(--hb-orange); letter-spacing: -2px; }
        
        .search-wrapper { flex: 1; position: relative; display: flex; }
        .search-wrapper input { 
            width: 100%; padding: 15px 25px; border-radius: 12px; border: 2px solid #eee; 
            background: #f8fafc; font-size: 15px; outline: none; transition: 0.3s;
        }
        .search-wrapper input:focus { border-color: var(--hb-orange); background: white; box-shadow: 0 0 0 4px rgba(255,96,0,0.1); }
        .search-wrapper button { 
            position: absolute; right: 6px; top: 6px; background: var(--hb-orange); 
            color: white; border: none; padding: 10px 25px; border-radius: 10px; 
            cursor: pointer; font-weight: 700; transition: 0.2s;
        }
        .search-wrapper button:hover { background: #e55600; }

        .nav-actions { display: flex; align-items: center; gap: 30px; }
        .nav-item { display: flex; flex-direction: column; align-items: center; position: relative; cursor: pointer; }
        .nav-item i { font-size: 28px; color: #4b5563; transition: 0.2s; }
        .nav-item span { font-size: 12px; font-weight: 700; margin-top: 4px; color: #6b7280; }
        .nav-item:hover i, .nav-item:hover span { color: var(--hb-orange); }
        
        .badge { 
            position: absolute; top: -5px; right: -8px; background: var(--hb-orange); 
            color: white; font-size: 11px; font-weight: 800; padding: 2px 7px; 
            border-radius: 50%; border: 2px solid white;
        }

        /* ANA İÇERİK - 2 SÜTUNLU YAPI */
        /* DÜZELTME: Taşma engelleme */
        .content-container { width: 100%; max-width: 1380px; margin: 30px auto; display: grid; grid-template-columns: 300px 1fr; gap: 30px; padding: 0 20px; }

        /* SOL SİDEBAR - GELİŞMİŞ FİLTRELER */
        .sidebar { background: white; border-radius: 20px; padding: 30px; border: 1px solid var(--border); height: fit-content; position: sticky; top: 110px; }
        .sidebar h2 { font-size: 18px; font-weight: 800; margin-bottom: 25px; color: #111; }
        
        .filter-group { margin-bottom: 35px; }
        .filter-group h3 { font-size: 14px; font-weight: 700; color: #374151; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 0.5px; border-left: 4px solid var(--hb-orange); padding-left: 10px; }
        
        .filter-list { list-style: none; }
        .filter-list li { margin-bottom: 12px; display: flex; align-items: center; gap: 12px; font-size: 14px; color: #4b5563; cursor: pointer; transition: 0.2s; }
        .filter-list li:hover { color: var(--hb-orange); }
        .filter-list li input[type="checkbox"] { 
            width: 20px; height: 20px; accent-color: var(--hb-orange); cursor: pointer; border-radius: 5px;
        }

        .price-range-inputs { display: flex; align-items: center; gap: 10px; }
        .price-range-inputs input { width: 100%; padding: 10px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 13px; outline: none; }
        .price-range-inputs input:focus { border-color: var(--hb-orange); }

        /* SAĞ PANEL - ÜRÜN LİSTELEME */
        .results-info-bar { 
            background: white; padding: 20px 30px; border-radius: 15px; border: 1px solid var(--border); 
            margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;
        }
        .results-info-bar h1 { font-size: 18px; font-weight: 700; }
        .sort-dropdown select { padding: 10px 20px; border-radius: 10px; border: 1px solid #ddd; font-weight: 600; outline: none; cursor: pointer; }

        /* ÜRÜN KARTLARI - FULL DETAY */
        /* DÜZELTME: repeat(4, 1fr) ile grid yapısı sağa taşmayı önler */
        .product-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; width: 100%; }
        
        /* Ekran küçüldüğünde ürünlerin taşmaması için Responsive ayar */
        @media (max-width: 1200px) { .product-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 900px) { .product-grid { grid-template-columns: repeat(2, 1fr); } }

        .product-card { 
            background: white; border-radius: 18px; padding: 20px; border: 1px solid var(--border); 
            display: flex; flex-direction: column; position: relative; transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            min-width: 0; /* İçerik taşmasını önler */
        }
        .product-card:hover { transform: translateY(-10px); box-shadow: var(--shadow); border-color: var(--hb-orange); }

        .fav-button { 
            position: absolute; top: 15px; right: 15px; width: 40px; height: 40px; 
            background: white; border-radius: 50%; display: flex; align-items: center; 
            justify-content: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); color: #ccc; cursor: pointer; z-index: 10;
        }
        .fav-button:hover { color: #ff4757; }

        .img-box { width: 100%; height: 230px; display: flex; align-items: center; justify-content: center; background: #fafafa; border-radius: 12px; margin-bottom: 15px; overflow: hidden; }
        .img-box img { max-width: 85%; max-height: 85%; object-fit: contain; transition: 0.5s; }
        .product-card:hover .img-box img { transform: scale(1.1); }

        .p-tag { background: #ecfdf5; color: #059669; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 6px; align-self: flex-start; margin-bottom: 10px; }
        .p-brand { font-size: 12px; font-weight: 900; color: var(--hb-orange); text-transform: uppercase; }
        .p-name { font-size: 14px; color: #4b5563; font-weight: 600; line-height: 1.5; height: 42px; overflow: hidden; margin: 6px 0 15px 0; }
        
        .p-rating { display: flex; align-items: center; gap: 2px; color: #f59e0b; font-size: 14px; margin-bottom: 15px; }
        .p-rating span { color: #9ca3af; font-size: 12px; margin-left: 5px; }

        .p-footer { margin-top: auto; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #f3f4f6; padding-top: 15px; }
        .p-price { font-size: 22px; font-weight: 800; color: #111; }
        .add-cart-btn { 
            width: 45px; height: 45px; background: #fff5f0; color: var(--hb-orange); 
            border-radius: 12px; display: flex; align-items: center; justify-content: center; 
            transition: 0.3s; cursor: pointer;
        }
        .product-card:hover .add-cart-btn { background: var(--hb-orange); color: white; transform: rotate(90deg); }

        /* FOOTER - DEVASA PAZARYERİ FOOTER'I */
        footer { background: #0f172a; color: #94a3b8; padding: 100px 0 50px; margin-top: 120px; }
        .footer-grid { width: 100%; max-width: 1380px; margin: auto; display: grid; grid-template-columns: repeat(4, 1fr); gap: 60px; padding: 0 20px 60px 20px; border-bottom: 1px solid #1e293b; }
        .f-column h4 { color: white; font-size: 18px; font-weight: 700; margin-bottom: 30px; position: relative; }
        .f-column h4::after { content: ''; position: absolute; left: 0; bottom: -10px; width: 40px; height: 3px; background: var(--hb-orange); }
        .f-column ul { list-style: none; }
        .f-column ul li { margin-bottom: 15px; font-size: 14px; transition: 0.3s; cursor: pointer; }
        .f-column ul li:hover { color: white; transform: translateX(8px); }
        
        .f-socials { display: flex; gap: 20px; margin-top: 20px; }
        .f-socials i { font-size: 24px; color: white; cursor: pointer; transition: 0.3s; }
        .f-socials i:hover { color: var(--hb-orange); transform: translateY(-5px); }
        
        .f-bottom { text-align: center; padding-top: 40px; font-size: 13px; color: #64748b; letter-spacing: 1px; }
    </style>
</head>
<body>

<header>
    <div class="header-container">
        <a href="index.php" class="logo">hepsiKADIN</a>
        
        <div class="search-wrapper">
            <form action="kategori.php" method="GET" style="width:100%; display:flex;">
                <input type="text" name="q" value="<?php echo htmlspecialchars($arama_kelimesi); ?>" placeholder="Emeğiyle dünyayı değiştiren kadınların ürünlerini ara...">
                <button type="submit">KEŞFET</button>
            </form>
        </div>

        <div class="nav-actions">
            <a href="<?php echo $is_logged_in ? 'profil.php' : 'giris.php'; ?>" class="nav-item">
                <i class="material-icons-outlined">account_circle</i>
                <span><?php echo $user_display; ?></span>
            </a>
            
            <a href="favorilerim.php" class="nav-item">
                <i class="material-icons-outlined">favorite_border</i>
                <span>Favorilerim</span>
                <div class="badge">12</div>
            </a>
            
            <a href="sepetim.php" class="nav-item">
                <i class="material-icons-outlined">shopping_cart</i>
                <span>Sepetim</span>
                <?php if($sepet_sayisi > 0): ?>
                    <div class="badge"><?php echo $sepet_sayisi; ?></div>
                <?php endif; ?>
            </a>
        </div>
    </div>
</header>

<div class="content-container">
    
    <aside class="sidebar">
        <h2>Filtreler</h2>
        
        <div class="filter-group">
            <h3>Kategoriler</h3>
            <ul class="filter-list">
                <li><input type="checkbox"> El Emeği Takı</li>
                <li><input type="checkbox"> Doğal Gıdalar</li>
                <li><input type="checkbox"> Ev Dekorasyon</li>
                <li><input type="checkbox"> Tekstil & Giyim</li>
                <li><input type="checkbox"> Sanat & Koleksiyon</li>
            </ul>
        </div>

        <div class="filter-group">
            <h3>Marka / Girişimci</h3>
            <ul class="filter-list">
                <li><input type="checkbox"> Kadın Emeği Vakfı</li>
                <li><input type="checkbox"> Anadolu Sanatı</li>
                <li><input type="checkbox"> Modern Tasarımlar</li>
                <li><input type="checkbox"> Yerel Üretici Birliği</li>
            </ul>
        </div>

        <div class="filter-group">
            <h3>Fiyat Aralığı</h3>
            <div class="price-range-inputs">
                <input type="number" placeholder="Min">
                <input type="number" placeholder="Max">
            </div>
            <button style="width:100%; margin-top:15px; padding:12px; background:#111; color:white; border:none; border-radius:10px; cursor:pointer; font-weight:800;">UYGULA</button>
        </div>

        <div class="filter-group">
            <h3>Hızlı Teslimat</h3>
            <ul class="filter-list">
                <li><input type="checkbox"> Yarın Kapında</li>
                <li><input type="checkbox"> Bugün Teslimat</li>
            </ul>
        </div>
    </aside>

    <main>
        <div class="results-info-bar">
            <h1>"<?php echo $arama_kelimesi ? htmlspecialchars($arama_kelimesi) : 'Tüm Ürünler'; ?>" için sonuçlar listeleniyor</h1>
            <div class="sort-dropdown">
                <select>
                    <option>Önerilen Sıralama</option>
                    <option>En Düşük Fiyat</option>
                    <option>En Yüksek Fiyat</option>
                    <option>En Yeni Ürünler</option>
                    <option>Müşteri Puanı</option>
                </select>
            </div>
        </div>

        <div class="product-grid">
            <?php
            // VERİTABANI MOTORU - SQL Sorgusu
            $sql = "SELECT * FROM urunler WHERE urun_adi LIKE '%$arama_kelimesi%' OR marka LIKE '%$arama_kelimesi%' ORDER BY id DESC";
            $result = mysqli_query($baglanti, $sql);

            if(mysqli_num_rows($result) > 0) {
                while($u = mysqli_fetch_assoc($result)) {
                    $img = !empty($u['urun_resim']) ? $u['urun_resim'] : "https://picsum.photos/seed/".$u['id']."/400/400";
                    ?>
                    <div class="product-card">
                        <div class="fav-button"><i class="material-icons-outlined">favorite_border</i></div>
                        <a href="urun-detay.php?id=<?php echo $u['id']; ?>">
                            <div class="img-box">
                                <img src="<?php echo $img; ?>" alt="Ürün">
                            </div>
                            <div class="p-tag">ÜCRETSİZ KARGO</div>
                            <div class="p-brand"><?php echo $u['marka']; ?></div>
                            <div class="p-name"><?php echo $u['urun_adi']; ?></div>
                            <div class="p-rating">
                                <i class="material-icons">star</i><i class="material-icons">star</i><i class="material-icons">star</i><i class="material-icons">star</i><i class="material-icons">star_half</i>
                                <span>(<?php echo rand(10, 200); ?> Değerlendirme)</span>
                            </div>
                        </a>
                        <div class="p-footer">
                            <div class="p-price"><?php echo number_format($u['fiyat'], 2, ',', '.'); ?> TL</div>
                            <a href="sepet_islem.php?ekle=<?php echo $u['id']; ?>" class="add-cart-btn">
                                <i class="material-icons-outlined">add_shopping_cart</i>
                            </a>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<div style='grid-column: 1/-1; text-align:center; padding:100px;'>
                        <i class='material-icons-outlined' style='font-size:100px; color:#ddd;'>inventory_2</i>
                        <h2 style='margin-top:20px; color:#999;'>Aradığınız kriterlerde ürün bulamadık.</h2>
                        <a href='kategori.php' style='color:var(--hb-orange); font-weight:800;'>Tüm Ürünlere Dön</a>
                      </div>";
            }
            ?>
        </div>
    </main>
</div>

<footer>
    <div class="footer-grid">
        <div class="f-column">
            <h4>HEPSİKADIN</h4>
            <ul>
                <li>Biz Kimiz?</li>
                <li>Girişimci Hikayeleri</li>
                <li>Kadın Emeği Politikamız</li>
                <li>İletişim & Destek</li>
                <li>Kariyer</li>
            </ul>
        </div>
        <div class="f-column">
            <h4>ALIŞVERİŞ DENEYİMİ</h4>
            <ul>
                <li>Güvenli Ödeme</li>
                <li>İade ve Değişim</li>
                <li>Kargo ve Teslimat</li>
                <li>Üyelik Avantajları</li>
                <li>İşlem Rehberi</li>
            </ul>
        </div>
        <div class="f-column">
            <h4>KATEGORİLER</h4>
            <ul>
                <li>El Yapımı Takı</li>
                <li>Organik Mutfak</li>
                <li>Ev Tekstili</li>
                <li>Moda & Aksesuar</li>
                <li>Hediye Kutuları</li>
            </ul>
        </div>
        <div class="f-column">
            <h4>BİZİMLE KALIN</h4>
            <p style="font-size:13px; line-height:1.6;">En yeni fırsatları ve kadın girişimcilerin başarı hikayelerini kaçırmayın.</p>
            <div class="f-socials">
                <i class="material-icons-outlined">facebook</i>
                <i class="material-icons-outlined">camera_alt</i>
                <i class="material-icons-outlined">alternate_email</i>
                <i class="material-icons-outlined">play_circle_filled</i>
            </div>
        </div>
    </div>
    <div class="f-bottom">
        &copy; 2026 hepsiKADIN - Kadının Gücüyle Geleceği İnşa Ediyoruz. Tüm Hakları Saklıdır.
    </div>
</footer>

</body>
</html>