<?php
session_start();
require_once "../db.php"; 

// 1. เช็คสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../logout.php");
    exit;
}

// 2. รับค่า ID และดึงข้อมูลสมาชิกที่ต้องการแก้ไข
$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    die("ไม่พบข้อมูลสมาชิกในระบบ <a href='admin_member.php'>ย้อนกลับ</a>");
}

// ตรวจสอบว่าเป็นเจ้าของบัญชีเองหรือไม่ (เพื่อใช้เปิดสิทธิ์แก้ไขรหัสผ่าน)
$is_own_account = ($user['username'] === $_SESSION['username']);

// 3. จัดการการบันทึกข้อมูล (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $new_password = $_POST['new_password'] ?? '';

    try {
        // เงื่อนไขการเปลี่ยนรหัสผ่าน: เป็น User ทั่วไป "หรือ" เป็นบัญชีของตัวเอง
        if (!empty($new_password) && ($user['role'] === 'user' || $is_own_account)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET username=?, email=?, role=?, password=? WHERE id=?";
            $params = [$username, $email, $role, $hashed_password, $id];
        } else {
            $sql = "UPDATE users SET username=?, email=?, role=? WHERE id=?";
            $params = [$username, $email, $role, $id];
        }

        if ($pdo->prepare($sql)->execute($params)) {
            // ถ้าแก้ไขชื่อตัวเองสำเร็จ อาจจะต้องอัปเดต Session ด้วยเพื่อให้ชื่อด้านบนเปลี่ยนตาม
            if ($is_own_account) {
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;
            }
            header("Location: admin_member.php?success=1");
            exit;
        }
    } catch (PDOException $e) {
        $error_msg = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขสิทธิ์สมาชิก - <?= htmlspecialchars($user['username']) ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        body { font-family: 'Sarabun', sans-serif; background: #fff6e5; margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .edit-card { background: white; width: 100%; max-width: 450px; padding: 40px; border-radius: 25px; box-shadow: 0 15px 35px rgba(150, 96, 39, 0.15); border-top: 6px solid #966027; }
        h2 { color: #5a320f; text-align: center; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #966027; }
        input, select { width: 100%; padding: 12px; border: 2px solid #eee; border-radius: 12px; box-sizing: border-box; font-family: 'Sarabun'; transition: 0.3s; }
        input:focus { border-color: #966027; outline: none; }
        .btn-save { background: linear-gradient(135deg, #966027, #7a4a1d); color: white; border: none; padding: 14px; border-radius: 12px; cursor: pointer; width: 100%; font-weight: bold; margin-top: 10px; font-size: 16px; }
        .btn-save:hover { opacity: 0.9; }
        .btn-back { display: block; text-align: center; margin-top: 15px; color: #888; text-decoration: none; font-size: 14px; }
        .info-box { background: #fdf2f2; color: #c0392b; padding: 10px; border-radius: 8px; font-size: 13px; margin-bottom: 15px; border: 1px solid #f5c6cb; }
        .pw-notice { font-size: 12px; color: #666; font-weight: normal; margin-top: 5px; }
        .own-account-tag { background: #e8f5e9; color: #2e7d32; padding: 4px 10px; border-radius: 20px; font-size: 12px; display: inline-block; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="edit-card">
    <?php if ($is_own_account): ?>
        <div class="own-account-tag">✅ นี่คือบัญชีของคุณ</div>
    <?php endif; ?>

    <h2>👤 แก้ไขสิทธิ์สมาชิก</h2>
    
    <?php if(isset($error_msg)): ?>
        <div class="info-box"><?= $error_msg ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>ชื่อผู้ใช้งาน</label>
            <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>

        <div class="form-group">
            <label>อีเมล</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>

        <?php if ($user['role'] === 'user' || $is_own_account): ?>
            <div class="form-group" style="background: #fff9f0; padding: 15px; border-radius: 12px; border: 1px dashed #d6a85a;">
                <label style="color: #a96a2d;">🔑 เปลี่ยนรหัสผ่านใหม่</label>
                <input type="password" name="new_password" placeholder="พิมพ์รหัสผ่านใหม่ที่นี่">
                <p class="pw-notice">* เว้นว่างไว้หากไม่ต้องการเปลี่ยนรหัสผ่าน</p>
            </div>
        <?php else: ?>
            <div class="form-group">
                <label>รหัสผ่าน</label>
                <input type="text" value="********" disabled style="background: #f9f9f9; color: #ccc;">
                <p class="pw-notice">ไม่อนุญาตให้แก้ไขรหัสผ่านของ Admin ท่านอื่น</p>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label>ระดับสิทธิ์ (Role)</label>
            <select name="role">
                <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>👤 สมาชิกทั่วไป (User)</option>
                <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>🛡️ ผู้ดูแลระบบ (Admin)</option>
            </select>
        </div>

        <button type="submit" class="btn-save">บันทึกการเปลี่ยนแปลง</button>
        <a href="admin_member.php" class="btn-back">⬅️ ยกเลิกและย้อนกลับ</a>
    </form>
</div>

</body>
</html>