<?php
session_start();
require_once __DIR__ . '/db.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION['cart'])) {
    header("Location: products.php");
    exit;
}

$cart = $_SESSION['cart'];
$total = 0;
foreach ($cart as $item) {
    $total += ($item['price'] * $item['qty']);
}

$_SESSION['checkout_token'] = bin2hex(random_bytes(16));
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ชำระเงิน - ร้านขนมไทย</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        
        :root {
            --primary: #a96a2d;
            --dark: #7a4a1d;
            --light-bg: #fff6e5;
            --accent: #d6a85a;
        }

        body { font-family: 'Sarabun', sans-serif; background: var(--light-bg); margin: 0; color: #5a320f; }
        .checkout-container { max-width: 1100px; margin: 40px auto; padding: 0 20px; display: grid; grid-template-columns: 1fr 400px; gap: 30px; }
        @media (max-width: 992px) { .checkout-container { grid-template-columns: 1fr; } }

        .card { background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .card-title { font-size: 1.5rem; color: var(--dark); margin-bottom: 25px; display: flex; align-items: center; gap: 10px; border-bottom: 2px solid var(--light-bg); padding-bottom: 10px; }

        .item-list { width: 100%; border-collapse: collapse; }
        .item-list td { padding: 12px 10px; border-bottom: 1px solid #f0f0f0; }
        .item-list .name { font-weight: 600; color: var(--dark); }
        .item-list .qty { color: #888; font-size: 0.85rem; }
        .item-list .price { text-align: right; font-weight: bold; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; }
        .form-control { width: 100%; padding: 12px 15px; border: 2px solid #eee; border-radius: 12px; box-sizing: border-box; font-family: inherit; transition: 0.3s; }
        .form-control:focus { border-color: var(--accent); outline: none; box-shadow: 0 0 0 4px rgba(214, 168, 90, 0.1); }

        .payment-option { border: 2px solid #eee; border-radius: 15px; padding: 15px; margin-bottom: 12px; cursor: pointer; display: flex; align-items: center; gap: 12px; transition: 0.3s; }
        .payment-option:hover { border-color: var(--accent); background: #fffaf0; }
        .payment-option input[type="radio"] { accent-color: var(--primary); transform: scale(1.2); }
        .payment-option.active { border-color: var(--primary); background: #fffaf0; }

        /* Slip Upload Styling */
        .slip-upload-box {
            margin-top: 20px;
            padding: 20px;
            border: 2px dashed #d1d5db;
            border-radius: 15px;
            text-align: center;
            background: #f9fafb;
            cursor: pointer;
            transition: 0.3s;
        }
        .slip-upload-box:hover { border-color: var(--primary); background: #fffaf0; }
        #slip-preview { max-width: 200px; border-radius: 10px; margin-top: 15px; display: none; margin-left: auto; margin-right: auto; }

        .summary-row { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .total-amount { font-size: 1.8rem; color: var(--primary); font-weight: bold; border-top: 2px dashed #eee; padding-top: 15px; margin-top: 15px; }

        .btn-submit { width: 100%; padding: 18px; background: var(--primary); color: white; border: none; border-radius: 50px; font-size: 1.2rem; font-weight: bold; cursor: pointer; transition: 0.3s; margin-top: 20px; box-shadow: 0 10px 20px rgba(169, 106, 45, 0.3); }
        .btn-submit:hover { background: var(--dark); transform: translateY(-2px); }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #888; text-decoration: none; font-size: 0.9rem; }
    </style>
</head>
<body>

<div class="checkout-container">
    <div class="main-content">
        <form id="orderForm" action="save_order_test.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="token" value="<?= $_SESSION['checkout_token'] ?>">
            <input type="hidden" name="total_price" value="<?= $total ?>">

            <div class="card" style="margin-bottom: 30px;">
                <div class="card-title">📍 ข้อมูลการจัดส่ง</div>
                <div class="form-group">
                    <label>ชื่อ-นามสกุล ผู้รับ</label>
                    <input type="text" name="receiver_name" class="form-control" placeholder="ชื่อผู้รับสินค้า" required>
                </div>
                <div class="form-group">
                    <label>เบอร์โทรศัพท์</label>
                    <input type="tel" name="phone" class="form-control" placeholder="08X-XXX-XXXX" required pattern="[0-9]{9,10}">
                </div>
                <div class="form-group">
                    <label>ที่อยู่สำหรับจัดส่ง</label>
                    <textarea name="address" class="form-control" rows="4" placeholder="บ้านเลขที่, ถนน, ตำบล, อำเภอ, จังหวัด..." required></textarea>
                </div>
            </div>

            <div class="card">
                <div class="card-title">💳 วิธีการชำระเงิน</div>
                
                <label class="payment-option active">
                    <input type="radio" name="payment_method" value="bank" required checked>
                    <div>
                        <strong>โอนเงินผ่านธนาคาร</strong><br>
                        <small>กสิกรไทย: 440-0-84897-5 (ร้านขนมไทย)</small>
                    </div>
                </label>

                <label class="payment-option" onclick="showQRCode()">
                    <input type="radio" name="payment_method" id="pay_promptpay" value="promptpay" required>
                    <div>
                        <strong>พร้อมเพย์ (PromptPay)</strong><br>
                        <small>คลิกเพื่อสแกน QR Code ชำระเงิน</small>
                    </div>
                </label>

                <div class="form-group" style="margin-top: 25px;">
                    <label>🧾 แนบหลักฐานการโอนเงิน</label>
                    <div class="slip-upload-box" onclick="document.getElementById('slip_image').click()">
                        <div id="upload-placeholder">
                            <span style="font-size: 2rem;">📸</span><br>
                            <small>คลิกเพื่อแนบรูปภาพสลิป</small>
                        </div>
                        <img id="slip-preview" src="#" alt="Preview">
                        <input type="file" name="slip_image" id="slip_image" hidden accept="image/*" onchange="previewImage(this)">
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="sidebar">
        <div class="card" style="position: sticky; top: 30px;">
            <div class="card-title">🛍️ สรุปรายการสินค้า</div>
            <table class="item-list">
                <?php foreach ($cart as $item): ?>
                <tr>
                    <td>
                        <div class="name"><?= htmlspecialchars($item['name']) ?></div>
                        <div class="qty">x <?= $item['qty'] ?></div>
                    </td>
                    <td class="price"><?= number_format($item['price'] * $item['qty'], 2) ?> ฿</td>
                </tr>
                <?php endforeach; ?>
            </table>

            <div style="margin-top: 20px;">
                <div class="summary-row"><span>ยอดรวมสินค้า</span><span><?= number_format($total, 2) ?> ฿</span></div>
                <div class="summary-row" style="color: #27ae60;"><span>ค่าจัดส่ง</span><span>ฟรี</span></div>
                <div class="summary-row total-amount"><span>ยอดสุทธิ</span><span><?= number_format($total, 2) ?> ฿</span></div>
            </div>

            <button type="submit" form="orderForm" class="btn-submit">ยืนยันการสั่งซื้อ</button>
            <a href="cart.php" class="back-link">← กลับไปแก้ไขตะกร้าสินค้า</a>
        </div>
    </div>
</div>

<script>
    // สลับ Class Active
    const options = document.querySelectorAll('.payment-option');
    options.forEach(option => {
        option.addEventListener('click', function() {
            options.forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // แสดง QR Code
    function showQRCode() {
        document.getElementById('pay_promptpay').checked = true;
        Swal.fire({
            title: 'พร้อมเพย์ (PromptPay)',
            html: '<p style="margin-bottom:10px;">ยอดชำระ: <b><?= number_format($total, 2) ?> บาท</b></p><p style="font-size:0.9rem; color:#666;">ชื่อบัญชี: ร้านขนมไทย</p>',
            imageUrl: 'assets/images/promptpay-qr.png', 
            imageWidth: 280,
            imageHeight: 280,
            confirmButtonText: 'สแกนเรียบร้อยแล้ว',
            confirmButtonColor: '#a96a2d'
        });
    }

    // ฟังก์ชันแสดงตัวอย่างรูปสลิป
    function previewImage(input) {
        const preview = document.getElementById('slip-preview');
        const placeholder = document.getElementById('upload-placeholder');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // แจ้งเตือนยืนยันก่อนส่ง
    document.getElementById('orderForm').addEventListener('submit', function(e) {
        const slip = document.getElementById('slip_image');
        if (slip.files.length === 0) {
            e.preventDefault();
            Swal.fire('กรุณาแนบสลิป', 'กรุณาแนบหลักฐานการโอนเงินเพื่อยืนยันคำสั่งซื้อ', 'warning');
            return;
        }

        e.preventDefault();
        Swal.fire({
            title: 'ยืนยันการสั่งซื้อ?',
            text: "ข้อมูลถูกต้องครบถ้วนแล้วใช่หรือไม่",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#a96a2d',
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) { this.submit(); }
        });
    });
</script>

</body>
</html>