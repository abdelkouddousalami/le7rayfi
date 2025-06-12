<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit(json_encode(['error' => 'Unauthorized']));
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    exit(json_encode(['error' => 'Order ID is required']));
}

$conn = getConnection();

try {
    $stmt = $conn->prepare("
        SELECT o.*, u.username, u.email, u.full_name, u.phone
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE o.id = ?
    ");
    $stmt->execute([$_GET['id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        http_response_code(404);
        exit(json_encode(['error' => 'Order not found']));
    }

    $stmt = $conn->prepare("
        SELECT oi.*, p.name, p.image_url
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$_GET['id']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'order_id' => $order['id'],
        'status' => $order['status'],
        'created_at' => $order['created_at'],
        'total_amount' => $order['total_amount'],
        'customer' => [
            'username' => $order['username'],
            'email' => $order['email'],
            'full_name' => $order['full_name'],
            'phone' => $order['phone']
        ],
        'items' => $items
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
} catch (PDOException $e) {
    http_response_code(500);
    exit(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
}