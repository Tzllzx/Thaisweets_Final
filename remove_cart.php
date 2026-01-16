<?php
session_start();

// รับค่า ID สินค้าที่ต้องการลบ
$id = $_GET['id'] ?? null;

if ($id !== null) {
    // ตรวจสอบว่ามีสินค้านี้อยู่ในตะกร้าจริงไหม
    if (isset($_SESSION['cart'][$id])) {
        // ลบสินค้าตัวนี้ออกจาก Session
        unset($_SESSION['cart'][$id]);
    }
}

// ลบเสร็จแล้วให้เด้งกลับไปที่หน้าตะกร้าสินค้า
header("Location: cart.php");
exit;