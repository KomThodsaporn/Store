<?php
session_start();
// เรียกใช้ไฟล์ config และ auth
require '../config.php';
require 'auth_admin.php';

// ---- ส่วนของการจัดการฟอร์ม (เพิ่ม, ลบสินค้า) ----

// ตรวจสอบว่าเป็นการ "เพิ่มสินค้า" หรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_id = intval($_POST['category_id']);

    // ตรวจสอบข้อมูลพื้นฐาน
    if ($name && $price > 0 && $category_id > 0) {
        $imageName = null;
        // ตรวจสอบและจัดการการอัปโหลดรูปภาพ
        if (!empty($_FILES['product_image']['name'])) {
            $file = $_FILES['product_image'];
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (in_array($file['type'], $allowed) && $file['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $imageName = 'product_' . time() . '.' . $ext;
                $path = realpath(__DIR__ . '/../product_images') . DIRECTORY_SEPARATOR . $imageName;
                move_uploaded_file($file['tmp_name'], $path);
            }
        }
        // เพิ่มข้อมูลสินค้าลงในฐานข้อมูล
        $stmt = $conn->prepare("INSERT INTO products (product_name, description, price, stock, category_id, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $stock, $category_id, $imageName]);
        $_SESSION['success'] = "เพิ่มสินค้าสำเร็จ";
    } else {
        $_SESSION['error'] = "กรุณากรอกข้อมูลสินค้าให้ครบถ้วนและถูกต้อง";
    }
    header("Location: products.php");
    exit;
}

// ตรวจสอบว่าเป็นการ "ลบสินค้า" หรือไม่
if (isset($_GET['delete'])) {
    $product_id = (int) $_GET['delete'];
    // ดึงชื่อไฟล์รูปภาพเพื่อใช้ในการลบไฟล์ออกจากเซิร์ฟเวอร์
    $stmt = $conn->prepare("SELECT image FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $imageName = $stmt->fetchColumn();

    $conn->beginTransaction();
    try {
        // ลบข้อมูลสินค้าออกจากฐานข้อมูล
        $del = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $del->execute([$product_id]);
        $conn->commit();
        $_SESSION['success'] = "ลบสินค้าเรียบร้อยแล้ว";
        // ถ้ามีไฟล์รูปภาพอยู่ ให้ลบออกจากโฟลเดอร์
        if ($imageName) {
            $filePath = realpath(__DIR__ . '/../product_images') . DIRECTORY_SEPARATOR . $imageName;
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการลบสินค้า";
    }
    header("Location: products.php");
    exit;
}

// ---- ส่วนของการดึงข้อมูลมาแสดงผล ----

// ดึงข้อมูลสินค้าทั้งหมดพร้อมชื่อหมวดหมู่
$products = $conn->query("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id ORDER BY p.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
// ดึงข้อมูลหมวดหมู่ทั้งหมดเพื่อใช้ในฟอร์มเพิ่มสินค้า
$categories = $conn->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll(PDO::FETCH_ASSOC);
// เก็บชื่อไฟล์ปัจจุบันสำหรับ active menu
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสินค้า - Admin Panel</title>
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
        <h1 class="h3 mb-0"><i class="bi bi-box-seam-fill"></i> จัดการสินค้า</h1>
    </div>

    <!-- ส่วนของฟอร์มเพิ่มสินค้า (Accordion) -->
    <div class="accordion mb-4" id="addProductAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                    <i class="bi bi-plus-circle-fill me-2"></i> เพิ่มสินค้าใหม่
                </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#addProductAccordion">
                <div class="accordion-body">
                    <form method="post" enctype="multipart/form-data" class="row g-3">
                        <div class="col-md-6"><label class="form-label">ชื่อสินค้า</label><input type="text" name="product_name" class="form-control" required></div>
                        <div class="col-md-3"><label class="form-label">ราคา</label><input type="number" step="0.01" name="price" class="form-control" required></div>
                        <div class="col-md-3"><label class="form-label">คงเหลือ</label><input type="number" name="stock" class="form-control" min="0" required></div>
                        <div class="col-md-7"><label class="form-label">หมวดหมู่</label><select name="category_id" class="form-select" required><option value="">--- เลือกหมวดหมู่ ---</option><?php foreach ($categories as $cat): ?><option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-5"><label class="form-label">รูปสินค้า</label><input type="file" name="product_image" class="form-control"></div>
                        <div class="col-12"><label class="form-label">รายละเอียด</label><textarea name="description" class="form-control" rows="3"></textarea></div>
                        <div class="col-12 text-end"><button type="submit" name="add_product" class="btn btn-primary"><i class="bi bi-plus-lg"></i> เพิ่มสินค้า</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ตารางแสดงรายการสินค้า -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-list-ul"></i> รายการสินค้า</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 50px;">รูป</th>
                            <th>ชื่อสินค้า</th>
                            <th>หมวดหมู่</th>
                            <th class="text-end">ราคา</th>
                            <th class="text-center">คงเหลือ</th>
                            <th class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">ยังไม่มีสินค้า</td></tr>
                        <?php else: ?>
                            <!-- วนลูปแสดงข้อมูลสินค้าแต่ละรายการ -->
                            <?php foreach ($products as $p): ?>
                                <tr>
                                    <td><img src="../product_images/<?= htmlspecialchars($p['image'] ?? 'no_image.png') ?>" width="40" height="40" class="rounded-circle object-fit-cover"></td>
                                    <td><?= htmlspecialchars($p['product_name']) ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($p['category_name']) ?></span></td>
                                    <td class="text-end"><?= number_format($p['price'], 2) ?></td>
                                    <td class="text-center"><?= $p['stock'] ?></td>
                                    <td class="text-center">
                                        <!-- ปุ่มแก้ไขและลบสินค้า -->
                                        <a href="edit_product.php?id=<?= $p['product_id'] ?>" class="btn btn-sm btn-outline-warning" title="แก้ไข"><i class="bi bi-pencil-square"></i></a>
                                        <a href="products.php?delete=<?= $p['product_id'] ?>" class="btn btn-sm btn-outline-danger delete-btn" title="ลบ"><i class="bi bi-trash-fill"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // แสดงข้อความแจ้งเตือน (Success/Error)
    <?php if (isset($_SESSION['success'])) : ?>
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: '<?= addslashes($_SESSION['success']) ?>', showConfirmButton: false, timer: 3000 });
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])) : ?>
        Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: '<?= addslashes($_SESSION['error']) ?>', showConfirmButton: false, timer: 3000 });
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    // เพิ่มการยืนยันก่อนลบข้อมูล
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const url = this.href;
            Swal.fire({
                title: 'ยืนยันการลบ',
                text: "คุณต้องการลบสินค้านี้ใช่หรือไม่?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ใช่, ลบเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });
    });
});
</script>
</body>
</html>
