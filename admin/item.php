<?php
session_start();
require_once "../db.php"; 

$search = $_GET['search'] ?? '';

// 1. ดึงข้อมูลสินค้า
if (isset($pdo)) {
    if ($search != '') {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? ORDER BY id DESC");
        $stmt->execute(["%$search%"]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM products ORDER BY id DESC");
        $stmt->execute();
    }
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. ดึงจำนวนรายการที่รอการยืนยัน (Dynamic Count)
    try {
        $stmt_count = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'waiting_verification'");
        $count_pending = $stmt_count->fetchColumn();
    } catch (PDOException $e) {
        $count_pending = 0;
    }
} else {
    die("ไม่พบการเชื่อมต่อฐานข้อมูล (\$pdo) กรุณาตรวจสอบไฟล์ db.php");
}

// 3. กำหนดสีปุ่มตามเงื่อนไข (ส้มเมื่อมีงาน / เขียวเมื่อเคลียร์หมด)
$btn_color = ($count_pending > 0) ? "#f39c12" : "#27ae60";
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สินค้าขนมไทย - ทั้งหมด (Admin)</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');

        body {
            margin: 0;
            font-family: 'Sarabun', sans-serif;
            background: #fff6e5;
            color: #5a320f;
        }

        /* ===== NAVBAR ===== */
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

        .navbar .logo {
            font-size: 24px;
            font-weight: bold;
            color: #fff3d6;
            text-decoration: none;
        }

        .navbar nav {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar nav a {
            text-decoration: none;
            color: #fff3d6;
            padding: 8px 18px;
            border-radius: 25px;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .navbar nav a:hover {
            background: rgba(255, 243, 214, 0.2);
        }

        .user-greeting {
            color: #fff3d6;
            font-size: 14px;
            border-right: 1px solid rgba(255, 255, 255, 0.3);
            padding-right: 15px;
            margin-right: 5px;
        }

        /* ปุ่มยืนยันชำระเงินแบบ Dynamic */
        .btn-verify {
            background-color: <?= $btn_color ?> !important;
            color: white !important;
            font-weight: bold !important;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .badge {
            background-color: #c0392b;
            color: white;
            padding: 1px 7px;
            border-radius: 50%;
            font-size: 11px;
            border: 1px solid white;
        }

        .btn-logout { background: #c0392b !important; color: white !important; }

        /* ===== CONTAINER & GRID ===== */
        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        h2 { color: #966027; border-left: 5px solid #a96a2d; padding-left: 15px; margin-bottom: 30px; }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }

        .product-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            text-align: center;
            transition: 0.3s;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 25px rgba(169, 106, 45, 0.2);
        }

        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 15px;
            margin-bottom: 15px;
        }

        .product-card h3 { margin: 10px 0; font-size: 18px; color: #5a320f; }
        .price { font-size: 20px; font-weight: bold; color: #a96a2d; margin-bottom: 15px; }

        .btn-cart {
            background: #a96a2d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
            transition: 0.3s;
        }
        .btn-cart:hover { background: #7a4a1d; }

        /* Floating Button ปรับเป็น Dynamic */
        .floating-check-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: <?= $btn_color ?>;
            color: white;
            padding: 15px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            z-index: 999;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: 0.3s;
        }

        .floating-check-btn:hover {
            transform: scale(1.05);
            filter: brightness(1.1);
        }
    </style>
</head>
<body>

<header class="navbar">
    <a href="index_admin.php" class="logo">🍡 ระบบจัดการร้านขนม</a>
    <nav>
        <a href="index_admin.php">หน้าแรก</a>
        <a href="item.php" style="background: rgba(255,255,255,0.2);">สินค้าทั้งหมด</a>

        <?php if(isset($_SESSION['user_id'])): ?>
            <span class="user-greeting">สวัสดี, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></span>
            

            
            <a href="admin_products.php">จัดการสินค้า</a>
            <a href="admin_member.php">สมาชิก</a>
            <a href="../logout.php" class="btn-logout">ออกจากระบบ</a>
        <?php else: ?>
            <a href="login.php">เข้าสู่ระบบ</a>
        <?php endif; ?>
    </nav>
</header>

<div class="container">
    <h2>🌸 สินค้าขนมไทยทั้งหมด</h2>

    <?php if (empty($products)): ?>
        <div style="text-align:center; padding: 50px;">
            <p style="font-size: 20px; color: #999;">❌ ไม่พบสินค้าที่คุณกำลังมองหา</p>
            <a href="item.php" style="color:#a96a2d;">ดูสินค้าทั้งหมด</a>
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $row): ?>
                <div class="product-card">
                    <a href="product_detail.php?id=<?= $row['id'] ?>" style="text-decoration: none; color: inherit;">
                        <?php 
                        $image_path = !empty($row['image']) ? "../assets/images/" . $row['image'] : "../assets/images/no-image.png";
                        ?>
                        <img src="<?= htmlspecialchars($image_path) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                        <h3><?= htmlspecialchars($row['name']) ?></h3>
                        <p class="price"><?= number_format($row['price'], 2) ?> บาท</p>
                    </a>

                    <form action="add_to_cart.php" method="post">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <input type="hidden" name="name" value="<?= htmlspecialchars($row['name']) ?>">
                        <input type="hidden" name="price" value="<?= $row['price'] ?>">
                        <button type="submit" class="btn-cart">🛒 ใส่ตะกร้า</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php if(isset($_SESSION['user_id'])): ?>
    <a href="admin_approve_payment.php" class="floating-check-btn">
        <span>🔍 ตรวจสอบการชำระ (<?= $count_pending ?>)</span>
    </a>
<?php endif; ?>

</body>
</html>