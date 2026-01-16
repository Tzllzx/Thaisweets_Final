<?php
session_start();
require_once __DIR__ . '/db.php'; 

// ดึงข้อมูลผู้ใช้ (ถ้ามีการ Login) เพื่อแสดงรูปโปรไฟล์ใน Navbar
$user_data = null;
if (isset($_SESSION['user_id'])) {
    $stmt_user = $pdo->prepare("SELECT profile_img, username FROM users WHERE id = ?");
    $stmt_user->execute([$_SESSION['user_id']]);
    $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);
}

// คำนวณจำนวนสินค้าในตะกร้า
$cart = $_SESSION['cart'] ?? [];
$total = 0;
$cart_count = 0;
if (!empty($cart)) {
    foreach ($cart as $item) {
        $cart_count += isset($item['qty']) ? $item['qty'] : 0;
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ตะกร้าสินค้า - ร้านขนมไทย</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        
        :root {
            --primary: #a96a2d;
            --dark: #7a4a1d;
            --light-bg: #fff6e5;
            --accent: #d6a85a;
            --danger: #e74c3c;
        }

        body { 
            font-family: 'Sarabun', sans-serif; 
            background: var(--light-bg); 
            margin: 0; 
            color: #5a320f; 
        }

        /* ===== NAVBAR ===== */
        .navbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0 40px; height: 75px;
            background: linear-gradient(90deg, #7a4a1d, #a96a2d);
            border-bottom: 4px solid #d6a85a;
            position: sticky; top: 0; z-index: 1000;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .navbar .logo { font-size: 26px; font-weight: bold; color: #fff3d6; text-decoration: none; }
        .navbar nav { display: flex; align-items: center; gap: 10px; }
        .navbar nav a {
            text-decoration: none; color: #fff3d6; padding: 8px 18px;
            border-radius: 25px; transition: all 0.3s ease; font-size: 15px;
        }
        .navbar nav a:hover { background: rgba(255, 243, 214, 0.2); }

        /* Profile Styles ใน Navbar */
        .user-avatar-nav {
            width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid #fff3d6;
        }
        .user-avatar-initial {
            width: 32px; height: 32px; border-radius: 50%; background: #d6a85a; color: white;
            display: flex; justify-content: center; align-items: center; font-size: 14px; font-weight: bold; border: 2px solid #fff3d6;
        }

        .user-menu-container { position: relative; display: inline-block; margin-left: 5px; }
        .user-trigger {
            color: #fff3d6; cursor: pointer; padding: 5px 12px; border-radius: 20px;
            display: flex; align-items: center; gap: 8px; background: rgba(255, 255, 255, 0.1); font-size: 14px;
        }
        .user-trigger:hover { background: rgba(255, 255, 255, 0.2); }

        .dropdown-content {
            display: none; position: absolute; right: 0; top: 100%; background-color: white;
            min-width: 190px; box-shadow: 0px 8px 16px rgba(0,0,0,0.15); border-radius: 15px;
            z-index: 1001; margin-top: 10px; border: 1px solid #eee; animation: fadeIn 0.3s ease;
        }
        .dropdown-content::before { content: ""; position: absolute; top: -20px; left: 0; width: 100%; height: 20px; }
        .user-menu-container:hover .dropdown-content { display: block; }
        
        .dropdown-content a {
            color: #5a320f !important; padding: 12px 16px; text-decoration: none; display: block;
            font-size: 14px; transition: 0.2s; border-radius: 0 !important; text-align: left;
        }
        .dropdown-content a:hover { background-color: #fff6e5 !important; color: #a96a2d !important; }
        .dropdown-content .divider { height: 1px; background-color: #eee; margin: 0; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        /* ตะกร้าสินค้าพร้อม Badge */
        .cart-link { position: relative; display: inline-flex; align-items: center; background: rgba(255, 255, 255, 0.2); }
        .cart-badge {
            position: absolute; top: -10px; right: -12px;
            background-color: #ff3b3b; color: white; font-size: 12px;
            font-weight: bold; border-radius: 50%; width: 22px; height: 22px;
            display: flex; justify-content: center; align-items: center;
            box-shadow: 0 3px 6px rgba(0,0,0,0.3); border: 2px solid white; z-index: 10;
        }

        /* ===== CART CONTENT ===== */
        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        .page-title { text-align: center; color: var(--dark); margin-bottom: 30px; font-size: 2.2rem; }
        .cart-item { 
            background: white; margin-bottom: 15px; padding: 20px; 
            border-radius: 20px; display: grid; 
            grid-template-columns: 2fr 1fr 1fr 0.5fr; 
            align-items: center; box-shadow: 0 5px 15px rgba(0,0,0,0.03); 
            border-left: 6px solid transparent; transition: 0.3s; 
        }
        .cart-item:hover { transform: scale(1.02); border-left-color: var(--accent); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
        .item-info h3 { margin: 0; font-size: 1.2rem; color: var(--dark); }
        .item-info p { margin: 5px 0 0; color: #888; }
        .item-qty { display: flex; align-items: center; background: #f8f8f8; padding: 6px; border-radius: 30px; justify-self: center; border: 1px solid #eee; }
        .qty-btn { width: 32px; height: 32px; border-radius: 50%; border: none; background: white; display: flex; align-items: center; justify-content: center; text-decoration: none; color: var(--dark); box-shadow: 0 2px 5px rgba(0,0,0,0.1); font-weight: bold; transition: 0.2s; }
        .qty-btn:hover { background: var(--accent); color: white; }
        .qty-val { margin: 0 15px; font-weight: bold; font-size: 1.1rem; }
        .item-price { font-weight: bold; color: var(--primary); text-align: right; font-size: 1.2rem; }
        .btn-delete { color: #ccc; text-decoration: none; font-size: 1.4rem; justify-self: end; transition: 0.3s; padding: 5px; cursor: pointer; }
        .btn-delete:hover { color: var(--danger); transform: rotate(90deg); }
        
        .summary-box { background: var(--dark); color: white; padding: 35px; border-radius: 25px; margin-top: 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 10px 25px rgba(122, 74, 29, 0.2); }
        .summary-text p { margin: 0; opacity: 0.8; font-size: 1.1rem; }
        .summary-text h2 { margin: 5px 0 0; font-size: 2.2rem; }
        
        .actions { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 25px; }
        .btn { padding: 18px; border-radius: 50px; text-decoration: none; text-align: center; font-weight: bold; font-size: 1.1rem; transition: 0.3s; }
        .btn-continue { background: white; color: var(--dark); border: 2px solid var(--dark); }
        .btn-continue:hover { background: #eee; }
        .btn-checkout { background: var(--accent); color: var(--dark); box-shadow: 0 4px 15px rgba(214, 168, 90, 0.4); }
        .btn-checkout:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(214, 168, 90, 0.6); }
        
        .cart-empty { text-align: center; background: white; padding: 60px; border-radius: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<header class="navbar">
    <a href="index.php" class="logo">🍡 ร้านขนมไทย</a>
    <nav>
        <a href="index.php">หน้าแรก</a>
        <a href="products.php">สินค้าทั้งหมด</a>

        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="cart.php" class="cart-link" style="background: rgba(255,255,255,0.2);">
                🛒 ตะกร้า
                <?php if ($cart_count > 0): ?>
                    <span class="cart-badge"><?= $cart_count ?></span>
                <?php endif; ?>
            </a>

            <div class="user-menu-container">
                <div class="user-trigger">
                    <?php if (!empty($user_data['profile_img']) && file_exists("uploads/" . $user_data['profile_img'])): ?>
                        <img src="uploads/<?= htmlspecialchars($user_data['profile_img']) ?>" class="user-avatar-nav">
                    <?php else: ?>
                        <div class="user-avatar-initial"><?= mb_substr(htmlspecialchars($user_data['username']), 0, 1) ?></div>
                    <?php endif; ?>
                    
                    <span>สวัสดี, <strong><?= htmlspecialchars($user_data['username']) ?></strong> ▾</span>
                </div>
                <div class="dropdown-content">
                    <a href="my_orders.php">📦 คำสั่งซื้อของฉัน</a>
                    <a href="profile.php">👤 ข้อมูลส่วนตัว</a>
                    <div class="divider"></div>
                    <a href="logout.php" style="color: #c0392b !important; font-weight: bold;">🚪 ออกจากระบบ</a>
                </div>
            </div>

        <?php else: ?>
            <a href="login.php">เข้าสู่ระบบ</a>
            <a href="register.php" style="background: var(--accent); color: var(--dark); font-weight: bold;">สมัครสมาชิก</a>
        <?php endif; ?>
    </nav>
</header>

<div class="container">
    <h1 class="page-title">รายการในตะกร้า</h1>

    <?php if (empty($cart)): ?>
        <div class="cart-empty">
            <div style="font-size: 80px; margin-bottom: 20px;">🛒</div>
            <h2>ตะกร้าของคุณยังว่างเปล่า</h2>
            <p style="color: #888;">ไปเลือกชมขนมอร่อยๆ แล้วค่อยกลับมาใหม่นะคะ</p>
            <br>
            <a href="products.php" class="btn btn-checkout" style="display:inline-block; padding: 15px 40px;">ไปเลือกสินค้า</a>
        </div>
    <?php else: ?>
        <div class="cart-list">
            <?php foreach ($cart as $id => $item): 
                $sum = $item['price'] * $item['qty'];
                $total += $sum;
            ?>
            <div class="cart-item">
                <div class="item-info">
                    <h3><?= htmlspecialchars($item['name']) ?></h3>
                    <p>ราคา: <?= number_format($item['price'], 2) ?> ฿</p>
                </div>
                
                <div class="item-qty">
                    <a href="update_cart.php?id=<?= $id ?>&action=decrease" class="qty-btn">−</a>
                    <span class="qty-val"><?= $item['qty'] ?></span>
                    <a href="update_cart.php?id=<?= $id ?>&action=increase" class="qty-btn">+</a>
                </div>

                <div class="item-price"><?= number_format($sum, 2) ?> ฿</div>

                <a href="javascript:void(0);" 
                   class="btn-delete" 
                   onclick="confirmDelete('<?= $id ?>', '<?= htmlspecialchars($item['name']) ?>')">
                    ✕
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="summary-box">
            <div class="summary-text">
                <p>ยอดชำระรวมทั้งหมด</p>
                <h2><?= number_format($total, 2) ?> บาท</h2>
            </div>
            <div style="font-size: 45px;">💰</div>
        </div>

        <div class="actions">
            <a href="products.php" class="btn btn-continue">← เลือกเพิ่ม</a>
            <a href="checkout.php" class="btn btn-checkout">ไปที่หน้าชำระเงิน 💳</a>
        </div>
    <?php endif; ?>
</div>

<script>
function confirmDelete(id, name) {
    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: `คุณต้องการลบ "${name}" ออกจากตะกร้าใช่หรือไม่?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#a96a2d',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก',
        reverseButtons: true,
        borderRadius: '20px'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `remove_cart.php?id=${id}`;
        }
    })
}
</script>

</body>
</html>