<?php
session_start();
require_once "../db.php";

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../logout.php");
    exit;
}

// 1. ดึงข้อมูลสินค้าทั้งหมด
$sql = "SELECT * FROM products ORDER BY id DESC";
$stmt = $pdo->query($sql);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. ดึงจำนวนรายการที่รอการยืนยัน
$count_pending = 0;
if (isset($pdo)) {
    try {
        $stmt_count = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'waiting_verification'");
        $count_pending = $stmt_count->fetchColumn();
    } catch (PDOException $e) {
        $count_pending = 0;
    }
}

// 3. กำหนดสีปุ่มตามสถานะงาน
$btn_color = ($count_pending > 0) ? "#f39c12" : "#27ae60";
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการสินค้า - Admin</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');

        body { margin: 0; font-family: 'Sarabun', sans-serif; background: #fff6e5; color: #5a320f; }

        /* ===== NAVBAR ===== */
        .navbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0 40px; height: 75px;
            background: linear-gradient(90deg, #2c3e50, #000000);
            border-bottom: 4px solid #d6a85a;
            position: sticky; top: 0; z-index: 1000;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .navbar .logo { font-size: 22px; font-weight: bold; color: #fff3d6; text-decoration: none; }
        .navbar nav { display: flex; align-items: center; gap: 8px; }
        .navbar nav a {
            text-decoration: none; color: #fff3d6; padding: 8px 15px;
            border-radius: 20px; transition: 0.3s; font-size: 14px;
        }
        .navbar nav a:hover { background: rgba(255, 243, 214, 0.15); }

        .nav-badge {
            background: #e74c3c; color: white; padding: 2px 8px;
            border-radius: 10px; font-size: 11px; margin-left: 5px; font-weight: bold;
        }

        /* ===== DROPDOWN SYSTEM ===== */
        .user-menu-container { position: relative; display: inline-block; margin-left: 10px; }
        
        .user-trigger {
            color: #fff3d6; cursor: pointer; padding: 8px 15px; border-radius: 25px;
            display: flex; align-items: center; gap: 8px; background: rgba(255, 255, 255, 0.1);
            font-size: 14px; transition: 0.3s; border: 1px solid rgba(255, 255, 255, 0.1);
            user-select: none;
        }

        .dropdown-content {
            display: none; position: absolute; right: 0; top: calc(100% + 10px);
            background: white; min-width: 220px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border-radius: 12px; z-index: 1001; overflow: hidden;
            border: 1px solid #eee;
        }

        .dropdown-content.active { display: block; animation: slideUp 0.2s ease-out; }

        .dropdown-content a {
            color: #5a320f !important; padding: 12px 20px;
            display: flex !important; align-items: center; gap: 10px;
            text-decoration: none; transition: 0.2s; font-size: 14px;
            border-bottom: 1px solid #f9f9f9;
        }
        .dropdown-content a:hover { background: #fff6e5 !important; padding-left: 25px; }

        @keyframes slideUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* ===== CONTENT SECTION ===== */
        .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .btn-add { background: #2e7d32; color: white; padding: 12px 25px; border-radius: 12px; text-decoration: none; font-weight: bold; transition: 0.3s; }
        .btn-add:hover { background: #1b5e20; transform: translateY(-2px); }

        .product-table { width: 100%; background: white; border-collapse: collapse; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .product-table th { background: #e6b264; color: #5a320f; padding: 15px; text-align: left; }
        .product-table td { padding: 15px; border-bottom: 1px solid #f0e0d0; vertical-align: middle; }
        .img-preview { width: 65px; height: 65px; object-fit: cover; border-radius: 10px; border: 1px solid #eee; }
        .btn-edit { background: #f39c12; color: white; padding: 7px 15px; border-radius: 8px; text-decoration: none; font-size: 14px; margin-right: 5px; }
        .btn-delete { background: #e74c3c; color: white; padding: 7px 15px; border-radius: 8px; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>

<header class="navbar">
    <a href="index_admin.php" class="logo">🔧 ระบบหลังบ้าน</a>
    <nav>
        <a href="index_admin.php">แผงควบคุม</a>
        <a href="admin_products.php" style="background: rgba(255,255,255,0.2);">จัดการสินค้า</a>
        <a href="admin_approve_payment.php">
            ตรวจสอบชำระเงิน <?php if($count_pending > 0): ?><span class="nav-badge"><?= $count_pending ?></span><?php endif; ?>
        </a>
        <a href="admin_member.php">สมาชิก</a>

        <div class="user-menu-container">
            <div class="user-trigger" id="adminBtn">
                <div style="width: 28px; height: 28px; background: #d6a85a; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 12px;">A</div>
                <span><strong>Admin</strong> ▾</span>
            </div>
            <div class="dropdown-content" id="adminDropdown">
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
        <h2 style="color: #966027; border-left: 5px solid #a96a2d; padding-left: 15px;">📦 รายการสินค้าทั้งหมด</h2>
        <a href="add_item.php" class="btn-add">+ เพิ่มสินค้าใหม่</a>
    </div>

    <table class="product-table">
        <thead>
            <tr>
                <th width="100">รูปภาพ</th>
                <th>ชื่อสินค้า</th>
                <th width="150">ราคา</th>
                <th width="200" style="text-align: center;">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $row): ?>
            <tr>
                <td>
                    <img src="../assets/images/<?= !empty($row['image']) ? htmlspecialchars($row['image']) : 'no-image.jpg' ?>" class="img-preview">
                </td>
                <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                <td><span style="font-weight: bold; color: #966027;"><?= number_format($row['price'], 2) ?> บาท</span></td>
                <td style="text-align: center;">
                    <a href="edit_item.php?id=<?= $row['id'] ?>" class="btn-edit">แก้ไข</a>
                    <a href="delete_item.php?id=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('คุณแน่ใจหรือไม่?')">ลบ</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    // สคริปต์สำหรับ Dropdown
    const adminBtn = document.getElementById('adminBtn');
    const adminDropdown = document.getElementById('adminDropdown');

    adminBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        adminDropdown.classList.toggle('active');
    });

    document.addEventListener('click', function(e) {
        if (!adminBtn.contains(e.target) && !adminDropdown.contains(e.target)) {
            adminDropdown.classList.remove('active');
        }
    });
</script>

</body>
</html>