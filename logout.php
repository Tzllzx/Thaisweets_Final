<?php
session_start();

// ล้างค่า Session ทั้งหมด
$_SESSION = array();

// ลบ Cookie ของ Session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// ทำลาย Session
session_destroy();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>กำลังออกจากระบบ...</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600&display=swap');

        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #fff6e5; /* สีพื้นหลังโทนเดียวกับร้านขนมไทย */
            font-family: 'Sarabun', sans-serif;
            overflow: hidden;
        }

        .logout-container {
            text-align: center;
        }

        /* Animation รูปวงกลมหมุนๆ */
        .loader {
            width: 80px;
            height: 80px;
            border: 8px solid #f3f3f3;
            border-top: 8px solid #966027; /* สีน้ำตาลร้านขนม */
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        /* Animation ตัวหนังสือจางเข้าจางออก */
        .status-text {
            font-size: 24px;
            color: #5a320f;
            font-weight: bold;
            animation: pulse 1.5s ease-in-out infinite;
        }

        /* กราฟิกรูปขนมไทย (ตกแต่งเพิ่มเติม) */
        .icon {
            font-size: 50px;
            margin-bottom: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.95); }
        }
    </style>
</head>
<body>

    <div class="logout-container">
        <div class="icon">🍡</div>
        <div class="loader"></div>
        <div class="status-text">กำลังออกจากระบบ...</div>
        <p style="color: #a96a2d;">ขอบคุณที่ใช้บริการร้านขนมไทยของเรา</p>
    </div>

    <script>
        // หน่วงเวลา 2.5 วินาที (2500ms) แล้วค่อยไปที่หน้า index.php
        setTimeout(function() {
            window.location.href = "login.php";
        }, 2500);
    </script>

</body>
</html>