<?php
session_start();
// เรียกใช้ไฟล์ config เพื่อเชื่อมต่อฐานข้อมูล
require_once 'config.php';

// หากผู้ใช้ล็อกอินอยู่แล้ว ให้ redirect ไปหน้า profile
if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit;
}

$error = '';
$success = '';

// ตรวจสอบว่ามีการส่งข้อมูลฟอร์มมาหรือไม่
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับข้อมูลจากฟอร์มและตัดช่องว่าง
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // ---- การตรวจสอบความถูกต้องของข้อมูล (Validation) ----
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'กรุณากรอกข้อมูลให้ครบทุกช่อง';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'รูปแบบอีเมลไม่ถูกต้อง';
    } elseif (strlen($password) < 6) {
        $error = 'รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร';
    } elseif ($password !== $confirm_password) {
        $error = 'รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน';
    } else {
        // ตรวจสอบว่า username หรือ email ซ้ำกับที่มีในระบบหรือไม่
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = 'ชื่อผู้ใช้หรืออีเมลนี้มีผู้ใช้งานแล้ว';
        } else {
            // หากข้อมูลถูกต้องและไม่ซ้ำ ให้ทำการเพิ่มผู้ใช้ใหม่ลงฐานข้อมูล
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'member')");
            if ($stmt->execute([$username, $email, $hashed_password])) {
                $success = 'สมัครสมาชิกสำเร็จ! คุณสามารถเข้าสู่ระบบได้แล้ว';
            } else {
                $error = 'เกิดข้อผิดพลาดในการสมัครสมาชิก';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* สไตล์เฉพาะสำหรับหน้านี้ */
        body {
            background: #f3e5f5;
        }
        .card {
            border: none;
            border-radius: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4 p-lg-5">
                        <h2 class="text-center mb-4">สมัครสมาชิก</h2>
                        
                        <!-- แสดงข้อความ Error หรือ Success -->
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>

                        <!-- ฟอร์มสมัครสมาชิก -->
                        <form action="register.php" method="post">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="username" name="username" placeholder="ชื่อผู้ใช้" required>
                                <label for="username">ชื่อผู้ใช้</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="email" name="email" placeholder="อีเมล" required>
                                <label for="email">อีเมล</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="password" name="password" placeholder="รหัสผ่าน" required>
                                <label for="password">รหัสผ่าน</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="ยืนยันรหัสผ่าน" required>
                                <label for="confirm_password">ยืนยันรหัสผ่าน</label>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">สมัครสมาชิก</button>
                            </div>
                        </form>
                        <hr>
                        <div class="text-center">
                            <p>มีบัญชีอยู่แล้ว? <a href="login.php">เข้าสู่ระบบที่นี่</a></p>
                            <a href="index.php">กลับไปหน้าแรก</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>