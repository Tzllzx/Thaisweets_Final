<?php
session_start();
require_once "db.php"; // ปรับ Path ตามโครงสร้างโฟลเดอร์ของคุณ

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $receiver_name = $_POST['receiver_name'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
    $payment_method = $_POST['payment_method'];
    $total_price = $_POST['total_price'];
    $order_code = 'TH' . time(); // สร้างรหัสออเดอร์สุ่ม

    // 1. จัดการอัปโหลดสลิป
    $slip_name = "";
    if (isset($_FILES['slip_image']) && $_FILES['slip_image']['error'] == 0) {
        $target_dir = "assets/slips/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $ext = pathinfo($_FILES["slip_image"]["name"], PATHINFO_EXTENSION);
        $slip_name = "slip_" . time() . "_" . uniqid() . "." . $ext;
        move_uploaded_file($_FILES["slip_image"]["tmp_name"], $target_dir . $slip_name);
    }

    try {
        $pdo->beginTransaction();

        // 2. บันทึกลงตาราง orders (สถานะเริ่มต้นเป็น waiting_verification)
        $sql = "INSERT INTO orders (order_code, user_id, receiver_name, phone, address, total_price, payment_method, slip_image, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'waiting_verification', NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$order_code, $user_id, $receiver_name, $phone, $address, $total_price, $payment_method, $slip_name]);
        $order_id = $pdo->lastInsertId();

        // 3. ย้ายรายการจากตะกร้าลงตาราง order_items (ถ้ามีตารางแยก)
        foreach ($_SESSION['cart'] as $item) {
            $sql_item = "INSERT INTO order_items (order_id, product_id, product_name, price, qty) VALUES (?, ?, ?, ?, ?)";
            $pdo->prepare($sql_item)->execute([$order_id, $item['id'], $item['name'], $item['price'], $item['qty']]);
        }

        $pdo->commit();
        unset($_SESSION['cart']); // ล้างตะกร้า

        // แสดงผลสำเร็จด้วย SweetAlert2
        echo "<!DOCTYPE html><html><head><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'สั่งซื้อสำเร็จ!',
                text: 'ออเดอร์เลขที่ $order_code อยู่ระหว่างรอตรวจสอบสลิป',
                confirmButtonColor: '#a96a2d'
            }).then(() => { window.location = 'my_orders.php'; });
        </script></body></html>";

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
}