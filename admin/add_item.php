<?php
session_start();
require_once "../db.php";

// 1. ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../logout.php");
    exit;
}

// 2. จัดการเมื่อมีการกด Submit Form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $detail = $_POST['detail'];
    $image_name = 'no-image.jpg'; // ค่าเริ่มต้นถ้าไม่ได้อัปโหลดรูป

    // จัดการอัปโหลดรูปภาพ
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = "product_" . time() . "." . $ext; // ตั้งชื่อไฟล์ใหม่ป้องกันซ้ำ
        $target = "../assets/images/" . $image_name;
        
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
    }

    // บันทึกลง Database
    $sql = "INSERT INTO products (name, price, detail, image) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$name, $price, $detail, $image_name])) {
        echo "<script>alert('เพิ่มสินค้าสำเร็จ!'); window.location='admin_products.php';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เพิ่มสินค้าใหม่ - ระบบหลังบ้าน</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        body { font-family: 'Sarabun', sans-serif; background-color: #fdfaf5; margin: 0; padding: 40px; }
        
        .form-card {
            background: white;
            max-width: 600px;
            margin: 0 auto;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border-top: 5px solid #966027;
        }

        h2 { color: #966027; text-align: center; margin-bottom: 25px; }

        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #5a320f; }
        
        input[type="text"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-sizing: border-box;
            font-family: 'Sarabun', sans-serif;
        }

        input[type="file"] { padding: 10px 0; }

        .btn-submit {
            background: #966027;
            color: white;
            border: none;
            padding: 15px;
            width: 100%;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-submit:hover { background: #7a4a1d; }

        .btn-back {
            display: block;
            text-align: center;
            margin-top: 15px;
            text-decoration: none;
            color: #888;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="form-card">
    <h2>➕ เพิ่มขนมใหม่เข้าหน้าร้าน</h2>
    
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>ชื่อขนม</label>
            <input type="text" name="name" placeholder="เช่น ทองหยอด, ข้าวเหนียวมะม่วง" required>
        </div>

        <div class="form-group">
            <label>ราคา (บาท)</label>
            <input type="number" name="price" placeholder="0.00" step="0.01" required>
        </div>

        <div class="form-group">
            <label>รายละเอียดสินค้า</label>
            <textarea name="detail" rows="4" placeholder="อธิบายความอร่อยของขนม..."></textarea>
        </div>

        <div class="form-group">
            <label>รูปภาพสินค้า</label>
            <input type="file" name="image" accept="image/*">
            <small style="color: #999;">แนะนำขนาดรูปสี่เหลี่ยมจัตุรัสเพื่อให้แสดงผลสวยงาม</small>
        </div>

        <button type="submit" class="btn-submit">บันทึกข้อมูลสินค้า</button>
        <a href="admin_products.php" class="btn-back">⬅️ ย้อนกลับไปหน้าจัดการ</a>
    </form>
</div>

</body>
</html>