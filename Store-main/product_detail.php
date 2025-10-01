<?php
// ตรวจสอบและเริ่มต้น session หากยังไม่ได้เริ่ม
session_start();
// เรียกใช้ไฟล์ config เพื่อเชื่อมต่อฐานข้อมูล
require_once 'config.php';

// ตรวจสอบว่ามี ID ของสินค้าส่งมาใน URL หรือไม่
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$product_id = (int)$_GET['id'];

// ---- ส่วนของการดึงข้อมูลมาแสดงผล ----

// ดึงข้อมูลสินค้าที่ต้องการแสดงจาก ID
$stmt = $conn->prepare("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id WHERE p.product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// หากไม่พบสินค้า ให้ redirect กลับไปหน้าแรก
if (!$product) {
    header('Location: index.php');
    exit;
}

// ตรวจสอบสถานะการล็อกอินของผู้ใช้
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['product_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #9575CD;
            --secondary-color: #F3E5F5;
            --dark-purple: #673AB7;
        }
        body { background-color: var(--secondary-color); }
        .navbar-dark { background-color: var(--dark-purple) !important; }
        .btn-primary { background-color: var(--primary-color); border-color: var(--primary-color); }
        .btn-primary:hover { background-color: #7E57C2; border-color: #7E57C2; }
        .price-detail { font-size: 2rem; font-weight: bold; color: var(--dark-purple); }
        .product-image-detail { max-width: 100%; height: auto; border-radius: 1rem; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-shop"></i> PurpleStore</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item">
                            <span class="navbar-text me-3">ยินดีต้อนรับ, <?= htmlspecialchars($_SESSION['username']) ?></span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php"><i class="bi bi-person-circle"></i> โปรไฟล์</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="order.php"><i class="bi bi-receipt"></i> ประวัติสั่งซื้อ</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-light position-relative" href="cart.php">
                                <i class="bi bi-cart-fill"></i> ตะกร้า
                            </a>
                        </li>
                        <li class="nav-item ms-2">
                            <a href="logout.php" class="btn btn-outline-light">ออกจากระบบ</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a href="login.php" class="btn btn-outline-light me-2">เข้าสู่ระบบ</a>
                        </li>
                        <li class="nav-item">
                            <a href="register.php" class="btn btn-light">สมัครสมาชิก</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container mt-5">
        <div class="card shadow-sm p-4">
            <div class="row g-5">
                <div class="col-md-6">
                    <?php
                    // ตรวจสอบและกำหนด path รูปภาพ
                    $img_path = !empty($product['image']) && file_exists('product_images/' . $product['image']) 
                                ? 'product_images/' . rawurlencode($product['image']) 
                                : 'product_images/no_image.png';
                    ?>
                    <img src="<?= htmlspecialchars($img_path) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>" class="product-image-detail shadow">
                </div>
                <div class="col-md-6">
                    <!-- แสดงข้อมูลรายละเอียดสินค้า -->
                    <h1 class="display-5"><?= htmlspecialchars($product['product_name']) ?></h1>
                    <p class="text-muted"><?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></p>
                    <p class="lead"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                    <p class="price-detail my-3"><?= number_format($product['price'], 2) ?> บาท</p>
                    <p>คงเหลือในคลัง: <?= $product['stock'] ?> ชิ้น</p>
                    
                    <hr>

                    <!-- ฟอร์มสำหรับเพิ่มสินค้าลงตะกร้า -->
                    <form action="cart.php" method="post">
                        <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                        <div class="row align-items-end g-2">
                            <div class="col-auto">
                                <label for="quantity" class="form-label">จำนวน:</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>" style="width: 100px;">
                            </div>
                            <div class="col-auto">
                                <!-- ปุ่มจะถูกปิดใช้งานหากผู้ใช้ยังไม่ล็อกอิน หรือสินค้าหมด -->
                                <button type="submit" class="btn btn-primary btn-lg" <?= (!$isLoggedIn || $product['stock'] <= 0) ? 'disabled' : '' ?>>
                                    <i class="bi bi-cart-plus-fill"></i> 
                                    <?= ($product['stock'] > 0) ? 'เพิ่มในตะกร้า' : 'สินค้าหมด' ?>
                                </button>
                            </div>
                        </div>
                    </form>
                    <?php if (!$isLoggedIn): ?>
                        <small class="text-danger mt-2 d-block">กรุณา <a href="login.php">เข้าสู่ระบบ</a> เพื่อสั่งซื้อ</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
