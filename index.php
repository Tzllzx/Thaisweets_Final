<?php
session_start();
require_once "db.php"; 

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
    <title>ร้านขนมไทย - หน้าหลัก</title>
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

        .navbar .logo { font-size: 26px; font-weight: bold; color: #fff3d6; text-decoration: none; }
        .navbar nav { display: flex; align-items: center; gap: 10px; }
        .navbar nav a {
            text-decoration: none;
            color: #fff3d6;
            padding: 8px 18px;
            border-radius: 25px;
            transition: all 0.3s ease;
            font-size: 15px;
        }
        .navbar nav a:hover { background: rgba(255, 243, 214, 0.2); }

        /* Profile Styles ใน Navbar */
        .user-avatar-nav {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff3d6;
        }

        .user-avatar-initial {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #d6a85a;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 14px;
            font-weight: bold;
            border: 2px solid #fff3d6;
        }

        /* ตะกร้าสินค้า Badge */
        .cart-link { position: relative; display: inline-flex; align-items: center; background: rgba(0, 0, 0, 0.15); }
        .cart-badge {
            position: absolute;
            top: -10px;
            right: -12px;
            background-color: #ff3b3b;
            color: white;
            font-size: 12px;
            font-weight: bold;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 3px 6px rgba(0,0,0,0.2);
            border: 2px solid white;
        }

        /* Dropdown Menu */
        .user-menu-container { position: relative; display: inline-block; margin-left: 10px; }
        .user-trigger {
            color: #fff3d6; cursor: pointer; padding: 5px 12px; border-radius: 20px;
            display: flex; align-items: center; gap: 8px; background: rgba(255, 255, 255, 0.1);
        }
        .dropdown-content {
            display: none; position: absolute; right: 0; top: 100%; background: white;
            min-width: 190px; box-shadow: 0 8px 16px rgba(0,0,0,0.15); border-radius: 12px;
            margin-top: 10px; z-index: 1001; animation: fadeIn 0.3s ease;
        }
        .dropdown-content::before { content: ""; position: absolute; top: -15px; width: 100%; height: 15px; }
        .user-menu-container:hover .dropdown-content { display: block; }
        
        .dropdown-content a { color: #5a320f !important; padding: 12px 16px; display: block; text-align: left; border-radius: 0; }
        .dropdown-content a:hover { background: #fff6e5 !important; }
        .divider { height: 1px; background: #eee; margin: 0; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        /* ===== HERO, SLIDESHOW & CARDS (เหมือนเดิม) ===== */
        .hero { text-align: center; padding: 80px 20px; background: linear-gradient(rgba(255, 239, 209, 0.8), rgba(255, 239, 209, 0.8)); }
        .hero h1 { font-size: 3rem; color: #7a4a1d; margin-bottom: 10px; }
        .search-box input { padding: 15px 25px; width: 400px; border-radius: 30px 0 0 30px; border: 2px solid #d6a85a; outline: none; }
        .search-box button { padding: 15px 30px; border-radius: 0 30px 30px 0; border: none; background: #a96a2d; color: white; cursor: pointer; font-size: 16px; margin-left: -5px; }
        
        .slideshow-container { max-width: 1100px; margin: -40px auto 40px; position: relative; border-radius: 25px; overflow: hidden; box-shadow: 0 15px 40px rgba(0,0,0,0.2); border: 5px solid white; }
        .slide { display: none; }
        .slide img { width: 100%; height: 450px; object-fit: cover; }
        .caption { position: absolute; bottom: 30px; left: 30px; background: rgba(122, 74, 29, 0.8); color: white; padding: 10px 25px; border-radius: 10px; }

        .prev, .next { cursor: pointer; position: absolute; top: 50%; padding: 16px; color: white; font-weight: bold; background: rgba(0,0,0,0.3); border-radius: 50%; margin: 0 10px; }
        .next { right: 0; }

        .section { padding: 60px 40px; max-width: 1200px; margin: 0 auto; }
        .products { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; }
        .product-card { background: white; border-radius: 20px; padding: 15px; text-align: center; transition: 0.3s; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .product-card:hover { transform: translateY(-10px); }
        .product-card img { width: 100%; height: 200px; object-fit: cover; border-radius: 15px; }

        .promo { background: linear-gradient(135deg, #a96a2d, #7a4a1d); color: white; border-radius: 30px; padding: 50px; text-align: center; }
        .btn-promo { background: white !important; color: #a96a2d !important; font-weight: bold; padding: 12px 30px !important; display: inline-block; margin-top: 20px; text-decoration: none; border-radius: 25px; }
    </style>
</head>
<body>

<header class="navbar">
    <a href="index.php" class="logo">🍡 ร้านขนมไทย</a>
    <nav>
        <a href="index.php" style="background: rgba(255,255,255,0.2);">หน้าแรก</a>
        <a href="products.php">สินค้าทั้งหมด</a>

        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="cart.php" class="cart-link">
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
            <a href="register.php" style="background: #d6a85a; color: #5a320f; font-weight: bold;">สมัครสมาชิก</a>
        <?php endif; ?>
    </nav>
</header>

<section class="hero">
    <h1>ขนมไทยแท้ สดใหม่ ทุกวัน</h1>
    <p>คัดสรรวัตถุดิบคุณภาพ หวานพอดี อร่อยแบบไทยต้นตำรับ</p>

    <form class="search-box" action="products.php" method="get">
        <input type="text" name="search" placeholder="ค้นหาชื่อขนมที่คุณชอบ...">
        <button type="submit">ค้นหา</button>
    </form>
</section>

<div class="slideshow-container">
    <div class="slide"><img src="assets/images/thongyip.jpg"><div class="caption">ทองหยิบ</div></div>
    <div class="slide"><img src="assets/images/Pandan layer cake.jpg"><div class="caption">ขนมชั้น</div></div>
    <div class="slide"><img src="assets/images/Thong Yod.jpg"><div class="caption">ทองหยอด</div></div>

    <a class="prev" onclick="changeSlide(-1)">❮</a>
    <a class="next" onclick="changeSlide(1)">❯</a>
</div>

<section class="section">
    <h2 style="text-align:center; margin-bottom: 40px; font-size: 2rem;">🌸 เมนูแนะนำยอดนิยม</h2>
    <div class="products">
        <div class="product-card">
            <img src="assets/images/Mango Sticky Rice.jpg">
            <h3>ข้าวเหนียวมะม่วง</h3>
            <p>มะม่วงน้ำดอกไม้หวานฉ่ำ กับข้าวเหนียวมูนกะทิสด</p>
        </div>
        <div class="product-card">
            <img src="assets/images/Taro curry pot.jpg">
            <h3>หม้อแกงเผือก</h3>
            <p>หอมกลิ่นหอมเจียว เนื้อเนียนนุ่มละมุนลิ้น</p>
        </div>
        <div class="product-card">
            <img src="assets/images/foithong.jpg">
            <h3>ฝอยทอง</h3>
            <p>เส้นไข่แดงละเอียด หวานกำลังดี ไม่คาว</p>
        </div>
        <div class="product-card">
            <img src="assets/images/Taro Pudding Jelly.jpg">
            <h3>วุ้นพุดดิ้งหม้อแกง</h3>
            <p>เมนูฟิวชั่นที่รวมความอร่อยสองสไตล์เข้าด้วยกัน</p>
        </div>
    </div>
    <div style="text-align:center; margin-top: 40px;">
        <a href="products.php" style="color: #a96a2d; text-decoration: none; font-weight: bold;">ดูสินค้าทั้งหมดทั้งหมด →</a>
    </div>
</section>

<section class="section">
    <div class="promo">
        <h2 style="margin:0; font-size: 2rem;">🎉 โปรโมชั่นพิเศษต้อนรับสมาชิกใหม่</h2>
        <p style="font-size: 1.1rem; opacity: 0.9;">ซื้อครบ 300 บาท รับฟรี! ขนมไทยชุดรวมรส 1 กล่องทันที</p>
        <?php if(!isset($_SESSION['user_id'])): ?>
            <a href="register.php" class="btn-promo">สมัครสมาชิกเลยตอนนี้!</a>
        <?php else: ?>
            <a href="products.php" class="btn-promo">ไปเลือกซื้อของกันเลย</a>
        <?php endif; ?>
    </div>
</section>

<script>
let slideIndex = 1;
showSlide(slideIndex);

function changeSlide(n) { showSlide(slideIndex += n); }

function showSlide(n) {
    let slides = document.getElementsByClassName("slide");
    if (n > slides.length) { slideIndex = 1; }
    if (n < 1) { slideIndex = slides.length; }
    for (let i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
    }
    if (slides.length > 0) {
        slides[slideIndex - 1].style.display = "block";
    }
}
setInterval(() => { changeSlide(1); }, 5000);
</script>

</body>
</html>