<?php
session_start();
// ปรับ Path ถอยออก 2 ระดับเพื่อหา db.php (หากไฟล์นี้อยู่ใน admin/admin_test/)
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
// ดึงรายการสั่งซื้อที่ยังไม่ได้แจ้งชำระเงิน
$stmt = $pdo->prepare("SELECT id, order_code, total_price FROM orders WHERE user_id = ? AND status = 'pending'");
$stmt->execute([$user_id]);
$pending_orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แจ้งชำระเงิน - ร้านขนมไทย</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        body { font-family: 'Sarabun', sans-serif; background: #fff6e5; margin: 0; padding: 20px; }
        .upload-card {
            background: white; max-width: 500px; margin: 40px auto; padding: 30px;
            border-radius: 25px; box-shadow: 0 10px 30px rgba(150, 96, 39, 0.1);
            border-top: 6px solid #966027;
        }
        h2 { color: #5a320f; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #966027; }
        select, input { width: 100%; padding: 12px; border: 2px solid #eee; border-radius: 12px; box-sizing: border-box; font-family: 'Sarabun'; }
        .btn-submit {
            background: #966027; color: white; border: none; padding: 15px;
            width: 100%; border-radius: 12px; cursor: pointer; font-size: 16px; font-weight: bold; transition: 0.3s;
        }
        .btn-submit:hover { background: #7a4a1d; transform: translateY(-2px); }
        .bank-info {
            background: #fffaf0; padding: 15px; border-radius: 15px;
            border: 1px dashed #d6a85a; margin-bottom: 25px; font-size: 14px;
        }
    </style>
</head>
<body>

<div class="upload-card">
    <h2>💳 แจ้งชำระเงิน</h2>
    
    <div class="bank-info">
        <strong>🏦 บัญชีสำหรับโอนเงิน:</strong><br>
        ธนาคารกสิกรไทย: 123-4-56789-0<br>
        ชื่อบัญชี: ร้านขนมไทย จำกัด
    </div>

    <form id="slipForm" action="process_slip.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>เลือกรายการสั่งซื้อ</label>
            <select name="order_id" required>
                <option value="">-- กรุณาเลือกรายการ --</option>
                <?php foreach ($pending_orders as $order): ?>
                    <option value="<?= $order['id'] ?>">
                        ออเดอร์ #<?= $order['order_code'] ?? $order['id'] ?> (<?= number_format($order['total_price'], 2) ?> บาท)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>วันที่และเวลาที่โอน (ตามสลิป)</label>
            <input type="datetime-local" name="transfer_time" required>
        </div>

        <div class="form-group">
            <label>แนบรูปภาพสลิป (JPG, PNG)</label>
            <input type="file" name="slip_image" accept="image/*" required>
        </div>

        <button type="submit" class="btn-submit">📤 ส่งหลักฐานการโอนเงิน</button>
        <a href="index.php" style="display:block; text-align:center; margin-top:15px; color:#999; text-decoration:none;">ย้อนกลับหน้าแรก</a>
    </form>
</div>

<script>
    document.getElementById('slipForm').addEventListener('submit', function(e) {
        e.preventDefault(); // หยุดการส่งฟอร์มชั่วคราวเพื่อโชว์ป๊อปอัพ
        
        Swal.fire({
            title: 'ยืนยันการส่งข้อมูล?',
            text: "กรุณาตรวจสอบความถูกต้องของสลิปก่อนส่ง",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#966027',
            cancelButtonColor: '#aaa',
            confirmButtonText: 'ใช่, ส่งเลย!',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                // แสดงป๊อปอัพกำลังโหลด
                Swal.fire({
                    title: 'กำลังอัปโหลด...',
                    text: 'กรุณารอสักครู่',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // ส่งฟอร์มจริงไปยัง PHP
                this.submit();
            }
        });
    });
</script>

</body>
</html>