<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$baglanti = mysqli_connect("localhost", "root", "", "kadin_girisimci_db");
if (!$baglanti) { die("Bağlantı hatası: " . mysqli_connect_error()); }

// 1. ÖNCE SATICILARI KONTROL EDELİM (Foreign Key hatasını önlemek için)
$satici_kontrol = mysqli_query($baglanti, "SELECT id FROM saticilar LIMIT 5");

if (mysqli_num_rows($satici_kontrol) == 0) {
    echo "Saticilar tablosu boş! Örnek satıcılar ekleniyor...<br>";
    mysqli_query($baglanti, "INSERT INTO saticilar (ad_soyad) VALUES ('Zeynep Yılmaz'), ('Fatma Demir'), ('Ayşe Kaya'), ('Emine Çelik'), ('Hatice Ak')");
    // Tekrar çekelim
    $satici_kontrol = mysqli_query($baglanti, "SELECT id FROM saticilar");
}

// Mevcut satıcı ID'lerini bir diziye toplayalım
$satici_ids = [];
while($row = mysqli_fetch_assoc($satici_kontrol)) {
    $satici_ids[] = $row['id'];
}

// 2. ÜRÜNLER TABLOSUNU TEMİZLE
mysqli_query($baglanti, "SET FOREIGN_KEY_CHECKS = 0;"); // Temizlik sırasında hata vermemesi için
mysqli_query($baglanti, "TRUNCATE TABLE urunler");
mysqli_query($baglanti, "SET FOREIGN_KEY_CHECKS = 1;");

$markalar = ["Lila Tasarım", "Anadolu El Emeği", "Zeynep Atölye", "Girişimci Kadınlar", "Modern Sanat", "Doğa Ana"];
$urun_tipleri = ["El Örmesi Kazak", "Doğal Çilek Reçeli", "Seramik Kupa", "Gümüş Küpe", "Hasır Çanta", "Makrome Süs"];

echo "<h2>Foreign Key Kurallarına Uygun 1000 Ürün Yükleniyor...</h2>";

for ($i = 1; $i <= 1000; $i++) {
    $isim = $urun_tipleri[array_rand($urun_tipleri)] . " - " . $i;
    $marka = $markalar[array_rand($markalar)];
    $fiyat = rand(150, 2500);
    $stok = rand(5, 50);
    $resim = "https://picsum.photos/seed/" . $i . "/400/400";
    
    // Gerçekten var olan bir satıcı ID'si seçiyoruz
    $rastgele_satici = $satici_ids[array_rand($satici_ids)];

    $ekle = "INSERT INTO urunler (urun_adi, fiyat, marka, stok_adedi, urun_resim, satici_id) 
             VALUES ('$isim', '$fiyat', '$marka', '$stok', '$resim', '$rastgele_satici')";
    
    if(!mysqli_query($baglanti, $ekle)) {
        die("Hata: " . mysqli_error($baglanti));
    }
}

echo "<h1 style='color:green;'>SİSTEM KURALLARINA UYGUN ŞEKİLDE TAMAMLANDI!</h1>";
echo "<p>Artık tüm ürünler gerçek satıcılara bağlı.</p>";
echo "<a href='kategori.php'>Ürünleri Görmeye Git</a>";
?>