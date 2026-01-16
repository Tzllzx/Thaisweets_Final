<?php
session_start();

// รับค่าจากฟอร์ม
$id    = $_POST['id'] ?? null;
$price = $_POST['price'] ?? null;
$qty   = $_POST['qty'] ?? 1;

// ตรวจสอบข้อมูล
if (!$id || !$price) {
    header("Location: products.php");
    exit;
}

// สร้างตะกร้าถ้ายังไม่มี
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ถ้ามีสินค้านี้ในตะกร้าแล้ว → เพิ่มจำนวน
if (isset($_SESSION['cart'][$id])) {
    $_SESSION['cart'][$id]['qty'] += $qty;
} else {
    // เพิ่มสินค้าใหม่
    $_SESSION['cart'][$id] = [
        'id'    => $id,
        'name'  => $_POST['name'] ?? '',
        'price' => $price,
        'qty'   => $qty
    ];
}

// กลับไปหน้าตะกร้า
header("Location: cart.php");
exit;
