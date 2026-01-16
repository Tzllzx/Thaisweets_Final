<?php
session_start();
require_once "db.php"; 

// เช็คชื่อไฟล์ปัจจุบันสำหรับ Navbar
$current_page = basename($_SERVER['PHP_SELF']);

// ดึงข้อมูลผู้ใช้ (ถ้ามีการ Login)
$user_data = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT profile_img, username FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
}

// คำนวณข้อมูลตะกร้า
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            margin: 0;
            font-family: 'Sarabun', sans-serif;
            background: var(--light-bg);
            color: #5a320f;
        }

        /* ===== NAVBAR (ใช้ Style เดียวกับ index_test.php) ===== */
        .navbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0 40px; height: 75px;
            background: linear-gradient(90deg, #7a4a1d, #a96a2d);
            border-bottom: 4px solid #d6a85a;
            position: sticky; top: 0; z-index: 1000;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .navbar .logo { font-size: 24px; font-weight: bold; color: #fff3d6; text-decoration: none; }
        .navbar nav { display: flex; align-items: center; gap: 8px; }
        .navbar nav a {
            text-decoration: none; color: #fff3d6; padding: 8px 18px;
            border-radius: 25px; transition: 0.3s; font-size: 14px;
        }
        .nav-active { background: rgba(255, 255, 255, 0.2) !important; font-weight: 600; }
        .navbar nav a:hover { background: rgba(255, 243, 214, 0.1); }

        /* Cart & User Avatar */
        .cart-link { position: relative; background: rgba(0, 0, 0, 0.15); display: flex; align-items: center; gap: 5px; }
        .cart-badge {
            position: absolute; top: -8px; right: -8px; background: #e74c3c; color: white;
            font-size: 11px; border-radius: 10px; padding: 2px 6px; border: 2px solid white;
        }
        .user-avatar-nav { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid #fff3d6; }
        .user-avatar-initial { width: 32px; height: 32px; border-radius: 50%; background: #d6a85a; color: white; display: flex; justify-content: center; align-items: center; font-size: 14px; font-weight: bold; border: 2px solid #fff3d6; }

        /* User Dropdown (ใช้ Logic เดียวกับ index_test.php) */
        .user-menu-container { position: relative; margin-left: 10px; }
        .user-trigger {
            color: #fff3d6; cursor: pointer; padding: 6px 15px; border-radius: 25px;
            display: flex; align-items: center; gap: 8px; background: rgba(255, 255, 255, 0.1);
            transition: 0.3s;
        }
        .dropdown-content {
            display: none; position: absolute; right: 0; top: calc(100% + 10px);
            background: white; min-width: 200px; border-radius: 12px; overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15); z-index: 1001;
        }
        .dropdown-content.active { display: block; animation: slideUp 0.2s ease; }
        .dropdown-content a { color: #5a320f !important; padding: 12px 18px; display: block; text-decoration: none; border-bottom: 1px solid #f8f8f8; text-align: left; }
        .dropdown-content a:hover { background: #fff6e5 !important; }

        @keyframes slideUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* ===== CART CONTENT ===== */
        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; min-height: 60vh; }
        .page-title { text-align: center; color: var(--dark); margin-bottom: 30px; font-size: 2.2rem; }

        .cart-item { 
            background: white; margin-bottom: 15px; padding: 15px 25px; 
            border-radius: 20px; display: grid; 
            grid-template-columns: 90px 2fr 1.2fr 1fr 40px; 
            align-items: center; gap: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: 0.3s;
        }
        .cart-item:hover { transform: translateY(-3px); }
        
        .item-img { width: 80px; height: 80px; border-radius: 15px; object-fit: cover; }
        .item-info h3 { margin: 0; font-size: 1.1rem; color: var(--dark); }
        .item-info p { margin: 5px 0 0; color: var(--primary); font-weight: bold; }
        
        .item-qty { display: flex; align-items: center; background: #f8f8f8; padding: 5px; border-radius: 30px; border: 1px solid #eee; width: fit-content; }
        .qty-btn { width: 30px; height: 30px; border-radius: 50%; border: none; background: white; display: flex; align-items: center; justify-content: center; text-decoration: none; color: var(--dark); font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .qty-val { margin: 0 15px; font-weight: bold; }
        
        .item-total { font-weight: bold; color: var(--dark); text-align: right; }
        .btn-delete { color: #ccc; cursor: pointer; font-size: 1.2rem; transition: 0.3s; text-align: center; }
        .btn-delete:hover { color: var(--danger); transform: rotate(90deg); }

        /* Summary & Actions */
        .summary-box { background: var(--dark); color: white; padding: 30px; border-radius: 25px; margin-top: 30px; display: flex; justify-content: space-between; align-items: center; }
        .summary-box h2 { margin: 0; font-size: 2rem; }
        
        .actions { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 25px; }
        .btn { padding: 18px; border-radius: 50px; text-decoration: none; text-align: center; font-weight: bold; transition: 0.3s; }
        .btn-continue { background: white; color: var(--dark); border: 2px solid var(--dark); }
        .btn-checkout { background: var(--accent); color: #5a320f; box-shadow: 0 4px 15px rgba(214, 168, 90, 0.4); }
        .btn-checkout:hover { transform: translateY(-3px); background: #eac17a; }

        /* Empty State */
        .cart-empty { text-align: center; background: white; padding: 60px; border-radius: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<header class="navbar">
    <a href="index_test.php" class="logo">🍡 ร้านขนมไทย</a>
    <nav>
        <a href="index_test.php" class="<?= ($current_page == 'index_test.php') ? 'nav-active' : '' ?>">หน้าแรก</a>
        <a href="products_test.php" class="<?= ($current_page == 'products_test.php') ? 'nav-active' : '' ?>">สินค้าทั้งหมด</a>

        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="cart_test.php" class="cart-link <?= ($current_page == 'cart_test.php') ? 'nav-active' : '' ?>">
                🛒 ตะกร้า
                <?php if ($cart_count > 0): ?>
                    <span class="cart-badge"><?= $cart_count ?></span>
                <?php endif; ?>
            </a>

            <div class="user-menu-container">
                <div class="user-trigger" id="userBtn">
                    <?php if (!empty($user_data['profile_img'])): ?>
                        <img src="uploads/<?= htmlspecialchars($user_data['profile_img']) ?>" class="user-avatar-nav">
                    <?php else: ?>
                        <div class="user-avatar-initial"><?= mb_substr(htmlspecialchars($user_data['username']), 0, 1) ?></div>
                    <?php endif; ?>
                    <span><?= htmlspecialchars($user_data['username']) ?> ▾</span>
                </div>
                <div class="dropdown-content" id="userDropdown">
                    <a href="my_orders_test.php">📦 คำสั่งซื้อของฉัน</a>
                    <a href="profile_test.php">👤 ข้อมูลส่วนตัว</a>
                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="../index_admin.php" style="color: #a96a2d !important; font-weight: bold;">🛠️ แผงควบคุม Admin</a>
                    <?php endif; ?>
                    <a href="../..logout.php" style="color: #c0392b !important; font-weight: bold;">🚪 ออกจากระบบ</a>
                </div>
            </div>
        <?php else: ?>
            <a href="../..login.php">เข้าสู่ระบบ</a>
            <a href="../..register.php" style="background: #d6a85a; color: #5a320f; font-weight: bold;">สมัครสมาชิก</a>
        <?php endif; ?>
    </nav>
</header>

<div class="container">
    <h1 class="page-title">รายการในตะกร้า</h1>

    <?php if (empty($cart)): ?>
        <div class="cart-empty">
            <div style="font-size: 80px; margin-bottom: 20px;">🛒</div>
            <h2>ตะกร้าของคุณยังว่างเปล่า</h2>
            <p style="color: #888;">ขนมอร่อยๆ กำลังรอคุณอยู่นะคะ</p><br>
            <a href="products_test.php" class="btn btn-checkout" style="display:inline-block; padding: 15px 40px;">ไปเลือกสินค้าเลย</a>
        </div>
    <?php else: ?>
        <div class="cart-list">
            <?php foreach ($cart as $id => $item): 
                $sum = $item['price'] * $item['qty'];
                $total += $sum;
                // Path รูปภาพ (ปรับให้ตรงกับโครงสร้างคุณ)
                $img = !empty($item['image']) ? "../assets/images/" . $item['image'] : "../assets/images/no-image.jpg";
            ?>
            <div class="cart-item">
                <img src="<?= $img ?>" class="item-img" onerror="this.src='https://via.placeholder.com/150?text=Dessert'">
                <div class="item-info">
                    <h3><?= htmlspecialchars($item['name']) ?></h3>
                    <p><?= number_format($item['price'], 2) ?> ฿</p>
                </div>
                
                <div class="item-qty">
                    <a href="update_cart.php?id=<?= $id ?>&action=decrease" class="qty-btn">−</a>
                    <span class="qty-val"><?= $item['qty'] ?></span>
                    <a href="update_cart.php?id=<?= $id ?>&action=increase" class="qty-btn">+</a>
                </div>

                <div class="item-total"><?= number_format($sum, 2) ?> ฿</div>

                <div class="btn-delete" onclick="confirmDelete('<?= $id ?>', '<?= htmlspecialchars($item['name']) ?>')">✕</div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="summary-box">
            <div>
                <p style="margin:0; opacity:0.8;">ยอดชำระรวมทั้งหมด</p>
                <h2><?= number_format($total, 2) ?> บาท</h2>
            </div>
            <div style="font-size: 45px;">💰</div>
        </div>

        <div class="actions">
            <a href="products_test.php" class="btn btn-continue">← กลับไปเลือกเพิ่ม</a>
            <a href="checkout_test.php" class="btn btn-checkout">ไปหน้าชำระเงิน 💳</a>
        </div>
    <?php endif; ?>
</div>

<script>
    // User Dropdown Logic (เหมือนหน้า index_test)
    const userBtn = document.getElementById('userBtn');
    const userDropdown = document.getElementById('userDropdown');

    if (userBtn) {
        userBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdown.classList.toggle('active');
        });
        document.addEventListener('click', () => {
            userDropdown.classList.remove('active');
        });
    }

    // SweetAlert ลบสินค้า
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'ยืนยันการลบ?',
            text: `คุณต้องการลบ "${name}" ออกจากตะกร้าใช่หรือไม่?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#a96a2d',
            cancelButtonColor: '#e74c3c',
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