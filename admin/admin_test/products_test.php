<?php
session_start();
require_once "db.php"; // ปรับ Path ตามตำแหน่งไฟล์จริง

// เช็คชื่อไฟล์ปัจจุบันสำหรับ Navbar Active
$current_page = basename($_SERVER['PHP_SELF']);

// ดึงข้อมูลผู้ใช้ (ถ้ามีการ Login)
$user_data = null;
if (isset($_SESSION['user_id'])) {
    $stmt_user = $pdo->prepare("SELECT profile_img, username FROM users WHERE id = ?");
    $stmt_user->execute([$_SESSION['user_id']]);
    $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);
}

// นับจำนวนสินค้าในตะกร้า
$cart_count = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += isset($item['qty']) ? $item['qty'] : 0;
    }
}

$search = $_GET['search'] ?? '';

// ดึงข้อมูลสินค้าพร้อมระบบค้นหา
try {
    if ($search != '') {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? ORDER BY created_at DESC");
        $stmt->execute(["%$search%"]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM products ORDER BY created_at DESC");
        $stmt->execute();
    }
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $products = []; // ป้องกัน Error ถ้าไม่มี Table
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการสินค้า - ร้านขนมไทย</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');

        body { margin: 0; font-family: 'Sarabun', sans-serif; background: #fff6e5; color: #5a320f; }

        /* ===== NAVBAR (Unified Style) ===== */
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

        /* Cart Badge */
        .cart-link { position: relative; background: rgba(0, 0, 0, 0.15); display: flex; align-items: center; gap: 5px; }
        .cart-badge {
            position: absolute; top: -8px; right: -8px; background: #e74c3c; color: white;
            font-size: 11px; border-radius: 10px; padding: 2px 6px; border: 2px solid white;
        }

        /* Dropdown */
        .user-menu-container { position: relative; margin-left: 10px; }
        .user-trigger {
            color: #fff3d6; cursor: pointer; padding: 6px 15px; border-radius: 25px;
            display: flex; align-items: center; gap: 8px; background: rgba(255, 255, 255, 0.1);
        }
        .user-avatar-nav { width: 30px; height: 30px; border-radius: 50%; object-fit: cover; border: 2px solid #fff3d6; }
        .user-avatar-initial { width: 30px; height: 30px; border-radius: 50%; background: #d6a85a; color: white; display: flex; justify-content: center; align-items: center; font-size: 12px; font-weight: bold; }
        
        .dropdown-content {
            display: none; position: absolute; right: 0; top: calc(100% + 10px);
            background: white; min-width: 190px; border-radius: 12px; overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15); z-index: 1001; animation: slideUp 0.2s ease;
        }
        .dropdown-content.active { display: block; }
        .dropdown-content a { color: #5a320f !important; padding: 12px 18px; display: block; text-decoration: none; font-size: 14px; border-bottom: 1px solid #f8f8f8; }
        .dropdown-content a:hover { background: #fff6e5 !important; }

        @keyframes slideUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* ===== CONTENT ===== */
        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        
        /* Search Box ปรับปรุงใหม่ */
        .search-container { 
            background: white; padding: 30px; border-radius: 25px; 
            box-shadow: 0 5px 20px rgba(169, 106, 45, 0.05);
            margin-bottom: 50px; text-align: center;
        }
        .search-box { display: flex; justify-content: center; max-width: 500px; margin: 0 auto; }
        .search-box input {
            flex: 1; padding: 15px 25px; border: 2px solid #f0e0d0;
            border-radius: 30px 0 0 30px; outline: none; transition: 0.3s;
        }
        .search-box input:focus { border-color: #d6a85a; }
        .search-box button {
            padding: 0 30px; background: #a96a2d; color: white; border: none;
            border-radius: 0 30px 30px 0; cursor: pointer; font-weight: bold;
        }

        /* Product Grid */
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 30px; }
        .product-card {
            background: white; border-radius: 25px; padding: 20px;
            text-align: center; transition: 0.3s; border: 1px solid #f9f0e5;
            display: flex; flex-direction: column;
        }
        .product-card:hover { transform: translateY(-10px); box-shadow: 0 15px 35px rgba(169, 106, 45, 0.15); }
        
        .img-wrapper { width: 100%; height: 200px; border-radius: 20px; overflow: hidden; margin-bottom: 15px; }
        .product-card img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
        .product-card:hover img { transform: scale(1.1); }

        .product-card h3 { margin: 10px 0; font-size: 1.25rem; color: #5a320f; }
        .price-tag { font-size: 1.4rem; font-weight: bold; color: #a96a2d; margin-bottom: 20px; }

        .btn-group { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: auto; }
        .btn-info { 
            text-decoration: none; padding: 10px; background: #fff; color: #a96a2d;
            border: 2px solid #a96a2d; border-radius: 12px; font-weight: 600; font-size: 14px;
        }
        .btn-add {
            border: none; padding: 10px; background: #a96a2d; color: white;
            border-radius: 12px; font-weight: 600; cursor: pointer; font-size: 14px;
        }
        .btn-add:hover { background: #7a4a1d; }
    </style>
</head>
<body>
<header class="navbar">
    <a href="index_test.php" class="logo">🍡 ร้านขนมไทย</a>
    <nav>
        <a href="index_test.php">หน้าแรก</a>
        <a href="products_test.php" class="nav-active">สินค้าทั้งหมด</a>

        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="cart_test.php" class="cart-link">
                🛒 ตะกร้า
                <?php if ($cart_count > 0): ?>
                    <span class="cart-badge" id="cart-count-badge"><?= $cart_count ?></span>
                <?php endif; ?>
            </a>

            <div class="user-menu-container">
                <div class="user-trigger" id="userBtn">
                    <?php if (!empty($user_data['profile_img'])): ?>
                        <img src="../../uploads/<?= htmlspecialchars($user_data['profile_img']) ?>" class="user-avatar-nav">
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
                    <a href="../../logout.php" style="color: #c0392b !important; font-weight: bold;">🚪 ออกจากระบบ</a>
                </div>
            </div>
        <?php else: ?>
            <a href="../../login.php">เข้าสู่ระบบ</a>
            <a href="../../register.php" style="background: #d6a85a; color: #5a320f; font-weight: bold;">สมัครสมาชิก</a>
        <?php endif; ?>
    </nav>
</header>

<div class="container">
    <div class="product-grid">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $p): ?>
                <div class="product-card">
                    <div class="img-wrapper">
                        <?php 
                            // แก้ Path รูปสินค้า: ถอยกลับ 2 ระดับไปที่ assets
                            $img_path = "../../assets/images/";
                            $img = (!empty($p['image']) && file_exists($img_path . $p['image'])) 
                                   ? $img_path . $p['image'] 
                                   : $img_path . "no-image.png"; 
                        ?>
                        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                    </div>
                    <h3><?= htmlspecialchars($p['name']) ?></h3>
                    <div class="price-tag"><?= number_format($p['price'], 2) ?> ฿</div>
                    
                    <div class="btn-group">
                        <a href="product_detail_test.php?id=<?= $p['id'] ?>" class="btn-info">รายละเอียด</a>
                        <button type="button" class="btn-add" 
                                onclick="addToCart(<?= $p['id'] ?>, '<?= addslashes(htmlspecialchars($p['name'])) ?>', <?= $p['price'] ?>)">
                            🛒 ใส่ตะกร้า
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 100px 0;">
                <img src="../../assets/images/empty-cart.png" style="width: 120px; opacity: 0.3;">
                <p style="color: #8d6e63; margin-top: 20px;">ขออภัย ไม่พบขนมที่คุณกำลังมองหา...</p>
                <a href="products_test.php" style="color: #a96a2d; font-weight: bold;">กลับไปดูสินค้าทั้งหมด</a>
            </div>
        <?php endif; ?>
    </div>
</div>

    <div class="product-grid">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $p): ?>
                <div class="product-card">
                    <div class="img-wrapper">
                        <?php 
                            // เช็คไฟล์รูปภาพ ถ้าไม่มีให้ใช้ Placeholder
                            $img = (!empty($p['image']) && file_exists("../../assets/images/" . $p['image'])) 
                                   ? "../../assets/images/" . $p['image'] 
                                   : "../../assets/images/no-image.png"; 
                        ?>
                        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                    </div>
                    <h3><?= htmlspecialchars($p['name']) ?></h3>
                    <div class="price-tag"><?= number_format($p['price'], 2) ?> ฿</div>
                    
                    <div class="btn-group">
                        <a href="product_detail.php?id=<?= $p['id'] ?>" class="btn-info">รายละเอียด</a>
                        <button type="button" class="btn-add" 
                                onclick="addToCart(<?= $p['id'] ?>, '<?= addslashes(htmlspecialchars($p['name'])) ?>', <?= $p['price'] ?>)">
                            🛒 ใส่ตะกร้า
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 100px 0;">
                <img src="../../assets/images/empty-cart.png" style="width: 120px; opacity: 0.3;">
                <p style="color: #8d6e63; margin-top: 20px;">ขออภัย ไม่พบขนมที่คุณกำลังมองหา...</p>
                <a href="products.php" style="color: #a96a2d; font-weight: bold;">กลับไปดูสินค้าทั้งหมด</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // --- Dropdown Management ---
    const userBtn = document.getElementById('userBtn');
    const userDropdown = document.getElementById('userDropdown');

    if(userBtn) {
        userBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdown.classList.toggle('active');
        });
        document.addEventListener('click', () => userDropdown.classList.remove('active'));
    }

    // --- AJAX Add to Cart ---
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
        .then(res => res.text()) // หรือ res.json() ถ้าไฟล์ปลายทางคืนค่าเป็น JSON
        .then(() => {
            // อัปเดตตัวเลข Badge
            let badge = document.getElementById('cart-count-badge');
            if(badge) {
                badge.innerText = parseInt(badge.innerText) + 1;
            } else {
                location.reload(); // ถ้าไม่มี badge เดิมให้โหลดใหม่เพื่อสร้าง
                return;
            }

            // แจ้งเตือนความสำเร็จ (Toast Style)
            const Toast = Swal.mixin({
                toast: true,
                position: 'bottom',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                background: '#fff6e5',
                color: '#5a320f',
                iconColor: '#a96a2d'
            });

            Toast.fire({
                icon: 'success',
                title: `เพิ่ม "${name}" เรียบร้อย! ✨`
            });
        })
        .catch(err => {
            Swal.fire('Error', 'ไม่สามารถเพิ่มลงตะกร้าได้', 'error');
        });
    }
</script>

</body>
</html>