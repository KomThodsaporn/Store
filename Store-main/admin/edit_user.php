<?php
session_start();
// เรียกใช้ไฟล์ config และ auth
require '../config.php';
require 'auth_admin.php';

// ตรวจสอบว่ามี ID ของสมาชิกส่งมาใน URL หรือไม่
if (!isset($_GET['id'])) {
    header("Location: users.php");
    exit;
}

$user_id = (int)$_GET['id'];

// ---- ส่วนของการดึงข้อมูลมาแสดงในฟอร์ม ----

// ดึงข้อมูลสมาชิกที่ต้องการแก้ไขจาก ID
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? AND role = 'member'");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// หากไม่พบสมาชิก ให้ redirect กลับไปหน้า users
if (!$user) {
    $_SESSION['error'] = "ไม่พบสมาชิกคนที่คุณต้องการแก้ไข";
    header("Location: users.php");
    exit;
}

// ---- ส่วนของการจัดการฟอร์มแก้ไข ----

$error = null;
// ตรวจสอบว่ามีการส่งข้อมูลมาแบบ POST หรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับข้อมูลจากฟอร์ม
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // ตรวจสอบความถูกต้องของข้อมูล (Validation)
    if (empty($username) || empty($email)) {
        $error = "กรุณากรอกชื่อผู้ใช้และอีเมล";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "รูปแบบอีเมลไม่ถูกต้อง";
    } elseif (strlen($password) > 0 && strlen($password) < 6) {
        $error = "รหัสผ่านใหม่ต้องยาวอย่างน้อย 6 อักขระ";
    } elseif ($password !== $confirm) {
        $error = "รหัสผ่านใหม่กับยืนยันรหัสผ่านไม่ตรงกัน";
    }

    // ตรวจสอบว่า username หรือ email ซ้ำกับคนอื่นหรือไม่
    if (!$error) {
        $chk = $conn->prepare("SELECT 1 FROM users WHERE (username = ? OR email = ?) AND user_id != ?");
        $chk->execute([$username, $email, $user_id]);
        if ($chk->fetch()) {
            $error = "ชื่อผู้ใช้หรืออีเมลนี้มีอยู่แล้วในระบบ";
        }
    }

    // หากไม่มีข้อผิดพลาด ให้ทำการอัปเดตข้อมูล
    if (!$error) {
        // ถ้ามีการกรอกรหัสผ่านใหม่ ให้ hash รหัสผ่านด้วย
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET username = ?, full_name = ?, email = ?, password = ? WHERE user_id = ?";
            $args = [$username, $full_name, $email, $hashed, $user_id];
        } else {
            // ถ้าไม่ได้กรอกรหัสผ่านใหม่ ไม่ต้องอัปเดต field password
            $sql = "UPDATE users SET username = ?, full_name = ?, email = ? WHERE user_id = ?";
            $args = [$username, $full_name, $email, $user_id];
        }
        $upd = $conn->prepare($sql);
        $upd->execute($args);
        $_SESSION['success'] = "แก้ไขข้อมูลสมาชิกสำเร็จ";
        header("Location: users.php");
        exit;
    }

    // หากมี error ให้เก็บค่าที่ผู้ใช้กรอกไว้เพื่อแสดงในฟอร์มอีกครั้ง
    $user['username'] = $username;
    $user['full_name'] = $full_name;
    $user['email'] = $email;
}

// เก็บชื่อไฟล์ปัจจุบันสำหรับ active menu
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขสมาชิก - Admin Panel</title>
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
        <h1 class="h3 mb-0"><i class="bi bi-pencil-square"></i> แก้ไขข้อมูลสมาชิก</h1>
        <a href="users.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> กลับหน้ารายชื่อ</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">ข้อมูลของ: <?= htmlspecialchars($user['username']) ?></h5>
                </div>
                <div class="card-body">
                    <!-- ฟอร์มสำหรับแก้ไขข้อมูลสมาชิก -->
                    <form method="post" class="row g-3">
                        <div class="col-md-6">
                            <label for="username" class="form-label">ชื่อผู้ใช้</label>
                            <input type="text" id="username" name="username" class="form-control" required value="<?= htmlspecialchars($user['username']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="full_name" class="form-label">ชื่อ-นามสกุล</label>
                            <input type="text" id="full_name" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>">
                        </div>
                        <div class="col-12">
                            <label for="email" class="form-label">อีเมล</label>
                            <input type="email" id="email" name="email" class="form-control" required value="<?= htmlspecialchars($user['email']) ?>">
                        </div>

                        <hr class="my-4">

                        <h6 class="text-muted">เปลี่ยนรหัสผ่าน (ถ้าไม่ต้องการเปลี่ยน ให้เว้นว่าง)</h6>
                        <div class="col-md-6">
                            <label for="password" class="form-label">รหัสผ่านใหม่</label>
                            <input type="password" id="password" name="password" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label">ยืนยันรหัสผ่านใหม่</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill"></i> บันทึกการแก้ไข</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // แสดงข้อความแจ้งเตือน (Error)
    <?php if (isset($error)): ?>
        Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด',
            text: '<?= addslashes($error) ?>',
        });
    <?php endif; ?>
});
</script>
</body>
</html>
