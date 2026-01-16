<?php
session_start();
require_once "../db.php"; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);

// 1. ดึงจำนวนรายการที่รอการยืนยัน
$count_pending = 0;
// 2. ดึงจำนวนสมาชิกทั้งหมด
$count_members = 0;

// ... โค้ดส่วนบนคงเดิม ...
if (isset($pdo)) {
    try {
        // 1. ดึงจำนวนรายการที่รอการยืนยัน (เพิ่มบรรทัดนี้ครับ)
        $stmt_pending = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'waiting_verification'");
        $count_pending = $stmt_pending->fetchColumn();

        // 2. นับเฉพาะคนที่ออนไลน์ (มีกิจกรรมภายใน 5 นาทีที่ผ่านมา)
        $stmt_online = $pdo->query("SELECT COUNT(*) FROM users WHERE last_active > NOW() - INTERVAL 5 MINUTE");
        $count_online = $stmt_online->fetchColumn();

        // 3. นับเฉพาะผู้ใช้งาน (role = 'user') ไม่รวม Admin
        $stmt_user = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
        $count_users = $stmt_user->fetchColumn();
        
    } catch (PDOException $e) { 
        $count_pending = 0; 
        $count_online = 0;
        $count_users = 0;
    }
}
// ... โค้ดส่วนล่างคงเดิม ...

