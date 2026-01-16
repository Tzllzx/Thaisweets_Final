<?php
session_start();
$error = '';
$success = '';

if (file_exists('db.php')) {
    include 'db.php';
} else {
    die("ไม่พบไฟล์เชื่อมต่อฐานข้อมูล");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (isset($pdo)) {
        // 1. ตรวจสอบว่ารหัสผ่านตรงกันไหม
        if ($password !== $confirm_password) {
            $error = "รหัสผ่านไม่ตรงกัน";
        } else {
            // 2. ตรวจสอบว่ามีชื่อผู้ใช้นี้อยู่แล้วหรือยัง
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = "ชื่อผู้ใช้นี้ถูกใช้งานแล้ว";
            } else {
                // 3. บันทึกลงฐานข้อมูล (แนะนำให้ใช้ password_hash เพื่อความปลอดภัย)
                // ในตัวอย่างนี้ผมใช้ $password ตรงๆ ตามโค้ด Login เดิมของคุณ
                // แต่ถ้าต้องการความปลอดภัยสูง ให้เปลี่ยนเป็น password_hash($password, PASSWORD_DEFAULT)
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
                if ($stmt->execute([$username, $password])) {
                    $success = "สมัครสมาชิกสำเร็จ! กำลังไปหน้าเข้าสู่ระบบ...";
                    header("refresh:2; url=login.php"); // ส่งไปหน้า login ใน 2 วินาที
                } else {
                    $error = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สมัครสมาชิก</title>
    <style>
        /* ใช้ CSS เดียวกับหน้า Login เพื่อความต่อเนื่อง */
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #be9764 100%);
            font-family: 'Sarabun', sans-serif;
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero {
            background: #ffffff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
        }

        h2 {
            color: #333;
            margin-bottom: 25px;
            font-weight: 600;
            text-align: center;
        }

        input[type="text"], 
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            outline: none;
        }

        .btn-main {
            width: 100%;
            padding: 12px;
            background: #a96a2d;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }

        .error-msg {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 20px;
            text-align: center;
        }

        .success-msg {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 10px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 20px;
            text-align: center;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }

        .login-link a {
            color: #a96a2d;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="hero">
    <h2>สมัครสมาชิก</h2>

    <?php if (!empty($error)): ?>
        <div class="error-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="success-msg"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="text" name="username" placeholder="ชื่อผู้ใช้" required>
        <input type="password" name="password" placeholder="รหัสผ่าน" required>
        <input type="password" name="confirm_password" placeholder="ยืนยันรหัสผ่าน" required>

        <button type="submit" class="btn-main">ยืนยันการสมัคร</button>
    </form>

    <div class="login-link">
        มีบัญชีอยู่แล้ว? <a href="login.php">เข้าสู่ระบบ</a>
    </div>
</div>

</body>
</html>