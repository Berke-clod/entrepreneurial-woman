<?php
error_reporting(0);
include("baglan.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 1;
$sorgu = mysqli_query($baglanti, "SELECT * FROM urunler WHERE id = $id");
$u = mysqli_fetch_assoc($sorgu);

if(!$u) { 
    $u = ['urun_adi' => 'Örnek Ürün', 'fiyat' => 750, 'marka' => 'hepsiKADIN Özel', 'stok_adedi' => 10];
}
$img = !empty($u['urun_resim']) ? $u['urun_resim'] : "https://picsum.photos/seed/shop/600/600";
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?php echo $u['urun_adi']; ?> | hepsiKADIN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700;800&display=swap');
        
        :root { --hb-orange: #FF6000; --hb-blue: #005fcc; --text: #333; --border: #e2e2e2; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Open Sans', sans-serif; background-color: #fff; color: var(--text); }
        .container { width: 1250px; margin: auto; }

        /* HEADER - HEPSİBURADA STİLİ */
        header { background: #fff; border-bottom: 1px solid var(--border); padding: 15px 0; position: sticky; top: 0; z-index: 999; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .header-main { display: flex; align-items: center; justify-content: space-between; }
        .logo { font-size: 32px; font-weight: 800; color: var(--hb-orange); text-decoration: none; letter-spacing: -1.5px; }
        
        .search-area { flex: 1; margin: 0 50px; position: relative; }
        .search-area input { width: 100%; padding: 12px 15px 12px 45px; border: 2px solid #eee; border-radius: 8px; background: #f5f5f5; outline: none; font-size: 14px; }
        .search-area i { position: absolute; left: 15px; top: 14px; color: #999; font-size: 18px; }
        .search-btn { position: absolute; right: 5px; top: 5px; background: var(--hb-orange); color: white; border: none; padding: 7px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; }

        .nav-actions { display: flex; gap: 25px; align-items: center; }
        .action-item { text-align: center; color: #666; cursor: pointer; font-size: 12px; font-weight: 700; text-decoration: none; }
        .action-item i { display: block; font-size: 22px; margin-bottom: 4px; color: #333; }
        .action-item:hover { color: var(--hb-orange); }

        /* ÜRÜN İÇERİK */
        .product-page { display: grid; grid-template-columns: 550px 1fr 320px; gap: 40px; margin-top: 30px; }
        
        /* SOL: RESİM VE FAVORİ */
        .img-box { position: relative; border: 1px solid var(--border); border-radius: 12px; padding: 20px; }
        .img-box img { width: 100%; border-radius: 8px; }
        .heart-btn { position: absolute; top: 20px; right: 20px; background: #fff; width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.1); cursor: pointer; color: #bbb; transition: 0.3s; }
        .heart-btn:hover { color: #ff4757; }

        /* ORTA: BİLGİLER */
        .info-box .brand { color: var(--hb-blue); font-weight: 700; font-size: 15px; text-decoration: none; }
        .info-box h1 { font-size: 26px; font-weight: 600; margin: 10px 0; color: #222; }
        .rating { color: #ffc107; margin-bottom: 20px; font-size: 18px; }

        /* SAĞ: SATIN ALMA */
        .buy-card { border: 1px solid var(--border); border-radius: 12px; padding: 25px; box-shadow: 0 8px 20px rgba(0,0,0,0.06); }
        .price-big { font-size: 36px; font-weight: 800; color: #222; margin-bottom: 20px; }
        .cart-btn { width: 100%; background: var(--hb-orange); color: white; border: none; padding: 18px; border-radius: 10px; font-size: 18px; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 12px; transition: 0.2s; }
        .cart-btn:hover { background: #e55600; transform: translateY(-2px); }

        /* FOOTER - KOYU VE DOLU */
        footer { background: #1a1a1a; color: #fff; padding: 60px 0; margin-top: 80px; }
        .footer-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 40px; }
        .f-col h4 { margin-bottom: 25px; font-size: 18px; color: var(--hb-orange); }
        .f-col ul { list-style: none; }
        .f-col li { margin-bottom: 12px; font-size: 14px; color: #ccc; cursor: pointer; }
        .f-col li:hover { color: #fff; text-decoration: underline; }
        
        .footer-bottom { border-top: 1px solid #333; margin-top: 40px; padding-top: 20px; text-align: center; color: #777; font-size: 12px; }
    </style>
</head>
<body>

<header>
    <div class="container header-main">
        <a href="index.php" class="logo">hepsiKADIN</a>
        
        <div class="search-area">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" placeholder="Ürün, kategori veya marka ara...">
            <button class="search-btn">ARA</button>
        </div>
        
        <div class="nav-actions">
            <a class="action-item"><i class="fa-regular fa-user"></i>Giriş Yap</a>
            <a class="action-item"><i class="fa-regular fa-heart"></i>Favorilerim</a>
            <a class="action-item" href="sepetim.php"><i class="fa-solid fa-cart-shopping"></i>Sepetim</a>
        </div>
    </div>
</header>

<div class="container product-page">
    <div class="img-box">
        <div class="heart-btn"><i class="fa-solid fa-heart"></i></div>
        <img src="<?php echo $img; ?>">
    </div>

    <div class="info-box">
        <a href="#" class="brand"><?php echo $u['marka']; ?></a>
        <h1><?php echo $u['urun_adi']; ?></h1>
        <div class="rating">★★★★★ <span style="color:var(--hb-blue); font-size:13px; margin-left:10px;">250 Yorum</span></div>
        
        <div style="background:#f6f6f6; padding:20px; border-radius:8px; line-height:1.8;">
            <p><b>Kadın Girişimci Ürünü:</b> Bu ürünün geliri doğrudan yerel üretici kadınlarımıza destek olarak aktarılmaktadır.</p>
        </div>
    </div>

    <div class="buy-section">
        <div class="buy-card">
            <div style="color:#999; text-decoration:line-through; font-size:16px;"><?php echo number_format($u['fiyat']*1.2, 2); ?> TL</div>
            <div class="price-big"><?php echo number_format($u['fiyat'], 2); ?> TL</div>
            
            <button class="cart-btn">
                <i class="fa-solid fa-cart-plus"></i> SEPETE EKLE
            </button>
            
            <div style="margin-top:20px; font-size:13px; color:#1e7e34; font-weight:700;">
                <i class="fa-solid fa-truck-fast"></i> ÜCRETSİZ KARGO
            </div>
        </div>
    </div>
</div>

<footer>
    <div class="container footer-grid">
        <div class="f-col">
            <h4>Kurumsal</h4>
            <ul><li>Hakkımızda</li><li>Sürdürülebilirlik</li><li>İletişim</li></ul>
        </div>
        <div class="f-col">
            <h4>Kategoriler</h4>
            <ul><li>Takı & Aksesuar</li><li>Ev & Yaşam</li><li>Doğal Gıda</li></ul>
        </div>
        <div class="f-col">
            <h4>Yardım</h4>
            <ul><li>İade Koşulları</li><li>Güvenli Alışveriş</li><li>S.S.S</li></ul>
        </div>
        <div class="f-col">
            <h4>Bizi Takip Edin</h4>
            <div style="display:flex; gap:20px; font-size:24px;">
                <i class="fa-brands fa-facebook"></i><i class="fa-brands fa-instagram"></i><i class="fa-brands fa-youtube"></i>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        © 2026 hepsiKADIN Pazaryeri A.Ş. Tüm hakları saklıdır.
    </div>
</footer>

</body>
</html>