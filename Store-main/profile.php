<?php
session_start();
// เรียกใช้ไฟล์ config เพื่อเชื่อมต่อฐานข้อมูล
require_once 'config.php';

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือยัง ถ้ายัง ให้ redirect ไปหน้า login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// ---- ส่วนของการดึงข้อมูลผู้ใช้ ----
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// ---- ส่วนของการจัดการฟอร์ม (อัปเดตโปรไฟล์) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ตรวจสอบว่าเป็นการ "อัปเดตข้อมูลส่วนตัว" หรือไม่
    if (isset($_POST['update_profile'])) {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'รูปแบบอีเมลไม่ถูกต้อง';
        } else {
            // ตรวจสอบว่าอีเมลซ้ำกับคนอื่นหรือไม่
            $chk = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
            $chk->execute([$email, $user_id]);
            if ($chk->fetch()) {
                $error = 'อีเมลนี้มีผู้ใช้งานแล้ว';
            } else {
                // อัปเดตข้อมูล
                $upd = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE user_id = ?");
                $upd->execute([$full_name, $email, $user_id]);
                $success = 'อัปเดตข้อมูลส่วนตัวสำเร็จ';
                // อัปเดตข้อมูล user ที่แสดงผลใหม่
                $user['full_name'] = $full_name;
                $user['email'] = $email;
            }
        }
    }

    // ตรวจสอบว่าเป็นการ "เปลี่ยนรหัสผ่าน" หรือไม่
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // ตรวจสอบรหัสผ่านปัจจุบัน
        if (!password_verify($current_password, $user['password'])) {
            $error = 'รหัสผ่านปัจจุบันไม่ถูกต้อง';
        } elseif (strlen($new_password) < 6) {
            $error = 'รหัสผ่านใหม่ต้องมีความยาวอย่างน้อย 6 ตัวอักษร';
        } elseif ($new_password !== $confirm_password) {
            $error = 'รหัสผ่านใหม่และการยืนยันไม่ตรงกัน';
        } else {
            // อัปเดตรหัสผ่านใหม่
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $upd = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $upd->execute([$hashed_password, $user_id]);
            $success = 'เปลี่ยนรหัสผ่านสำเร็จ';
        }
    }
}

$isLoggedIn = true; // กำหนดให้เป็น true เพราะหน้านี้ต้องล็อกอินถึงจะเข้าได้
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์ของฉัน</title>
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
    <!-- แถบเมนูหลัก (Navbar) -->
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
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h2 class="mb-4">โปรไฟล์ของฉัน</h2>

                <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
                <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">ข้อมูลส่วนตัว</h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">ชื่อผู้ใช้</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label for="full_name" class="form-label">ชื่อ-นามสกุล</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">อีเมล</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary">บันทึกข้อมูลส่วนตัว</button>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">เปลี่ยนรหัสผ่าน</h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">รหัสผ่านปัจจุบัน</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">รหัสผ่านใหม่</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">ยืนยันรหัสผ่านใหม่</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" name="change_password" class="btn btn-warning">เปลี่ยนรหัสผ่าน</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
