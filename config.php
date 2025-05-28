<?php

$db_host = 'localhost';
$db_name = 'kutuphane_takip';
$db_user = 'root';
$db_pass = 'yeni_sifren';


try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


date_default_timezone_set('Europe/Istanbul');
?>