<?php
session_start();
require 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$errors = [];
$success = "";
// ดงึขอ้ มลู สมำชกิ
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
// เมอื่ มกี ำรสง่ ฟอรม์
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    // ตรวจสอบชอื่ และอเีมลไมว่ ำ่ ง
    if (empty($full_name) || empty($email)) {
        $errors[] = "กรณุ ำกรอกชอื่ -นำมสกุลและอีเมล";
    }
    // ตรวจสอบอเีมลซ ้ำ
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND user_id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "อเีมลนถี้ กู ใชง้ำนแลว้";
    }
    // ตรวจสอบกำรเปลี่ยนรหัสผ่ำน (ถ ้ำมี)
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = "รหัสผ่ำนเดิมไม่ถูกต ้อง";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "รหัสผ่ำนใหม่ต ้องมีอย่ำงน้อย 6 ตัวอักษร";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "รหัสผ่ำนใหม่และกำรยืนยันไม่ตรงกัน";
        } else {
            $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
        }
    }
    // อัปเดตข ้อมูลหำกไม่มี error
    if (empty($errors)) {
        if (!empty($new_hashed)) {
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, password = ? WHERE user_id = ?");
            $stmt->execute([$full_name, $email, $new_hashed, $user_id]);
        } else {
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE user_id = ?");
            $stmt->execute([$full_name, $email, $user_id]);
        }
        $success = "บันทึกข ้อมูลเรียบร ้อยแล้ว";
        // อัปเดต session หำกจ ำเป็น
        $_SESSION['username'] = $user['username'];
        $user['full_name'] = $full_name;
        $user['email'] = $email;
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>โปรไฟลส์ มำชกิ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <!-- Navigation Bar -->
    <?php //require_once 'navbar.php'; ?>
    <div class="container mt-4">
        <h2>โปรไฟล์ของคุณ</h2>
        <a href="index.php" class="btn btn-secondary mb-3">← กลับหน้ำหลัก</a>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php elseif (!empty($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <form method="post" class="row g-3">
            <div class="col-md-6">
                <label for="full_name" class="form-label">ชอื่ -นำมสกุล</label>
                <input type="text" name="full_name" class="form-control" required value="<?=
                    htmlspecialchars($user['full_name']) ?>">
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">อีเมล</label>
                <input type="email" name="email" class="form-control" required value="<?=
                    htmlspecialchars($user['email']) ?>">
            </div>
            <div class="col-12">
                <hr>
                <h5>เปลี่ยนรหัสผ่ำน (ไม่จ ำเป็น)</h5>
            </div>
            <div class="col-md-6">
                <label for="current_password" class="form-label">รหัสผ่ำนเดิม</label>
                <input type="password" name="current_password" id="current_password" class="form-control">
            </div>
            <div class="col-md-6">
                <label for="new_password" class="form-label">รหัสผ่ำนใหม่ (≥ 6 ตัวอักษร)</label>
                <input type="password" name="new_password" id="new_password" class="form-control">
            </div>
            <div class="col-md-6">
                <label for="confirm_password" class="form-label">ยืนยันรหัสผ่ำนใหม่</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">บันทึกกำรเปลี่ยนแปลง</button>
            </div>
        </form>
    </div>
</body>

</html>