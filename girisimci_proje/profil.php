<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Giriş kontrolü - Oturum açılmamışsa ana sayfaya gönder
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: index.php");
    exit();
}

// Veritabanı bağlantısı
$host = "localhost"; $user = "root"; $pass = ""; $db = "kadin_girisimci_db";
$baglanti = @mysqli_connect($host, $user, $pass, $db);

$user_id = $_SESSION['kullanici_id'];
$mesaj = "";

// Bilgileri Güncelleme İşlemi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $yeni_ad = mysqli_real_escape_string($baglanti, $_POST['ad']);
    $yeni_email = mysqli_real_escape_string($baglanti, $_POST['email']);
    $yeni_tel = mysqli_real_escape_string($baglanti, $_POST['telefon']);

    $guncelle = mysqli_query($baglanti, "UPDATE kullanicilar SET kullanici_ad='$yeni_ad', email='$yeni_email', telefon='$yeni_tel' WHERE id='$user_id'");
    
    if ($guncelle) {
        $_SESSION['kullanici_ad'] = $yeni_ad; // Session ismini de tazele
        $mesaj = "<div style='padding:15px; background:#e8f5e9; color:#2e7d32; border-radius:8px; margin-bottom:20px;'>Bilgileriniz başarıyla güncellendi.</div>";
    } else {
        $mesaj = "<div style='padding:15px; background:#ffebee; color:#c62828; border-radius:8px; margin-bottom:20px;'>Bir hata oluştu!</div>";
    }
}

// Güncel kullanıcı verilerini çek
$sorgu = mysqli_query($baglanti, "SELECT * FROM kullanicilar WHERE id='$user_id'");
$kullanici = mysqli_fetch_assoc($sorgu);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilim | hepsiKADIN</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <style>
        :root { --primary: #FF6000; --dark: #333; --light: #f1f3f5; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: var(--light); color: var(--dark); }
        
        .container { width: 95%; max-width: 1250px; margin: auto; }

        /* HEADER (Ana sayfa ile aynı) */
        header { background: white; padding: 15px 0; border-bottom: 1px solid #ddd; margin-bottom: 30px; }
        .header-main { display: flex; align-items: center; justify-content: space-between; }
        .logo { color: var(--primary); font-size: 32px; font-weight: 900; text-decoration: none; letter-spacing: -1.5px; }

        /* PROFİL PANELİ */
        .profile-layout { display: grid; grid-template-columns: 280px 1fr; gap: 30px; margin-bottom: 50px; }
        
        /* Sol Menü */
        .side-menu { background: white; padding: 20px; border-radius: 15px; border: 1px solid #eee; height: fit-content; }
        .side-menu ul { list-style: none; }
        .side-menu li { padding: 12px 0; border-bottom: 1px solid #f5f5f5; }
        .side-menu li a { text-decoration: none; color: #555; display: flex; align-items: center; gap: 10px; font-weight: 600; }
        .side-menu li a.active { color: var(--primary); }
        .side-menu li a:hover { color: var(--primary); }

        /* Sağ Form Alanı */
        .profile-card { background: white; padding: 40px; border-radius: 15px; border: 1px solid #eee; }
        .profile-card h2 { margin-bottom: 30px; font-weight: 800; color: var(--dark); border-left: 5px solid var(--primary); padding-left: 15px; }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group label { font-size: 14px; font-weight: 700; color: #666; }
        .form-group input { padding: 12px; border-radius: 8px; border: 2px solid #eee; outline: none; transition: 0.3s; font-size: 15px; }
        .form-group input:focus { border-color: var(--primary); }

        .update-btn { background: var(--primary); color: white; border: none; padding: 15px 30px; border-radius: 8px; cursor: pointer; font-weight: 800; font-size: 16px; margin-top: 20px; transition: 0.3s; }
        .update-btn:hover { background: #e65600; transform: translateY(-2px); }

        @media (max-width: 768px) {
            .profile-layout { grid-template-columns: 1fr; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<header>
    <div class="container header-main">
        <a href="index.php" class="logo">hepsiKADIN</a>
        <div style="font-weight: bold; color: #777;">Profil Yönetimi</div>
        <a href="index.php" style="text-decoration:none; color:var(--primary); font-weight:bold;">Alışverişe Dön</a>
    </div>
</header>

<div class="container">
    <div class="profile-layout">
        
        <aside class="side-menu">
            <ul>
                <li><a href="profil.php" class="active"><span class="material-icons-outlined">person</span> Kullanıcı Bilgilerim</a></li>
                <li><a href="siparislerim.php"><span class="material-icons-outlined">local_shipping</span> Siparişlerim</a></li>
                <li><a href="adreslerim.php"><span class="material-icons-outlined">location_on</span> Adreslerim</a></li>
                <li><a href="cikis.php" style="color:#d32f2f;"><span class="material-icons-outlined">logout</span> Çıkış Yap</a></li>
            </ul>
        </aside>

        <main class="profile-card">
            <h2>Kişisel Bilgilerim</h2>
            <?php echo $mesaj; ?>

            <form action="" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Adınız Soyadınız</label>
                        <input type="text" name="ad" value="<?php echo htmlspecialchars($kullanici['kullanici_ad']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>E-Posta Adresiniz</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($kullanici['email']); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Telefon Numaranız</label>
                        <input type="text" name="telefon" value="<?php echo htmlspecialchars($kullanici['telefon'] ?? ''); ?>" placeholder="05xx xxx xx xx">
                    </div>
                    <div class="form-group">
                        <label>Kayıt Tarihi</label>
                        <input type="text" value="<?php echo $kullanici['kayit_tarihi'] ?? 'Belirtilmemiş'; ?>" readonly style="background:#f9f9f9; color:#999; cursor:not-allowed;">
                    </div>
                </div>

                <button type="submit" class="update-btn">DEĞİŞİKLİKLERİ KAYDET</button>
            </form>
        </main>

    </div>
</div>

<footer style="text-align:center; padding:30px; color:#aaa; font-size:12px;">
    &copy; 2026 hepsiKADIN | Güvenli Profil Yönetimi
</footer>

</body>
</html>