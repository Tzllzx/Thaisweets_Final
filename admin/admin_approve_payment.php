<?php
session_start();
require_once "../db.php";

// 1. เช็คสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../logout.php");
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);

// --- 2. Logic การจัดการ ---

// Logic การกดอนุมัติ
if (isset($_GET['approve_id'])) {
    $id = $_GET['approve_id'];
    try {
        $update = $pdo->prepare("UPDATE orders SET status = 'paid', reject_note = NULL WHERE id = ?");
        $update->execute([$id]);
        header("Location: admin_approve_payment.php?success=1");
        exit;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

// Logic การปฏิเสธ (เพื่อให้ลูกค้าแก้ไขสลิป)
if (isset($_GET['reject_id'])) {
    $id = $_GET['reject_id'];
    $reason = $_GET['reason'] ?? 'ข้อมูลสลิปไม่ชัดเจน กรุณาตรวจสอบและส่งใหม่อีกครั้ง';
    try {
        // เปลี่ยนกลับเป็น pending เพื่อให้ปุ่มชำระเงินฝั่งลูกค้าปรากฏขึ้นอีกครั้ง
        // และลบชื่อไฟล์สลิปเดิมทิ้งเพื่อให้ลูกค้าแนบไฟล์ใหม่ได้
        $update = $pdo->prepare("UPDATE orders SET status = 'pending', slip_image = NULL, reject_note = ? WHERE id = ?");
        $update->execute([$reason, $id]);
        
        header("Location: admin_approve_payment.php?rejected=1");
        exit;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

// 3. ดึงรายการออเดอร์ที่รอตรวจสอบ
$orders = [];
try {
    $sql = "SELECT o.id, o.total_price, o.slip_image, o.transfer_time, o.status, u.username 
            FROM orders o
            INNER JOIN users u ON o.user_id = u.id
            WHERE o.status = 'waiting_verification' 
            ORDER BY o.id DESC";
    $stmt = $pdo->query($sql);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count_pending = count($orders);
} catch (PDOException $e) { 
    $count_pending = 0; 
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตรวจสอบการชำระเงิน - Admin Panel</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        body { font-family: 'Sarabun', sans-serif; background: #fff6e5; margin: 0; color: #5a320f; overflow-x: hidden; }
        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 0 40px; height: 75px; background: linear-gradient(90deg, #2c3e50, #000000); border-bottom: 4px solid #d6a85a; position: sticky; top: 0; z-index: 1000; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .navbar .logo { font-size: 22px; font-weight: bold; color: #fff3d6; text-decoration: none; }
        .navbar nav { display: flex; align-items: center; gap: 8px; }
        .navbar nav a { text-decoration: none; color: #fff3d6; padding: 8px 15px; border-radius: 20px; transition: 0.3s; font-size: 14px; }
        .navbar nav a:hover { background: rgba(255, 243, 214, 0.15); }
        .active-nav { background: rgba(255, 255, 255, 0.2) !important; font-weight: 600; }
        .user-menu-container { position: relative; display: inline-block; margin-left: 10px; }
        .user-trigger { color: #fff3d6; cursor: pointer; padding: 8px 15px; border-radius: 25px; display: flex; align-items: center; gap: 8px; background: rgba(255, 255, 255, 0.1); font-size: 14px; transition: 0.3s; border: 1px solid rgba(255, 255, 255, 0.1); }
        .dropdown-content { display: none; position: absolute; right: 0; top: calc(100% + 10px); background: white; min-width: 200px; border-radius: 12px; z-index: 1001; overflow: hidden; border: 1px solid #eee; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .dropdown-content.active { display: block; animation: slideUp 0.2s ease-out; }
        .dropdown-content a { color: #5a320f !important; padding: 12px 20px; display: flex !important; align-items: center; gap: 10px; text-decoration: none; font-size: 14px; }
        .dropdown-content a:hover { background: #fff6e5 !important; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .nav-badge { background-color: #e74c3c; color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px; margin-left: 5px; font-weight: bold; }
        .container { max-width: 1200px; margin: 40px auto; background: white; padding: 35px; border-radius: 25px; box-shadow: 0 10px 30px rgba(169, 106, 45, 0.1); }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 3px solid #f0e0d0; padding-bottom: 20px; }
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #fdfaf5; color: #8d6e63; padding: 18px; text-align: center; font-weight: 600; border-bottom: 2px solid #eee; }
        td { padding: 15px; text-align: center; border-bottom: 1px solid #f9f9f9; }
        .price { color: #27ae60; font-weight: 800; font-size: 18px; }
        .slip-img { width: 80px; height: 100px; object-fit: cover; border-radius: 10px; border: 2px solid #eee; transition: 0.3s; cursor: zoom-in; }
        .slip-img:hover { transform: scale(1.05); border-color: #d6a85a; }
        .btn-approve { background: #27ae60; color: white !important; padding: 10px 18px; border-radius: 50px; font-weight: bold; border: none; cursor: pointer; transition: 0.3s; margin-bottom: 5px; width: 100%; display: block; }
        .btn-approve:hover { background: #219150; box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3); }
        .btn-reject { background: #e74c3c; color: white !important; padding: 8px 18px; border-radius: 50px; font-weight: bold; border: none; cursor: pointer; transition: 0.3s; width: 100%; font-size: 13px; display: block; }
        .btn-reject:hover { background: #c0392b; }
        .btn-back { text-decoration: none; background: #f3f3f3; color: #5a320f; padding: 8px 15px; border-radius: 10px; font-size: 14px; display: inline-flex; align-items: center; gap: 5px; transition: 0.3s; }
        .user-tag { background: #f0e0d0; color: #7a4a1d; padding: 3px 10px; border-radius: 12px; font-size: 13px; font-weight: bold; }
    </style>
</head>
<body>

<header class="navbar">
    <a href="index_admin.php" class="logo">🍡 Admin Panel</a>
    <nav>
        <a href="index_admin.php" class="<?= ($current_page == 'index_admin.php') ? 'active-nav' : '' ?>">แผงควบคุม</a>
        <a href="admin_products.php" class="<?= ($current_page == 'admin_products.php') ? 'active-nav' : '' ?>">จัดการสินค้า</a>
        <a href="admin_approve_payment.php" class="<?= ($current_page == 'admin_approve_payment.php') ? 'active-nav' : '' ?>">
            ตรวจสอบชำระเงิน 
            <?php if($count_pending > 0): ?>
                <span class="nav-badge"><?= $count_pending ?></span>
            <?php endif; ?>
        </a>
        <a href="admin_member.php" class="<?= ($current_page == 'admin_member.php') ? 'active-nav' : '' ?>">สมาชิก</a>
        
        <div class="user-menu-container">
            <div class="user-trigger" id="adminBtn">
                <div style="width: 28px; height: 28px; background: #d6a85a; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 12px;">A</div>
                <span><strong>Admin</strong> ▾</span>
            </div>
            <div class="dropdown-content" id="adminDropdown">
                <div style="padding: 15px; background: #fdfaf5; font-size: 12px; color: #8d6e63; border-bottom: 1px solid #eee;">สถานะ: <b>ผู้ดูแลระบบ</b></div>
                <a href="admin_test/index_test.php">🌐 ไปยังหน้าเว็บลูกค้า</a>
                <a href="admin_settings.php">⚙️ ตั้งค่าระบบ</a>
                <div style="height: 1px; background: #eee;"></div>
                <a href="../logout.php" style="color: #c0392b !important; font-weight: bold;">🚪 ออกจากระบบ</a>
            </div>
        </div>
    </nav>
</header>

<div class="container">
    <div class="header-flex">
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="index_admin.php" class="btn-back">🔙 แผงควบคุม</a>
            <h2 style="margin: 0; color: #5a320f;">รายการที่รอการอนุมัติ (<?= $count_pending ?>)</h2>
        </div>
    </div>
    
    <div class="table-responsive">
        <?php if(!empty($orders)): ?>
            <table>
                <thead>
                    <tr>
                        <th width="100">ออเดอร์</th>
                        <th>ชื่อลูกค้า</th>
                        <th>ยอดที่ต้องโอน</th>
                        <th>วัน-เวลาแจ้งโอน</th>
                        <th>หลักฐานสลิป</th>
                        <th width="160">การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong>#<?= $order['id'] ?></strong></td>
                        <td><span class="user-tag">👤 <?= htmlspecialchars($order['username']) ?></span></td>
                        <td><span class="price"><?= number_format($order['total_price'], 2) ?> ฿</span></td>
                        <td>
                            <div><?= date('d/m/Y', strtotime($order['transfer_time'] ?? 'now')) ?></div>
                            <div style="font-size: 12px; color: #888;"><?= date('H:i', strtotime($order['transfer_time'] ?? 'now')) ?> น.</div>
                        </td>
                        <td>
                            <?php if ($order['slip_image']): ?>
                                <a href="../assets/slips/<?= $order['slip_image'] ?>" target="_blank">
                                    <img src="../assets/slips/<?= $order['slip_image'] ?>" class="slip-img" title="คลิกเพื่อดูรูปใหญ่">
                                </a>
                            <?php else: ?>
                                <span style="color:#e74c3c; font-size: 12px;">ไม่มีไฟล์สลิป</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn-approve" onclick="confirmApprove(<?= $order['id'] ?>)">
                                ✅ อนุมัติ
                            </button>
                            <button class="btn-reject" onclick="confirmReject(<?= $order['id'] ?>)">
                                ❌ ปฏิเสธ (ให้แก้ไข)
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="text-align: center; padding: 80px 20px;">
                <div style="font-size: 60px; margin-bottom: 20px;">🍵</div>
                <h3 style="color: #8d6e63; margin: 0;">ไม่มีรายการรอตรวจสอบในขณะนี้</h3>
                <p style="color: #bfa59b;">พักจิบชาสักครู่ แล้วค่อยกลับมาตรวจสอบใหม่นะครับ</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
const adminBtn = document.getElementById('adminBtn');
const adminDropdown = document.getElementById('adminDropdown');
if(adminBtn) {
    adminBtn.addEventListener('click', (e) => { 
        e.stopPropagation(); 
        adminDropdown.classList.toggle('active'); 
    });
}
document.addEventListener('click', () => { adminDropdown.classList.remove('active'); });

function confirmApprove(id) {
    Swal.fire({
        title: 'ยืนยันยอดเงิน?',
        text: "ตรวจสอบสลิปและยอดเงินในบัญชีเรียบร้อยแล้วใช่หรือไม่?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#27ae60',
        confirmButtonText: 'ยืนยัน',
        cancelButtonText: 'ยกเลิก'
    }).then((r) => { 
        if (r.isConfirmed) {
            window.location.href = 'admin_approve_payment.php?approve_id=' + id;
        } 
    });
}

function confirmReject(id) {
    Swal.fire({
        title: 'ปฏิเสธและให้แก้ไข?',
        text: "ระบุเหตุผลเพื่อให้ลูกค้าส่งสลิปใหม่ (เช่น ภาพเบลอ, ยอดไม่ตรง)",
        input: 'text',
        inputPlaceholder: 'ระบุเหตุผลที่นี่...',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        confirmButtonText: 'ยืนยันการปฏิเสธ',
        cancelButtonText: 'ยกเลิก',
        preConfirm: (value) => {
            if (!value) {
                Swal.showValidationMessage('กรุณาระบุเหตุผล เพื่อแจ้งให้ลูกค้าทราบ');
            }
            return value;
        }
    }).then((r) => { 
        if (r.isConfirmed) {
            window.location.href = 'admin_approve_payment.php?reject_id=' + id + '&reason=' + encodeURIComponent(r.value);
        } 
    });
}

const urlParams = new URLSearchParams(window.location.search);
if (urlParams.has('success')) {
    Swal.fire({ icon: 'success', title: 'อนุมัติเรียบร้อย', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
}
if (urlParams.has('rejected')) {
    Swal.fire({ icon: 'info', title: 'ส่งกลับให้ลูกค้าแก้ไขแล้ว', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
}
</script>
</body>
</html>