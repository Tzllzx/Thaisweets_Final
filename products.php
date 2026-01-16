<?php
session_start();
require_once __DIR__ . '/db.php'; 

// ดึงข้อมูลผู้ใช้ (ถ้ามีการ Login) เพื่อแสดงรูปโปรไฟล์
$user_data = null;
if (isset($_SESSION['user_id'])) {
    $stmt_user = $pdo->prepare("SELECT profile_img, username FROM users WHERE id = ?");
    $stmt_user->execute([$_SESSION['user_id']]);
    $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);
}

// นับจำนวนสินค้าในตะกร้าสำหรับ Badge
$cart_count = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += isset($item['qty']) ? $item['qty'] : 0;
    }
}

$search = $_GET['search'] ?? '';

if (isset($pdo)) {
    if ($search != '') {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? ORDER BY created_at DESC");
        $stmt->execute(["%$search%"]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM products ORDER BY created_at DESC");
        $stmt->execute();
    }
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    die("ไม่พบการเชื่อมต่อฐานข้อมูล (\$pdo)");
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สินค้าขนมไทย - ทั้งหมด</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');

        body { margin: 0; font-family: 'Sarabun', sans-serif; background: #fff6e5; color: #5a320f; }

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

        /* Profile Styles */
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
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .user-menu-container:hover .dropdown-content { display: block; }

        .dropdown-content a {
            color: #5a320f !important; padding: 12px 16px; text-decoration: none; display: block;
            font-size: 14px; transition: 0.2s; border-radius: 0 !important; text-align: left;
        }
        .dropdown-content a:hover { background-color: #fff6e5 !important; color: #a96a2d !important; }
        .dropdown-content .divider { height: 1px; background-color: #eee; margin: 0; }

        /* ตะกร้าสินค้าพร้อม Badge */
        .cart-link { position: relative; display: inline-flex; align-items: center; background: rgba(0, 0, 0, 0.15); }
        .cart-badge {
            position: absolute; top: -10px; right: -12px;
            background-color: #ff3b3b; color: white; font-size: 12px;
            font-weight: bold; border-radius: 50%; width: 22px; height: 22px;
            display: flex; justify-content: center; align-items: center;
            box-shadow: 0 3px 6px rgba(0,0,0,0.3); border: 2px solid white; z-index: 10;
        }

        .btn-register { background: #d6a85a !important; color: #5a320f !important; font-weight: bold; }

        /* ===== PRODUCT GRID & SEARCH ===== */
        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .search-area { text-align: center; margin-bottom: 40px; }
        .search-area input {
            padding: 15px 25px; width: 350px; border-radius: 30px 0 0 30px;
            border: 2px solid #d6a85a; outline: none; font-size: 16px;
        }
        .search-area button {
            padding: 15px 30px; border-radius: 0 30px 30px 0; border: none;
            background: #a96a2d; color: white; font-weight: bold;
            cursor: pointer; transition: 0.3s; margin-left: -5px; font-size: 16px;
        }

        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }
        .product-card {
            background: white; border-radius: 20px; padding: 20px;
            text-align: center; transition: 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            display: flex; flex-direction: column;
        }
        .product-card:hover { transform: translateY(-10px); box-shadow: 0 12px 30px rgba(169, 106, 45, 0.2); }
        .product-card img { width: 100%; height: 200px; object-fit: cover; border-radius: 15px; margin-bottom: 15px; }
        .product-card h3 { margin: 10px 0; font-size: 1.2rem; color: #5a320f; }
        .price { font-size: 1.3rem; font-weight: bold; color: #a96a2d; margin-bottom: 20px; }

        .card-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: auto; }
        .btn-view {
            padding: 10px; background: #fdf2e2; color: #a96a2d;
            border: 1px solid #d6a85a; border-radius: 12px;
            text-decoration: none; font-weight: 600; transition: 0.3s; font-size: 14px;
        }
        .btn-view:hover { background: #d6a85a; color: white; }
        .btn-cart {
            padding: 10px; background: #a96a2d; color: white;
            border: none; border-radius: 12px; font-weight: 600;
            cursor: pointer; transition: 0.3s; font-size: 14px;
        }
    </style>
</head>
<body>

<header class="navbar">
    <a href="index.php" class="logo">🍡 ร้านขนมไทย</a>
    <nav>
        <a href="index.php">หน้าแรก</a>
        <a href="products.php" style="background: rgba(255,255,255,0.2);">สินค้าทั้งหมด</a>

        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="cart.php" class="cart-link">
                🛒 ตะกร้า
                <?php if ($cart_count > 0): ?>
                    <span class="cart-badge" id="cart-count-badge"><?= $cart_count ?></span>
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
            <a href="register.php" class="btn-register">สมัครสมาชิก</a>
        <?php endif; ?>
    </nav>
</header>

<div class="container">
    <div class="search-area">
        <form action="products.php" method="get">
            <input type="text" name="search" placeholder="ค้นหาขนมไทย..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">ค้นหา</button>
        </form>
    </div>

    <h2 style="text-align:center; color: #7a4a1d;">🌸 สินค้าขนมไทยทั้งหมด</h2>

    <div class="product-grid">
        <?php if (count($products) > 0): ?>
            <?php foreach ($products as $row): ?>
                <div class="product-card">
                    <?php $image_path = !empty($row['image']) ? "assets/images/" . $row['image'] : "assets/images/no-image.png"; ?>
                    <img src="<?= htmlspecialchars($image_path) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                    <h3><?= htmlspecialchars($row['name']) ?></h3>
                    <p class="price"><?= number_format($row['price'], 2) ?> ฿</p>

                    <div class="card-actions">
                        <a href="product_detail.php?id=<?= $row['id'] ?>" class="btn-view">รายละเอียด</a>
                        <button type="button" class="btn-cart" 
                                onclick="addToCart(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name']) ?>', <?= $row['price'] ?>)">
                            🛒 ใส่ตะกร้า
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="grid-column: 1/-1; text-align: center; padding: 50px; color: #888;">ไม่พบสินค้าที่ค้นหา</p>
        <?php endif; ?>
    </div>
</div>

<script>
function addToCart(id, name, price) {
    let formData = new FormData();
    formData.append('id', id);
    formData.append('name', name);
    formData.append('price', price);
    formData.append('qty', 1);

    fetch('add_to_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // อัปเดตตัวเลข Badge บนตะกร้า
        let badge = document.getElementById('cart-count-badge');
        if(badge) {
            badge.innerText = parseInt(badge.innerText) + 1;
        } else {
            // กรณีไม่มี Badge มาก่อน (เช่นตะกร้าว่าง) ให้โหลดหน้าใหม่เพื่อแสดง Badge
            location.reload(); 
            return;
        }

        // --- ส่วนที่ปรับใหม่: แสดงแจ้งเตือนด้านล่าง ---
        const Toast = Swal.mixin({
            toast: true,
            position: 'bottom', // เปลี่ยนจาก 'top-end' เป็น 'bottom'
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true,
            background: '#fff6e5', // ใช้สีเดียวกับพื้นหลังเว็บ
            color: '#5a320f',      // สีตัวอักษรน้ำตาลเข้ม
            iconColor: '#a96a2d',  // สีไอคอน
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        Toast.fire({
            icon: 'success',
            title: `เพิ่ม "${name}" ลงตะกร้าเรียบร้อยแล้ว ✨`
        });
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด',
            text: 'ไม่สามารถเพิ่มสินค้าลงตะกร้าได้',
            confirmButtonColor: '#a96a2d'
        });
    });
}
</script>

</body>
</html>