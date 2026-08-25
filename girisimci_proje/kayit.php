<?php
// 1. OTURUMU BAŞLAT
session_start();

// 2. VERİTABANI BAĞLANTISI
$host = "localhost"; 
$user = "root"; 
$pass = ""; 
$db   = "kadin_girisimci_db";
$baglanti = mysqli_connect($host, $user, $pass, $db);

// Veritabanı hatası kontrolü
if (!$baglanti) { die("Bağlantı hatası: " . mysqli_connect_error()); }

$mesaj = "";

// 3. KAYIT BUTONUNA BASILDIĞINDA
if (isset($_POST['kayit_ol'])) {
    $ad    = mysqli_real_escape_string($baglanti, $_POST['ad']);
    $mail  = mysqli_real_escape_string($baglanti, $_POST['email']);
    $sifre = password_hash($_POST['sifre'], PASSWORD_DEFAULT);

    $ekle = "INSERT INTO kullanicilar (ad_soyad, eposta, sifre) VALUES ('$ad', '$mail', '$sifre')";
    
    if (mysqli_query($baglanti, $ekle)) {
        $_SESSION['kullanici_id'] = mysqli_insert_id($baglanti);
        $_SESSION['kullanici_ad'] = $ad;

        header("Location: index.php");
        exit();
    } else {
        $mesaj = "Bir hata oluştu veya bu e-posta zaten kayıtlı!";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol | hepsiKADIN</title>
    <style>
        :root { --primary: #FF6000; --bg: #f0f2f5; }
        * { box-sizing: border-box; margin: 0; padding: 0; transition: 0.3s; }
        
        body { 
            font-family: 'Segoe UI', sans-serif; 
            background: var(--bg); 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; /* Yükseklik esnek olsun */
            padding: 20px; 
        }

        .kayit-kutu { 
            background: white; 
            padding: 40px; 
            border-radius: 24px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.1); 
            width: 100%; /* Mobilde tam genişlik */
            max-width: 420px; /* Bilgisayarda sabit genişlik */
            text-align: center; 
        }

        .logo { 
            color: var(--primary); 
            font-size: 35px; 
            font-weight: 900; 
            text-decoration: none; 
            display: block; 
            margin-bottom: 20px; 
            letter-spacing: -1.5px;
        }

        input { 
            width: 100%; 
            padding: 14px; 
            margin-bottom: 15px; 
            border: 2px solid #eee; 
            border-radius: 12px; 
            outline: none; 
            font-size: 15px;
        }

        input:focus { border-color: var(--primary); background: #fffcfb; }

        button { 
            width: 100%; 
            padding: 16px; 
            background: var(--primary); 
            color: white; 
            border: none; 
            border-radius: 12px; 
            font-weight: 800; 
            font-size: 16px; 
            cursor: pointer; 
            box-shadow: 0 4px 15px rgba(255, 96, 0, 0.2);
        }

        button:hover { background: #e65600; transform: translateY(-2px); }

        .hata { 
            background: #fff0f0; 
            color: #d93025; 
            padding: 10px; 
            border-radius: 8px; 
            margin-bottom: 15px; 
            font-size: 14px; 
            border: 1px solid #ffdada;
        }

        /* MOBİL İÇİN EKSTRA AYAR */
        @media (max-width: 480px) {
            .kayit-kutu { padding: 30px 20px; }
            .logo { font-size: 28px; }
        }
    </style>
</head>
<body>

<div class="kayit-kutu">
    <a href="index.php" class="logo">hepsiKADIN</a>
    <h2 style="margin-bottom: 10px; font-weight: 800;">Aramıza Katıl</h2>
    <p style="color:#777; margin-bottom: 25px; font-size: 14px;">Kadın girişimcilerin dünyasına ilk adımı at.</p>
    
    <?php if($mesaj != "") echo "<div class='hata'>$mesaj</div>"; ?>

    <form method="POST" action="">
        <input type="text" name="ad" placeholder="Ad Soyad" required>
        <input type="email" name="email" placeholder="E-posta Adresiniz" required>
        <input type="password" name="sifre" placeholder="Güçlü bir şifre oluştur" required>
        <button type="submit" name="kayit_ol">Kaydı Tamamla</button>
    </form>
    
    <p style="margin-top: 25px; font-size: 14px; color: #555;">
        Zaten hesabın var mı? <a href="giris.php" style="color:var(--primary); font-weight:bold; text-decoration: none;">Giriş Yap</a>
    </p>
</div>

</body>
</html>