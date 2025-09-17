<?php
session_start();
require '../config.php';
require 'auth_admin.php';

// ... (PHP logic remains the same)
if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];
    if ($user_id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND role = 'member'");
        $stmt->execute([$user_id]);
        $_SESSION['success'] = "ลบสมาชิกเรียบร้อยแล้ว";
    }
    header("Location: users.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE role = 'member' ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$today_users = array_filter($users, function ($user) {
    return date('Y-m-d', strtotime($user['created_at'])) == date('Y-m-d');
});

$week_users = array_filter($users, function ($user) {
    return strtotime($user['created_at']) >= strtotime('-7 days');
});

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-11">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3"><i class="bi bi-people-fill"></i> จัดการสมาชิก</h1>
                    <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> กลับหน้าผู้ดูแล</a>
                </div>

                <?php if (isset($_SESSION['success'])) : ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION['success'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <!-- Summary Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white shadow-sm">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">สมาชิกทั้งหมด</h5>
                                    <h3 class="mb-0"><?= count($users) ?> คน</h3>
                                </div>
                                <i class="bi bi-people-fill opacity-50" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white shadow-sm">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">ใหม่วันนี้</h5>
                                    <h3 class="mb-0"><?= count($today_users) ?> คน</h3>
                                </div>
                                <i class="bi bi-person-check-fill opacity-50" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white shadow-sm">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">ใหม่สัปดาห์นี้</h5>
                                    <h3 class="mb-0"><?= count($week_users) ?> คน</h3>
                                </div>
                                <i class="bi bi-calendar-plus-fill opacity-50" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                     <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-list-ul"></i> รายการสมาชิก</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($users)) : ?>
                            <div class="text-center p-5">
                                <i class="bi bi-person-x-fill text-muted" style="font-size: 4rem;"></i>
                                <h4 class="mt-3 text-muted">ยังไม่มีสมาชิกในระบบ</h4>
                            </div>
                        <?php else : ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0 align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th scope="col">ชื่อผู้ใช้</th>
                                            <th scope="col">ชื่อ-นามสกุล</th>
                                            <th scope="col">อีเมล</th>
                                            <th scope="col">วันที่สมัคร</th>
                                            <th scope="col" class="text-center">จัดการ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user) : ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold me-2" style="width: 40px; height: 40px;">
                                                            <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                                        </div>
                                                        <span><?= htmlspecialchars($user['username']) ?></span>
                                                    </div>
                                                </td>
                                                <td><?= htmlspecialchars($user['full_name']) ?: '<em class="text-muted">ไม่มีข้อมูล</em>' ?></td>
                                                <td><?= htmlspecialchars($user['email']) ?></td>
                                                <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                                <td class="text-center">
                                                    <a href="edit_user.php?id=<?= $user['user_id'] ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil-square"></i></a>
                                                    <a href="users.php?delete=<?= $user['user_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('คุณต้องการลบสมาชิกนี้หรือไม่?')"><i class="bi bi-trash-fill"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
