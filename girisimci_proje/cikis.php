<?php
session_start();
session_destroy(); // Tüm hafızayı sil
header("Location: index.php"); // Ana sayfaya yolla
exit();
?>