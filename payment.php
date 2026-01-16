<?php
session_start();
require_once "../db.php"; 

$search = $_GET['search'] ?? '';

if (isset($pdo)) {
    if ($search != '') {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? ORDER BY id DESC");
        $stmt->execute(["%$search%"]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM products ORDER BY id DESC");
        $stmt->execute();
    }
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    die("ไม่พบการเชื่อมต่อฐานข้อมูล (\$pdo) กรุณาตรวจสอบไฟล์ db.php");
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สินค้าขนมไทย - ทั้งหมด</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');

        :root {
            --primary: #a96a2d;
            --dark: #7a4a1d;
            --light-bg: #fff6e5;
            --accent: #d6a85a;
            --success: #27ae60;
        }

        body {
            margin: 0;
            font-family: 'Sarabun', sans-serif;
            background: var(--light-bg);
            color: #5a320f;
        }

        /* ===== NAVBAR ===== */
        .navbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0 40px; height: 75px;
            background: linear-gradient(90deg, var(--dark), var(--primary));
            border-bottom: 4px solid var(--accent);
            position: sticky; top: 0; z-index: 1000;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .navbar .logo { font-size: 26px; font-weight: bold; color: #fff3d6; text-decoration: none; }
        .navbar nav { display: flex; align-items: center; gap: 8px; }
        .navbar nav a {
            text-decoration: none; color: #fff3d6; padding: 8px 18px;
            border-radius: 25px; transition: 0.3s; font-size: 14px;
        }
        .navbar nav a:hover { background: rgba(255, 255, 255, 0.2); }
        
        .user-greeting {
            color: #fff3d6; font-size: 14px; margin-right: 10px;
            padding-right: 15px; border-right: 1px solid rgba(255, 255, 255, 0.3);
        }

        .btn-payment { background: var(--success) !important; color: white !important; font-weight: bold; }
        .btn-logout { background: #c0392b !important; color: white !important; }
        .btn-register { background: var(--accent) !important; color: #5a320f !important; font-weight: bold; }

        /* ===== CONTAINER & GRID ===== */
        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        h2 { color: var(--dark); border-left: 5px solid var(--primary); padding-left: 15px; margin-bottom: 30px; }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }

        .product-card {
            background: white; border-radius: 20px; padding: 20px;
            text-align: center; transition: 0.3s;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            display: flex; flex-direction: column; justify-content: space-between;
        }
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 25px rgba(169, 106, 45, 0.2);
        }
        .product-card img {
            width: 100%; height: 200px; object-fit: cover;
            border-radius: 15px; margin-bottom: 15px;
        }
        .product-card h3 { margin: 10px 0; font-size: 18px; color: #5a320f; }
        .price { font-size: 20px; font-weight: bold; color: var(--primary); margin-bottom: 15px; }

        .btn-cart {
            background: var(--primary); color: white; border: none;
            padding: 12px; border-radius: 25px; cursor: pointer;
            font-weight: bold; width: 100%; transition: 0.3s;
        }
        .btn-cart:hover { background: var(--dark); }

        /* ===== FLOATING BUTTON ===== */
        .floating-payment-btn {
            position: fixed; bottom: 30px; right: 30px;
            background: var(--success); color: white;
            padding: 15px 25px; border-radius: 50px;
            text-decoration: none; font-weight: bold;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            z-index: 999; display: flex; align-items: center; gap: 10px;
            transition: 0.3s; border: 2px solid white;
        }
        .floating-payment-btn:hover { transform: scale(1.1); background: #2ecc71; }
    </style>
</head>
<body>

<header class="navbar">
    <a href="index_admin.php" class="logo">🍡 ร้านขนมไทย</a>
    <nav>
        <a href="index_admin.php">หน้าแรก</a>
        <a href="item.php" style="background: rgba(255,255,255,0.2);">สินค้าทั้งหมด</a>

        <?php if(isset($_SESSION['user_id'])): ?>
            <span class="user-greeting">สวัสดี, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></span>
            
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                <a href="admin_products.php">จัดการสินค้า</a>
                <a href="admin_member.php">รายชื่อผู้ใช้งาน</a>
                <a href="admin_approve_payment.php">ตรวจสอบยอดชำระ</a>
            <?php endif; ?>

            <a href="my_orders.php">รายการสั่งซื้อ</a>
            <a href="payment.php" class="btn-payment">💳 แจ้งชำระเงิน</a>
            <a href="../logout.php" class="btn-logout">ออกจากระบบ</a>
        <?php else: ?>
            <a href="login.php">เข้าสู่ระบบ</a>
            <a href="register.php" class="btn-register">สมัครสมาชิก</a>
        <?php endif; ?>
    </nav>
</header>

<div class="container">
    <h2>🌸 สินค้าขนมไทยทั้งหมด</h2>

    <?php if (empty($products)): ?>
        <div style="text-align:center; padding: 50px;">
            <p style="font-size: 20px; color: #999;">❌ ไม่พบสินค้าที่คุณกำลังมองหา</p>
            <a href="item.php" style="color:var(--primary);">ดูสินค้าทั้งหมด</a>
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
    <a href="payment.php" class="floating-payment-btn">
        <span>💰 ยืนยันชำระเงินที่นี่</span>
    </a>
<?php endif; ?>

</body>
</html>