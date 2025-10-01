<?php
session_start();
// เรียกใช้ไฟล์ config เพื่อเชื่อมต่อฐานข้อมูล
require_once 'config.php';

// ตรวจสอบสถานะการล็อกอินของผู้ใช้
$isLoggedIn = isset($_SESSION['user_id']);

// ---- ส่วนของการดึงข้อมูลมาแสดงผล ----

// ดึงข้อมูลสินค้าทั้งหมดพร้อมชื่อหมวดหมู่จากฐานข้อมูล
$stmt = $conn->query("SELECT p.*, c.category_name
FROM products p
LEFT JOIN categories c ON p.category_id = c.category_id
ORDER BY p.created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลัก - ร้านค้าออนไลน์</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* สไตล์ CSS สำหรับตกแต่งหน้าเว็บ */
        :root {
            --primary-color: #9575CD;
            --secondary-color: #F3E5F5;
            --dark-purple: #673AB7;
            --light-gray: #f8f9fa;
        }

        body {
            background-color: var(--secondary-color);
            font-family: 'Sarabun', sans-serif; /* Optional: Add a nice Thai font */
        }

        .navbar-dark {
            background-color: var(--dark-purple) !important;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #7E57C2;
            border-color: #7E57C2;
        }
        .btn-outline-primary {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }

        .product-card {
            border: none;
            border-radius: 0.75rem;
            transition: transform .2s, box-shadow .2s;
            background: white;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .product-thumb {
            height: 200px;
            object-fit: cover;
            border-radius: 0.75rem 0.75rem 0 0;
        }

        .product-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            text-decoration: none;
        }
        .product-title:hover {
            color: var(--primary-color);
        }

        .price {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--dark-purple);
        }

        .welcome-text {
            color: var(--dark-purple);
        }
    </style>
</head>

<body>

    <!-- แถบเมนูหลัก (Navbar) -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-shop"></i> PurpleStore</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <!-- ตรวจสอบสถานะล็อกอินเพื่อแสดงเมนูต่างกัน -->
                    <?php if ($isLoggedIn): ?>
                        <!-- เมนูสำหรับผู้ใช้ที่ล็อกอินแล้ว -->
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
                        <!-- เมนูสำหรับผู้ใช้ทั่วไป (ยังไม่ล็อกอิน) -->
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

    <main class="container">


        <h2 class="pb-2 border-bottom mb-4">รายการสินค้า</h2>

        <!-- ส่วนของการแสดงผลรายการสินค้า -->
        <div class="row g-4">
            <!-- วนลูปเพื่อแสดงสินค้าแต่ละชิ้น -->
            <?php foreach ($products as $p): ?>
                <?php
                // ตรวจสอบว่าสินค้ามีรูปภาพหรือไม่ ถ้าไม่มีให้ใช้รูปภาพเริ่มต้น
                $img = !empty($p['image']) && file_exists('product_images/' . $p['image']) 
                    ? 'product_images/' . rawurlencode($p['image']) 
                    : 'product_images/no_image.png';
                ?>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card product-card h-100">
                        <a href="product_detail.php?id=<?= (int) $p['product_id'] ?>">
                            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p['product_name']) ?>" class="img-fluid product-thumb">
                        </a>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">
                                <a href="product_detail.php?id=<?= (int) $p['product_id'] ?>" class="product-title">
                                    <?= htmlspecialchars($p['product_name']) ?>
                                </a>
                            </h5>
                            <p class="card-text text-muted small"><?= htmlspecialchars($p['category_name'] ?? 'Uncategorized') ?></p>
                            <p class="price mb-3 mt-auto"><?= number_format((float) $p['price'], 2) ?> บาท</p>
                            
                            <!-- ฟอร์มสำหรับเพิ่มสินค้าลงตะกร้า -->
                            <form action="cart.php" method="post" class="d-grid">
                                <input type="hidden" name="product_id" value="<?= (int) $p['product_id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <!-- ปุ่มจะถูกปิดใช้งานหากผู้ใช้ยังไม่ล็อกอิน -->
                                <button type="submit" class="btn btn-primary" <?= (!$isLoggedIn) ? 'disabled' : '' ?>>
                                    <i class="bi bi-cart-plus-fill"></i> เพิ่มในตะกร้า
                                </button>
                            </form>
                            <?php if (!$isLoggedIn): ?>
                                <small class="text-center text-muted mt-2">เข้าสู่ระบบเพื่อสั่งซื้อ</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- ส่วนท้ายของเว็บ (Footer) -->
    <footer class="py-4 mt-5 text-center text-muted">
        <div class="container">
            <p>&copy; <?= date('Y') ?> PurpleStore. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

