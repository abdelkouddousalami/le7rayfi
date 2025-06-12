<?php
session_start();
header('Content-Type: application/json');
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Veuillez vous connecter pour modifier le panier'
    ]);
    exit();
}

if (!isset($_POST['cart_id']) || !isset($_POST['action'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Paramètres manquants'
    ]);
    exit();
}

$cart_id = (int)$_POST['cart_id'];
$action = $_POST['action'];

try {
    $conn->beginTransaction();
    
    $stmt = $conn->prepare("
        SELECT c.quantity, p.stock, c.product_id
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.id = ? AND c.user_id = ?
        FOR UPDATE
    ");
    $stmt->execute([$cart_id, $_SESSION['user_id']]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$item) {
        $conn->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Article non trouvé dans le panier'
        ]);
        exit();
    }
    
    $newQuantity = $item['quantity'];
    if ($action === 'increase') {
        if ($item['quantity'] >= $item['stock']) {
            $conn->rollBack();
            echo json_encode([
                'success' => false,
                'message' => 'Stock insuffisant'
            ]);
            exit();
        }
        $newQuantity++;
    } elseif ($action === 'decrease') {
        if ($item['quantity'] <= 1) {
            $conn->rollBack();
            echo json_encode([
                'success' => false,
                'message' => 'Quantité minimale atteinte'
            ]);
            exit();
        }
        $newQuantity--;
    }
    
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$newQuantity, $cart_id, $_SESSION['user_id']]);
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Panier mis à jour avec succès'
    ]);
    
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Une erreur est survenue: ' . $e->getMessage()
    ]);
}
?>