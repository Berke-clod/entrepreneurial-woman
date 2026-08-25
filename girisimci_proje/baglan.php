<?php
$host = "localhost";
$kullanici = "root";
$sifre = ""; // XAMPP varsayılan şifre boştur
$veritabani = "kadin_girisimci_db";

$baglanti = mysqli_connect($host, $kullanici, $sifre, $veritabani);

if (!$baglanti) {
    die("Veritabanı bağlantısı başarısız: " . mysqli_connect_error());
}
// Bağlantı başarılıysa hata vermeyecek, sessizce çalışacak.
?>