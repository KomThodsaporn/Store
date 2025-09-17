<?php
session_start();
require_once 'config.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$product_id = $_GET['id'];
$stmt = $conn->prepare("SELECT p.*, c.category_name
FROM products p
LEFT JOIN categories c ON p.category_id = c.category_id
WHERE p.product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายละเอียดสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <a href="index.php" class="btn btn-secondary btn-sm">← กลับหน้ารายการสินค้า</a>
                    </div>
                    <div class="card-body">
                        <h3 class="card-title"><?= htmlspecialchars($product['product_name']) ?></h3>
                        <h6 class="text-muted mb-3">หมวดหมู่: <?= htmlspecialchars($product['category_name']) ?></h6>
                        <p class="card-text"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                        <hr>
                        <p><strong>ราคา:</strong> <span class="text-success fs-5"><?= number_format($product['price'], 2) ?></span> บาท</p>
                        <p><strong>คงเหลือ:</strong> <?= htmlspecialchars($product['stock']) ?> ชิ้น</p>
                        <?php if ($isLoggedIn): ?>
                            <form action="cart.php" method="post" class="mt-3">
                                <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <label for="quantity" class="col-form-label">จำนวน:</label>
                                    </div>
                                    <div class="col-auto">
                                        <input type="number" name="quantity" id="quantity" class="form-control"
                                            value="1" min="1" max="<?= $product['stock'] ?>" required>
                                    </div>
                                    <div class="col-auto">
                                        <button type="submit" class="btn btn-success">เพิ่มในตะกร้า</button>
                                    </div>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-info mt-3">กรุณาเข้าสู่ระบบเพื่อสั่งซื้อสินค้า</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>