<?php
session_start();
require 'config.php';
// ตรวจสอบสิทธิ์admin
// ตรวจสอบการส่งข้อมูลจากฟอร์ม
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $user = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM tb_664230040 WHERE id = ?");
    $stmt->execute([$user]);

    // ส่งผลลัพธ์กลับไปยังหน้า users.php
    header("Location: users.php");
    exit;
}
?>