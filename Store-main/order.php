<?php
// ตรวจสอบและเริ่มต้น session หากยังไม่ได้เริ่ม
session_start();
// เรียกใช้ไฟล์ config และ function
require_once 'config.php';
require_once 'function.php';

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือยัง
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// ---- ส่วนของการดึงข้อมูลมาแสดงผล ----

// ตรวจสอบว่ามี ID ของคำสั่งซื้อส่งมาใน URL หรือไม่
if (isset($_GET['id'])) {
    // แสดงรายละเอียดของคำสั่งซื้อเดียว
    $order_id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();

    if ($order) {
        $order_items = getOrderItems($conn, $order_id);
        $shipping_info = getShippingInfo($conn, $order_id);
    }
} else {
    // หากไม่มี ID ใน URL ให้แสดงประวัติคำสั่งซื้อทั้งหมดของผู้ใช้
    $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
    $stmt->execute([$user_id]);
    $orders_history = $stmt->fetchAll();
}

$isLoggedIn = true;
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คำสั่งซื้อของฉัน</title>
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
        .card { border-radius: 1rem; }
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
        <?php if (isset($order) && $order): ?>
            <!-- ส่วนแสดงรายละเอียดคำสั่งซื้อเดียว -->
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">ขอบคุณสำหรับคำสั่งซื้อของคุณ!</h4>
                </div>
                <div class="card-body">
                    <p>คำสั่งซื้อหมายเลข <strong>#<?= $order['order_id'] ?></strong> ของคุณได้รับการยืนยันแล้ว</p>
                    <div class="row">
                        <div class="col-md-6">
                            <h5>รายการสินค้า:</h5>
                            <ul class="list-group">
                                <?php foreach ($order_items as $item): ?>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <?= htmlspecialchars($item['product_name']) ?> (x<?= $item['quantity'] ?>)
                                        <span><?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                                    </li>
                                <?php endforeach; ?>
                                <li class="list-group-item d-flex justify-content-between active">
                                    <strong>ยอดรวม</strong>
                                    <strong><?= number_format($order['total_amount'], 2) ?> บาท</strong>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>ที่อยู่สำหรับจัดส่ง:</h5>
                            <p><?= nl2br(htmlspecialchars($shipping_info['address'])) ?></p>
                            <p><?= htmlspecialchars($shipping_info['city']) ?>, <?= htmlspecialchars($shipping_info['postal_code']) ?></p>
                            <p>โทร: <?= htmlspecialchars($shipping_info['phone']) ?></p>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <a href="index.php" class="btn btn-primary">กลับไปหน้าแรก</a>
                        <a href="order.php" class="btn btn-secondary">ดูประวัติการสั่งซื้อทั้งหมด</a>
                    </div>
                </div>
            </div>
        <?php elseif (isset($orders_history)): ?>
            <!-- ส่วนแสดงประวัติคำสั่งซื้อทั้งหมด -->
            <h2 class="mb-4">ประวัติการสั่งซื้อ</h2>
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php if (empty($orders_history)): ?>
                        <div class="alert alert-info">คุณยังไม่มีประวัติการสั่งซื้อ</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>หมายเลขคำสั่งซื้อ</th>
                                        <th>วันที่</th>
                                        <th class="text-end">ยอดรวม</th>
                                        <th>สถานะ</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders_history as $hist_order): ?>
                                        <tr>
                                            <td>#<?= $hist_order['order_id'] ?></td>
                                            <td><?= date('d/m/Y', strtotime($hist_order['order_date'])) ?></td>
                                            <td class="text-end"><?= number_format($hist_order['total_amount'], 2) ?></td>
                                            <td><span class="badge bg-info text-dark"><?= ucfirst($hist_order['status']) ?></span></td>
                                            <td><a href="order.php?id=<?= $hist_order['order_id'] ?>" class="btn btn-sm btn-outline-primary">ดูรายละเอียด</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">ไม่พบคำสั่งซื้อที่ระบุ</div>
        <?php endif; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
