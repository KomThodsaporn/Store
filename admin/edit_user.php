<?php
session_start();
require '../config.php';
require 'auth_admin.php';

// ... (PHP logic remains the same)
if (!isset($_GET['id'])) {
    header("Location: users.php");
    exit;
}

$user_id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? AND role = 'member'");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error'] = "ไม่พบสมาชิกคนที่คุณต้องการแก้ไข";
    header("Location: users.php");
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($username === '' || $email === '') {
        $error = "กรุณากรอกข้อมูลให้ครบถ้วน";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "รูปแบบอีเมลไม่ถูกต้อง";
    }

    if (!$error) {
        $chk = $conn->prepare("SELECT 1 FROM users WHERE (username = ? OR email = ?) AND user_id != ?");
        $chk->execute([$username, $email, $user_id]);
        if ($chk->fetch()) {
            $error = "ชื่อผู้ใช้หรืออีเมลนี้มีอยู่แล้วในระบบ";
        }
    }

    $updatePassword = false;
    if (!$error && ($password !== '' || $confirm !== '')) {
        if (strlen($password) < 6) {
            $error = "รหัสผ่านต้องยาวอย่างน้อย 6 อักขระ";
        } elseif ($password !== $confirm) {
            $error = "รหัสผ่านใหม่กับยืนยันรหัสผ่านไม่ตรงกัน";
        } else {
            $updatePassword = true;
        }
    }

    if (!$error) {
        if ($updatePassword) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET username = ?, full_name = ?, email = ?, password = ? WHERE user_id = ?";
            $args = [$username, $full_name, $email, $hashed, $user_id];
        } else {
            $sql = "UPDATE users SET username = ?, full_name = ?, email = ? WHERE user_id = ?";
            $args = [$username, $full_name, $email, $user_id];
        }
        $upd = $conn->prepare($sql);
        $upd->execute($args);
        $_SESSION['success'] = "แก้ไขข้อมูลสมาชิกสำเร็จ";
        header("Location: users.php");
        exit;
    }

    $user['username'] = $username;
    $user['full_name'] = $full_name;
    $user['email'] = $email;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3"><i class="bi bi-pencil-square"></i> แก้ไขข้อมูลสมาชิก</h1>
                    <a href="users.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> กลับหน้ารายชื่อ</a>
                </div>

                <?php if (isset($error)) : ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">ข้อมูลของ: <?= htmlspecialchars($user['username']) ?></h5>
                    </div>
                    <div class="card-body">
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
