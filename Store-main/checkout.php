<?php
// ตรวจสอบและเริ่มต้น session หากยังไม่ได้เริ่ม
session_start();
// เรียกใช้ไฟล์ config เพื่อเชื่อมต่อฐานข้อมูล
require_once 'config.php';

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือยัง และมีสินค้าในตะกร้าหรือไม่
if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';

// ---- ส่วนของการดึงข้อมูลตะกร้าและคำนวณราคารวม ----
$cart_items = [];
$total_price = 0;
if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id IN ($placeholders)");
    $stmt->execute($product_ids);
    $products = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        if (isset($products[$product_id])) {
            $total_price += $products[$product_id]['price'] * $quantity;
        }
    }
}

// ---- ส่วนของการจัดการฟอร์ม (ยืนยันคำสั่งซื้อ) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับข้อมูลที่อยู่จัดส่งจากฟอร์ม
    $full_name = trim($_POST['full_name']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $postal_code = trim($_POST['postal_code']);
    $phone = trim($_POST['phone']);

    // ตรวจสอบว่ากรอกข้อมูลครบหรือไม่
    if (empty($full_name) || empty($address) || empty($city) || empty($postal_code) || empty($phone)) {
        $error = 'กรุณากรอกข้อมูลที่อยู่จัดส่งให้ครบถ้วน';
    } else {
        // เริ่มต้น Transaction เพื่อให้แน่ใจว่าทุกอย่างทำงานสำเร็จทั้งหมดหรือล้มเหลวทั้งหมด
        $conn->beginTransaction();
        try {
            // 1. สร้างคำสั่งซื้อ (Order)
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')");
            $stmt->execute([$user_id, $total_price]);
            $order_id = $conn->lastInsertId(); // ดึง ID ของคำสั่งซื้อที่เพิ่งสร้าง

            // 2. เพิ่มรายการสินค้าในคำสั่งซื้อ (Order Items)
            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($_SESSION['cart'] as $product_id => $quantity) {
                if (isset($products[$product_id])) {
                    $item_stmt->execute([$order_id, $product_id, $quantity, $products[$product_id]['price']]);
                }
            }

            // 3. เพิ่มข้อมูลการจัดส่ง (Shipping)
            $ship_stmt = $conn->prepare("INSERT INTO shipping (order_id, address, city, postal_code, phone) VALUES (?, ?, ?, ?, ?)");
            $ship_stmt->execute([$order_id, "$full_name\n$address", $city, $postal_code, $phone]);

            // 4. ตัดสต็อกสินค้า (ยังไม่ได้ทำในโค้ดนี้ แต่เป็นสิ่งที่ควรทำ)

            // หากทุกอย่างสำเร็จ ให้ commit transaction
            $conn->commit();

            // 5. ล้างตะกร้าสินค้าและ redirect ไปหน้าขอบคุณ
            unset($_SESSION['cart']);
            header("Location: order.php?id=" . $order_id);
            exit;

        } catch (Exception $e) {
            // หากมีข้อผิดพลาด ให้ rollback transaction
            $conn->rollBack();
            $error = "เกิดข้อผิดพลาดในการสั่งซื้อ: " . $e->getMessage();
        }
    }
}

$isLoggedIn = true;
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ชำระเงิน</title>
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
                            <span class="navbar-text me-3">ยินดีต้อนรับ,<?= htmlspecialchars($_SESSION['username']) ?></span>
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

    <main class="container mt-4">
        <h2 class="mb-4 text-center">ยืนยันคำสั่งซื้อและชำระเงิน</h2>
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">ข้อมูลสำหรับจัดส่ง</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        <!-- ฟอร์มกรอกที่อยู่จัดส่ง -->
                        <form method="post">
                            <div class="mb-3">
                                <label for="full_name" class="form-label">ชื่อ-นามสกุลผู้รับ</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">ที่อยู่</label>
                                <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">เมือง/จังหวัด</label>
                                    <input type="text" class="form-control" id="city" name="city" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="postal_code" class="form-label">รหัสไปรษณีย์</label>
                                    <input type="text" class="form-control" id="postal_code" name="postal_code" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">เบอร์โทรศัพท์</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <h4>ยอดชำระเงิน: <span class="text-primary"><?= number_format($total_price, 2) ?></span> บาท</h4>
                                <button type="submit" class="btn btn-primary btn-lg">ยืนยันการสั่งซื้อ</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
