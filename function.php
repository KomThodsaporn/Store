<?php
// -----------------------------
// ฟังกช์ นั ดงึรำยกำรสนิ คำ้ในค ำสั่งซอื้
// -----------------------------
function getOrderItems($conn, $order_id)
{
    $stmt = $conn->prepare("SELECT oi.quantity, oi.price, p.product_name
FROM order_items oi
JOIN products p ON oi.product_id = p.product_id
WHERE oi.order_id = ?");
    $stmt->execute([$order_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// -----------------------------
// ฟังกช์ นั ดงึขอ้ มลู จัดสง่
// -----------------------------
function getShippingInfo($conn, $order_id)
{
    $stmt = $conn->prepare("SELECT * FROM shipping WHERE order_id = ?"); // shipping table
    $stmt->execute([$order_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>