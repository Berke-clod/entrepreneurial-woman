<?php
session_start();
$host = "localhost"; $user = "root"; $pass = ""; $db = "kadin_girisimci_db";
$baglanti = mysqli_connect($host, $user, $pass, $db);

$mesaj = "";

if (isset($_POST['giris_yap'])) {
    $mail = mysqli_real_escape_string($baglanti, $_POST['email']);
    $sifre = $_POST['sifre'];

    $sorgu = mysqli_query($baglanti, "SELECT * FROM kullanicilar WHERE eposta = '$mail'");
    
    if (mysqli_num_rows($sorgu) > 0) {
        $kullanici = mysqli_fetch_assoc($sorgu);
        // Şifre kontrolü
        if (password_verify($sifre, $kullanici['sifre'])) {
            $_SESSION['kullanici_id'] = $kullanici['id'];
            $_SESSION['kullanici_ad'] = $kullanici['ad_soyad'];
            header("Location: index.php");
            exit();
        } else {
            $mesaj = "Şifre hatalı!";
        }
    } else {
        $mesaj = "Bu e-posta ile kayıtlı kullanıcı bulunamadı!";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Giriş Yap | hepsiKADIN</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin:0; }
        .giris-kutu { background: white; padding: 40px; border-radius: 24px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); width: 400px; text-align: center; }
        .logo { color: #FF6000; font-size: 35px; font-weight: 900; text-decoration: none; display: block; margin-bottom: 20px; }
        input { width: 100%; padding: 14px; margin-bottom: 15px; border: 2px solid #eee; border-radius: 12px; outline: none; box-sizing: border-box; }
        button { width: 100%; padding: 16px; background: #FF6000; color: white; border: none; border-radius: 12px; font-weight: 800; cursor: pointer; }
        .hata { color: red; margin-bottom: 15px; font-size: 14px; }
        .alt-link { margin-top: 20px; font-size: 13px; }
    </style>
</head>
<body>

<div class="giris-kutu">
    <a href="index.php" class="logo">hepsiKADIN</a>
    <h2 style="margin-bottom: 20px;">Tekrar Hoş Geldin</h2>
    
    <?php if($mesaj != "") echo "<div class='hata'>$mesaj</div>"; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="E-posta Adresiniz" required>
        <input type="password" name="sifre" placeholder="Şifreniz" required>
        <button type="submit" name="giris_yap">Giriş Yap</button>
    </form>
    
    <div class="alt-link">
        Henüz hesabın yok mu? <a href="kayit.php" style="color:#FF6000; font-weight:bold;">Kayıt Ol</a>
    </div>
</div>

</body>
</html>