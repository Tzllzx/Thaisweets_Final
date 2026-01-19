<?php
session_start();
include 'db.php'; 

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $address  = trim($_POST['address']);
    
    // จัดการอัปโหลดรูปภาพ
    $profile_img = 'default.png'; 
    if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_img']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            // สร้างโฟลเดอร์ uploads ถ้ายังไม่มี
            if (!file_exists('uploads')) { mkdir('uploads', 0777, true); }
            
            $new_name = uniqid('profile_') . "." . $ext;
            $upload_path = 'uploads/' . $new_name;
            if (move_uploaded_file($_FILES['profile_img']['tmp_name'], $upload_path)) {
                $profile_img = $new_name;
            }
        }
    }

    if (isset($pdo)) {
        if ($password !== $confirm_password) {
            $error = "รหัสผ่านไม่ตรงกัน";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = "ชื่อผู้ใช้นี้ถูกใช้งานแล้ว";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (username, password, email, phone, address, profile_img, role) 
                        VALUES (?, ?, ?, ?, ?, ?, 'user')";
                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute([$username, $hashed_password, $email, $phone, $address, $profile_img])) {
                    $success = "สมัครสมาชิกสำเร็จ! กำลังไปหน้าเข้าสู่ระบบ...";
                    header("refresh:2; url=login.php");
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
        /* CSS สำหรับตกแต่งหน้าตา */
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #be9764 100%);
            font-family: 'Sarabun', sans-serif;
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            box-sizing: border-box;
        }

        .hero {
            background: #ffffff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
            box-sizing: border-box;
            text-align: center;
        }

        h2 {
            color: #333;
            margin-bottom: 25px;
            font-weight: 600;
        }

        input[type="text"], 
        input[type="password"],
        input[type="email"],
        textarea {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            outline: none;
            font-family: 'Sarabun', sans-serif;
        }

        input[type="file"] {
            margin-bottom: 20px;
            font-size: 14px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: #666;
            text-align: left;
            font-weight: 600;
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
            transition: background 0.3s;
        }

        .btn-main:hover {
            background: #8b5725;
        }

        .error-msg {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .success-msg {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 10px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .login-link {
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }

        .login-link a {
            color: #a96a2d;
            text-decoration: none;
            font-weight: bold;
        }

        hr {
            margin: 20px 0;
            border: 0;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>

<div class="hero">
    <h2>สมัครสมาชิก</h2>

    <?php if ($error): ?> <div class="error-msg"><?= htmlspecialchars($error) ?></div> <?php endif; ?>
    <?php if ($success): ?> <div class="success-msg"><?= htmlspecialchars($success) ?></div> <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <input type="text" name="username" placeholder="ชื่อผู้ใช้" required>
        <input type="password" name="password" placeholder="รหัสผ่าน" required>
        <input type="password" name="confirm_password" placeholder="ยืนยันรหัสผ่าน" required>
        
        <hr>
        
        <input type="email" name="email" placeholder="อีเมล (email)">
        <input type="text" name="phone" placeholder="เบอร์โทรศัพท์ (phone)">
        <textarea name="address" rows="3" placeholder="ที่อยู่ (address)"></textarea>
        
        <label>รูปโปรไฟล์ (profile_img):</label>
        <input type="file" name="profile_img" accept="image/*">

        <button type="submit" class="btn-main">ยืนยันการสมัคร</button>
    </form>

    <div class="login-link">
        มีบัญชีอยู่แล้ว? <a href="login.php">เข้าสู่ระบบ</a>
    </div>
</div>

</body>
</html>