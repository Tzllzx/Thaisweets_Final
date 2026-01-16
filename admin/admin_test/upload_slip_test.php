<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// ดึงรายการสั่งซื้อที่ยังไม่ได้แจ้งชำระเงิน (ตัวอย่าง)
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT id, total_price FROM orders WHERE user_id = ? AND status = 'pending'");
$stmt->execute([$user_id]);
$pending_orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แจ้งชำระเงิน - ร้านขนมไทย</title>
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
        select, input { width: 100%; padding: 12px; border: 2px solid #eee; border-radius: 12px; box-sizing: border-box; }
        .btn-submit {
            background: #966027; color: white; border: none; padding: 15px;
            width: 100%; border-radius: 12px; cursor: pointer; font-size: 16px; font-weight: bold;
        }
        .btn-submit:hover { background: #7a4a1d; }
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

    <form action="process_slip_test.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>เลือกรายการสั่งซื้อ</label>
            <select name="order_id" required>
                <option value="">-- กรุณาเลือกรายการ --</option>
                <?php foreach ($pending_orders as $order): ?>
                    <option value="<?= $order['id'] ?>">ออเดอร์ #<?= $order['id'] ?> (<?= number_format($order['total_price'], 2) ?> บาท)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>วันที่และเวลาที่โอน</label>
            <input type="datetime-local" name="transfer_time" required>
        </div>

        <div class="form-group">
            <label>แนบรูปภาพสลิป (JPG, PNG)</label>
            <input type="file" name="slip_image" accept="image/*" required>
        </div>

        <button type="submit" class="btn-submit">📤 ส่งหลักฐานการโอนเงิน</button>
        <a href="index_test.php" style="display:block; text-align:center; margin-top:15px; color:#999; text-decoration:none;">ย้อนกลับ</a>
    </form>
</div>

</body>
</html>