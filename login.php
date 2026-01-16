<?php
session_start();
$error = ''; 

if (file_exists('db.php')) {
    include 'db.php';
} else {
    die("ไม่พบไฟล์เชื่อมต่อฐานข้อมูล");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (isset($pdo)) {
        // แนะนำ: ในอนาคตควรใช้ password_hash และ password_verify เพื่อความปลอดภัย
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
        $stmt->execute([$username, $password]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role']; 

            // --- ส่วนที่เพิ่มใหม่: อัปเดตสถานะออนไลน์ทันทีที่ล็อกอิน ---
            try {
                $update_status = $pdo->prepare("UPDATE users SET last_active = NOW() WHERE id = ?");
                $update_status->execute([$user['id']]);
            } catch (PDOException $e) {
                // หากอัปเดตไม่ได้ ให้ข้ามไปก่อนเพื่อให้ล็อกอินสำเร็จ
            }
            // -----------------------------------------------------

            // แยกหน้าตาม Role
            if ($user['role'] === 'admin') {
                header("Location: admin/index_admin.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
        }
    } else {
        $error = 'ระบบเชื่อมต่อฐานข้อมูลมีปัญหา';
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เข้าสู่ระบบ</title>
    <style>
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
            transition: all 0.3s ease;
            outline: none;
        }

        input:focus {
            border-color: #a96a2d;
            box-shadow: 0 0 8px rgba(169, 106, 45, 0.2);
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
            transition: all 0.3s ease;
        }

        .btn-main:hover {
            background: #8b5725;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .error-msg {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #ffcdd2;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }

        .register-link a {
            color: #a96a2d;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="hero">
    <h2>เข้าสู่ระบบ</h2>

    <?php if (!empty($error)): ?>
        <div class="error-msg">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <input type="text" name="username" placeholder="ชื่อผู้ใช้" required>
        <input type="password" name="password" placeholder="รหัสผ่าน" required>

        <button type="submit" class="btn-main">
            เข้าสู่ระบบ
        </button>
    </form>

    <div class="register-link">
        ยังไม่มีบัญชี? <a href="register.php">สมัครสมาชิก</a>
    </div>
</div>

</body>
</html>