<?php
session_start();
// 1. ปรับ Path ถอยออก 2 ระดับไปที่ root เพื่อหา db.php
require_once "../../db.php"; 

// รับ ID สินค้า
$id = $_GET['id'] ?? 0;

// ดึงข้อมูลสินค้า
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// 2. ถ้าไม่พบสินค้าให้กลับไปหน้ารายการสินค้า (ในโฟลเดอร์เดียวกัน)
if (!$product) {
    header("Location: products_test.php"); 
    exit;
}

// นับจำนวนสินค้าในตะกร้า
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['qty'];
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product['name']) ?> - ร้านขนมไทย</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* ... (CSS ส่วนเดิมคงไว้ทั้งหมด) ... */
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        :root { --primary: #a96a2d; --dark: #7a4a1d; --light-bg: #fff6e5; --accent: #d6a85a; }
        body { font-family: 'Sarabun', sans-serif; background: var(--light-bg); margin: 0; color: #5a320f; }
        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 0 40px; height: 80px; background: linear-gradient(90deg, var(--dark), var(--primary)); border-bottom: 4px solid var(--accent); position: sticky; top: 0; z-index: 1000; }
        .navbar .logo { font-size: 24px; font-weight: bold; color: #fff3d6; text-decoration: none; }
        .navbar nav a { text-decoration: none; color: #fff3d6; padding: 8px 18px; font-size: 14px; }
        .cart-link { position: relative; }
        .cart-badge { position: absolute; top: -5px; right: -5px; background: #ff3b3b; color: white; font-size: 11px; width: 20px; height: 20px; border-radius: 50%; display: flex; justify-content: center; align-items: center; border: 2px solid white; }
        .container { max-width: 1000px; margin: 50px auto; padding: 0 20px; }
        .product-wrapper { display: grid; grid-template-columns: 1fr 1fr; gap: 50px; background: white; padding: 40px; border-radius: 30px; box-shadow: 0 15px 40px rgba(0,0,0,0.05); }
        .product-image img { width: 100%; height: 450px; object-fit: cover; border-radius: 20px; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .product-info { display: flex; flex-direction: column; justify-content: center; }
        .category-tag { color: var(--primary); font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; display: block; }
        .product-info h1 { font-size: 2.5rem; margin: 0 0 20px 0; color: var(--dark); }
        .price-tag { font-size: 2rem; font-weight: bold; color: #27ae60; margin-bottom: 25px; }
        .description { line-height: 1.8; color: #777; margin-bottom: 30px; font-size: 1.1rem; }
        .qty-selector { display: flex; align-items: center; gap: 20px; margin-bottom: 30px; }
        .qty-input { display: flex; align-items: center; background: #f8f8f8; border-radius: 30px; padding: 5px 15px; border: 1px solid #eee; }
        .qty-input button { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--dark); width: 30px; }
        .qty-input input { width: 50px; text-align: center; border: none; background: none; font-size: 1.2rem; font-weight: bold; }
        .action-btns { display: flex; gap: 15px; }
        .btn-add-cart { flex: 2; padding: 18px; background: var(--primary); color: white; border: none; border-radius: 50px; font-size: 1.1rem; font-weight: bold; cursor: pointer; transition: 0.3s; display: flex; justify-content: center; align-items: center; gap: 10px; }
        .btn-add-cart:hover { background: var(--dark); transform: translateY(-3px); box-shadow: 0 10px 20px rgba(169, 106, 45, 0.3); }
        .btn-back { flex: 1; padding: 18px; background: #eee; color: #666; text-align: center; text-decoration: none; border-radius: 50px; font-weight: bold; transition: 0.3s; }
        .btn-back:hover { background: #ddd; }
        @media (max-width: 768px) { .product-wrapper { grid-template-columns: 1fr; } .product-image img { height: 300px; } .product-info h1 { font-size: 1.8rem; } }
    </style>
</head>
<body>

<header class="navbar">
    <a href="../../index.php" class="logo">🍡 ร้านขนมไทย</a>
    <nav>
        <a href="../../index.php">หน้าแรก</a>
        <a href="products_test.php">สินค้าทั้งหมด</a>
        <a href="../../cart.php" class="cart-link">
            🛒 ตะกร้า <span class="cart-badge" id="cart-count-badge"><?= $cart_count ?></span>
        </a>
    </nav>
</header>

<div class="container">
    <div class="product-wrapper">
        <div class="product-image">
            <?php 
                $img_path = "../../assets/images/";
                $image_src = !empty($product['image']) ? $img_path . $product['image'] : $img_path . "no-image.png"; 
            ?>
            <img src="<?= htmlspecialchars($image_src) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        </div>

        <div class="product-info">
            <span class="category-tag">สินค้าแนะนำ</span>
            <h1><?= htmlspecialchars($product['name']) ?></h1>
            <div class="price-tag"><?= number_format($product['price'], 2) ?> ฿</div>
            
            <div class="description">
                <?= nl2br(htmlspecialchars($product['description'] ?? 'ขนมไทยต้นตำรับ รสชาติกลมกล่อม หอมหวานกำลังดี ทำสดใหม่ทุกวัน...')) ?>
            </div>

            <div class="qty-selector">
                <span>จำนวน:</span>
                <div class="qty-input">
                    <button type="button" onclick="changeQty(-1)">−</button>
                    <input type="number" id="product_qty" value="1" min="1" readonly>
                    <button type="button" onclick="changeQty(1)">+</button>
                </div>
            </div>

            <div class="action-btns">
                <button type="button" class="btn-add-cart" onclick="addToCart()">
                    🛒 ใส่ตะกร้าสินค้า
                </button>
                <a href="products_test.php" class="btn-back">ย้อนกลับ</a>
            </div>
        </div>
    </div>
</div>

<script>
function changeQty(amt) {
    let input = document.getElementById('product_qty');
    let newVal = parseInt(input.value) + amt;
    if (newVal >= 1) input.value = newVal;
}

function addToCart() {
    let id = <?= $product['id'] ?>;
    let name = '<?= addslashes($product['name']) ?>';
    let price = <?= $product['price'] ?>;
    let qty = document.getElementById('product_qty').value;

    let formData = new FormData();
    formData.append('id', id);
    formData.append('name', name);
    formData.append('price', price);
    formData.append('qty', qty);

    // 5. ปรับ Path AJAX ให้ถอยออกไปที่ไฟล์ add_to_cart.php หลัก
    fetch('../../add_to_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        let badge = document.getElementById('cart-count-badge');
        badge.innerText = parseInt(badge.innerText) + parseInt(qty);

        Swal.fire({
            icon: 'success',
            title: 'เพิ่มลงตะกร้าแล้ว!',
            text: `เพิ่ม ${name} จำนวน ${qty} ชิ้นเรียบร้อย`,
            confirmButtonColor: '#a96a2d',
            timer: 2000
        });
    })
    .catch(error => console.error('Error:', error));
}
</script>

</body>
</html>