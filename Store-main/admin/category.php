<?php
session_start();
// เรียกใช้ไฟล์ config และ auth
require '../config.php';
require 'auth_admin.php';

// ---- ส่วนของการจัดการฟอร์ม (เพิ่ม, แก้ไข, ลบ) ----

// ตรวจสอบว่ามีการส่งข้อมูลมาแบบ POST หรือไม่ (สำหรับการเพิ่มและแก้ไข)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ตรวจสอบว่าเป็นการ "เพิ่มหมวดหมู่" หรือไม่
    if (isset($_POST['add_category'])) {
        $category_name = trim($_POST['category_name']);
        if ($category_name) {
            $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
            $stmt->execute([$category_name]);
            $_SESSION['success'] = "เพิ่มหมวดหมู่สำเร็จ";
        } else {
            $_SESSION['error'] = "กรุณากรอกชื่อหมวดหมู่";
        }
    // ตรวจสอบว่าเป็นการ "แก้ไขหมวดหมู่" หรือไม่
    } elseif (isset($_POST['update_category'])) {
        $category_id = $_POST['category_id'];
        $category_name = trim($_POST['new_name']);
        if ($category_name) {
            $stmt = $conn->prepare("UPDATE categories SET category_name = ? WHERE category_id = ?");
            $stmt->execute([$category_name, $category_id]);
            $_SESSION['success'] = "แก้ไขชื่อหมวดหมู่สำเร็จ";
        } else {
            $_SESSION['error'] = "กรุณากรอกชื่อใหม่";
        }
    }
    // Redirect กลับไปหน้าเดิมเพื่อป้องกันการส่งฟอร์มซ้ำ
    header("Location: category.php");
    exit;
}

// ตรวจสอบว่ามีการส่งข้อมูลมาแบบ GET และมี action เป็น 'delete' หรือไม่
if (isset($_GET['delete'])) {
    $category_id = $_GET['delete'];
    // ตรวจสอบว่ามีสินค้าอยู่ในหมวดหมู่นี้หรือไม่
    $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $stmt->execute([$category_id]);
    if ($stmt->fetchColumn() > 0) {
        // ถ้ามีสินค้าอยู่ จะไม่สามารถลบได้
        $_SESSION['error'] = "ไม่สามารถลบได้ เนื่องจากยังมีสินค้าในหมวดหมู่นี้";
    } else {
        // ถ้าไม่มีสินค้าอยู่ ให้ทำการลบหมวดหมู่
        $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
        $stmt->execute([$category_id]);
        $_SESSION['success'] = "ลบหมวดหมู่เรียบร้อยแล้ว";
    }
    // Redirect กลับไปหน้าเดิม
    header("Location: category.php");
    exit;
}

// ---- ส่วนของการดึงข้อมูลมาแสดงผล ----

// ดึงข้อมูลหมวดหมู่ทั้งหมดจากฐานข้อมูลเพื่อนำไปแสดงในตาราง
$categories = $conn->query("SELECT * FROM categories ORDER BY category_id ASC")->fetchAll(PDO::FETCH_ASSOC);
// เก็บชื่อไฟล์ปัจจุบันสำหรับ active menu
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการหมวดหมู่ - Admin Panel</title>
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
        <h1 class="h3 mb-0"><i class="bi bi-tags-fill"></i> จัดการหมวดหมู่สินค้า</h1>
    </div>

    <div class="row">
        <!-- ฟอร์มสำหรับเพิ่มหมวดหมู่ใหม่ -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-plus-circle-fill"></i> เพิ่มหมวดหมู่ใหม่</h5>
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="input-group">
                            <input type="text" name="category_name" class="form-control" placeholder="ชื่อหมวดหมู่ใหม่" required>
                            <button type="submit" name="add_category" class="btn btn-primary">เพิ่ม</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- ตารางแสดงรายการหมวดหมู่ทั้งหมด -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-list-ul"></i> รายการหมวดหมู่</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>ชื่อหมวดหมู่</th>
                                    <th>แก้ไขชื่อ</th>
                                    <th class="text-center">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($categories)): ?>
                                    <tr><td colspan="3" class="text-center text-muted py-4">ยังไม่มีหมวดหมู่</td></tr>
                                <?php else: ?>
                                    <!-- วนลูปแสดงข้อมูลหมวดหมู่แต่ละรายการ -->
                                    <?php foreach ($categories as $cat): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($cat['category_name']) ?></td>
                                            <td>
                                                <!-- ฟอร์มสำหรับแก้ไขชื่อหมวดหมู่ -->
                                                <form method="post" class="d-flex">
                                                    <input type="hidden" name="category_id" value="<?= $cat['category_id'] ?>">
                                                    <input type="text" name="new_name" class="form-control form-control-sm me-2" placeholder="ชื่อใหม่" required>
                                                    <button type="submit" name="update_category" class="btn btn-sm btn-outline-warning" title="บันทึก"><i class="bi bi-save"></i></button>
                                                </form>
                                            </td>
                                            <td class="text-center">
                                                <!-- ปุ่มสำหรับลบหมวดหมู่ -->
                                                <a href="category.php?delete=<?= $cat['category_id'] ?>" class="btn btn-sm btn-outline-danger delete-btn" title="ลบ">
                                                    <i class="bi bi-trash-fill"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ---- ส่วนของ Javascript ----
document.addEventListener('DOMContentLoaded', function () {
    // แสดงข้อความแจ้งเตือน (Success/Error) โดยใช้ SweetAlert2
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
            e.preventDefault(); // หยุดการทำงานปกติของลิงก์
            const url = this.href;
            Swal.fire({
                title: 'ยืนยันการลบ',
                text: "คุณต้องการลบหมวดหมู่นี้ใช่หรือไม่?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ใช่, ลบเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    // หากผู้ใช้ยืนยัน ให้ไปที่ URL สำหรับลบ
                    window.location.href = url;
                }
            });
        });
    });
});
</script>
</body>
</html>
