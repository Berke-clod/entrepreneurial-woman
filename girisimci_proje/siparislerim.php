<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Giriş kontrolü - Giriş yoksa kapı dışarı
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: index.php");
    exit();
}

// Veritabanı bağlantısı
$host = "localhost"; $user = "root"; $pass = ""; $db = "kadin_girisimci_db";
$baglanti = @mysqli_connect($host, $user, $pass, $db);

$user_id = $_SESSION['kullanici_id'];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siparişlerim | hepsiKADIN</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <style>
        :root { --primary: #FF6000; --dark: #333; --light: #f1f3f5; --success: #2e7d32; --warning: #ffa000; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: var(--light); color: var(--dark); }
        
        .container { width: 95%; max-width: 1250px; margin: auto; }

        /* HEADER */
        header { background: white; padding: 15px 0; border-bottom: 1px solid #ddd; margin-bottom: 30px; }
        .header-main { display: flex; align-items: center; justify-content: space-between; }
        .logo { color: var(--primary); font-size: 32px; font-weight: 900; text-decoration: none; letter-spacing: -1.5px; }

        /* LAYOUT */
        .profile-layout { display: grid; grid-template-columns: 280px 1fr; gap: 30px; margin-bottom: 50px; }
        
        /* Sol Menü (Profil sayfanla birebir aynı tuttum, bütünlük bozulmasın) */
        .side-menu { background: white; padding: 20px; border-radius: 15px; border: 1px solid #eee; height: fit-content; }
        .side-menu ul { list-style: none; }
        .side-menu li { padding: 12px 0; border-bottom: 1px solid #f5f5f5; }
        .side-menu li a { text-decoration: none; color: #555; display: flex; align-items: center; gap: 10px; font-weight: 600; }
        .side-menu li a.active { color: var(--primary); }
        .side-menu li a:hover { color: var(--primary); }

        /* Sipariş Listesi */
        .order-card { background: white; padding: 30px; border-radius: 15px; border: 1px solid #eee; }
        .order-card h2 { margin-bottom: 25px; font-weight: 800; border-left: 5px solid var(--primary); padding-left: 15px; }

        .order-item { border: 1px solid #eee; border-radius: 10px; padding: 20px; margin-bottom: 15px; transition: 0.3s; }
        .order-item:hover { border-color: var(--primary); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        
        .order-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px dashed #eee; }
        .order-info { display: flex; gap: 20px; font-size: 13px; color: #666; }
        .order-info b { color: #222; }

        .order-status { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; }
        .status-hazirlaniyor { background: #fff8e1; color: var(--warning); }
        .status-kargoda { background: #e3f2fd; color: #1976d2; }
        .status-tamamlandi { background: #e8f5e9; color: var(--success); }

        .order-details { display: flex; align-items: center; justify-content: space-between; }
        .product-preview { display: flex; align-items: center; gap: 15px; }
        .product-preview img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #eee; }
        
        .price-tag { font-size: 18px; font-weight: 800; color: var(--dark); }

        .btn-detail { text-decoration: none; color: var(--primary); font-size: 14px; font-weight: 700; border: 1px solid var(--primary); padding: 8px 15px; border-radius: 6px; }
        .btn-detail:hover { background: var(--primary); color: white; }

        /* Boş Sipariş Durumu */
        .empty-orders { text-align: center; padding: 50px 0; }
        .empty-orders i { font-size: 60px; color: #ccc; margin-bottom: 15px; }
    </style>
</head>
<body>

<header>
    <div class="container header-main">
        <a href="index.php" class="logo">hepsiKADIN</a>
        <div style="font-weight: bold; color: #777;">Sipariş Geçmişim</div>
        <a href="index.php" style="text-decoration:none; color:var(--primary); font-weight:bold;">Alışverişe Devam Et</a>
    </div>
</header>

<div class="container">
    <div class="profile-layout">
        
        <aside class="side-menu">
            <ul>
                <li><a href="profil.php"><span class="material-icons-outlined">person</span> Kullanıcı Bilgilerim</a></li>
                <li><a href="siparislerim.php" class="active"><span class="material-icons-outlined">local_shipping</span> Siparişlerim</a></li>
                <li><a href="adreslerim.php"><span class="material-icons-outlined">location_on</span> Adreslerim</a></li>
                <li><a href="cikis.php" style="color:#d32f2f;"><span class="material-icons-outlined">logout</span> Çıkış Yap</a></li>
            </ul>
        </aside>

        <main class="order-card">
            <h2>Tüm Siparişlerim</h2>

            <?php
            // Siparişler tablosundan verileri çekiyoruz
            // Tablo yapısı: id, kullanici_id, toplam_tutar, durum, tarih
            $sorgu = mysqli_query($baglanti, "SELECT * FROM siparisler WHERE kullanici_id = '$user_id' ORDER BY tarih DESC");

            if (mysqli_num_rows($sorgu) > 0) {
                while ($siparis = mysqli_fetch_assoc($sorgu)) {
                    $durumClass = "status-" . strtolower(str_replace('ı', 'i', $siparis['durum']));
                    echo '
                    <div class="order-item">
                        <div class="order-header">
                            <div class="order-info">
                                <span>Sipariş Tarihi: <b>' . date("d.m.Y", strtotime($siparis['tarih'])) . '</b></span>
                                <span>Sipariş No: <b>#' . $siparis['id'] . '</b></span>
                            </div>
                            <span class="order-status ' . $durumClass . '">' . $siparis['durum'] . '</span>
                        </div>
                        <div class="order-details">
                            <div class="product-preview">
                                <img src="https://picsum.photos/seed/' . $siparis['id'] . '/100/100" alt="Ürün">
                                <div>
                                    <p style="font-weight: 600;">Sipariş Toplamı</p>
                                    <p class="price-tag">' . number_format($siparis['toplam_tutar'], 2, ',', '.') . ' TL</p>
                                </div>
                            </div>
                            <a href="siparis_detay.php?id=' . $siparis['id'] . '" class="btn-detail">SİPARİŞ DETAYI</a>
                        </div>
                    </div>';
                }
            } else {
                // Sipariş yoksa gösterilecek alan
                echo '
                <div class="empty-orders">
                    <i class="material-icons-outlined">shopping_bag</i>
                    <p style="color:#666; font-size:18px;">Henüz bir siparişiniz bulunmuyor.</p>
                    <a href="index.php" style="display:inline-block; margin-top:20px; color:var(--primary); font-weight:700;">Hemen Alışverişe Başla</a>
                </div>';
            }
            ?>
        </main>

    </div>
</div>

<footer style="text-align:center; padding:30px; color:#aaa; font-size:12px;">
    &copy; 2026 hepsiKADIN | Güvenli Alışverişin Adresi
</footer>

</body>
</html>