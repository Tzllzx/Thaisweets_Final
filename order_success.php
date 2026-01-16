<?php
session_start();
require_once "db.php";

$order_id = $_GET['order_id'] ?? 0;

// ดึงเลข Order Code มาแสดง
$stmt = $pdo->prepare("SELECT order_code FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();
$display_code = $order ? $order['order_code'] : 'ไม่พบรหัส';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สั่งซื้อสำเร็จ - ร้านขนมไทย</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        body { font-family: 'Sarabun', sans-serif; background-color: #fdfaf5; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .success-card { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); text-align: center; max-width: 400px; width: 90%; }
        .icon { font-size: 60px; color: #2e7d32; margin-bottom: 20px; }
        h2 { color: #8B5A2B; margin-bottom: 10px; }
        p { color: #666; margin-bottom: 30px; }
        .btn-group { display: flex; flex-direction: column; gap: 10px; }
        .btn-main { background: #8B5A2B; color: white; padding: 12px; border-radius: 10px; text-decoration: none; font-weight: bold; }
        .btn-sub { color: #8B5A2B; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>
    <div class="success-card">
        <div class="icon">✅</div>
        <h2>สั่งซื้อสำเร็จ!</h2>
        <p>ขอบคุณที่อุดหนุนขนมไทยร้านเรา<br>
        เลขออเดอร์ของคุณคือ: <strong>#<?= htmlspecialchars($display_code) ?></strong></p>
        
        <div class="btn-group">
            <a href="my_orders.php" class="btn-main">ดูประวัติการสั่งซื้อ</a>
            <a href="index.php" class="btn-sub">กลับหน้าหลัก</a>
        </div>
    </div>
</body>
</html>