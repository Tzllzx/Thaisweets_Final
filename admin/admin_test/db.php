<?php
$host = 'localhost';
$db   = 'thai_sweets'; // ชื่อฐานข้อมูลของคุณ
$user = 'root';        // user ของ xampp ปกติคือ root
$pass = '';            // password ของ xampp ปกติคือค่าว่าง
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options); // สร้างตัวแปร $pdo
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>