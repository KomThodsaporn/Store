<?php
session_start();
require_once '../config.php';
require_once 'auth_admin.php';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แผงควบคุมผู้ดูแลระบบ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-speedometer2"></i> Admin Panel
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">
                            <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">แผงควบคุมผู้ดูแลระบบ</h1>
            <p class="text-muted">ยินดีต้อนรับ, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="card text-white bg-danger h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-people-fill fs-1"></i>
                        <h5 class="card-title mt-3">จัดการสมาชิก</h5>
                        <a href="users.php" class="btn btn-light stretched-link">ไปที่หน้าจัดการ</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card text-white bg-warning h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-tags-fill fs-1"></i>
                        <h5 class="card-title mt-3">จัดการหมวดหมู่</h5>
                        <a href="category.php" class="btn btn-light stretched-link">ไปที่หน้าจัดการ</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card text-white bg-success h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-box-seam-fill fs-1"></i>
                        <h5 class="card-title mt-3">จัดการสินค้า</h5>
                        <a href="products.php" class="btn btn-light stretched-link">ไปที่หน้าจัดการ</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card text-white bg-primary h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-receipt-cutoff fs-1"></i>
                        <h5 class="card-title mt-3">จัดการคำสั่งซื้อ</h5>
                        <a href="orders.php" class="btn btn-light stretched-link">ไปที่หน้าจัดการ</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
