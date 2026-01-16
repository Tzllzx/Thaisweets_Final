<?php
session_start();
require_once "../../db.php"; // ตรวจสอบว่า db.php อยู่ที่ root หรือใน admin_test

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $receiver_name = $_POST['receiver_name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $total_price = $_POST['total_price'];
    $order_code = 'OD' . date('YmdHis'); // สร้างรหัสออเดอร์

    // จัดการอัปโหลดสลิป
    $slip_name = null;
    if (isset($_FILES['slip_image']) && $_FILES['slip_image']['error'] == 0) {
        $target_dir = "../../assets/slips/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        $ext = pathinfo($_FILES["slip_image"]["name"], PATHINFO_EXTENSION);
        $slip_name = "slip_" . time() . "." . $ext;
        move_uploaded_file($_FILES["slip_image"]["tmp_name"], $target_dir . $slip_name);
    }

    // บันทึกข้อมูล
    $sql = "INSERT INTO orders (order_code, user_id, receiver_name, phone, address, total_price, status, slip_image, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, 'waiting_verification', ?, NOW())";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$order_code, $user_id, $receiver_name, $phone, $address, $total_price, $slip_name])) {
        unset($_SESSION['cart']); // ล้างตะกร้า
        echo "<!DOCTYPE html><html><head><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'สั่งซื้อสำเร็จ!',
                text: 'ระบบกำลังรอตรวจสอบหลักฐานการโอนเงิน',
                confirmButtonColor: '#a96a2d'
            }).then(() => { window.location = 'my_orders_test.php'; });
        </script></body></html>";
    }
}
?>