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

// ตรวจสอบว่ามีการส่งข้อมูลฟอร์มมาหรือไม่
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับข้อมูลจากฟอร์ม
    $username = $_POST['username'];
    $password = $_POST['password'];

    // ตรวจสอบว่ากรอกข้อมูลครบหรือไม่
    if (empty($username) || empty($password)) {
        $error = 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน';
    } else {
        // ค้นหาผู้ใช้จาก username ในฐานข้อมูล
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // ตรวจสอบว่าพบผู้ใช้และรหัสผ่านถูกต้องหรือไม่
        if ($user && password_verify($password, $user['password'])) {
            // หากถูกต้อง ให้เก็บข้อมูลผู้ใช้ลงใน session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // ตรวจสอบ role เพื่อ redirect ไปยังหน้าที่เหมาะสม
            if ($user['role'] == 'admin') {
                header('Location: admin/index.php');
            } else {
                header('Location: index.php');
            }
            exit;
        } else {
            // หากไม่พบผู้ใช้หรือรหัสผ่านไม่ถูกต้อง
            $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
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
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body p-4 p-lg-5">
                        <h2 class="text-center mb-4">เข้าสู่ระบบ</h2>
                        
                        <!-- แสดงข้อความ Error -->
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <!-- ฟอร์มเข้าสู่ระบบ -->
                        <form action="login.php" method="post">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="username" name="username" placeholder="ชื่อผู้ใช้" required>
                                <label for="username">ชื่อผู้ใช้</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="password" name="password" placeholder="รหัสผ่าน" required>
                                <label for="password">รหัสผ่าน</label>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">เข้าสู่ระบบ</button>
                            </div>
                        </form>
                        <hr>
                        <div class="text-center">
                            <p>ยังไม่มีบัญชี? <a href="register.php">สมัครสมาชิกที่นี่</a></p>
                            <a href="index.php">กลับไปหน้าแรก</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>