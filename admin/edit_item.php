<?php
session_start();
require_once "../db.php";

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: logout.php");
    exit;
}

// 1. ตรวจสอบว่ามีการส่ง ID มาหรือไม่
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: ไม่พบ ID สินค้าที่ต้องการแก้ไข <a href='admin_products.php'>ย้อนกลับ</a>");
}

$id = $_GET['id'];

// 2. ดึงข้อมูลสินค้า
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    die("Error: ไม่พบข้อมูลสินค้าในระบบ <a href='admin_products.php'>ย้อนกลับ</a>");
}

// 3. จัดการการ Update ข้อมูล
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $detail = $_POST['detail'];
    
    // จัดการเรื่องรูปภาพ (ถ้ามีการอัปโหลดใหม่)
    if (!empty($_FILES['image']['name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = "product_" . time() . "." . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], "../assets/images/" . $image_name);
        
        $sql = "UPDATE products SET name=?, price=?, detail=?, image=? WHERE id=?";
        $params = [$name, $price, $detail, $image_name, $id];
    } else {
        $sql = "UPDATE products SET name=?, price=?, detail=? WHERE id=?";
        $params = [$name, $price, $detail, $id];
    }

    if ($pdo->prepare($sql)->execute($params)) {
        echo "<script>alert('แก้ไขข้อมูลสำเร็จ'); window.location='admin_products.php';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขสินค้า - <?= htmlspecialchars($product['name']) ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        
        body {
            margin: 0;
            font-family: 'Sarabun', sans-serif;
            background: #fff6e5;
            color: #5a320f;
        }

        /* ===== NAVBAR สไตล์เดียวกับหน้ารายการ ===== */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 40px;
            height: 75px;
            background: linear-gradient(90deg, #7a4a1d, #a96a2d);
            border-bottom: 4px solid #d6a85a;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .navbar .logo { font-size: 24px; font-weight: bold; color: #fff3d6; }
        .navbar nav a { text-decoration: none; color: #fff3d6; padding: 8px 18px; border-radius: 25px; transition: 0.3s; font-size: 15px; }
        .navbar nav a:hover { background: rgba(255, 243, 214, 0.2); }
        .user-info { color: #ffd68a; font-weight: bold; padding-right: 15px; border-right: 1px solid rgba(255, 243, 214, 0.3); margin-right: 10px; }
        .btn-logout { background: #c0392b !important; color: white !important; }

        /* ===== EDIT CARD ===== */
        .container { padding: 40px 20px; display: flex; justify-content: center; }
        .edit-card { 
            background: white; 
            width: 100%;
            max-width: 600px; 
            padding: 40px; 
            border-radius: 25px; 
            box-shadow: 0 15px 35px rgba(150, 96, 39, 0.1); 
            border-top: 6px solid #966027;
        }

        h2 { color: #5a320f; margin-bottom: 30px; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #966027; }
        
        input[type="text"], input[type="number"], textarea {
            width: 100%; padding: 12px 15px; border: 2px solid #f1f1f1;
            border-radius: 12px; box-sizing: border-box; font-family: 'Sarabun', sans-serif;
            font-size: 15px; transition: 0.3s; background: #fafafa;
        }
        input:focus, textarea:focus { border-color: #d6a85a; outline: none; background: white; }

        .current-img { 
            width: 120px; height: 120px; object-fit: cover; 
            border-radius: 15px; border: 3px solid #f0e0d0; margin-bottom: 10px; 
        }

        .btn-save { 
            background: linear-gradient(135deg, #966027, #7a4a1d); color: white;
            border: none; padding: 15px; border-radius: 12px; cursor: pointer;
            width: 100%; font-size: 16px; font-weight: bold; transition: 0.3s;
            box-shadow: 0 5px 15px rgba(150, 96, 39, 0.3);
        }
        .btn-save:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(150, 96, 39, 0.4); }

        .btn-back {
            display: block; text-align: center; margin-top: 20px;
            color: #888; text-decoration: none; font-size: 14px; transition: 0.3s;
        }
        .btn-back:hover { color: #966027; text-decoration: underline; }
    </style>
</head>
<body>

<header class="navbar">
    <div class="logo">🔧 ระบบหลังบ้าน</div>
    <nav>
        <span class="user-info">ผู้ดูแล: <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
        <a href="index_admin.php">หน้าแรกเว็บ</a>
        <a href="admin_products.php">จัดการสินค้า</a>
        <a href="edit_member.php">จัดการสมาชิก</a>
        <a href="../logout.php" class="btn-logout">ออกจากระบบ</a>
    </nav>
</header>

<div class="container">
    <div class="edit-card">
        <h2>📝 แก้ไขข้อมูลสินค้า</h2>
        
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>ชื่อขนมไทย</label>
                <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>

            <div class="form-group">
                <label>ราคา (บาท)</label>
                <input type="number" name="price" step="0.01" value="<?= $product['price'] ?>" required>
            </div>

            <div class="form-group">
                <label>รายละเอียดความอร่อย</label>
                <textarea name="detail" rows="4"><?= htmlspecialchars($product['detail']) ?></textarea>
            </div>

            <div class="form-group">
                <label>รูปภาพสินค้า</label>
                <div style="display: flex; flex-direction: column; align-items: center; background: #fffaf0; padding: 15px; border-radius: 15px; border: 1px dashed #d6a85a;">
                    <small style="color: #966027; margin-bottom: 10px;">รูปภาพปัจจุบัน:</small>
                    <img src="../assets/images/<?= !empty($product['image']) ? $product['image'] : 'no-image.jpg' ?>" class="current-img">
                    <input type="file" name="image" accept="image/*">
                </div>
            </div>

            <button type="submit" class="btn-save">✨ บันทึกการเปลี่ยนแปลง</button>
            <a href="admin_products.php" class="btn-back">⬅️ ยกเลิกและย้อนกลับ</a>
        </form>
    </div>
</div>

</body>
</html>