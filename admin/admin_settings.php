<?php
session_start();
require_once "../db.php";

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);
$msg = "";

// ประมวลผลการบันทึกข้อมูล
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST['settings'] as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) 
                               VALUES (?, ?) 
                               ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$key, $value, $value]);
    }
    $msg = "success";
}

// ดึงข้อมูลการตั้งค่าปัจจุบัน
$settings = [];
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    // ข้ามหากไม่มีตาราง
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ตั้งค่าระบบ - Admin Panel</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        body { margin: 0; font-family: 'Sarabun', sans-serif; background: #fff6e5; color: #5a320f; }

        /* Navbar Style */
        .navbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0 40px; height: 75px; background: linear-gradient(90deg, #2c3e50, #000000);
            border-bottom: 4px solid #d6a85a; position: sticky; top: 0; z-index: 1000;
        }
        .navbar .logo { font-size: 22px; font-weight: bold; color: #fff3d6; text-decoration: none; }
        .navbar nav { display: flex; align-items: center; gap: 8px; }
        .navbar nav a { text-decoration: none; color: #fff3d6; padding: 8px 15px; border-radius: 20px; font-size: 14px; transition: 0.3s; }
        .navbar nav a:hover { background: rgba(255, 253, 214, 0.1); }
        .active-nav { background: rgba(255, 255, 255, 0.2) !important; font-weight: 600; }

        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        
        .settings-card {
            background: white; border-radius: 25px; padding: 40px;
            box-shadow: 0 10px 30px rgba(169, 106, 45, 0.1); border: 1px solid #f0e0d0;
        }

        /* ส่วนหัวของฟอร์ม */
        .header-flex {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 30px; border-bottom: 2px solid #fff6e5; padding-bottom: 15px;
        }

        .btn-back {
            text-decoration: none; color: #8d6e63; background: #fdfaf5;
            padding: 8px 18px; border-radius: 12px; font-size: 14px;
            font-weight: 600; border: 1px solid #f0e0d0; transition: 0.3s;
            display: flex; align-items: center; gap: 8px;
        }
        .btn-back:hover { background: #fff; color: #5a320f; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }

        .section-title {
            font-size: 18px; font-weight: 600; color: #7a4a1d;
            margin-bottom: 25px; padding-bottom: 10px; border-bottom: 2px solid #fff6e5;
            display: flex; align-items: center; gap: 10px;
        }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 14px; font-weight: 600; }
        .form-control {
            width: 100%; padding: 12px; border: 2px solid #eee; border-radius: 12px;
            font-family: 'Sarabun'; box-sizing: border-box; transition: 0.3s;
        }
        .form-control:focus { outline: none; border-color: #d6a85a; background: #fffcf5; }

        .full-width { grid-column: span 2; }

        .btn-save {
            background: #a96a2d; color: white; border: none; padding: 16px;
            border-radius: 15px; font-size: 16px; font-weight: bold; cursor: pointer;
            transition: 0.3s; width: 100%; box-shadow: 0 4px 15px rgba(169, 106, 45, 0.2);
        }
        .btn-save:hover { background: #7a4a1d; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(122, 74, 29, 0.3); }

        .alert {
            background: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 12px;
            margin-bottom: 25px; text-align: center; border: 1px solid #c8e6c9; animation: fadeIn 0.5s;
        }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<header class="navbar">
    <a href="index_admin.php" class="logo">🍡 Admin Panel</a>
    <nav>
        <a href="index_admin.php">แผงควบคุม</a>
        <a href="admin_products.php">จัดการสินค้า</a>
        <a href="admin_member.php">สมาชิก</a>
        <a href="admin_settings.php" class="active-nav">ตั้งค่าระบบ</a>
    </nav>
</header>

<div class="container">
    <div class="settings-card">
        <div class="header-flex">
            <h2 style="margin:0;">⚙️ ตั้งค่าเว็บไซต์</h2>
            <a href="index_admin.php" class="btn-back">🔙 กลับหน้าแรก</a>
        </div>
        
        <?php if($msg == "success"): ?>
            <div class="alert">✅ บันทึกการตั้งค่าเรียบร้อยแล้ว</div>
        <?php endif; ?>

        <form method="POST">
            <div class="section-title">🏠 ข้อมูลทั่วไปของร้าน</div>
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>ชื่อร้านค้า</label>
                    <input type="text" name="settings[site_name]" class="form-control" value="<?= htmlspecialchars($settings['site_name'] ?? 'ร้านขนมไทย') ?>">
                </div>
                <div class="form-group">
                    <label>เบอร์โทรศัพท์ติดต่อ</label>
                    <input type="text" name="settings[site_phone]" class="form-control" value="<?= htmlspecialchars($settings['site_phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>อีเมลติดต่อ</label>
                    <input type="email" name="settings[site_email]" class="form-control" value="<?= htmlspecialchars($settings['site_email'] ?? '') ?>">
                </div>
                <div class="form-group full-width">
                    <label>ที่อยู่ร้าน (สำหรับหน้าติดต่อเรา)</label>
                    <textarea name="settings[site_address]" class="form-control" rows="3"><?= htmlspecialchars($settings['site_address'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="section-title">💳 บัญชีธนาคารสำหรับรับโอน</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>ธนาคาร</label>
                    <select name="settings[bank_name]" class="form-control">
                        <option value="กสิกรไทย" <?= ($settings['bank_name'] ?? '') == 'กสิกรไทย' ? 'selected' : '' ?>>ธนาคารกสิกรไทย</option>
                        <option value="ไทยพาณิชย์" <?= ($settings['bank_name'] ?? '') == 'ไทยพาณิชย์' ? 'selected' : '' ?>>ธนาคารไทยพาณิชย์</option>
                        <option value="กรุงไทย" <?= ($settings['bank_name'] ?? '') == 'กรุงไทย' ? 'selected' : '' ?>>ธนาคารกรุงไทย</option>
                        <option value="กรุงเทพ" <?= ($settings['bank_name'] ?? '') == 'กรุงเทพ' ? 'selected' : '' ?>>ธนาคารกรุงเทพ</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>ชื่อบัญชี</label>
                    <input type="text" name="settings[bank_account_name]" class="form-control" value="<?= htmlspecialchars($settings['bank_account_name'] ?? '') ?>">
                </div>
                <div class="form-group full-width">
                    <label>เลขที่บัญชี</label>
                    <input type="text" name="settings[bank_account_number]" class="form-control" value="<?= htmlspecialchars($settings['bank_account_number'] ?? '') ?>">
                </div>
            </div>

            <button type="submit" class="btn-save">💾 บันทึกการตั้งค่าทั้งหมด</button>
        </form>
    </div>
</div>

</body>
</html>