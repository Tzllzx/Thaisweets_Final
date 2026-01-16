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

// โค้ดเดิมของคุณ...
try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     
     // --- ส่วนที่เพิ่มใหม่: อัปเดตเวลาใช้งานล่าสุด ---
  if (isset($_SESSION['user_id']) && isset($pdo)) {
    // อัปเดตเวลาการทำงานล่าสุดทุกครั้งที่มีการโหลดหน้าเว็บ
    $pdo->prepare("UPDATE users SET last_active = NOW() WHERE id = ?")
        ->execute([$_SESSION['user_id']]);
}
     // ------------------------------------------

} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}