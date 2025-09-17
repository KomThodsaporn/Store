<?php
session_start();
require '../config.php'; // เชื่อมต่อฐานข้อมูลด้วย PDO
require 'auth_admin.php'; // ตรวจสอบสิทธิ์ admin

// ... (PHP logic remains the same)

// เพิ่มหมวดหมู่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name']);
    if ($category_name) {
        $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
        $stmt->execute([$category_name]);
        $_SESSION['success'] = "เพิ่มหมวดหมู่สำเร็จ";
        header("Location: category.php");
        exit;
    } else {
        $_SESSION['error'] = "กรุณากรอกชื่อหมวดหมู่";
    }
}

// ลบหมวดหมู่
if (isset($_GET['delete'])) {
    $category_id = $_GET['delete'];
    $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $productCount = $stmt->fetchColumn();
    if ($productCount > 0) {
        $_SESSION['error'] = "ไม่สามารถลบหมวดหมู่นี้ได้ เนื่องจากยังมีสินค้าในหมวดหมู่นี้อยู่";
    } else {
        $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
        $stmt->execute([$category_id]);
        $_SESSION['success'] = "ลบหมวดหมู่เรียบร้อยแล้ว";
    }
    header("Location: category.php");
    exit;
}

// แก้ไขหมวดหมู่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $category_id = $_POST['category_id'];
    $category_name = trim($_POST['new_name']);
    if ($category_name) {
        $stmt = $conn->prepare("UPDATE categories SET category_name = ? WHERE category_id = ?");
        $stmt->execute([$category_name, $category_id]);
        $_SESSION['success'] = "แก้ไขชื่อหมวดหมู่สำเร็จ";
        header("Location: category.php");
        exit;
    } else {
        $_SESSION['error'] = "กรุณากรอกชื่อใหม่";
    }
}

$categories = $conn->query("SELECT * FROM categories ORDER BY category_id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการหมวดหมู่</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3"><i class="bi bi-tags-fill"></i> จัดการหมวดหมู่สินค้า</h1>
                    <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> กลับหน้าผู้ดูแล</a>
                </div>

                <?php if (isset($_SESSION['error'])) : ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $_SESSION['error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                <?php if (isset($_SESSION['success'])) : ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION['success'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-plus-circle-fill"></i> เพิ่มหมวดหมู่ใหม่</h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="input-group">
                                <input type="text" name="category_name" class="form-control" placeholder="ชื่อหมวดหมู่ใหม่" required>
                                <button type="submit" name="add_category" class="btn btn-primary">เพิ่มหมวดหมู่</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-list-ul"></i> รายการหมวดหมู่</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th scope="col">ชื่อหมวดหมู่</th>
                                        <th scope="col">แก้ไขชื่อ</th>
                                        <th scope="col" class="text-center">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($categories)) : ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">ยังไม่มีหมวดหมู่</td>
                                        </tr>
                                    <?php else : ?>
                                        <?php foreach ($categories as $cat) : ?>
                                            <tr>
                                                <td><?= htmlspecialchars($cat['category_name']) ?></td>
                                                <td>
                                                    <form method="post" class="d-flex">
                                                        <input type="hidden" name="category_id" value="<?= $cat['category_id'] ?>">
                                                        <input type="text" name="new_name" class="form-control form-control-sm me-2" placeholder="ชื่อใหม่" required>
                                                        <button type="submit" name="update_category" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil-square"></i></button>
                                                    </form>
                                                </td>
                                                <td class="text-center">
                                                    <a href="category.php?delete=<?= $cat['category_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('คุณต้องการลบหมวดหมู่นี้หรือไม่? การกระทำนี้ไม่สามารถย้อนกลับได้')"><i class="bi bi-trash-fill"></i></a>
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
