<?php
session_start();
require_once "db.php"; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = false;

// นับจำนวนสินค้าในตะกร้า
$cart_count = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += isset($item['qty']) ? $item['qty'] : 0;
    }
}

// เมื่อมีการกดบันทึกข้อมูล
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $profile_img = $_POST['old_profile_img']; // ใช้รูปเดิมไว้ก่อน

    // การจัดการไฟล์รูปภาพ
    if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_img']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            // สร้างชื่อไฟล์ใหม่เพื่อป้องกันชื่อซ้ำ
            $new_filename = "profile_" . $user_id . "_" . time() . "." . $ext;
            $upload_path = "uploads/" . $new_filename;

            // สร้างโฟลเดอร์ uploads ถ้ายังไม่มี
            if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }

            if (move_uploaded_file($_FILES['profile_img']['tmp_name'], $upload_path)) {
                $profile_img = $new_filename;
            }
        }
    }

    $sql = "UPDATE users SET email = ?, phone = ?, address = ?, profile_img = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$email, $phone, $address, $profile_img, $user_id])) {
        $success = true;
    }
}

// ดึงข้อมูลปัจจุบัน
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขข้อมูลส่วนตัว - ร้านขนมไทย</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        body { margin: 0; font-family: 'Sarabun', sans-serif; background: #fff6e5; color: #5a320f; }
        
        /* Navbar Style เดิมของคุณ... (ข้ามเพื่อความกระชับ) */
        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 0 40px; height: 75px; background: linear-gradient(90deg, #7a4a1d, #a96a2d); border-bottom: 4px solid #d6a85a; position: sticky; top: 0; z-index: 1000; }
        .navbar .logo { font-size: 26px; font-weight: bold; color: #fff3d6; text-decoration: none; }
        .navbar nav { display: flex; align-items: center; gap: 10px; }
        .navbar nav a { text-decoration: none; color: #fff3d6; padding: 8px 18px; border-radius: 25px; font-size: 15px; }
        .user-menu-container { position: relative; display: inline-block; }
        .user-trigger { color: #fff3d6; cursor: pointer; padding: 8px 15px; background: rgba(255,255,255,0.1); border-radius: 20px; font-size: 14px; }
        .dropdown-content { display: none; position: absolute; right: 0; top: 100%; background-color: white; min-width: 190px; border-radius: 15px; box-shadow: 0px 8px 16px rgba(0,0,0,0.15); margin-top: 10px; z-index: 1001; }
        .user-menu-container:hover .dropdown-content { display: block; }
        .dropdown-content a { color: #5a320f !important; padding: 12px 16px; display: block; text-decoration: none; }
        .dropdown-content a:hover { background-color: #fff6e5 !important; }

        /* Profile Image Preview */
        .image-upload-section { text-align: center; margin-bottom: 25px; position: relative; }
        .profile-preview {
            width: 120px; height: 120px; border-radius: 50%; object-fit: cover;
            border: 4px solid #d6a85a; box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .file-input-label {
            display: inline-block; padding: 8px 15px; background: #a96a2d; color: white;
            border-radius: 20px; font-size: 12px; cursor: pointer; margin-top: 10px;
        }

        .container { max-width: 600px; margin: 40px auto; padding: 0 20px; }
        .form-card { background: white; border-radius: 30px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #a96a2d; }
        .form-group input, .form-group textarea { width: 100%; padding: 12px 15px; border: 2px solid #eee; border-radius: 12px; outline: none; box-sizing: border-box; }
        .btn-save { width: 100%; padding: 15px; background: #a96a2d; color: white; border: none; border-radius: 12px; font-size: 16px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>

<?php if ($success): ?>
<script>
    Swal.fire({ icon: 'success', title: 'บันทึกสำเร็จ!', confirmButtonColor: '#a96a2d' }).then(() => { window.location.href = 'profile.php'; });
</script>
<?php endif; ?>

<header class="navbar">
    <a href="index.php" class="logo">🍡 ร้านขนมไทย</a>
    <nav>
        <a href="index.php">หน้าแรก</a>
        <a href="products.php">สินค้าทั้งหมด</a>
        <div class="user-menu-container">
            <div class="user-trigger">สวัสดี, <strong><?= htmlspecialchars($user['username']) ?></strong> ▾</div>
            <div class="dropdown-content">
                <a href="my_orders.php">📦 คำสั่งซื้อของฉัน</a>
                <a href="profile.php">👤 ข้อมูลส่วนตัว</a>
                <a href="logout.php" style="color: #c0392b !important;">ออกจากระบบ</a>
            </div>
        </div>
    </nav>
</header>

<div class="container">
    <div class="form-card">
        <h2 style="text-align:center; color:#7a4a1d;">แก้ไขข้อมูลส่วนตัว</h2>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="image-upload-section">
                <?php 
                    $img_path = (!empty($user['profile_img']) && file_exists("uploads/".$user['profile_img'])) 
                                ? "uploads/".$user['profile_img'] 
                                : "https://ui-avatars.com/api/?name=".urlencode($user['username'])."&background=d6a85a&color=fff&size=128";
                ?>
                <img src="<?= $img_path ?>" id="preview" class="profile-preview">
                <br>
                <label for="profile_img" class="file-input-label">📸 เปลี่ยนรูปโปรไฟล์</label>
                <input type="file" name="profile_img" id="profile_img" hidden accept="image/*" onchange="previewImage(this)">
                <input type="hidden" name="old_profile_img" value="<?= htmlspecialchars($user['profile_img'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>ชื่อผู้ใช้งาน</label>
                <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled style="background: #f5f5f5;">
            </div>

            <div class="form-group">
                <label>อีเมล</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>เบอร์โทรศัพท์</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>ที่อยู่สำหรับการจัดส่ง</label>
                <textarea name="address" rows="3"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn-save">💾 บันทึกการเปลี่ยนแปลง</button>
            <a href="profile.php" style="display:block; text-align:center; margin-top:15px; color:#888; text-decoration:none;">ยกเลิก</a>
        </form>
    </div>
</div>

<script>
// ฟังก์ชันพรีวิวรูปภาพก่อนอัปโหลด
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

</body>
</html>