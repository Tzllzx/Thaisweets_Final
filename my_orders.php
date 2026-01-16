<?php
session_start();
require_once "db.php"; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id']; 
$orders = []; 
$user_data = null;

// ดึงข้อมูลผู้ใช้สำหรับ Navbar
$stmt_user = $pdo->prepare("SELECT profile_img, username FROM users WHERE id = ?");
$stmt_user->execute([$user_id]);
$user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);

// คำนวณจำนวนสินค้าในตะกร้า
$cart_count = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += isset($item['qty']) ? $item['qty'] : 0;
    }
}

// ดึงข้อมูลคำสั่งซื้อ
try {
    $sql = "SELECT id, order_code, total_price, status, created_at, reject_note 
            FROM orders 
            WHERE user_id = ? 
            ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Query Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ประวัติคำสั่งซื้อ - ร้านขนมไทย</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        
        body { margin: 0; font-family: 'Sarabun', sans-serif; background: #fff6e5; color: #5a320f; }

        /* ===== NAVBAR (ปรับให้ Contrast สูงขึ้น) ===== */
        .navbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0 40px; height: 75px;
            background: linear-gradient(90deg, #7a4a1d, #a96a2d); /* น้ำตาลเข้มจัด ตัดกับพื้นหลังเว็บ */
            border-bottom: 4px solid #d6a85a;
            position: sticky; top: 0; z-index: 1000;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .navbar .logo { font-size: 26px; font-weight: bold; color: #fff3d6; text-decoration: none; }
        .navbar nav { display: flex; align-items: center; gap: 10px; }
        .navbar nav a { text-decoration: none; color: #fff3d6; padding: 8px 18px; border-radius: 25px; transition: 0.3s; font-size: 15px; }
        .navbar nav a:hover { background: rgba(255, 243, 214, 0.2); }

        /* Profile Styles */
        .user-avatar-nav { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid #fff3d6; }
        .user-avatar-initial { width: 32px; height: 32px; border-radius: 50%; background: #d6a85a; color: white; display: flex; justify-content: center; align-items: center; font-size: 14px; font-weight: bold; border: 2px solid #fff3d6; }
        .cart-link { position: relative; background: rgba(0, 0, 0, 0.2) !important; }
        .cart-badge { position: absolute; top: -10px; right: -12px; background: #ff3b3b; color: white; font-size: 12px; font-weight: bold; border-radius: 50%; width: 22px; height: 22px; display: flex; justify-content: center; align-items: center; border: 2px solid #5a320f; }

        /* Dropdown */
        .user-menu-container { position: relative; display: inline-block; margin-left: 10px; }
        .user-trigger { color: #fff3d6; cursor: pointer; padding: 5px 12px; border-radius: 20px; display: flex; align-items: center; gap: 8px; background: rgba(255, 255, 255, 0.1); }
        .dropdown-content { display: none; position: absolute; right: 0; top: 100%; background: white; min-width: 190px; box-shadow: 0 8px 16px rgba(0,0,0,0.2); border-radius: 12px; margin-top: 10px; z-index: 1001; }
        .user-menu-container:hover .dropdown-content { display: block; }
        .dropdown-content a { color: #5a320f !important; padding: 12px 16px; display: block; text-decoration: none; }
        .dropdown-content a:hover { background: #fff6e5 !important; border-radius: 12px; }

        /* ===== TABLE SECTION (High Contrast) ===== */
        .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }
        h2 { border-left: 8px solid #7a4a1d; padding-left: 15px; margin-bottom: 30px; }

        .order-table-container { 
            background: #ffffff; border-radius: 15px; overflow: hidden; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 2px solid #7a4a1d; /* ขอบเข้ม */
        }
        table { width: 100%; border-collapse: collapse; }
        th { 
            background-color: #7a4a1d; color: #ffffff; padding: 20px; 
            text-align: center; font-size: 15px; border-bottom: 3px solid #d6a85a;
        }
        td { 
            padding: 18px; text-align: center; border-bottom: 1px solid #eee; 
            border-right: 1px solid #f9f9f9; color: #333; 
        }
        td:last-child { border-right: none; }
        tbody tr:nth-child(even) { background-color: #fdf6ec; } /* สลับแถวสีครีมเข้ม */
        tbody tr:hover { background-color: #fff2e0 !important; }

        /* Status Pills */
        .status-pill { padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: bold; border: 1px solid; display: inline-block; }
        .status-pending { background: #fffde7; color: #f57f17; border-color: #fbc02d; }
        .status-paid { background: #e8f5e9; color: #2e7d32; border-color: #a5d6a7; }
        .status-wait { background: #e3f2fd; color: #1565c0; border-color: #90caf9; }
        .status-rejected { background: #ffebee; color: #c62828; border-color: #ef9a9a; }

        .reject-box { margin-top: 8px; padding: 10px; background: #fff0f0; border-left: 5px solid #ff4d4f; border-radius: 4px; font-size: 13px; color: #a8071a; text-align: left; }
        
        .btn-action { 
            background: #a96a2d; color: #fff !important; padding: 10px 20px; 
            border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: bold; 
            box-shadow: 0 4px 0 #5a320f; transition: 0.2s;
        }
        .btn-action:hover { background: #7a4a1d; transform: translateY(-2px); }
        .btn-action:active { transform: translateY(2px); box-shadow: 0 0 0; }
    </style>
</head>
<body>

<header class="navbar">
    <a href="index.php" class="logo">🍡 ร้านขนมไทย</a>
    <nav>
        <a href="index.php">หน้าแรก</a>
        <a href="products.php">สินค้าทั้งหมด</a>

        <a href="cart.php" class="cart-link">
            🛒 ตะกร้า
            <?php if ($cart_count > 0): ?>
                <span class="cart-badge"><?= $cart_count ?></span>
            <?php endif; ?>
        </a>

        <div class="user-menu-container">
            <div class="user-trigger">
                <?php if (!empty($user_data['profile_img']) && file_exists("uploads/" . $user_data['profile_img'])): ?>
                    <img src="uploads/<?= htmlspecialchars($user_data['profile_img']) ?>" class="user-avatar-nav">
                <?php else: ?>
                    <div class="user-avatar-initial"><?= mb_substr(htmlspecialchars($user_data['username']), 0, 1) ?></div>
                <?php endif; ?>
                <span><strong><?= htmlspecialchars($user_data['username']) ?></strong> ▾</span>
            </div>
            <div class="dropdown-content">
                <a href="my_orders.php">📦 คำสั่งซื้อของฉัน</a>
                <a href="profile.php">👤 ข้อมูลส่วนตัว</a>
                <div style="height:1px; background:#eee;"></div>
                <a href="logout.php" style="color: #c0392b !important; font-weight: bold;">🚪 ออกจากระบบ</a>
            </div>
        </div>
    </nav>
</header>

<div class="container">
    <h2>📦 ประวัติคำสั่งซื้อของคุณ</h2>
    
    <div class="order-table-container">
        <table>
            <thead>
                <tr>
                    <th>เลขที่คำสั่งซื้อ</th>
                    <th>วันที่สั่งซื้อ</th>
                    <th>ยอดรวม</th>
                    <th>สถานะ</th>
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $row): ?>
                    <tr>
                        <td style="font-weight:bold; color: #7a4a1d;">#<?= htmlspecialchars($row['order_code'] ?? $row['id']) ?></td>
                        <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                        <td style="font-weight:bold; color: #222;"><?= number_format($row['total_price'], 2) ?> บาท</td>
                        <td>
                            <?php if ($row['status'] == 'paid'): ?>
                                <span class="status-pill status-paid">ชำระเงินสำเร็จ</span>
                            <?php elseif ($row['status'] == 'waiting_verification'): ?>
                                <span class="status-pill status-wait">ตรวจสอบสลิป</span>
                            <?php elseif ($row['status'] == 'pending' && !empty($row['reject_note'])): ?>
                                <span class="status-pill status-rejected">ต้องแก้ไขสลิป</span>
                                <div class="reject-box">
                                    <strong>แอดมินแจ้ง:</strong> <?= htmlspecialchars($row['reject_note']) ?>
                                </div>
                            <?php else: ?>
                                <span class="status-pill status-pending">รอชำระเงิน</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['status'] == 'pending'): ?>
                                <a href="upload_slip.php?order_id=<?= $row['id'] ?>" class="btn-action">
                                    <?= !empty($row['reject_note']) ? 'ส่งสลิปใหม่' : 'แจ้งโอนเงิน' ?>
                                </a>
                            <?php elseif ($row['status'] == 'waiting_verification'): ?>
                                <span style="color: #666; font-size: 13px; font-style: italic;">⏳ กำลังตรวจสอบ...</span>
                            <?php else: ?>
                                <span style="color: #2e7d32; font-weight: bold;">✅ เรียบร้อย</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding: 80px; color: #7a4a1d;">
                            ยังไม่มีรายการสั่งซื้อของคุณ
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>