 <?php
session_start();
require_once "db.php"; // ปรับ Path ตามตำแหน่งไฟล์จริง

// เช็คชื่อไฟล์ปัจจุบัน
$current_page = basename($_SERVER['PHP_SELF']);

// ดึงข้อมูลผู้ใช้ (ถ้ามีการ Login)
$user_data = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT profile_img, username FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
}

// คำนวณจำนวนสินค้าในตะกร้า
$cart_count = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += isset($item['qty']) ? $item['qty'] : 0;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ร้านขนมไทย - หน้าหลักสำหรับทดสอบ</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');

        body {
            margin: 0;
            font-family: 'Sarabun', sans-serif;
            background: #fff6e5;
            color: #5a320f;
            overflow-x: hidden;
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

        .navbar .logo { font-size: 24px; font-weight: bold; color: #fff3d6; text-decoration: none; }
        .navbar nav { display: flex; align-items: center; gap: 8px; }
        
        .navbar nav a {
            text-decoration: none;
            color: #fff3d6;
            padding: 8px 18px;
            border-radius: 25px;
            transition: 0.3s;
            font-size: 14px;
        }

        /* ไฮไลท์หน้าปัจจุบัน */
        .nav-active {
            background: rgba(255, 255, 255, 0.2) !important;
            font-weight: 600;
        }

        .navbar nav a:hover { background: rgba(255, 243, 214, 0.1); }

        /* Profile & Cart Styles */
        .user-avatar-nav { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid #fff3d6; }
        .user-avatar-initial { width: 32px; height: 32px; border-radius: 50%; background: #d6a85a; color: white; display: flex; justify-content: center; align-items: center; font-size: 14px; font-weight: bold; border: 2px solid #fff3d6; }

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
            transition: 0.3s;
        }
        .dropdown-content {
            display: none; position: absolute; right: 0; top: calc(100% + 10px);
            background: white; min-width: 200px; border-radius: 12px; overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15); z-index: 1001;
        }
        .dropdown-content.active { display: block; animation: slideUp 0.2s ease; }
        .dropdown-content a { color: #5a320f !important; padding: 12px 18px; display: block; text-decoration: none; border-bottom: 1px solid #f8f8f8; }
        .dropdown-content a:hover { background: #fff6e5 !important; }

        @keyframes slideUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* Hero & Content */
        .hero { text-align: center; padding: 70px 20px; background: #ffefd1; }
        .hero h1 { font-size: 2.8rem; color: #7a4a1d; margin: 0; }
        .search-box { margin-top: 25px; }
        .search-box input { padding: 15px 25px; width: 350px; border-radius: 30px 0 0 30px; border: 2px solid #d6a85a; outline: none; }
        .search-box button { padding: 15px 30px; border-radius: 0 30px 30px 0; border: none; background: #a96a2d; color: white; cursor: pointer; }

        .slideshow-container { max-width: 1000px; margin: -40px auto 50px; border-radius: 20px; overflow: hidden; border: 6px solid white; box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
        .slide img { width: 100%; height: 400px; object-fit: cover; }

        .product-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 25px; padding: 40px; max-width: 1200px; margin: 0 auto; }
        .product-card { background: white; border-radius: 20px; padding: 15px; text-align: center; transition: 0.3s; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .product-card:hover { transform: translateY(-8px); box-shadow: 0 10px 25px rgba(169, 106, 45, 0.15); }
        .product-card img { width: 100%; height: 180px; object-fit: cover; border-radius: 15px; margin-bottom: 15px; }
        
        .btn-view-all { display: inline-block; margin-top: 30px; color: #a96a2d; font-weight: bold; text-decoration: none; border-bottom: 2px solid transparent; transition: 0.3s; }
        .btn-view-all:hover { border-color: #a96a2d; }

        .promo-banner { background: linear-gradient(135deg, #a96a2d, #7a4a1d); color: white; padding: 50px; text-align: center; border-radius: 30px; margin: 40px; }
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
                    <a href="logout.php" style="color: #c0392b !important; font-weight: bold;">🚪 ออกจากระบบ</a>
                </div>
            </div>
        <?php else: ?>
            <a href="login.php">เข้าสู่ระบบ</a>
            <a href="register.php" style="background: #d6a85a; color: #5a320f; font-weight: bold;">สมัครสมาชิก</a>
        <?php endif; ?>
    </nav>
</header>

<section class="hero">
    <h1>ขนมไทยแท้ สดใหม่ ทุกวัน</h1>
    <p>สัมผัสความหวานละมุนแบบต้นตำรับ ได้แล้ววันนี้</p>
    <form class="search-box" action="products_test.php" method="get">
        <input type="text" name="search" placeholder="ค้นหาชื่อขนมที่คุณชอบ...">
        <button type="submit">ค้นหา</button>
    </form>
</section>

<div class="slideshow-container">
    <div class="slide"><img src="../../assets/images/thongyip.jpg"></div>
    <div class="slide"><img src="../../assets/images/Pandan layer cake.jpg"></div>
    <div class="slide"><img src="../../assets/images/Thong Yod.jpg"></div>
</div>

<section style="text-align: center; padding: 50px 20px;">
    <h2 style="font-size: 2rem;">🌸 เมนูแนะนำยอดนิยม</h2>
    <div class="product-grid">
        <div class="product-card">
            <img src="../../assets/images/Mango Sticky Rice.jpg">
            <h3>ข้าวเหนียวมะม่วง</h3>
            <p style="color: #888; font-size: 14px;">มะม่วงน้ำดอกไม้หวานฉ่ำ กับข้าวเหนียวมูนกะทิสด</p>
            <p style="color: #a96a2d; font-weight: bold; font-size: 1.2rem;">120 ฿</p>
        </div>
        <div class="product-card">
            <img src="../../assets/images/foithong.jpg">
            <h3>ฝอยทอง</h3>
            <p style="color: #888; font-size: 14px;">เส้นไข่แดงละเอียด หวานกำลังดี ไม่คาว</p>
            <p style="color: #a96a2d; font-weight: bold; font-size: 1.2rem;">85 ฿</p>
        </div>
        <div class="product-card">
            <img src="../../assets/images/Taro curry pot.jpg">
            <h3>หม้อแกงเผือก</h3>
            <p style="color: #888; font-size: 14px;">เนื้อเนียนนุ่มละมุนลิ้น หอมกลิ่นหอมเจียว</p>
            <p style="color: #a96a2d; font-weight: bold; font-size: 1.2rem;">60 ฿</p>
        </div>
    </div>
    <a href="products_test.php" class="btn-view-all">ดูสินค้าทั้งหมดทั้งหมด →</a>
</section>

<section class="promo-banner">
    <h2>🎉 โปรโมชั่นสมาชิกใหม่</h2>
    <p>สมัครสมาชิกวันนี้ รับส่วนลดพิเศษสำหรับสั่งซื้อครั้งแรก!</p>
    <?php if(!isset($_SESSION['user_id'])): ?>
        <a href="register.php" style="background: white; color: #7a4a1d; padding: 12px 30px; border-radius: 30px; text-decoration: none; font-weight: bold; display: inline-block; margin-top: 15px;">สมัครสมาชิกเลย</a>
    <?php endif; ?>
</section>

<script>
    // Slideshow Logic
    let slideIndex = 0;
    const slides = document.getElementsByClassName("slide");
    
    function showSlides() {
        for (let i = 0; i < slides.length; i++) { slides[i].style.display = "none"; }
        slideIndex++;
        if (slideIndex > slides.length) {slideIndex = 1}
        slides[slideIndex-1].style.display = "block";
        setTimeout(showSlides, 4000);
    }
    showSlides();

    // Dropdown Logic (Click instead of Hover for better UX)
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
</script>

</body>
</html>