<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);

// 1. ดึงจำนวนรายการที่รอการยืนยัน
$count_pending = 0;
try {
    $stmt_count = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'waiting_verification'");
    $count_pending = $stmt_count->fetchColumn();
} catch (PDOException $e) { $count_pending = 0; }

// 2. ดึงข้อมูลสมาชิก
$members = [];
try {
    $sql = "SELECT id, username, email, role FROM users ORDER BY id DESC";
    $stmt = $pdo->query($sql);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $members = []; }
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการสมาชิก - Admin Panel</title>
    <style>
        /* --- ใช้ CSS เดิมของคุณ และเพิ่มส่วนนี้ --- */
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        body { margin: 0; font-family: 'Sarabun', sans-serif; background: #fff6e5; color: #5a320f; }

        /* Navbar & Dropdown (เหมือนเดิม) */
        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 0 40px; height: 75px; background: linear-gradient(90deg, #2c3e50, #000000); border-bottom: 4px solid #d6a85a; position: sticky; top: 0; z-index: 1000; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .navbar .logo { font-size: 22px; font-weight: bold; color: #fff3d6; text-decoration: none; }
        .navbar nav { display: flex; align-items: center; gap: 8px; }
        .navbar nav a { text-decoration: none; color: #fff3d6; padding: 8px 15px; border-radius: 20px; transition: 0.3s; font-size: 14px; }
        .active-nav { background: rgba(255, 255, 255, 0.2) !important; font-weight: 600; }
        .nav-badge { background-color: #e74c3c; color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px; margin-left: 5px; font-weight: bold; }
        
        /* Dropdown Styles */
        .user-menu-container { position: relative; display: inline-block; margin-left: 10px; }
        .user-trigger { color: #fff3d6; cursor: pointer; padding: 8px 15px; border-radius: 25px; display: flex; align-items: center; gap: 8px; background: rgba(255, 255, 255, 0.1); font-size: 14px; transition: 0.3s; border: 1px solid rgba(255, 255, 255, 0.1); }
        .dropdown-content { display: none; position: absolute; right: 0; top: calc(100% + 10px); background: white; min-width: 200px; border-radius: 12px; z-index: 1001; overflow: hidden; border: 1px solid #eee; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .dropdown-content.active { display: block; animation: slideUp 0.2s ease-out; }
        .dropdown-content a { color: #5a320f !important; padding: 12px 20px; display: flex !important; align-items: center; gap: 10px; text-decoration: none; font-size: 14px; }
        .dropdown-content a:hover { background: #fff6e5 !important; }

        /* Container & Table */
        .container { max-width: 1100px; margin: 40px auto; background: white; padding: 35px; border-radius: 25px; box-shadow: 0 10px 30px rgba(169, 106, 45, 0.1); }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 3px solid #f0e0d0; padding-bottom: 20px; }
        
        .btn-create { background: #2e7d32; color: white !important; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-weight: bold; display: inline-flex; align-items: center; gap: 8px; transition: 0.3s; font-size: 14px; }
        .btn-create:hover { background: #1b5e20; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(46, 125, 50, 0.3); }

        .member-table { width: 100%; border-collapse: collapse; }
        .member-table th { background: #fdfaf5; color: #8d6e63; padding: 18px; text-align: left; border-bottom: 2px solid #eee; }
        .member-table td { padding: 15px; border-bottom: 1px solid #f9f9f9; }
        
        .role-badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .role-admin { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
        .role-user { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }

        .btn-edit { background: #ff9800; color: white !important; padding: 7px 12px; border-radius: 8px; text-decoration: none; font-size: 13px; transition: 0.3s; margin-right: 5px; }
        .btn-delete { background: #e74c3c; color: white !important; padding: 7px 12px; border-radius: 8px; text-decoration: none; font-size: 13px; transition: 0.3s; border: none; cursor: pointer; }
        .btn-delete:hover { background: #c0392b; }
        
        @keyframes slideUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
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

        <a href="admin_member.php" class="<?= (in_array($current_page, ['admin_member.php', 'add_member.php', 'edit_member.php'])) ? 'active-nav' : '' ?>">สมาชิก</a>
        
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
        <div>
            <h2>👥 รายชื่อสมาชิกทั้งหมด (<?= count($members) ?>)</h2>
        </div>
        <a href="add_member.php" class="btn-create">➕ เพิ่มสมาชิกใหม่</a>
    </div>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <div style="background: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-size: 14px;">ลบสมาชิกเรียบร้อยแล้ว</div>
    <?php endif; ?>

    <div style="overflow-x: auto;">
        <table class="member-table">
            <thead>
                <tr>
                    <th width="80">ID</th>
                    <th>ชื่อผู้ใช้งาน</th>
                    <th>อีเมล</th>
                    <th>สถานะ</th>
                    <th style="text-align: center;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $m): ?>
                <tr>
                    <td>#<?= $m['id'] ?></td>
                    <td><strong><?= htmlspecialchars($m['username']) ?></strong></td>
                    <td><?= htmlspecialchars($m['email'] ?? 'ไม่ได้ระบุ') ?></td>
                    <td>
                        <span class="role-badge <?= $m['role'] == 'admin' ? 'role-admin' : 'role-user' ?>">
                            <?= $m['role'] == 'admin' ? '🛡️ ผู้ดูแลระบบ' : '👤 สมาชิกทั่วไป' ?>
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <a href="edit_member.php?id=<?= $m['id'] ?>" class="btn-edit">แก้ไข</a>
                        
                        <?php if($m['id'] != $_SESSION['user_id']): ?>
                            <button onclick="confirmDelete(<?= $m['id'] ?>, '<?= htmlspecialchars($m['username']) ?>')" class="btn-delete">ลบ</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // ยืนยันการลบ
    function confirmDelete(id, name) {
        if (confirm('คุณแน่ใจหรือไม่ที่จะลบสมาชิก "' + name + '"? การกระทำนี้ไม่สามารถย้อนคืนได้')) {
            window.location.href = 'process_delete_member.php?id=' + id;
        }
    }

    // Dropdown Logic
    const adminBtn = document.getElementById('adminBtn');
    const adminDropdown = document.getElementById('adminDropdown');
    adminBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        adminDropdown.classList.toggle('active');
    });
    document.addEventListener('click', () => {
        adminDropdown.classList.remove('active');
    });
</script>

</body>
</html>