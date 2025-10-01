<?php
// --- การตั้งค่าการเชื่อมต่อฐานข้อมูล (Database Connection Configuration) ---

// ชื่อโฮสต์ของฐานข้อมูล (ส่วนใหญ่เป็น 'localhost')
$host = 'localhost';
// ชื่อฐานข้อมูลที่จะเชื่อมต่อ
$dbname = 'online_shop';
// ชื่อผู้ใช้สำหรับเข้าสู่ระบบฐานข้อมูล (default ของ xampp คือ 'root')
$user = 'root';
// รหัสผ่านสำหรับเข้าสู่ระบบฐานข้อมูล (default ของ xampp คือ เว้นว่างไว้)
$pass = '';
// ชุดอักขระ (character set) ที่ใช้ในการสื่อสารกับฐานข้อมูล
$charset = 'utf8mb4';

// Data Source Name (DSN) สำหรับ PDO ซึ่งเป็นรูปแบบสตริงที่บอกข้อมูลการเชื่อมต่อ
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

// ตัวเลือกเพิ่มเติมสำหรับ PDO (PHP Data Objects)
$options = [
    // ตั้งค่าให้ PDO แสดงข้อผิดพลาดในรูปแบบของ Exception เพื่อให้ดักจับได้ง่าย
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // ตั้งค่ารูปแบบการดึงข้อมูลเริ่มต้นเป็น associative array (key เป็นชื่อคอลัมน์)
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // ปิดการจำลอง prepared statements เพื่อความปลอดภัยและประสิทธิภาพที่ดีกว่า
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// บล็อก try-catch สำหรับดักจับข้อผิดพลาดที่อาจเกิดขึ้นระหว่างการเชื่อมต่อฐานข้อมูล
try {
    // สร้าง object PDO (ตัวแปร $conn) สำหรับการเชื่อมต่อฐานข้อมูล
    $conn = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // หากการเชื่อมต่อล้มเหลว ให้โยน (throw) Exception ออกมาเพื่อหยุดการทำงานและแสดงข้อผิดพลาด
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>