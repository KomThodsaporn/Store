<?php
session_start();

// ลบข้อมูลทั้งหมดใน session
session_unset();

// ทำลาย session ที่มีอยู่
session_destroy();

// ส่งผู้ใช้กลับไปที่หน้าแรก
header("Location: index.php");
exit; // จบการทำงานของสคริปต์ทันที
?>