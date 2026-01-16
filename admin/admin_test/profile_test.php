<?php
session_start();
require_once "db.php"; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// นับจำนวนสินค้าในตะกร้า
$cart_count = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += isset($item['qty']) ? $item['qty'] : 0;
    }
}

// ดึงข้อมูลผู้ใช้
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("ไม่พบข้อมูลผู้ใช้");
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ข้อมูลส่วนตัว - ร้านขนมไทย</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        body { margin: 0; font-family: 'Sarabun', sans-serif; background: #fff6e5; color: #5a320f; }

        /* ===== NAVBAR ===== */
        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 0 40px; height: 75px; background: linear-gradient(90deg, #7a4a1d, #a96a2d); border-bottom: 4px solid #d6a85a; position: sticky; top: 0; z-index: 1000; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .navbar .logo { font-size: 26px; font-weight: bold; color: #fff3d6; text-decoration: none; }
        .navbar nav { display: flex; align-items: center; gap: 10px; }
        .navbar nav a { text-decoration: none; color: #fff3d6; padding: 8px 18px; border-radius: 25px; transition: 0.3s; font-size: 15px; }
        .navbar nav a:hover { background: rgba(255, 243, 214, 0.2); }

        .user-menu-container { position: relative; display: inline-block; margin-left: 5px; }
        .user-trigger { color: #fff3d6; cursor: pointer; padding: 8px 15px; border-radius: 20px; display: flex; align-items: center; gap: 5px; background: rgba(255, 255, 255, 0.1); font-size: 14px; }
        .dropdown-content { display: none; position: absolute; right: 0; top: 100%; background-color: white; min-width: 190px; border-radius: 15px; box-shadow: 0px 8px 16px rgba(0,0,0,0.15); margin-top: 10px; z-index: 1001; }
        .user-menu-container:hover .dropdown-content { display: block; }
        .dropdown-content a { color: #5a320f !important; padding: 12px 16px; text-decoration: none; display: block; font-size: 14px; }
        .dropdown-content a:hover { background-color: #fff6e5 !important; }

        /* ===== PROFILE CARD ===== */
        .container { max-width: 600px; margin: 50px auto; padding: 0 20px; }
        .profile-card { background: white; border-radius: 30px; padding: 40px; box-shadow: 0 10px 30px rgba(169, 106, 45, 0.1); text-align: center; }

        /* ส่วนแสดงรูปภาพ */
        .avatar-wrapper { margin-bottom: 20px; display: flex; justify-content: center; }
        
        /* กรณีมีรูปภาพ */
        .profile-img-show {
            width: 120px; height: 120px; border-radius: 50%; object-fit: cover;
            border: 4px solid #d6a85a; box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        /* กรณีไม่มีรูปภาพ (ตัวอักษร) */
        .profile-avatar-default {
            width: 110px; height: 110px; background: #d6a85a; color: white;
            font-size: 45px; display: flex; justify-content: center; align-items: center;
            border-radius: 50%; box-shadow: 0 5px 15px rgba(214, 168, 90, 0.4);
        }

        .profile-card h2 { margin: 10px 0; color: #7a4a1d; }
        .profile-card p { color: #888; margin-bottom: 30px; }

        .info-group { text-align: left; margin-bottom: 15px; padding: 15px; background: #fdfaf5; border-radius: 15px; border-left: 5px solid #d6a85a; }
        .info-label { font-size: 12px; color: #a96a2d; font-weight: 600; display: block; margin-bottom: 3px; }
        .info-value { font-size: 16px; color: #5a320f; }

        .btn-edit { display: inline-block; margin-top: 20px; padding: 12px 35px; background: #a96a2d; color: white; text-decoration: none; border-radius: 25px; font-weight: bold; transition: 0.3s; }
        .btn-edit:hover { background: #7a4a1d; transform: translateY(-2px); }

        .cart-badge { position: absolute; top: -10px; right: -12px; background: #ff3b3b; color: white; font-size: 12px; width: 22px; height: 22px; border-radius: 50%; display: flex; justify-content: center; align-items: center; border: 2px solid white; }
    </style>
</head>
<body>

<header class="navbar">
    <a href="index_test.php" class="logo">🍡 ร้านขนมไทย</a>
    <nav>
        <a href="index_test.php">หน้าแรก</a>
        <a href="products_test.php">สินค้าทั้งหมด</a>
        <a href="cart_test.php" style="position: relative; background: rgba(0,0,0,0.1);">
            🛒 ตะกร้า <?php if ($cart_count > 0): ?><span class="cart-badge"><?= $cart_count ?></span><?php endif; ?>
        </a>
        <div class="user-menu-container">
            <div class="user-trigger">สวัสดี, <strong><?= htmlspecialchars($user['username']) ?></strong> ▾</div>
            <div class="dropdown-content">
                <a href="my_orders_test.php">📦 คำสั่งซื้อของฉัน</a>
                <a href="profile_test.php">👤 ข้อมูลส่วนตัว</a>
                <div style="height: 1px; background: #eee;"></div>
                <a href="../../logout.php" style="color: #c0392b !important; font-weight: bold;">🚪 ออกจากระบบ</a>
            </div>
        </div>
    </nav>
</header>

<div class="container">
    <div class="profile-card">
        
        <div class="avatar-wrapper">
            <?php if (!empty($user['profile_img']) && file_exists("uploads/" . $user['profile_img'])): ?>
                <img src="uploads/<?= htmlspecialchars($user['profile_img']) ?>" class="profile-img-show">
            <?php else: ?>
                <div class="profile-avatar-default">
                    <?= mb_substr(htmlspecialchars($user['username']), 0, 1) ?>
                </div>
            <?php endif; ?>
        </div>

        <h2>ข้อมูลส่วนตัว</h2>
        <p>ยินดีต้อนรับคุณ <?= htmlspecialchars($user['username']) ?></p>

        <div class="info-group">
            <span class="info-label">ชื่อผู้ใช้งาน</span>
            <span class="info-value"><?= htmlspecialchars($user['username']) ?></span>
        </div>

        <div class="info-group">
            <span class="info-label">อีเมล</span>
            <span class="info-value"><?= htmlspecialchars($user['email'] ?? 'ยังไม่ได้ระบุ') ?></span>
        </div>

        <div class="info-group">
            <span class="info-label">เบอร์โทรศัพท์</span>
            <span class="info-value"><?= htmlspecialchars($user['phone'] ?? 'ยังไม่ได้ระบุ') ?></span>
        </div>

        <div class="info-group">
            <span class="info-label">ที่อยู่สำหรับการจัดส่ง</span>
            <span class="info-value"><?= nl2br(htmlspecialchars($user['address'] ?? 'ยังไม่ได้ระบุ')) ?></span>
        </div>

        <a href="edit_profile.php" class="btn-edit">แก้ไขข้อมูลส่วนตัว</a>
    </div>
</div>

</body>
</html>