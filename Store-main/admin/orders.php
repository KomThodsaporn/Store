<?php
session_start();
// เรียกใช้ไฟล์ config, function และ auth
require '../config.php';
require '../function.php';
require 'auth_admin.php';

// ---- ส่วนของการจัดการฟอร์ม (อัปเดตสถานะ) ----

// ตรวจสอบว่ามีการส่งข้อมูลมาแบบ POST หรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ตรวจสอบว่าเป็นการ "อัปเดตสถานะคำสั่งซื้อ" หรือไม่
    if (isset($_POST['update_status'])) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->execute([$_POST['status'], $_POST['order_id']]);
        $_SESSION['success'] = "อัปเดตสถานะคำสั่งซื้อสำเร็จ";
    }
    // ตรวจสอบว่าเป็นการ "อัปเดตสถานะการจัดส่ง" หรือไม่
    if (isset($_POST['update_shipping'])) {
        $stmt = $conn->prepare("UPDATE shipping SET shipping_status = ? WHERE shipping_id = ?");
        $stmt->execute([$_POST['shipping_status'], $_POST['shipping_id']]);
        $_SESSION['success'] = "อัปเดตสถานะการจัดส่งสำเร็จ";
    }
    // Redirect กลับไปหน้าเดิมเพื่อป้องกันการส่งฟอร์มซ้ำ
    header("Location: orders.php");
    exit;
}

// ---- ส่วนของการดึงข้อมูลมาแสดงผล ----

// ดึงข้อมูลคำสั่งซื้อทั้งหมดจากฐานข้อมูล
$orders = $conn->query("SELECT o.*, u.username FROM orders o LEFT JOIN users u ON o.user_id = u.user_id ORDER BY o.order_date DESC")->fetchAll(PDO::FETCH_ASSOC);

// ฟังก์ชันสำหรับเปลี่ยนสีป้ายสถานะ (badge) ตามสถานะของคำสั่งซื้อ
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'pending': return 'bg-warning text-dark';
        case 'processing': return 'bg-info text-dark';
        case 'shipped': return 'bg-primary';
        case 'completed': return 'bg-success';
        case 'cancelled': return 'bg-danger';
        default: return 'bg-secondary';
    }
}

// เก็บชื่อไฟล์ปัจจุบันสำหรับ active menu
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการคำสั่งซื้อ - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-shield-lock-fill"></i> Shop</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <span class="navbar-text me-3">
                            <i class="bi bi-person-circle"></i> ยินดีต้อนรับ, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">ภาพรวม</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">สินค้า</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="category.php">หมวดหมู่</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">คำสั่งซื้อ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">สมาชิก</a>
                    </li>
                    <li class="nav-item ms-3">
                        <a href="../index.php" target="_blank" class="btn btn-outline-info btn-sm"><i class="bi bi-shop"></i> ไปที่หน้าร้าน</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a href="../logout.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right"></i> ออกจากระบบ</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

<main class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="bi bi-receipt-cutoff"></i> จัดการคำสั่งซื้อ</h1>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">คำสั่งซื้อทั้งหมด</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($orders)): ?>
                <div class="text-center p-5">ยังไม่มีคำสั่งซื้อ</div>
            <?php else: ?>
                <!-- แสดงรายการคำสั่งซื้อแบบ Accordion -->
                <div class="accordion accordion-flush" id="ordersAccordion">
                    <?php foreach ($orders as $index => $order): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading<?= $index ?>">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>">
                                    <span class="fw-bold me-2">#<?= $order['order_id'] ?></span>
                                    <span class="me-3">โดย: <?= htmlspecialchars($order['username'] ?? 'N/A') ?></span>
                                    <span class="text-muted me-auto">(<?= date('d/m/Y H:i', strtotime($order['order_date'])) ?>)</span>
                                    <span class="badge <?= getStatusBadgeClass($order['status']) ?>"><?= ucfirst($order['status']) ?></span>
                                </button>
                            </h2>
                            <div id="collapse<?= $index ?>" class="accordion-collapse collapse" data-bs-parent="#ordersAccordion">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <!-- แสดงรายการสินค้าในคำสั่งซื้อ -->
                                            <h6><i class="bi bi-list-ul"></i> รายการสินค้า (ยอดรวม: <?= number_format($order['total_amount'], 2) ?> บาท)</h6>
                                            <ul class="list-group mb-3">
                                                <?php foreach (getOrderItems($conn, $order['order_id']) as $item): ?>
                                                    <li class="list-group-item d-flex justify-content-between">
                                                        <?= htmlspecialchars($item['product_name']) ?> (x<?= $item['quantity'] ?>)
                                                        <span><?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                            <!-- แสดงข้อมูลการจัดส่ง -->
                                            <h6><i class="bi bi-truck"></i> ข้อมูลจัดส่ง</h6>
                                            <?php $shipping = getShippingInfo($conn, $order['order_id']); ?>
                                            <?php if ($shipping): ?>
                                                <p class="mb-1"><?= htmlspecialchars($shipping['address']) ?>, <?= htmlspecialchars($shipping['city']) ?> <?= $shipping['postal_code'] ?></p>
                                                <p class="mb-0">โทร: <?= htmlspecialchars($shipping['phone']) ?></p>
                                            <?php else: ?>
                                                <p class="text-muted">ไม่มีข้อมูลการจัดส่ง</p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <!-- ฟอร์มอัปเดตสถานะคำสั่งซื้อและสถานะการจัดส่ง -->
                                            <h6><i class="bi bi-arrow-repeat"></i> อัปเดตสถานะ</h6>
                                            <form method="post" class="row g-2 mb-3">
                                                <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                                <div class="col-8">
                                                    <select name="status" class="form-select">
                                                        <?php foreach (['pending', 'processing', 'shipped', 'completed', 'cancelled'] as $status): ?>
                                                            <option value="<?= $status ?>" <?= ($order['status'] === $status) ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-4 d-grid"><button type="submit" name="update_status" class="btn btn-sm btn-primary">อัปเดต</button></div>
                                            </form>
                                            <?php if ($shipping): ?>
                                                <form method="post" class="row g-2">
                                                    <input type="hidden" name="shipping_id" value="<?= $shipping['shipping_id'] ?>">
                                                    <div class="col-8">
                                                        <select name="shipping_status" class="form-select">
                                                            <?php foreach (['not_shipped', 'shipped', 'delivered'] as $s): ?>
                                                                <option value="<?= $s ?>" <?= ($shipping['shipping_status'] === $s) ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-4 d-grid"><button type="submit" name="update_shipping" class="btn btn-sm btn-success">อัปเดต</button></div>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // แสดงข้อความแจ้งเตือน (Success)
    <?php if (isset($_SESSION['success'])) : ?>
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: '<?= addslashes($_SESSION['success']) ?>', showConfirmButton: false, timer: 3000 });
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
});
</script>
</body>
</html>
