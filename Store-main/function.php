<?php
// ไฟล์นี้เก็บฟังก์ชันที่อาจถูกเรียกใช้ซ้ำๆ ในหลายหน้า

/**
 * ดึงข้อมูลรายการสินค้าในคำสั่งซื้อ (order items) จากฐานข้อมูล
 * @param PDO $conn - object การเชื่อมต่อฐานข้อมูล
 * @param int $order_id - ID ของคำสั่งซื้อ
 * @return array - อาร์เรย์ของรายการสินค้า
 */
function getOrderItems(PDO $conn, int $order_id): array
{
    $stmt = $conn->prepare("SELECT oi.*, p.product_name FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
    $stmt->execute([$order_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * ดึงข้อมูลการจัดส่ง (shipping info) จากฐานข้อมูล
 * @param PDO $conn - object การเชื่อมต่อฐานข้อมูล
 * @param int $order_id - ID ของคำสั่งซื้อ
 * @return mixed - ข้อมูลการจัดส่ง หรือ false หากไม่พบ
 */
function getShippingInfo(PDO $conn, int $order_id)
{
    $stmt = $conn->prepare("SELECT * FROM shipping WHERE order_id = ?");
    $stmt->execute([$order_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>