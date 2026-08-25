<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Veritabanı bağlantısı
$host = "localhost"; $user = "root"; $pass = ""; $db = "kadin_girisimci_db";
$baglanti = @mysqli_connect($host, $user, $pass, $db);

// Giriş kontrolü
$isLoggedIn = isset($_SESSION['kullanici_id']);
$userName = $isLoggedIn ? $_SESSION['kullanici_ad'] : "Giriş Yap";

// --- YENİ EKLEME: Arama ve Kategori Filtreleme Mantığı ---
$search = isset($_GET['q']) ? mysqli_real_escape_string($baglanti, $_GET['q']) : "";
$kat_id = isset($_GET['kat']) ? intval($_GET['kat']) : 0;

$sorgu = "SELECT * FROM urunler WHERE 1=1";
if (!empty($search)) { $sorgu .= " AND urun_adi LIKE '%$search%'"; }
if ($kat_id > 0) { $sorgu .= " AND kategori_id = $kat_id"; }

// Eğer arama veya kategori seçiliyse 40 ürün, ana sayfadaysa rastgele 12 ürün
if (!empty($search) || $kat_id > 0) {
    $sorgu .= " ORDER BY id DESC LIMIT 40";
} else {
    $sorgu .= " ORDER BY RAND() LIMIT 12";
}
// -------------------------------------------------------
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>hepsiKADIN | Kadın Girişimci Pazaryeri</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <style>
        :root { --primary: #FF6000; --primary-hover: #e55600; --dark: #333; --light: #f1f3f5; --white: #ffffff; --footer-bg: #1a1a1a; --success: #1e7e34; }
        * { box-sizing: border-box; margin: 0; padding: 0; transition: all 0.25s ease-in-out; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: var(--light); color: var(--dark); overflow-x: hidden; }
        
        .container { width: 95%; max-width: 1300px; margin: auto; position: relative; }

        /* --- HEADER --- */
        header { background: white; padding: 20px 0; border-bottom: 1px solid #ddd; position: sticky; top: 0; z-index: 1000; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .header-main { display: flex; align-items: center; justify-content: space-between; }
        .logo { color: var(--primary); font-size: 36px; font-weight: 900; text-decoration: none; letter-spacing: -2px; flex-shrink: 0; }
        
        .search-area { flex-grow: 1; position: relative; max-width: 650px; margin: 0 50px; }
        .search-area input { width: 100%; padding: 14px 50px; border-radius: 12px; border: 2px solid #eee; outline: none; background: #f8f9fa; font-size: 15px; }
        .search-area input:focus { border-color: var(--primary); background: white; box-shadow: 0 0 0 4px rgba(255,96,0,0.1); }
        .search-area i { position: absolute; left: 18px; top: 15px; color: #999; font-size: 22px; }
        .search-area button { position: absolute; right: 7px; top: 7px; background: var(--primary); color: white; border: none; padding: 9px 25px; border-radius: 9px; cursor: pointer; font-weight: bold; }
        .search-area button:hover { background: var(--primary-hover); }

        /* --- KULLANICI PANELİ --- */
        .user-menu { display: flex; align-items: center; gap: 25px; }
        .user-profile-wrapper { position: relative; cursor: pointer; display: flex; align-items: center; gap: 10px; padding: 8px 15px; border-radius: 12px; border: 1px solid transparent; }
        .user-profile-wrapper:hover { background: #fff8f5; border-color: #ffe0cc; }
        .user-avatar { width: 40px; height: 40px; background: linear-gradient(135deg, var(--primary), #ff8c42); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px; box-shadow: 0 4px 10px rgba(255,96,0,0.2); }
        
        .profile-dropdown { 
            position: absolute; top: 110%; right: 0; width: 220px; background: white; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.15); border-radius: 14px; display: none; flex-direction: column; padding: 12px 0; z-index: 1001; 
            border: 1px solid #eee;
        }
        .user-profile-wrapper:hover .profile-dropdown { display: flex; animation: slideUp 0.3s ease; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        .profile-dropdown a { padding: 12px 20px; text-decoration: none; color: #444; font-size: 14px; display: flex; align-items: center; gap: 12px; font-weight: 600; }
        .profile-dropdown a:hover { background: #fff5ed; color: var(--primary); }

        .cart-link { text-decoration: none; color: inherit; display: flex; align-items: center; gap: 8px; position: relative; font-weight: 700; padding: 8px 15px; border-radius: 12px; }
        .cart-link:hover { background: #f8f9fa; }
        .cart-count { background: var(--primary); color: white; border-radius: 8px; padding: 2px 7px; font-size: 11px; position: absolute; top: 0; right: 0; font-weight: 900; }

        /* --- NAV & MEGA MENÜ --- */
        nav { background: white; border-bottom: 3px solid var(--primary); position: relative; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .nav-links { display: flex; list-style: none; justify-content: center; }
        .nav-item { padding: 18px 30px; font-size: 15px; font-weight: 700; cursor: pointer; position: static; color: #444; }
        .nav-item:hover { color: var(--primary); background: #fff8f5; }
        
        .mega-drop { 
            position: absolute; top: 100%; left: 0; width: 100%; background: white; 
            display: none; grid-template-columns: repeat(4, 1fr) 350px; padding: 45px; 
            box-shadow: 0 25px 50px rgba(0,0,0,0.1); border-bottom: 5px solid var(--primary); z-index: 1000;
        }
        .nav-item:hover .mega-drop { display: grid; }
        .menu-col h4 { color: var(--primary); font-size: 15px; margin-bottom: 15px; border-left: 3px solid var(--primary); padding-left: 10px; font-weight: 800; }
        .menu-col ul { list-style: none; font-size: 14px; color: #666; }
        .menu-col li { padding: 7px 0; cursor: pointer; transition: 0.2s; }
        .menu-col li:hover { color: var(--primary); transform: translateX(5px); }

        .promo-box { background: linear-gradient(135deg, #fff5ed 0%, #fff 100%); padding: 25px; border-radius: 15px; text-align: center; border: 1px solid #ffe0cc; }
        .promo-box h4 { margin-bottom: 10px; font-size: 16px; }
        .promo-box img { width: 100%; border-radius: 12px; margin-top: 15px; object-fit: cover; height: 160px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }

        /* --- HERO & SLIDER --- */
        .hero-section { display: grid; grid-template-columns: 2.8fr 1.2fr; gap: 20px; margin: 30px 0; }
        .main-slider { 
            background: linear-gradient(to right, rgba(255, 96, 0, 0.9), rgba(0,0,0,0.2)), 
                        url('https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=1200'); 
            background-size: cover; border-radius: 20px; height: 420px; display: flex; flex-direction: column; justify-content: center; padding: 60px; color: white;
            box-shadow: 0 15px 35px rgba(255, 96, 0, 0.2);
        }
        .main-slider h1 { font-size: 52px; font-weight: 900; line-height: 1; margin-bottom: 20px; text-shadow: 0 2px 10px rgba(0,0,0,0.2); }
        .main-slider p { font-size: 18px; margin-bottom: 30px; opacity: 0.9; max-width: 500px; }
        .cta-btn { background: white; color: var(--primary); padding: 15px 35px; border-radius: 12px; text-decoration: none; font-weight: 900; width: fit-content; font-size: 16px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .cta-btn:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.2); }

        .side-banners { display: flex; flex-direction: column; gap: 20px; }
        .side-card { border-radius: 20px; flex: 1; background-size: cover; text-decoration: none; color: white; display: flex; flex-direction: column; align-items: center; justify-content: center; transition: 0.4s; font-weight: 800; text-align: center; position: relative; overflow: hidden; }
        .side-card::before { content: ''; position: absolute; inset: 0; background: rgba(0,0,0,0.3); transition: 0.3s; }
        .side-card:hover::before { background: rgba(255, 96, 0, 0.4); }
        .side-card span { position: relative; z-index: 1; font-size: 20px; }
        .card-1 { background-image: url('https://images.unsplash.com/photo-1513519245088-0e12902e5a38?w=500'); }
        .card-2 { background-image: url('https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=500'); }

        /* --- KATEGORİ DAİRELERİ --- */
        .cat-circles { display: flex; justify-content: space-between; margin: 50px 0; padding: 15px 5px; gap: 25px; background: white; border-radius: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid #eee; }
        .cat-item { text-align: center; flex: 1; cursor: pointer; text-decoration: none; color: inherit; }
        .cat-circle { width: 90px; height: 90px; background: #fff5ed; border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; border: 2px solid transparent; transition: 0.4s; }
        .cat-item:hover .cat-circle { border-color: var(--primary); background: white; transform: rotate(10deg) scale(1.1); box-shadow: 0 10px 20px rgba(255,96,0,0.15); }
        .cat-circle i { font-size: 40px; color: var(--primary); }
        .cat-item span { font-size: 14px; font-weight: 700; color: #555; }

        /* --- FLAŞ ÜRÜNLER ALANI --- */
        .flash-deals { background: #fff; padding: 30px; border-radius: 20px; margin-bottom: 50px; border: 2px solid #ffe0cc; }
        .flash-header { display: flex; align-items: center; gap: 20px; margin-bottom: 25px; }
        .flash-header h2 { font-size: 24px; color: var(--primary); font-weight: 900; }
        .timer { background: var(--primary); color: white; padding: 8px 15px; border-radius: 8px; font-family: monospace; font-size: 20px; font-weight: bold; }

        /* --- ÜRÜN KARTLARI --- */
        .section-title { font-size: 26px; font-weight: 900; margin: 50px 0 30px; border-left: 6px solid var(--primary); padding-left: 20px; display: flex; justify-content: space-between; align-items: center; }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 20px; margin-bottom: 70px; }
        
        .p-card-wrapper { position: relative; background: white; border-radius: 18px; border: 1px solid #eee; overflow: hidden; height: 100%; display: flex; flex-direction: column; }
        .p-card-wrapper:hover { border-color: var(--primary); box-shadow: 0 15px 40px rgba(0,0,0,0.08); transform: translateY(-10px); }
        
        .p-card { text-decoration: none; color: inherit; display: block; padding: 20px; flex-grow: 1; }
        .p-img-box { position: relative; height: 200px; width: 100%; background: #fdfdfd; border-radius: 12px; margin-bottom: 15px; display: flex; align-items: center; justify-content: center; }
        .p-card img { max-width: 90%; max-height: 90%; object-fit: contain; }
        
        .fav-btn { position: absolute; top: 15px; right: 15px; background: white; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; box-shadow: 0 5px 15px rgba(0,0,0,0.1); color: #ddd; cursor: pointer; z-index: 10; font-size: 24px; }
        .fav-btn:hover { color: #ff4757; transform: scale(1.1); }
        
        .p-badge { position: absolute; top: 15px; left: 15px; background: var(--success); color: white; font-size: 11px; padding: 4px 10px; border-radius: 6px; font-weight: 800; z-index: 5; }

        .p-info { margin-top: 10px; }
        .p-name { font-size: 15px; font-weight: 600; color: #444; height: 42px; overflow: hidden; line-height: 1.4; }
        .p-price-row { display: flex; align-items: center; justify-content: space-between; margin-top: 15px; }
        .p-price { font-size: 22px; font-weight: 900; color: #222; letter-spacing: -0.5px; }
        .p-old-price { font-size: 14px; text-decoration: line-through; color: #aaa; margin-right: 5px; }

        .add-to-cart-mini { width: 100%; background: #f8f9fa; border: 1px solid #eee; color: #555; padding: 12px; border-radius: 0 0 18px 18px; cursor: pointer; font-weight: 800; font-size: 13px; }
        .p-card-wrapper:hover .add-to-cart-mini { background: var(--primary); color: white; border-color: var(--primary); }

        /* --- INFO BAR --- */
        .info-bar { display: grid; grid-template-columns: repeat(4, 1fr); gap: 25px; margin: 80px 0; padding: 40px; background: white; border-radius: 25px; border: 1px solid #eee; box-shadow: 0 15px 40px rgba(0,0,0,0.02); }
        .info-box { display: flex; align-items: center; gap: 20px; }
        .info-box i { font-size: 45px; color: var(--primary); background: #fff5ed; padding: 15px; border-radius: 18px; }
        .info-box h5 { font-size: 16px; font-weight: 800; margin-bottom: 5px; }
        .info-box p { font-size: 13px; color: #777; line-height: 1.4; }

        /* --- FOOTER --- */
        .main-footer { background: var(--footer-bg); color: #bbb; padding: 80px 0 40px; margin-top: 80px; }
        .footer-grid { display: grid; grid-template-columns: 1.8fr 1fr 1fr 1fr; gap: 60px; margin-bottom: 60px; border-bottom: 1px solid #2d2d2d; padding-bottom: 60px; }
        .f-col h4 { margin-bottom: 30px; font-size: 20px; color: var(--white); font-weight: 800; position: relative; }
        .f-col h4::after { content: ''; width: 40px; height: 3px; background: var(--primary); position: absolute; left: 0; bottom: -10px; border-radius: 2px; }
        .f-col ul { list-style: none; }
        .f-col li { margin-bottom: 15px; font-size: 15px; cursor: pointer; transition: 0.3s; display: flex; align-items: center; gap: 8px; }
        .f-col li:hover { color: var(--primary); transform: translateX(8px); }
        .f-col p { line-height: 1.8; font-size: 15px; margin-bottom: 25px; }
        
        .footer-bottom { display: flex; justify-content: space-between; align-items: center; padding-top: 30px; font-size: 14px; color: #666; }
        .social-icons { display: flex; gap: 20px; }
        .social-icons i { font-size: 24px; cursor: pointer; color: #888; }
        .social-icons i:hover { color: var(--primary); transform: translateY(-3px); }

        /* SCROLLBAR */
        ::-webkit-scrollbar { width: 10px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #ccc; border-radius: 5px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--primary); }
    </style>
</head>
<body>

<header>
    <div class="container header-main">
        <a href="index.php" class="logo">hepsiKADIN</a>
        
       <form action="kategori.php" method="GET" class="search-area">
    <i class="material-icons-outlined">search</i>
    <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Kadın girişimcilerin eşsiz ürünlerini keşfet...">
    <button type="submit">ARA</button>
</form>

        <div class="user-menu">
            <div class="user-profile-wrapper">
                <div class="user-avatar"><?php echo $isLoggedIn ? mb_substr($userName, 0, 1) : '<i class="material-icons-outlined" style="font-size:24px">person</i>'; ?></div>
                <div style="display:flex; flex-direction:column;">
                    <span style="font-size:11px; color:#888;">Merhaba,</span>
                    <span style="font-weight:800; font-size:14px;"><?php echo $userName; ?></span>
                </div>
                
                <div class="profile-dropdown">
                    <?php if($isLoggedIn): ?>
                        <a href="profil.php"><i class="material-icons-outlined">account_circle</i> Profil Bilgilerim</a>
                        <a href="siparislerim.php"><i class="material-icons-outlined">local_shipping</i> Tüm Siparişlerim</a>
                        <a href="favorilerim.php"><i class="material-icons-outlined">favorite</i> Favori Ürünlerim</a>
                        <a href="cikis.php" style="color:#ff4757; border-top:1px solid #eee; margin-top:5px; padding-top:15px;"><i class="material-icons-outlined">logout</i> Güvenli Çıkış</a>
                    <?php else: ?>
                        <a href="giris.php"><i class="material-icons-outlined">login</i> Giriş Yap</a>
                        <a href="kayit.php"><i class="material-icons-outlined">person_add</i> Ücretsiz Üye Ol</a>
                    <?php endif; ?>
                </div>
            </div>
            <a href="sepetim.php" class="cart-link">
                <span class="material-icons-outlined" style="font-size:30px; color:var(--dark)">shopping_cart</span>
                <span>Sepetim</span>
                <span class="cart-count">3</span>
            </a>
        </div>
    </div>
</header>

<nav>
    <div class="container">
        <ul class="nav-links">
            <?php
            if($baglanti){
                $kats = mysqli_query($baglanti, "SELECT * FROM kategoriler");
                while($k = mysqli_fetch_assoc($kats)){
                    echo '
                    <li class="nav-item">
                        <a href="index.php?kat='.$k['id'].'" style="text-decoration:none; color:inherit;">' . $k['kategori_adi'] . '</a>
                        <div class="mega-drop">
                            <div class="menu-col"><h4>Trend Kategoriler</h4><ul><li>En Çok Değerlendirilenler</li><li>Flaş İndirimdekiler</li><li>Yarın Kapında Ürünler</li></ul></div>
                            <div class="menu-col"><h4>Kadın Gücü</h4><ul><li>Yeni Girişimcilerimiz</li><li>Başarı Hikayeleri</li><li>Kooperatif Ürünleri</li></ul></div>
                            <div class="menu-col"><h4>Hizmetler</h4><ul><li>Hediye Paketi</li><li>Kişiye Özel Tasarım</li><li>Kurumsal Satış</li></ul></div>
                            <div class="menu-col"><h4>Koleksiyonlar</h4><ul><li>Bohem Tarz</li><li>Ofis Şıklığı</li><li>Doğal Yaşam</li></ul></div>
                            <div class="promo-box">
                                <h4 style="color:var(--primary); font-weight:900;">HAFTANIN GİRİŞİMCİSİ</h4>
                                <p style="font-size:12px; color:#888;">El emeği seramikleriyle Ayşe Hanım...</p>
                                <img src="https://picsum.photos/seed/'.$k['id'].'/400/200">
                                <a href="#" style="font-size:12px; color:var(--primary); font-weight:bold; display:block; margin-top:10px;">Mağazayı Gör ></a>
                            </div>
                        </div>
                    </li>';
                }
            }
            ?>
        </ul>
    </div>
</nav>

<div class="container">
    <section class="hero-section">
        <div class="main-slider">
            <span style="background:rgba(255,255,255,0.2); padding:5px 15px; border-radius:50px; font-size:12px; font-weight:bold; margin-bottom:15px; width:fit-content;">BAHAR KAMPANYASI BAŞLADI</span>
            <h1>KADININ EMEĞİ<br>DÜNYAYI DEĞİŞTİRİR</h1>
            <p>Binlerce kadın girişimcinin el emeği ürünlerini keşfedin, yerel üretimi destekleyin.</p>
            <a href="#kesfet" class="cta-btn">ŞİMDİ KEŞFET</a>
        </div>
        <div class="side-banners">
            <a href="#" class="side-card card-1"><span>SOFRA <br>TASARIMLARI</span></a>
            <a href="#" class="side-card card-2"><span>BAŞARI <br>HİKAYELERİ</span></a>
        </div>
    </section>

    <div class="cat-circles">
        <a href="index.php?kat=1" class="cat-item"><div class="cat-circle"><i class="material-icons-outlined">auto_awesome</i></div><span>Takı & Aksesuar</span></a>
        <a href="index.php?kat=2" class="cat-item"><div class="cat-circle"><i class="material-icons-outlined">home</i></div><span>Ev & Yaşam</span></a>
        <a href="index.php?kat=3" class="cat-item"><div class="cat-circle"><i class="material-icons-outlined">restaurant</i></div><span>Doğal Gıda</span></a>
        <a href="index.php?kat=4" class="cat-item"><div class="cat-circle"><i class="material-icons-outlined">brush</i></div><span>Sanat & Hobi</span></a>
        <a href="index.php?kat=5" class="cat-item"><div class="cat-circle"><i class="material-icons-outlined">checkroom</i></div><span>Giyim & Moda</span></a>
        <a href="index.php?kat=6" class="cat-item"><div class="cat-circle"><i class="material-icons-outlined">face</i></div><span>Kozmetik</span></a>
    </div>

    <?php if(empty($search) && $kat_id == 0): ?>
    <div class="flash-deals">
        <div class="flash-header">
            <i class="material-icons-outlined" style="color:var(--primary); font-size:40px;">bolt</i>
            <h2>GÜNÜN FLAŞ FIRSATLARI</h2>
            <div class="timer" id="countdown">04:22:15</div>
            <a href="#" style="margin-left:auto; color:var(--primary); font-weight:bold;">Tümünü Gör ></a>
        </div>
        </div>
    <?php endif; ?>

    <div id="kesfet" class="section-title">
        <h2>
            <?php 
                if(!empty($search)) echo "'$search' Araması İçin Sonuçlar";
                elseif($kat_id > 0) echo "Kategoriye Özel Ürünler";
                else echo "Sizin İçin Seçtiklerimiz";
            ?>
        </h2>
        <div style="font-size:14px; color:#888; font-weight:normal;">Toplam <?php echo @mysqli_num_rows(mysqli_query($baglanti, $sorgu)); ?> ürün listeleniyor</div>
    </div>

    <div class="product-grid">
        <?php
        if ($baglanti) {
            $urunler = mysqli_query($baglanti, $sorgu);
            if(@mysqli_num_rows($urunler) == 0){
                echo "
                <div style='grid-column: 1/-1; text-align:center; padding:100px; background:white; border-radius:20px;'>
                    <i class='material-icons-outlined' style='font-size:80px; color:#ddd;'>search_off</i>
                    <h3 style='margin-top:20px; color:#999;'>Üzgünüz, aradığınız kriterde ürün bulamadık.</h3>
                    <a href='index.php' style='color:var(--primary); display:block; margin-top:10px; font-weight:bold;'>Anasayfaya Dön</a>
                </div>";
            }

            while($u = mysqli_fetch_assoc($urunler)) {
                $resim = !empty($u['urun_resim']) ? $u['urun_resim'] : "https://picsum.photos/seed/".$u['id']."/400/400";
                $indirimli_fiyat = $u['fiyat'] * 1.2; // Görsel amaçlı eski fiyat
                echo '
                <div class="p-card-wrapper">
                    <div class="p-badge">KARGO BEDAVA</div>
                    <div class="fav-btn"><i class="material-icons-outlined">favorite_border</i></div>
                    <a href="urun-detay.php?id='.$u['id'].'" class="p-card">
                        <div class="p-img-box">
                            <img src="'.$resim.'" alt="'.$u['urun_adi'].'">
                        </div>
                        <div class="p-info">
                            <div style="font-size:11px; font-weight:900; color:var(--primary); margin-bottom:5px;">BAŞARILI MAĞAZA</div>
                            <div class="p-name">'.$u['urun_adi'].'</div>
                            <div style="display:flex; align-items:center; gap:5px; margin-top:5px;">
                                <div style="color:#ffc107; font-size:14px;">★★★★★</div>
                                <span style="font-size:10px; color:#aaa;">(42)</span>
                            </div>
                            <div class="p-price-row">
                                <div>
                                    <span class="p-old-price">'.number_format($indirimli_fiyat, 2, ',', '.').'</span>
                                    <span class="p-price">'.number_format($u['fiyat'], 2, ',', '.').' TL</span>
                                </div>
                            </div>
                        </div>
                    </a>
                    <button class="add-to-cart-mini">SEPETE EKLE</button>
                </div>';
            }
        }
        ?>
    </div>

    <div class="info-bar">
        <div class="info-box">
            <i class="material-icons-outlined">local_shipping</i>
            <div>
                <h5>Hızlı Teslimat</h5>
                <p>Güvenilir kargo partnerlerimizle Türkiye'nin her yerine.</p>
            </div>
        </div>
        <div class="info-box">
            <i class="material-icons-outlined">verified_user</i>
            <div>
                <h5>Güvenli Ödeme</h5>
                <p>256-bit SSL sertifikası ile %100 güvenli alışveriş deneyimi.</p>
            </div>
        </div>
        <div class="info-box">
            <i class="material-icons-outlined">support_agent</i>
            <div>
                <h5>Girişimci Desteği</h5>
                <p>Sorularınız için kadın girişimcilerimize direkt ulaşın.</p>
            </div>
        </div>
        <div class="info-box">
            <i class="material-icons-outlined">assignment_return</i>
            <div>
                <h5>Kolay İade</h5>
                <p>14 gün içerisinde koşulsuz şartsız kolay iade hakkı.</p>
            </div>
        </div>
    </div>
</div>

<footer class="main-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="f-col">
                <h2 style="color:var(--white); font-size:32px; margin-bottom:20px; letter-spacing:-1.5px;">hepsi<span style="color:var(--primary)">KADIN</span></h2>
                <p>Türkiye'nin ilk ve en büyük kadın girişimci pazaryeri platformu. El emeği ve alın terini dijital dünya ile buluşturuyor, yerel ekonomiyi birlikte büyütüyoruz.</p>
                <div class="social-icons">
                    <i class="material-icons-outlined">facebook</i>
                    <i class="material-icons-outlined">camera_alt</i>
                    <i class="material-icons-outlined">language</i>
                    <i class="material-icons-outlined">alternate_email</i>
                </div>
            </div>
            <div class="f-col">
                <h4>Kurumsal</h4>
                <ul>
                    <li><i class="material-icons-outlined" style="font-size:16px;">info</i> Hakkımızda</li>
                    <li><i class="material-icons-outlined" style="font-size:16px;">auto_awesome</i> Başarı Hikayeleri</li>
                    <li><i class="material-icons-outlined" style="font-size:16px;">work_outline</i> Kariyer</li>
                    <li><i class="material-icons-outlined" style="font-size:16px;">mail_outline</i> İletişim</li>
                    <li><i class="material-icons-outlined" style="font-size:16px;">policy</i> Sürdürülebilirlik</li>
                </ul>
            </div>
            <div class="f-col">
                <h4>Kategoriler</h4>
                <ul>
                    <li>Takı & Aksesuar</li>
                    <li>Ev & Yaşam Tasarımları</li>
                    <li>Geleneksel Doğal Gıdalar</li>
                    <li>Sanat & Koleksiyon</li>
                    <li>Kişiye Özel Hediyeler</li>
                </ul>
            </div>
            <div class="f-col">
                <h4>Sözleşmeler</h4>
                <ul>
                    <li>Yardım & Destek</li>
                    <li>İptal & İade Koşulları</li>
                    <li>Kullanım Şartları</li>
                    <li>Gizlilik Politikası</li>
                    <li>KVKK Aydınlatma Metni</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2026 hepsiKADIN. Kadınların Gücüyle Geleceği Tasarlıyoruz. Tüm hakları saklıdır.</p>
            <div style="display:flex; gap:15px; align-items:center;">
                <span style="font-size:12px; color:#555;">Güvenle Öde:</span>
                <img src="https://img.icons8.com/color/40/000000/visa.png"/>
                <img src="https://img.icons8.com/color/40/000000/mastercard.png"/>
                <img src="https://img.icons8.com/color/40/000000/amex.png"/>
            </div>
        </div>
    </div>
</footer>

<script>
    // Küçük bir sayaç animasyonu
    let time = 15735; // saniye
    const timerElem = document.getElementById('countdown');
    setInterval(() => {
        time--;
        let h = Math.floor(time / 3600);
        let m = Math.floor((time % 3600) / 60);
        let s = time % 60;
        timerElem.innerHTML = `${h < 10 ? '0'+h : h}:${m < 10 ? '0'+m : m}:${s < 10 ? '0'+s : s}`;
    }, 1000);
</script>

</body>
</html>