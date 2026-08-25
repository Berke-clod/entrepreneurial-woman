<?php
// 1. OTURUMU BAŞLAT
session_start();

// 2. VERİTABANI BAĞLANTISI
$host = "localhost"; $user = "root"; $pass = ""; $db = "kadin_girisimci_db";
$baglanti = @mysqli_connect($host, $user, $pass, $db);

// Kullanıcı giriş yapmış mı?
$isLoggedIn = isset($_SESSION['kullanici_id']);
$userName = $isLoggedIn ? $_SESSION['kullanici_ad'] : "Giriş Yap";

// SEPET KONTROLÜ (Session'da 'sepet' dizisi olduğunu varsayıyoruz)
// Eğer sepet boşsa veya tanımlı değilse boş dizi ata
$sepet = isset($_SESSION['sepet']) ? $_SESSION['sepet'] : [];
$toplamTutar = 0;
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sepetim | hepsiKADIN</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    
    <style>
        /* SENİN MEVCUT STİLLERİN (DOKUNULMADI) */
        * { box-sizing: border-box; margin: 0; padding: 0; transition: all 0.2s ease; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f6f6f6; color: #333; overflow-x: hidden; }
        .container { width: 95%; max-width: 1200px; margin: auto; }
        a { text-decoration: none; color: inherit; }
        header { background: white; padding: 15px 0; border-bottom: 1px solid #ddd; position: sticky; top: 0; z-index: 1000; }
        .header-main { display: flex; align-items: center; justify-content: space-between; gap: 20px; }
        .logo { color: #FF6000; font-size: 30px; font-weight: 900; letter-spacing: -1px; flex-shrink: 0; }
        .search-area { flex-grow: 1; max-width: 500px; position: relative; }
        .search-area input { width: 100%; padding: 10px 15px 10px 40px; border-radius: 8px; border: 1px solid #ddd; background: #f9f9f9; outline: none; }
        .search-area .search-icon { position: absolute; left: 12px; top: 10px; color: #999; }
        .header-actions { display: flex; align-items: center; gap: 20px; }
        .user-menu-wrapper { position: relative; padding: 5px 10px; border-radius: 10px; display: flex; align-items: center; cursor: pointer; border: 1px solid transparent; }
        .user-menu-wrapper:hover { background: #fff5ed; border-color: #ffe0cc; }
        .user-avatar { width: 40px; height: 40px; background: linear-gradient(135deg, #FF6000, #FF9E00); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(255, 96, 0, 0.2); margin-right: 10px; font-weight: bold; }
        .user-info { display: flex; flex-direction: column; line-height: 1.2; }
        .user-info .welcome { font-size: 11px; color: #888; font-weight: 600; }
        .user-info .name { font-size: 14px; font-weight: 700; color: #333; }
        .user-dropdown { position: absolute; top: 115%; right: 0; width: 230px; background: white; border-radius: 15px; box-shadow: 0 15px 50px rgba(0,0,0,0.15); border: 1px solid #eee; padding: 10px 0; display: none; z-index: 2000; animation: slideUp 0.3s ease; }
        .user-menu-wrapper:hover .user-dropdown { display: block; }
        .user-dropdown li { padding: 12px 20px; font-size: 13px; display: flex; align-items: center; gap: 12px; color: #555; cursor: pointer; }
        .user-dropdown li:hover { background: #fdf2e9; color: #FF6000; }
        .divider { height: 1px; background: #eee; margin: 8px 0; }

        /* YENİ EKLENEN SEPET DOLU STİLLERİ */
        .cart-grid { display: grid; grid-template-columns: 1fr 350px; gap: 30px; padding: 40px 0; }
        .cart-list { background: white; border-radius: 20px; padding: 25px; box-shadow: 0 4px 25px rgba(0,0,0,0.04); }
        .cart-item { display: flex; align-items: center; gap: 20px; padding: 20px 0; border-bottom: 1px solid #eee; }
        .cart-item:last-child { border-bottom: none; }
        .cart-item img { width: 100px; height: 100px; object-fit: cover; border-radius: 12px; }
        .cart-item-info { flex-grow: 1; }
        .cart-item-info h4 { font-size: 16px; margin-bottom: 5px; }
        .cart-item-price { font-size: 18px; font-weight: 800; color: #FF6000; }
        
        .cart-summary { background: white; border-radius: 20px; padding: 25px; box-shadow: 0 4px 25px rgba(0,0,0,0.04); height: fit-content; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 15px; }
        .summary-total { border-top: 2px solid #f6f6f6; padding-top: 15px; margin-top: 15px; font-size: 20px; font-weight: 900; color: #FF6000; }
        .btn-complete { background: #FF6000; color: white; width: 100%; padding: 15px; border-radius: 12px; border: none; font-weight: 800; cursor: pointer; margin-top: 20px; box-shadow: 0 6px 20px rgba(255, 96, 0, 0.2); }
        
        .cart-section { padding: 40px 0; }
        .cart-empty-card { background: white; border-radius: 20px; padding: 60px 20px; text-align: center; box-shadow: 0 4px 25px rgba(0,0,0,0.04); }
        .cart-big-icon { width: 120px; height: 120px; background: #fff5ed; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; color: #FF6000; }
        .cart-big-icon i { font-size: 60px; }
        .btn-go-home { background: #FF6000; color: white; padding: 16px 40px; border-radius: 12px; font-weight: 800; display: inline-block; box-shadow: 0 6px 20px rgba(255, 96, 0, 0.3); }
        .info-bar { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-top: 40px; }
        .info-box { background: white; padding: 20px; border-radius: 15px; border: 1px solid #eee; display: flex; align-items: center; gap: 15px; }
        .info-box .icon-circle { width: 45px; height: 45px; background: #fdf2e9; color: #FF6000; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }

        @keyframes slideUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

    <header>
        <div class="container header-main">
            <a href="index.php" class="logo">hepsiKADIN</a>
            <div class="search-area">
                <span class="material-icons-outlined search-icon">search</span>
                <input type="text" placeholder="Ürün veya kategori ara...">
            </div>
            <div class="header-actions">
                <div class="user-menu-wrapper">
                    <div class="user-avatar">
                        <?php echo $isLoggedIn ? mb_substr($userName, 0, 1) : '<span class="material-icons-outlined">person</span>'; ?>
                    </div>
                    <div class="user-info">
                        <span class="welcome">Hoş Geldin,</span>
                        <span class="name"><?php echo $userName; ?></span>
                    </div>
                    <span class="material-icons-outlined" style="color: #FF6000; margin-left: 5px;">expand_more</span>
                    <div class="user-dropdown">
                        <?php if($isLoggedIn): ?>
                            <a href="siparislerim.php"><li><i class="material-icons-outlined">local_mall</i> Siparişlerim</li></a>
                            <li><i class="material-icons-outlined">favorite_border</i> Beğendiklerim</li>
                            <div class="divider"></div>
                            <a href="profil.php"><li><i class="material-icons-outlined">manage_accounts</i> Profil Ayarları</li></a>
                            <a href="cikis.php"><li style="color:red"><i class="material-icons-outlined">logout</i> Güvenli Çıkış</li></a>
                        <?php else: ?>
                            <a href="giris.php"><li><i class="material-icons-outlined">login</i> Giriş Yap</li></a>
                            <a href="kayit.php"><li><i class="material-icons-outlined">person_add</i> Üye Ol</li></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <?php if(empty($sepet)): ?>
            <section class="cart-section">
                <div class="cart-empty-card">
                    <div class="cart-big-icon">
                        <span class="material-icons-outlined">shopping_basket</span>
                    </div>
                    <h2>Sepetin şu an boş!</h2>
                    <p>Girişimci kadınlarımızın el emeği göz nuru ürünleri seni bekliyor.</p>
                    <a href="index.php" class="btn-go-home">Alışverişe Başla</a>
                </div>
            </section>
        <?php else: ?>
            <div class="cart-grid">
                <div class="cart-list">
                    <h2>Sepetim (<?php echo count($sepet); ?> Ürün)</h2>
                    <?php 
                    foreach($sepet as $key => $item): 
                        $toplamTutar += $item['fiyat'];
                    ?>
                    <div class="cart-item">
                        <img src="<?php echo $item['resim']; ?>" alt="Ürün">
                        <div class="cart-item-info">
                            <h4><?php echo $item['ad']; ?></h4>
                            <p style="font-size:12px; color:#888;">Satıcı: Kadın Girişimci</p>
                        </div>
                        <div class="cart-item-price"><?php echo number_format($item['fiyat'], 2, ',', '.'); ?> TL</div>
                        <a href="sepet_islem.php?sil=<?php echo $key; ?>" style="color:#ccc; margin-left:20px;"><i class="material-icons-outlined">delete</i></a>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary">
                    <h3>Sipariş Özeti</h3>
                    <div class="summary-row" style="margin-top:20px;">
                        <span>Ürün Toplamı</span>
                        <span><?php echo number_format($toplamTutar, 2, ',', '.'); ?> TL</span>
                    </div>
                    <div class="summary-row">
                        <span>Kargo Toplam</span>
                        <span style="color:green;">Bedava</span>
                    </div>
                    <div class="summary-row summary-total">
                        <span>Toplam</span>
                        <span><?php echo number_format($toplamTutar, 2, ',', '.'); ?> TL</span>
                    </div>
                    <button class="btn-complete">ALIŞVERİŞİ TAMAMLA</button>
                </div>
            </div>
        <?php endif; ?>

        <div class="info-bar" style="margin-bottom: 50px;">
            <div class="info-box">
                <div class="icon-circle"><span class="material-icons-outlined">rocket_launch</span></div>
                <div><h4>Hızlı Teslimat</h4><p>Ürünlerin en kısa sürede kapında.</p></div>
            </div>
            <div class="info-box">
                <div class="icon-circle"><span class="material-icons-outlined">verified</span></div>
                <div><h4>Güvenli Ödeme</h4><p>Ödemelerin %100 güvende.</p></div>
            </div>
            <div class="info-box">
                <div class="icon-circle"><span class="material-icons-outlined">support_agent</span></div>
                <div><h4>Canlı Destek</h4><p>Her an yanındayız.</p></div>
            </div>
            <div class="info-box">
                <div class="icon-circle"><span class="material-icons-outlined">volunteer_activism</span></div>
                <div><h4>Kadına Destek</h4><p>Emeğe değer kat.</p></div>
            </div>
        </div>
    </div>

    <footer style="text-align: center; padding: 30px; color: #999; font-size: 13px;">
        &copy; 2026 hepsiKADIN | Kadın Emeği Platformu
    </footer>

</body>
</html>