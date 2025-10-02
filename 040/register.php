<?php
session_start();
// เรียกใช้ไฟล์ config เพื่อเชื่อมต่อฐานข้อมูล
require_once 'config.php';


$error = '';
$success = '';

// ตรวจสอบว่ามีการส่งข้อมูลฟอร์มมาหรือไม่
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับข้อมูลจากฟอร์มและตัดช่องว่าง
    $std_id = trim($_POST['std_id']);
    $f_name = trim($_POST['f_name']);
    $L_name = trim($_POST['L_name']);
    $mail = trim($_POST['mail']);
    $tel = trim($_POST['tel']);
    $class = trim($_POST['class']);

    // ---- การตรวจสอบความถูกต้องของข้อมูล (Validation) ----
    if (empty($std_id) || empty($mail)) {
        $error = 'กรุณากรอกข้อมูลให้ครบทุกช่อง';
    } elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        $error = 'รูปแบบอีเมลไม่ถูกต้อง';
    } else {
        // ตรวจสอบว่า std_id หรือ mail ซ้ำกับที่มีในระบบหรือไม่
        $stmt = $conn->prepare("SELECT * FROM tb_664230040 WHERE std_id = ? OR mail = ?");
        $stmt->execute([$std_id, $mail]);
        if ($stmt->fetch()) {
            $error = 'ชื่อผู้ใช้หรืออีเมลนี้มีผู้ใช้งานแล้ว';
        } else {
            // หากข้อมูลถูกต้องและไม่ซ้ำ ให้ทำการเพิ่มผู้ใช้ใหม่ลงฐานข้อมูล
            $stmt = $conn->prepare("INSERT INTO tb_664230040 (std_id,f_name,L_name,mail,tel,class) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$std_id, $f_name, $L_name, $mail, $tel, $class])) {
                $success = 'เพิ่มสมาชิกสำเร็จ!';
            } else {
                $error = 'เกิดข้อผิดพลาดในการเพิ่มสมาชิก';
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
    <title>เพิ่มสมาชิก</title>
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
                        <h2 class="text-center mb-4">เพิ่มสมาชิก</h2>

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
                                <input type="text" class="form-control" id="std_id" name="std_id"
                                    placeholder="ชื่อผู้ใช้" required>
                                <label for="std_id">รหัสนักศึกษา</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="f_name" name="f_name" placeholder="ชื่อ"
                                    required>
                                <label for="f_name">ชื่อ</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="L_name" name="L_name" placeholder="สกุล"
                                    required>
                                <label for="L_name">สกุล</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="mail" class="form-control" id="mail" name="mail" placeholder="เมล"
                                    required>
                                <label for="mail">เมล</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="tel" name="tel" placeholder="เบอร์โทร"
                                    required>
                                <label for="tel">เบอร์โทร</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="class" name="class" placeholder="ห้อง"
                                    required>
                                <label for="tel">ห้อง</label>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">เพิ่มสมาชิก</button>
                            </div>
                        </form>
                        <hr>
                        <div class="text-center">
                            <a href="users.php">กลับไปหน้าแรก</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>