<?php
session_start();
// เรียกใช้ไฟล์ config และ auth
require 'config.php';

// ตรวจสอบว่ามี ID ของสมาชิกส่งมาใน URL หรือไม่
if (!isset($_GET['id'])) {
    header("Location: users.php");
    exit;
}

$user_id = (int) $_GET['id'];

// ---- ส่วนของการดึงข้อมูลมาแสดงในฟอร์ม ----

// ดึงข้อมูลสมาชิกที่ต้องการแก้ไขจาก ID
$stmt = $conn->prepare("SELECT * FROM tb_664230040 WHERE id = ?");
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
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับข้อมูลจากฟอร์มและตัดช่องว่าง
    $std_id = trim($_POST['std_id']);
    $f_name = trim($_POST['f_name']);
    $L_name = trim($_POST['L_name']);
    $mail = trim($_POST['mail']);
    $tel = trim($_POST['tel']);
    $class = trim($_POST['class']);

    // ตรวจสอบความถูกต้องของข้อมูล (Validation)
    if (empty($std_id) || empty($mail)) {
        $error = 'กรุณากรอกข้อมูลให้ครบทุกช่อง';
    } elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        $error = 'รูปแบบอีเมลไม่ถูกต้อง';
    }

    // ตรวจสอบว่า username หรือ email ซ้ำกับคนอื่นหรือไม่
    if (!$error) {
        $stmt = $conn->prepare("SELECT * FROM tb_664230040 WHERE std_id = ? OR mail = ?");
        $stmt->execute([$std_id, $mail]);
        if ($stmt->fetch()) {
            $error = "ชื่อผู้ใช้หรืออีเมลนี้มีอยู่แล้วในระบบ";
        }
    }

    // หากไม่มีข้อผิดพลาด ให้ทำการอัปเดตข้อมูล

    // ถ้ามีการกรอกรหัสผ่านใหม่ ให้ hash รหัสผ่านด้วย
    $sql = "UPDATE tb_664230040 SET std_id = ?, f_name = ?, L_name = ?,mail = ?, tel = ?, class = ? WHERE key = ?";
    $args = [$std_id, $f_name, $L_name, $mail, $tel, $class];
    $upd = $conn->prepare($sql);
    $upd->execute($args);
    $_SESSION['success'] = "แก้ไขข้อมูลสมาชิกสำเร็จ";
    $user['std_id'] = $std_id;
    $user['f_name'] = $f_name;
    $user['L_name'] = $L_name;
    $user['mail'] = $mail;
    $user['tel'] = $tel;
    $user['class'] = $class;
    header("Location: users.php");
    exit;
}
if (!$error) {
    // ถ้ามีการกรอกรหัสผ่านใหม่ ให้ hash รหัสผ่านด้วย
    if (!empty($password)) {
        $sql = "UPDATE tb_664230040 SET std_id = ?, f_name = ?, L_name = ?,mail = ?, tel = ?, class = ? WHERE key = ?";
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
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <span class="navbar-text me-3">
                            <i class="bi bi-person-circle"></i> ยินดีต้อนรับ,
                            <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>
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
                        <a href="../index.php" target="_blank" class="btn btn-outline-info btn-sm"><i
                                class="bi bi-shop"></i> ไปที่หน้าร้าน</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a href="../logout.php" class="btn btn-outline-light btn-sm"><i
                                class="bi bi-box-arrow-right"></i> ออกจากระบบ</a>
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
                        <h5 class="mb-0">ข้อมูลของ: <?= htmlspecialchars($user['f_name']) ?></h5>
                    </div>
                    <div class="card-body">
                        <!-- ฟอร์มสำหรับแก้ไขข้อมูลสมาชิก -->
                        <form method="post" class="row g-3">
                            <div class="col-md-12">
                                <label for="std_id" class="form-label">รหัสนักศึกษา</label>
                                <input type="text" id="std_id" name="std_id" class="form-control" required
                                    value="<?= htmlspecialchars($user['std_id']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="f_name" class="form-label">ชื่อ</label>
                                <input type="text" id="f_name" name="f_name" class="form-control" required
                                    value="<?= htmlspecialchars($user['f_name']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="L_name" class="form-label">สกุล</label>
                                <input type="text" id="L_name" name="L_name" class="form-control"
                                    value="<?= htmlspecialchars($user['L_name']) ?>">
                            </div>
                            <div class="col-12">
                                <label for="mail" class="form-label">เมล</label>
                                <input type="mail" id="mail" name="mail" class="form-control" required
                                    value="<?= htmlspecialchars($user['mail']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="tel" class="form-label">เบอร์โทร</label>
                                <input type="text" id="tel" name="tel" class="form-control"
                                    value="<?= htmlspecialchars($user['tel']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="class" class="form-label">ห้อง</label>
                                <input type="text" id="class" name="class" class="form-control"
                                    value="<?= htmlspecialchars($user['class']) ?>">
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill"></i>
                                    บันทึกการแก้ไข</button>
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