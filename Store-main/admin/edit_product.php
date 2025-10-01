<?php
session_start();
// เรียกใช้ไฟล์ config และ auth
require '../config.php';
require 'auth_admin.php';

// ตรวจสอบว่ามี ID ของสินค้าส่งมาใน URL หรือไม่
if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit;
}
$product_id = $_GET['id'];

// ---- ส่วนของการดึงข้อมูลมาแสดงในฟอร์ม ----

// ดึงข้อมูลสินค้าที่ต้องการแก้ไขจาก ID
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// หากไม่พบสินค้า ให้ redirect กลับไปหน้า products
if (!$product) {
    $_SESSION['error'] = "ไม่พบสินค้าที่ต้องการแก้ไข";
    header("Location: products.php");
    exit;
}

// ดึงข้อมูลหมวดหมู่ทั้งหมดเพื่อใช้ใน dropdown
$categories = $conn->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// ---- ส่วนของการจัดการฟอร์มแก้ไข ----

// ตรวจสอบว่ามีการส่งข้อมูลมาแบบ POST หรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับข้อมูลจากฟอร์ม
    $name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = (float) $_POST['price'];
    $stock = (int) $_POST['stock'];
    $category_id = (int) $_POST['category_id'];
    $oldImage = $_POST['old_image'] ?? null;
    $removeImage = isset($_POST['remove_image']);

    // ตรวจสอบข้อมูลพื้นฐาน
    if ($name && $price >= 0 && $stock >= 0) {
        $newImageName = $oldImage;

        // ถ้าผู้ใช้ติ๊ก "ลบรูปเดิม"
        if ($removeImage) {
            $newImageName = null;
        }

        // ตรวจสอบและจัดการการอัปโหลดรูปภาพใหม่
        if (!empty($_FILES['product_image']['name'])) {
            $file = $_FILES['product_image'];
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (in_array($file['type'], $allowed) && $file['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $newImageName = 'product_' . time() . '.' . $ext;
                $uploadDir = realpath(__DIR__ . '/../product_images');
                $destPath = $uploadDir . DIRECTORY_SEPARATOR . $newImageName;
                if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                    $newImageName = $oldImage; // หากอัปโหลดไม่สำเร็จ ให้ใช้รูปเดิม
                }
            }
        }

        // อัปเดตข้อมูลสินค้าในฐานข้อมูล
        $sql = "UPDATE products SET product_name = ?, description = ?, price = ?, stock = ?, category_id = ?, image = ? WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$name, $description, $price, $stock, $category_id, $newImageName, $product_id]);

        // หากมีการเปลี่ยนรูป ให้ลบรูปเก่าออกจากเซิร์ฟเวอร์
        if (!empty($oldImage) && $oldImage !== $newImageName) {
            $filePath = realpath(__DIR__ . '/../product_images') . DIRECTORY_SEPARATOR . $oldImage;
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }
        $_SESSION['success'] = "แก้ไขข้อมูลสินค้าสำเร็จ";
        header("Location: products.php");
        exit;
    } else {
        $_SESSION['error'] = "ข้อมูลไม่ถูกต้อง";
        header("Location: edit_product.php?id=" . $product_id);
        exit;
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
    <title>แก้ไขสินค้า - Admin Panel</title>
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
        <h1 class="h3 mb-0"><i class="bi bi-pencil-square"></i> แก้ไขสินค้า</h1>
        <a href="products.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> กลับไปหน้ารายการ</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">แก้ไข: <?= htmlspecialchars($product['product_name']) ?></h5>
        </div>
        <div class="card-body">
            <!-- ฟอร์มสำหรับแก้ไขข้อมูลสินค้า -->
            <form method="post" enctype="multipart/form-data" class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">ชื่อสินค้า</label>
                    <input type="text" name="product_name" class="form-control" value="<?= htmlspecialchars($product['product_name']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">หมวดหมู่</label>
                    <select name="category_id" class="form-select" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['category_id'] ?>" <?= ($product['category_id'] == $cat['category_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['category_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">ราคา</label>
                    <input type="number" step="0.01" name="price" class="form-control" value="<?= $product['price'] ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">จำนวนในคลัง</label>
                    <input type="number" name="stock" class="form-control" value="<?= $product['stock'] ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label">รายละเอียดสินค้า</label>
                    <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($product['description']) ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label d-block">รูปปัจจุบัน</label>
                    <?php if (!empty($product['image'])): ?>
                        <img src="../product_images/<?= htmlspecialchars($product['image']) ?>" width="120" height="120" class="rounded mb-2 object-fit-cover">
                    <?php else: ?>
                        <span class="text-muted d-block mb-2">ไม่มีรูป</span>
                    <?php endif; ?>
                    <input type="hidden" name="old_image" value="<?= htmlspecialchars($product['image'] ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">อัปโหลดรูปใหม่ (ไม่บังคับ)</label>
                    <input type="file" name="product_image" class="form-control">
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image" value="1">
                        <label class="form-check-label" for="remove_image">ลบรูปเดิมและไม่ใช้รูปใหม่</label>
                    </div>
                </div>

                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill"></i> บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // แสดงข้อความแจ้งเตือน (Error)
    <?php if (isset($_SESSION['error'])) : ?>
        Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: '<?= addslashes($_SESSION['error']) ?>', showConfirmButton: false, timer: 3000 });
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
});
</script>
</body>
</html>
