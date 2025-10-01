<?php
session_start();
// เรียกใช้ไฟล์ config.php เพื่อเชื่อมต่อฐานข้อมูล
require_once '../config.php';
// เรียกใช้ไฟล์ auth_admin.php เพื่อตรวจสอบว่าผู้ใช้เป็น admin หรือไม่
require_once 'auth_admin.php';

// ---- ส่วนของการดึงข้อมูลสรุปสำหรับแสดงบนแดชบอร์ด ----

// นับจำนวนผู้ใช้ทั้งหมดที่มีบทบาทเป็น 'member'
$user_count = $conn->query("SELECT count(*) FROM users WHERE role = 'member'")->fetchColumn();
// นับจำนวนหมวดหมู่สินค้าทั้งหมด
$category_count = $conn->query("SELECT count(*) FROM categories")->fetchColumn();
// นับจำนวนสินค้าทั้งหมด
$product_count = $conn->query("SELECT count(*) FROM products")->fetchColumn();
// นับจำนวนคำสั่งซื้อทั้งหมด
$order_count = $conn->query("SELECT count(*) FROM orders")->fetchColumn();

// เก็บชื่อไฟล์ปัจจุบันเพื่อใช้ในการทำให้เมนูที่กำลังเปิดอยู่ active
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-shield-lock-fill"></i> Shop</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <span class="navbar-text me-3">
                            <i class="bi bi-person-circle"></i> ยินดีต้อนรับ, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>
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
                        <a href="../index.php" target="_blank" class="btn btn-outline-info btn-sm"><i class="bi bi-shop"></i> ไปที่หน้าร้าน</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a href="../logout.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right"></i> ออกจากระบบ</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

<main class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Dashboard</h1>
        <span class="text-muted">ภาพรวมของระบบ</span>
    </div>

    <!-- ส่วนของการแสดงผลบัตรข้อมูลสรุป (Stat Cards) -->
    <div class="row g-4">
        <!-- บัตรแสดงจำนวนสมาชิก -->
        <div class="col-md-6 col-xl-3">
            <div class="card text-white h-100" style="background-color: #7E57C2;">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">สมาชิกทั้งหมด</h5>
                        <h2 class="mb-0 fw-bold"><?= $user_count ?></h2>
                    </div>
                    <i class="bi bi-people-fill opacity-50" style="font-size: 3.5rem;"></i>
                </div>
                <a href="users.php" class="card-footer text-white d-flex justify-content-between">
                    <span>ดูรายละเอียด</span>
                    <i class="bi bi-arrow-right-circle-fill"></i>
                </a>
            </div>
        </div>
        <!-- บัตรแสดงจำนวนหมวดหมู่ -->
        <div class="col-md-6 col-xl-3">
            <div class="card text-white h-100" style="background-color: #5C6BC0;">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">หมวดหมู่สินค้า</h5>
                        <h2 class="mb-0 fw-bold"><?= $category_count ?></h2>
                    </div>
                    <i class="bi bi-tags-fill opacity-50" style="font-size: 3.5rem;"></i>
                </div>
                <a href="category.php" class="card-footer text-white d-flex justify-content-between">
                    <span>ดูรายละเอียด</span>
                    <i class="bi bi-arrow-right-circle-fill"></i>
                </a>
            </div>
        </div>
        <!-- บัตรแสดงจำนวนสินค้า -->
        <div class="col-md-6 col-xl-3">
            <div class="card text-white h-100" style="background-color: #26A69A;">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">สินค้าทั้งหมด</h5>
                        <h2 class="mb-0 fw-bold"><?= $product_count ?></h2>
                    </div>
                    <i class="bi bi-box-seam-fill opacity-50" style="font-size: 3.5rem;"></i>
                </div>
                <a href="products.php" class="card-footer text-white d-flex justify-content-between">
                    <span>ดูรายละเอียด</span>
                    <i class="bi bi-arrow-right-circle-fill"></i>
                </a>
            </div>
        </div>
        <!-- บัตรแสดงจำนวนคำสั่งซื้อ -->
        <div class="col-md-6 col-xl-3">
            <div class="card text-white h-100" style="background-color: #42A5F5;">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">คำสั่งซื้อ</h5>
                        <h2 class="mb-0 fw-bold"><?= $order_count ?></h2>
                    </div>
                    <i class="bi bi-receipt-cutoff opacity-50" style="font-size: 3.5rem;"></i>
                </div>
                <a href="orders.php" class="card-footer text-white d-flex justify-content-between">
                    <span>ดูรายละเอียด</span>
                    <i class="bi bi-arrow-right-circle-fill"></i>
                </a>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>