$btn_color = ($count_pending > 0) ? "#f39c12" : "#27ae60";
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ระบบจัดการ Admin - ร้านขนมไทย</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');

        body { margin: 0; font-family: 'Sarabun', sans-serif; background: #fff6e5; color: #5a320f; overflow-x: hidden; }

        /* ===== NAVBAR ===== */
        .navbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0 40px; height: 75px;
            background: linear-gradient(90deg, #2c3e50, #000000);
            border-bottom: 4px solid #d6a85a;
            position: sticky; top: 0; z-index: 1000;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .navbar .logo { font-size: 22px; font-weight: bold; color: #fff3d6; text-decoration: none; }
        .navbar nav { display: flex; align-items: center; gap: 8px; }
        .navbar nav a {
            text-decoration: none; color: #fff3d6; padding: 8px 15px;
            border-radius: 20px; transition: 0.3s; font-size: 14px;
        }
        .navbar nav a:hover { background: rgba(255, 243, 214, 0.15); }

        .nav-badge {
            background: #e74c3c; color: white; padding: 2px 8px;
            border-radius: 10px; font-size: 11px; margin-left: 5px; font-weight: bold;
        }

        /* ===== DROPDOWN ===== */
        .user-menu-container { position: relative; display: inline-block; margin-left: 10px; }
        .user-trigger {
            color: #fff3d6; cursor: pointer; padding: 8px 15px; border-radius: 25px;
            display: flex; align-items: center; gap: 8px; background: rgba(255, 255, 255, 0.1);
            font-size: 14px; transition: 0.3s; border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .dropdown-content {
            display: none; position: absolute; right: 0; top: calc(100% + 10px);
            background: white; min-width: 220px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border-radius: 12px; z-index: 1001; overflow: hidden; border: 1px solid #eee;
        }
        .dropdown-content.active { display: block; animation: slideUp 0.2s ease-out; }
        .dropdown-content a {
            color: #5a320f !important; padding: 12px 20px; display: flex !important;
            align-items: center; gap: 10px; text-decoration: none; font-size: 14px;
        }
        .dropdown-content a:hover { background: #fff6e5 !important; }

        @keyframes slideUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* ===== HERO SECTION & BACK BUTTON ===== */
        .hero { text-align: center; padding: 60px 20px; background: #ffefd1; position: relative; }
        
        .btn-back-home {
            position: absolute; top: 20px; left: 40px;
            text-decoration: none; background: #8d6e63; color: white;
            padding: 10px 18px; border-radius: 12px; font-size: 14px;
            display: flex; align-items: center; gap: 8px;
            transition: 0.3s; box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .btn-back-home:hover { background: #5a320f; transform: translateX(-5px); }

        /* ===== CONTENT STRUCTURE ===== */
        .slideshow-container { max-width: 1100px; margin: -30px auto 40px; position: relative; border-radius: 25px; overflow: hidden; box-shadow: 0 15px 40px rgba(0,0,0,0.2); border: 5px solid white; }
        .slide img { width: 100%; height: 400px; object-fit: cover; }
        .dot-container { text-align: center; margin-top: -60px; position: relative; z-index: 5; padding-bottom: 20px; }
        .dot { cursor: pointer; height: 10px; width: 10px; margin: 0 5px; background-color: rgba(255,255,255,0.5); border-radius: 50%; display: inline-block; }
        .dot.active { background-color: white; transform: scale(1.4); }

        .admin-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; max-width: 1200px; margin: 0 auto; padding: 40px; }
        .admin-card { background: white; border-radius: 20px; padding: 30px; text-align: center; transition: 0.3s; box-shadow: 0 5px 15px rgba(0,0,0,0.05); text-decoration: none; color: inherit; border: 1px solid transparent; }
        .admin-card:hover { transform: translateY(-10px); box-shadow: 0 10px 25px rgba(169, 106, 45, 0.2); border-color: #d6a85a; }
        .icon-circle { width: 60px; height: 60px; background: #fff6e5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 24px; }

        .floating-check-btn { position: fixed; bottom: 30px; right: 30px; background-color: <?= $btn_color ?>; color: white; padding: 15px 25px; border-radius: 50px; text-decoration: none; font-weight: bold; box-shadow: 0 8px 20px rgba(0,0,0,0.3); z-index: 999; display: flex; align-items: center; gap: 10px; transition: 0.3s; }
        .floating-check-btn:hover { transform: scale(1.05); filter: brightness(1.1); }
    </style>
</head>
<body>

<header class="navbar">
    <a href="index_admin.php" class="logo">🍡 Admin Panel</a>
    <nav>
        <a href="index_admin.php" class="<?= ($current_page == 'index_admin.php') ? 'active-nav' : '' ?>">แผงควบคุม</a>
        <a href="admin_products.php" class="<?= ($current_page == 'admin_products.php') ? 'active-nav' : '' ?>">จัดการสินค้า</a>
        <a href="admin_approve_payment.php" class="<?= ($current_page == 'admin_approve_payment.php') ? 'active-nav' : '' ?>">
            ตรวจสอบชำระเงิน 
            <?php if($count_pending > 0): ?>
                <span class="nav-badge"><?= $count_pending ?></span>
            <?php endif; ?>
        </a>

        <a href="admin_member.php" class="<?= (in_array($current_page, ['admin_member.php', 'add_member.php', 'edit_member.php'])) ? 'active-nav' : '' ?>">สมาชิก</a>
        
        <div class="user-menu-container">
            <div class="user-trigger" id="adminBtn">
                <div style="width: 28px; height: 28px; background: #d6a85a; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 12px;">A</div>
                <span><strong>Admin</strong> ▾</span>
            </div>
            <div class="dropdown-content" id="adminDropdown">
                <div style="padding: 15px; background: #fdfaf5; font-size: 12px; color: #8d6e63; border-bottom: 1px solid #eee;">สถานะ: <b>ผู้ดูแลระบบ</b></div>
                <a href="admin_test/index_test.php">🌐 ไปยังหน้าเว็บลูกค้า</a>
                <a href="admin_settings.php">⚙️ ตั้งค่าระบบ</a>
                <div style="height: 1px; background: #eee;"></div>
                <a href="../logout.php" style="color: #c0392b !important; font-weight: bold;">🚪 ออกจากระบบ</a>
            </div>
        </div>
    </nav>
</header>

<section class="hero">
    
    
    <h1>ยินดีต้อนรับคุณดูแลระบบ</h1>
    <p>จัดการคำสั่งซื้อและดูแลความเรียบร้อยของร้านขนมไทย</p>
</section>

<div class="slideshow-container">
    <div class="slide"><img src="../assets/images/thongyip.jpg"></div>
    <div class="slide"><img src="../assets/images/Pandan layer cake.jpg"></div>
    <div class="slide"><img src="../assets/images/Thong Yod.jpg"></div>
</div>

<div class="dot-container">
    <span class="dot" onclick="currentSlide(1)"></span>
    <span class="dot" onclick="currentSlide(2)"></span>
    <span class="dot" onclick="currentSlide(3)"></span>
</div>

<div class="admin-grid">
    <a href="admin_approve_payment.php" class="admin-card">
        <div class="icon-circle">💰</div>
        <p>รายการรอตรวจสอบ</p>
        <h2 style="margin: 5px 0; color: #d35400;"><?= $count_pending ?></h2>
        <small style="color: #888;">รายการใหม่ที่ต้องอนุมัติ</small>
    </a>

    <a href="admin_products.php" class="admin-card">
        <div class="icon-circle">📦</div>
        <p>สินค้าทั้งหมด</p>
        <h2 style="margin: 5px 0; color: #2980b9;">⚡</h2>
        <small style="color: #888;">จัดการ แก้ไข เพิ่มสินค้า</small>
    </a>

   <a href="admin_member.php" class="admin-card">
    <div class="icon-circle" style="background: #e8f5e9;">🟢</div>
    <p>ผู้ที่กำลังใช้งาน</p>
    <h2 style="margin: 5px 0; color: #2ecc71;"><?= number_format($count_online) ?></h2>
    <small style="color: #888;">คน (Online ขณะนี้)</small>
</a>
</div>

<?php if($count_pending > 0): ?>
    <a href="admin_approve_payment.php" class="floating-check-btn">
        <span>🔍 มีรายการรอตรวจสอบ (<?= $count_pending ?>)</span>
    </a>
<?php endif; ?>

<script>
    // --- สคริปต์จัดการ Dropdown ---
    const adminBtn = document.getElementById('adminBtn');
    const adminDropdown = document.getElementById('adminDropdown');

    adminBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        adminDropdown.classList.toggle('active');
    });

    document.addEventListener('click', (e) => {
        if (!adminBtn.contains(e.target) && !adminDropdown.contains(e.target)) {
            adminDropdown.classList.remove('active');
        }
    });

    // --- สคริปต์ Slideshow ---
    let slideIndex = 1;
    let timer;
    showSlides(slideIndex);

    function currentSlide(n) { clearTimeout(timer); showSlides(slideIndex = n); }

    function showSlides(n) {
        let slides = document.getElementsByClassName("slide");
        let dots = document.getElementsByClassName("dot");
        if (n > slides.length) {slideIndex = 1}    
        if (n < 1) {slideIndex = slides.length}
        for (let i = 0; i < slides.length; i++) { slides[i].style.display = "none"; }
        for (let i = 0; i < dots.length; i++) { dots[i].className = dots[i].className.replace(" active", ""); }
        slides[slideIndex-1].style.display = "block";  
        dots[slideIndex-1].className += " active";
        timer = setTimeout(() => { showSlides(slideIndex += 1); }, 5000); 
    }
</script>
</body>
</html>