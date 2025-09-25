<?php
session_start();
require 'config.php';
require 'function.php';
// ตรวจสอบกำรล็อกอิน
if (!isset($_SESSION['user_id'])) { // TODO: ใส่ session ของ user
    header("Location: login.php"); // TODO: หน้ำ login
    exit;
}
$user_id = $_SESSION['user_id']; // TODO: ก ำหนด user_idid
// -----------------------------
// ดงึค ำสั่งซอื้ ของผใู้ช ้
// -----------------------------
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC"); // orders
//table และ order_date
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ประวัตกิ ำรสั่งซอื้ </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-4">
    <h2>ประวัตกิ ำรสั่งซอื้ </h2>
    <a href="index.php" class="btn btn-secondary mb-3">← กลับหน้ำหลัก</a>
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">ท ำรำยกำรสั่งซอื้ เรยีบรอ้ ยแลว้</div>
    <?php endif; ?>
    <?php if (count($orders) === 0): ?>
        <div class="alert alert-warning">คณุ ยังไมเ่ คยสั่งซอื้ สนิ คำ้</div>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <strong>รหัสค ำสั่งซอื้ :</strong> #<?= $order['order_id'] ?> |
                    <strong>วันที่:</strong> <?= $order['order_date'] ?> |
                    <strong>สถำนะ:</strong> <?= ucfirst($order['status']) ?>
                </div>
                <div class="card-body">
                    <ul class="list-group mb-3">
                        <?php foreach (getOrderItems($conn, $order['order_id']) as $item): ?>
                            <li class="list-group-item">
                                <?= htmlspecialchars($item['product_name']) ?> × <?= $item['quantity'] ?> =
                                <?=
                                    number_format($item['price'] * $item['quantity'], 2) ?> บำท
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <p><strong>รวมทัง้สนิ้ :</strong> <?= number_format($order['total_amount'], 2) ?> บำท</p>
                    <?php $shipping = getShippingInfo($conn, $order['order_id']); ?>
                    <?php if ($shipping): ?>
                        <p><strong>ทอี่ ยจู่ ัดสง่ :</strong> <?= htmlspecialchars($shipping['address']) ?>,
                            <?=
                                htmlspecialchars($shipping['city']) ?>             <?= $shipping['postal_code'] ?>
                        </p>
                        <p><strong>สถำนะกำรจัดสง่ :</strong> <?= ucfirst($shipping['shipping_status']) ?></p>
                        <p><strong>เบอร์โทร:</strong> <?= htmlspecialchars($shipping['phone']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>

</html>