<?php
session_start();

$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? '';

if ($id !== null && isset($_SESSION['cart'][$id])) {
    if ($action === 'increase') {
        // เพิ่มจำนวน
        $_SESSION['cart'][$id]['qty'] += 1;
    } 
    elseif ($action === 'decrease') {
        // ลดจำนวน
        if ($_SESSION['cart'][$id]['qty'] > 1) {
            $_SESSION['cart'][$id]['qty'] -= 1;
        } else {
            // ถ้าเหลือ 1 แล้วกดลด ให้ลบทิ้งเลย
            unset($_SESSION['cart'][$id]);
        }
    }
}

// กลับไปหน้าตะกร้า
header("Location: cart_test.php");
exit;