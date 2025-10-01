<?php
// ตรวจสอบและเริ่มต้น session หากยังไม่ได้เริ่ม
session_start();
// เรียกใช้ไฟล์ config เพื่อเชื่อมต่อฐานข้อมูล
require_once 'config.php';

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือยัง ถ้ายัง ให้ redirect ไปหน้า login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// ---- ส่วนของการจัดการตะกร้าสินค้า ----

// หากยังไม่มีตะกร้าสินค้าใน session ให้สร้างขึ้นมาเป็น array ว่าง
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ตรวจสอบว่ามีการส่งข้อมูลมาแบบ POST หรือไม่ (สำหรับการเพิ่ม, อัปเดต, ลบสินค้า)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // การเพิ่มสินค้าลงตะกร้า
    if (isset($_POST['product_id'])) {
        $product_id = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'];

        if ($quantity > 0) {
            // ถ้ามีสินค้านี้ในตะกร้าแล้ว ให้บวกจำนวนเพิ่มเข้าไป
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id] += $quantity;
            } else {
                // ถ้ายังไม่มี ให้เพิ่มเข้าไปใหม่
                $_SESSION['cart'][$product_id] = $quantity;
            }
        }
    }

    // การอัปเดตจำนวนสินค้าในตะกร้า
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantities'] as $product_id => $quantity) {
            $product_id = (int)$product_id;
            $quantity = (int)$quantity;
            if ($quantity > 0) {
                $_SESSION['cart'][$product_id] = $quantity;
            } else {
                // ถ้าจำนวนเป็น 0 หรือน้อยกว่า ให้ลบออกจากตะกร้า
                unset($_SESSION['cart'][$product_id]);
            }
        }
    }

    // การลบสินค้าออกจากตะกร้า
    if (isset($_POST['remove_item'])) {
        $product_id_to_remove = (int)$_POST['remove_item'];
        unset($_SESSION['cart'][$product_id_to_remove]);
    }

    // Redirect เพื่อป้องกันการส่งฟอร์มซ้ำ
    header('Location: cart.php');
    exit;
}

// ---- ส่วนของการดึงข้อมูลมาแสดงผล ----

$cart_items = [];
$total_price = 0;

// ตรวจสอบว่ามีสินค้าในตะกร้าหรือไม่
if (!empty($_SESSION['cart'])) {
    // ดึงข้อมูลสินค้าจากฐานข้อมูลตาม ID ที่มีอยู่ใน session cart
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id IN ($placeholders)");
    $stmt->execute($product_ids);
    $products = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // จัดเตรียมข้อมูลสำหรับแสดงผลและคำนวณราคารวม
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        if (isset($products[$product_id])) {
            $product = $products[$product_id];
            $subtotal = $product['price'] * $quantity;
            $cart_items[] = [
                'id' => $product_id,
                'name' => $product['product_name'],
                'price' => $product['price'],
                'quantity' => $quantity,
                'subtotal' => $subtotal,
                'image' => $product['image']
            ];
            $total_price += $subtotal;
        }
    }
}

$isLoggedIn = true; // หน้านี้ต้องล็อกอินเสมอ
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตะกร้าสินค้า</title>
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

    <main class="container mt-4">
        <h2 class="mb-4">ตะกร้าสินค้าของคุณ</h2>
        <?php if (empty($cart_items)): ?>
            <div class="alert alert-info text-center">ตะกร้าสินค้าของคุณว่างเปล่า <a href="index.php">เลือกซื้อสินค้าต่อ</a></div>
        <?php else: ?>
            <form action="cart.php" method="post">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>สินค้า</th>
                                <th class="text-end">ราคาต่อหน่วย</th>
                                <th class="text-center">จำนวน</th>
                                <th class="text-end">ราคารวม</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- วนลูปแสดงรายการสินค้าในตะกร้า -->
                            <?php foreach ($cart_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="product_images/<?= htmlspecialchars($item['image'] ?? 'no_image.png') ?>" width="60" class="me-3 rounded">
                                            <?= htmlspecialchars($item['name']) ?>
                                        </div>
                                    </td>
                                    <td class="text-end"><?= number_format($item['price'], 2) ?></td>
                                    <td class="text-center">
                                        <input type="number" name="quantities[<?= $item['id'] ?>]" value="<?= $item['quantity'] ?>" min="1" class="form-control form-control-sm" style="width: 80px; margin: auto;">
                                    </td>
                                    <td class="text-end"><?= number_format($item['subtotal'], 2) ?></td>
                                    <td class="text-center">
                                        <button type="submit" name="remove_item" value="<?= $item['id'] ?>" class="btn btn-sm btn-outline-danger" title="ลบรายการนี้">✖</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <button type="submit" name="update_cart" class="btn btn-secondary">อัปเดตตะกร้า</button>
                    <h4 class="mb-0">ยอดรวมทั้งหมด: <span class="fw-bold"><?= number_format($total_price, 2) ?></span> บาท</h4>
                </div>
            </form>
            <div class="text-end mt-4">
                <a href="checkout.php" class="btn btn-primary btn-lg">ดำเนินการสั่งซื้อ</a>
            </div>
        <?php endif; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
