<?php
session_start();
require '../config.php';
require 'auth_admin.php';

// ... (PHP logic remains the same)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_id = intval($_POST['category_id']);

    if ($name && $price > 0 && $category_id > 0) {
        $stmt = $conn->prepare("INSERT INTO products (product_name, description, price, stock, category_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $stock, $category_id]);
        $_SESSION['success'] = "เพิ่มสินค้าสำเร็จ";
    } else {
        $_SESSION['error'] = "กรุณากรอกข้อมูลสินค้าให้ครบถ้วนและถูกต้อง";
    }
    header("Location: products.php");
    exit;
}

if (isset($_GET['delete'])) {
    $product_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $_SESSION['success'] = "ลบสินค้าเรียบร้อยแล้ว";
    header("Location: products.php");
    exit;
}

$stmt = $conn->query("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id ORDER BY p.created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
$categories = $conn->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-11">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3"><i class="bi bi-box-seam-fill"></i> จัดการสินค้า</h1>
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
                        <h5 class="mb-0"><i class="bi bi-plus-circle-fill"></i> เพิ่มสินค้าใหม่</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" class="row g-3">
                            <div class="col-md-6">
                                <label for="product_name" class="form-label">ชื่อสินค้า</label>
                                <input type="text" id="product_name" name="product_name" class="form-control" placeholder="เช่น. ขนมเค้ก" required>
                            </div>
                            <div class="col-md-3">
                                <label for="price" class="form-label">ราคา</label>
                                <div class="input-group">
                                    <input type="number" id="price" step="0.01" name="price" class="form-control" placeholder="0.00" required>
                                    <span class="input-group-text">บาท</span>
                                </div>
                            </div>
                             <div class="col-md-3">
                                <label for="stock" class="form-label">คงเหลือ</label>
                                <input type="number" id="stock" name="stock" class="form-control" placeholder="0" min="0" required>
                            </div>
                            <div class="col-md-12">
                                <label for="category_id" class="form-label">หมวดหมู่</label>
                                <select id="category_id" name="category_id" class="form-select" required>
                                    <option value="">--- เลือกหมวดหมู่ ---</option>
                                    <?php foreach ($categories as $cat) : ?>
                                        <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="description" class="form-label">รายละเอียดสินค้า</label>
                                <textarea id="description" name="description" class="form-control" rows="3" placeholder="คำอธิบายสั้นๆ เกี่ยวกับสินค้า"></textarea>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" name="add_product" class="btn btn-primary"><i class="bi bi-plus-lg"></i> เพิ่มสินค้า</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-list-ul"></i> รายการสินค้า</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0 align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th scope="col">ชื่อสินค้า</th>
                                        <th scope="col">หมวดหมู่</th>
                                        <th scope="col" class="text-end">ราคา</th>
                                        <th scope="col" class="text-center">คงเหลือ</th>
                                        <th scope="col" class="text-center">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($products)) : ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">ยังไม่มีสินค้า</td>
                                        </tr>
                                    <?php else : ?>
                                        <?php foreach ($products as $p) : ?>
                                            <tr>
                                                <td><?= htmlspecialchars($p['product_name']) ?></td>
                                                <td><span class="badge bg-secondary"><?= htmlspecialchars($p['category_name']) ?></span></td>
                                                <td class="text-end"><?= number_format($p['price'], 2) ?></td>
                                                <td class="text-center"><?= $p['stock'] ?></td>
                                                <td class="text-center">
                                                    <a href="edit_product.php?id=<?= $p['product_id'] ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil-square"></i></a>
                                                    <a href="products.php?delete=<?= $p['product_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('ยืนยันการลบสินค้านี้?')"><i class="bi bi-trash-fill"></i></a>
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
