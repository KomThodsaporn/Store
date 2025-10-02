<?php
session_start();
// เรียกใช้ไฟล์ config และ auth
require 'config.php';

// ---- ส่วนของการจัดการฟอร์ม (ลบสมาชิก) ----

// ตรวจสอบว่าเป็นการ "ลบสมาชิก" หรือไม่
if (isset($_GET['delete'])) {
    $user = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM tb_664230040 WHERE id");
    $stmt->execute();
    $_SESSION['success'] = "ลบสมาชิกเรียบร้อยแล้ว";
    header("Location: users.php");
    exit;
} else {
    $_SESSION['error'] = "ไม่สามารถลบผู้ใช้ปัจจุบันได้";
}



// ---- ส่วนของการดึงข้อมูลมาแสดงผล ----
// ดึงข้อมูลสมาชิกทั้งหมด
$stmt = $conn->prepare("SELECT * FROM tb_664230040 WHERE std_id");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// เก็บชื่อไฟล์ปัจจุบันสำหรับ active menu
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสมาชิก - Admin Panel</title>
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
            <h1 class="h3 mb-0"><i class="bi bi-people-fill"></i> สมาชิก</h1>
        </div>


        <!-- ตารางแสดงรายชื่อสมาชิก -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ลำดับ</th>
                                <th>รหัสนักศึกษา</th>
                                <th>ชื่อ</th>
                                <th>สกุล</th>
                                <th>อีเมล</th>
                                <th>เบอร์โทร</th>
                                <th>เวลาสร้าง</th>
                                <th>ห้อง</th>
                                <th class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">ยังไม่มีสมาชิก</td>
                                </tr>
                            <?php else: ?>
                                <!-- วนลูปแสดงข้อมูลสมาชิก -->
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold me-2"
                                                    style="width: 40px; height: 40px;">
                                                    <?= strtoupper(substr($user['id'], 0, 1)) ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($user['std_id']) ?: '<em class="text-muted">ไม่มีข้อมูล</em>' ?>
                                        </td>
                                        <td><?= htmlspecialchars($user['f_name']) ?: '<em class="text-muted">ไม่มีข้อมูล</em>' ?>
                                        </td>
                                        <td><?= htmlspecialchars($user['L_name']) ?: '<em class="text-muted">ไม่มีข้อมูล</em>' ?>
                                        </td>
                                        <td><?= htmlspecialchars($user['mail']) ?></td>
                                        <td><?= htmlspecialchars($user['tel']) ?: '<em class="text-muted">ไม่มีข้อมูล</em>' ?>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                        <td><?= htmlspecialchars($user['class']) ?: '<em class="text-muted">ไม่มีข้อมูล</em>' ?>
                                        </td>
                                        <td class="text-center">
                                            <!-- ปุ่มแก้ไขและลบ -->
                                            <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-warning"
                                                title="แก้ไข"><i class="bi bi-pencil-square"></i></a>
                                            <a href="users.php?delete=<?= $user['id'] ?>"
                                                class="btn btn-sm btn-outline-danger delete-btn" title="ลบ"><i
                                                    class="bi bi-trash-fill"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // แสดงข้อความแจ้งเตือน (Success/Error)
            <?php if (isset($_SESSION['success'])): ?>
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: '<?= addslashes($_SESSION['success']) ?>', showConfirmButton: false, timer: 3000 });
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: '<?= addslashes($_SESSION['error']) ?>', showConfirmButton: false, timer: 3000 });
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            // เพิ่มการยืนยันก่อนลบข้อมูล
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    const url = this.href;
                    Swal.fire({
                        title: 'ยืนยันการลบ',
                        text: "คุณต้องการลบสมาชิกนี้ใช่หรือไม่?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'ใช่, ลบเลย!',
                        cancelButtonText: 'ยกเลิก'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = url;
                        }
                    });
                });
            });
        });
    </script>
</body>

</html>