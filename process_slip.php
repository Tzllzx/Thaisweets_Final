<?php
session_start();
// ถอยกลับ 2 ระดับไปหา db.php
require_once "db.php";

// เตรียม Header สำหรับใช้ SweetAlert2
echo '<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>body { font-family: "Sarabun", sans-serif; }</style>
</head>
<body>';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['slip_image'])) {
    $order_id = $_POST['order_id'];
    $transfer_time = $_POST['transfer_time'];
    $user_id = $_SESSION['user_id'];

    // 1. จัดการเรื่องไฟล์รูปภาพ (ถอยออกจาก admin/admin_test/ ไปที่ assets/slips/)
    $target_dir = "assets/slips/"; 
    if (!is_dir($target_dir)) { 
        mkdir($target_dir, 0777, true); 
    }

    $file_ext = strtolower(pathinfo($_FILES["slip_image"]["name"], PATHINFO_EXTENSION));
    $new_filename = "slip_" . $order_id . "_" . time() . "." . $file_ext;
    $target_file = $target_dir . $new_filename;

    // ตรวจสอบนามสกุลไฟล์เบื้องต้น
    $allowed_ext = ['jpg', 'jpeg', 'png'];

    if (!in_array($file_ext, $allowed_ext)) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'ไฟล์ไม่ถูกต้อง',
                text: 'อนุญาตเฉพาะไฟล์ JPG, JPEG และ PNG เท่านั้น',
                confirmButtonColor: '#966027'
            }).then(() => { window.history.back(); });
        </script>";
    } else if (move_uploaded_file($_FILES["slip_image"]["tmp_name"], $target_file)) {
        
        // 2. อัปเดตข้อมูลในตาราง orders
        $sql = "UPDATE orders SET status = 'waiting_verification', slip_image = ?, transfer_time = ? WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$new_filename, $transfer_time, $order_id, $user_id])) {
            // ป๊อปอัพเมื่อสำเร็จ
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'แจ้งชำระเงินสำเร็จ!',
                    text: 'กรุณารอเจ้าหน้าที่ตรวจสอบความถูกต้อง',
                    confirmButtonColor: '#966027',
                    confirmButtonText: 'ตกลง'
                }).then(() => {
                    window.location = 'my_orders.php'; // ไปที่หน้าประวัติการสั่งซื้อในโฟลเดอร์เดียวกัน
                });
            </script>";
        } else {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่สามารถบันทึกข้อมูลลงฐานข้อมูลได้',
                }).then(() => { window.history.back(); });
            </script>";
        }
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'อัปโหลดล้มเหลว',
                text: 'เกิดปัญหาขณะย้ายไฟล์ไปยังโฟลเดอร์เป้าหมาย',
            }).then(() => { window.history.back(); });
        </script>";
    }
}

echo '</body></html>';
?>