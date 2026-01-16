<?php
session_start();
require_once "../db.php";

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (empty($username) || empty($password)) {
        $error = "กรุณากรอกชื่อผู้ใช้งานและรหัสผ่าน";
    } else {
        try {
            // เช็คว่า Username ซ้ำหรือไม่
            $stmt_check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt_check->execute([$username]);
            
            if ($stmt_check->rowCount() > 0) {
                $error = "ชื่อผู้ใช้งานนี้ถูกใช้ไปแล้ว กรุณาใช้ชื่ออื่น";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password, $role]);
                
                // ส่งกลับไปหน้าจัดการสมาชิกพร้อมข้อความแจ้งเตือน
                header("Location: admin_members.php?msg=added");
                exit;
            }
        } catch (PDOException $e) {
            $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มสมาชิกใหม่ - Admin Panel</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        
        body { 
            margin: 0; 
            font-family: 'Sarabun', sans-serif; 
            background: #fff6e5; 
            color: #5a320f;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .form-card {
            background: white;
            width: 100%;
            max-width: 450px;
            padding: 40px;
            border-radius: 30px;
            box-shadow: 0 20px 40px rgba(169, 106, 45, 0.15);
            border: 1px solid #f0e0d0;
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-header h2 {
            margin: 0;
            font-size: 24px;
            color: #7a4a1d;
        }

        .form-header p {
            color: #8d6e63;
            font-size: 14px;
            margin-top: 5px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
            color: #5a320f;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #eee;
            border-radius: 12px;
            font-family: 'Sarabun', sans-serif;
            font-size: 15px;
            transition: 0.3s;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: #d6a85a;
            background-color: #fffcf5;
            box-shadow: 0 0 0 4px rgba(214, 168, 90, 0.1);
        }

        select.form-control {
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%235a320f' d='M10.293 3.293L6 7.586 1.707 3.293A1 1 0 00.293 4.707l5 5a1 1 0 001.414 0l5-5a1 1 0 10-1.414-1.414z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            appearance: none;
        }

        .btn-submit {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #a96a2d, #7a4a1d);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(122, 74, 29, 0.3);
        }

        .btn-cancel {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #8d6e63;
            text-decoration: none;
            font-size: 14px;
            transition: 0.3s;
        }

        .btn-cancel:hover {
            color: #e74c3c;
        }

        .alert {
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }

        .alert-error {
            background: #fff0f0;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
    </style>
</head>
<body>

<div class="form-card">
    <div class="form-header">
        <div style="font-size: 40px; margin-bottom: 10px;">👤</div>
        <h2>เพิ่มสมาชิกใหม่</h2>
        <p>สร้างบัญชีผู้ใช้งานใหม่เข้าสู่ระบบ</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>ชื่อผู้ใช้งาน (Username)</label>
            <input type="text" name="username" class="form-control" placeholder="Username/Email" required>
        </div>

        <div class="form-group">
            <label>อีเมล (Email)</label>
            <input type="email" name="email" class="form-control" placeholder="example@mail.com">
        </div>

        <div class="form-group">
            <label>รหัสผ่าน (Password)</label>
            <input type="password" name="password" class="form-control" placeholder="ตั้งรหัสผ่าน 6 ตัวขึ้นไป" required>
        </div>

        <div class="form-group">
            <label>สิทธิ์การใช้งาน (Role)</label>
            <select name="role" class="form-control">
                <option value="user">👤 สมาชิกทั่วไป (User)</option>
                <option value="admin">🛡️ ผู้ดูแลระบบ (Admin)</option>
            </select>
        </div>

        <button type="submit" class="btn-submit">✅ บันทึกและสร้างสมาชิก</button>
        
        <a href="admin_member.php" class="btn-cancel">ยกเลิกและกลับหน้าเดิม</a>
    </form>
</div>

</body>
</html>