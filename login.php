<?php
session_start();
require_once 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username_or_email = trim($_POST['username_or_email']);
    $password = $_POST['password'];

    //เอาค่าที่รับมาจากฟอร์ม ไปตรวจสอบว่ามีข้อมูลตรงกับใน db หรือไม่
    $sql = "SELECT * FROM users WHERE (username = ? OR email = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$username_or_email, $username_or_email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {

        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: admin/index.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center vh-100 align-items-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h3>เข้าสู่ระบบ</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['register']) && $_GET['register'] === 'success'): ?>
                            <div class="alert alert-success">สมัครสมาชิกสำเร็จ กรุณาเข้าสู่ระบบ</div>
                        <?php endif; ?>
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <form method="post">
                            <div class="mb-3">
                                <label for="username_or_email" class="form-label">ชื่อผู้ใช้หรืออีเมล</label>
                                <input type="text" name="username_or_email" id="username_or_email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">รหัสผ่าน</label>
                                <input type="password" name="password" id="password" class="form-control" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">เข้าสู่ระบบ</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <small>ยังไม่มีบัญชี? <a href="register.php">สมัครสมาชิกที่นี่</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>
</body>

</html>