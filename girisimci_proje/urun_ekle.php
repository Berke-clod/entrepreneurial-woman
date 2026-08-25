<?php
include("baglan.php");

if(isset($_POST['urun_kaydet'])) {
    $ad = mysqli_real_escape_string($baglanti, $_POST['urun_adi']);
    $marka = mysqli_real_escape_string($baglanti, $_POST['marka']);
    $fiyat = $_POST['fiyat'];
    $kat = $_POST['kategori'];

    // RESİM YÜKLEME MOTORU
    $hedef_klasor = "img/"; // Resimlerin yükleneceği klasör
    if (!file_exists($hedef_klasor)) { mkdir($hedef_klasor, 0777, true); }
    
    $dosya_adi = $hedef_klasor . basename($_FILES["resim"]["name"]);
    
    if (move_uploaded_file($_FILES["resim"]["tmp_name"], $dosya_adi)) {
        $sql = "INSERT INTO urunler (urun_adi, marka, fiyat, urun_resim, kategori) 
                VALUES ('$ad', '$marka', '$fiyat', '$dosya_adi', '$kat')";
        
        if(mysqli_query($baglanti, $sql)) {
            $mesaj = "Ürün başarıyla vitrine eklendi!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ürün Yönetimi | hepsiKADIN</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; padding: 50px; }
        .admin-card { max-width: 600px; margin: auto; background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        h2 { color: #FF6000; margin-bottom: 30px; display: flex; align-items: center; gap: 10px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 700; font-size: 14px; }
        input, select { width: 100%; padding: 12px; border: 2px solid #eee; border-radius: 10px; outline: none; transition: 0.3s; }
        input:focus { border-color: #FF6000; }
        .btn-submit { background: #FF6000; color: white; border: none; padding: 15px; width: 100%; border-radius: 10px; font-weight: 800; cursor: pointer; font-size: 16px; margin-top: 10px; }
        .success-msg { background: #dcfce7; color: #166534; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; text-align: center; }
    </style>
</head>
<body>

<div class="admin-card">
    <h2><i class="material-icons-outlined">add_business</i> Yeni Ürün Ekle</h2>
    
    <?php if(isset($mesaj)) echo "<div class='success-msg'>$mesaj</div>"; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Ürün Adı</label>
            <input type="text" name="urun_adi" placeholder="Örn: El Örmesi Hasır Çanta" required>
        </div>

        <div class="form-group">
            <label>Marka / Girişimci Adı</label>
            <input type="text" name="marka" placeholder="Örn: Anadolu Kadınları Kooperatifi" required>
        </div>

        <div class="form-group" style="display:flex; gap:20px;">
            <div style="flex:1;">
                <label>Fiyat (TL)</label>
                <input type="number" step="0.01" name="fiyat" placeholder="0.00" required>
            </div>
            <div style="flex:1;">
                <label>Kategori</label>
                <select name="kategori">
                    <option>Takı & Aksesuar</option>
                    <option>Ev & Yaşam</option>
                    <option>Gıda</option>
                    <option>Giyim</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Ürün Fotoğrafı</label>
            <input type="file" name="resim" accept="image/*" required>
        </div>

        <button type="submit" name="urun_kaydet" class="btn-submit">ÜRÜNÜ VİTRİNE EKLE</button>
    </form>
    
    <div style="margin-top:20px; text-align:center;">
        <a href="kategori.php" style="color:#666; font-size:13px;">← Mağazaya Geri Dön</a>
    </div>
</div>

</body>
</html>