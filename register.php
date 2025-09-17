<?php
require_once 'config.php';

$error = []; // ตัวแปรสำหรับเก็บ error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // ตรวจสอบว่ากรอกข้อมูลมาครบหรือไม่ (emtry)
    if (empty($username) || empty($fullname) || empty($email) || empty($password) || empty($confirm_password)) {
        $error[] = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // ตรวจสอบว่าอีเมลถูกต้องหรือไม่ (filter_var)
        $error[] = "อีเมลไม่ถูกต้อง";

    } elseif ($password !== $confirm_password) {
        // ตรวจสอบว่ารหัสผ่านตรงกันหรือไม่
        $error[] = "รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน";

    } else {
        // ตรวจสอบว่าชื่อผู้ใช้หรืออีเมลถูกใช้ไปแล้วหรือไม่
        $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username, $email]);

        if ($stmt->rowCount() > 0) {
            $error[] = "ชื่อผู้ใช้หรืออีเมลนี้ถูกใช้ไปแล้ว";
        }
    }

    if (empty($error)) { // ถ้าไม่มีข้อผิดพลาดใดๆ

        // นำข้อมูลไปบันทึกในฐานข้อมูล
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users(username,full_name,email,password,role) VALUES (?, ?, ?, ?, 'member')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username, $fullname, $email, $hashedPassword]);

        // ถ้าบันทึกสำเร็จ ให้เปลี่ยนเส้นทางไปหน้า login
        header("Location: login.php?register=succes");
        exit(); // หยุดการทำงานของสคริปต์หลังจากเปลี่ยนเส้นทาง
    }
}
?>

<!DOCTYPE html>
<html lang="en">



<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>register</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">


</head>

<bod y>
    <div class="container d-flex justify-content-center align-items-center">
    <div class="card shadow-lg p-4 w-100" style="max-width: 500px;">
        <h2 class="mb-4">สมัครสมาชิก</h2>

        <?php if (!empty($error)): // ถ ้ำมีข ้อผิดพลำด ให้แสดงข ้อควำม ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($error as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                        <!-- ใช ้ htmlspecialchars เพื่อป้องกัน XSS -->
                        <!-- < ?=คือ short echo tag ?> -->
                        <!-- ถ ้ำเขียนเต็ม จะได ้แบบด ้ำนล่ำง -->
                        <?php // echo "<li>" . htmlspecialchars($e) . "</li>"; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>


        <form action="" method="post">
            <div>
                <label for="username" class="form-label">ชื่อผู้ใช้</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="ชื่อผู้ใช้"
                    value=<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>>
            </div>
            <div>
                <label for="fullname" class="form-label">ชื่อ-นามสกุล</label>
                <input type="text" class="form-control" id="fullname" name="fullname" placeholder="ชื่อ-นามสกุล"
                    value=<?= isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : '' ?>>
            </div>
            <div>
                <label for="email" class="form-label">อีเมล</label>
                <input type="email" class="form-control" id="email" placeholder="อีเมล" name="email"
                    value=<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>>
            </div>
            <div>
                <label for="password" class="form-label">รหัสผ่าน</label>
                <input type="password" class="form-control" id="password" placeholder="รหัสผ่าน" name="password">
            </div>
            <div>
                <label for="confirm_password" class="form-label">ยืนยันรหัสผ่าน</label>
                <input type="password" class="form-control" id="confirm_password" placeholder="ยืนยันรหัสผ่าน"
                    name="confirm_password">
            </div>
            <br>

            <button type="submit" class="btn btn-primary">สมัครสมาชิก</button>
            <a href="login.php" class="btn btn-link">เข้าสู่ระบบ</a>

        </form>
    </div>
    </div>
    </form>

    </div>





    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>
    </body>

</html>