<?php
session_start();
require_once 'config/db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Veuillez vous connecter pour ajouter des produits au panier'
    ]);
    exit();
}

// Check if product_id was sent
if (!isset($_POST['product_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID du produit manquant'
    ]);
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = (int)$_POST['product_id'];

try {
    // Start transaction to prevent race conditions
    $conn->beginTransaction();
    
    // Check if product exists and has stock
    $stmt = $conn->prepare("SELECT id, stock FROM products WHERE id = ? FOR UPDATE");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        $conn->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Produit non trouvé'
        ]);
        exit();
    }

    if ($product['stock'] <= 0) {
        $conn->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Produit en rupture de stock'
        ]);
        exit();
    }

    // Check if product already in cart
    $stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cartItem) {
        // Check if adding one more would exceed stock
        if ($cartItem['quantity'] >= $product['stock']) {
            $conn->rollBack();
            echo json_encode([
                'success' => false,
                'message' => 'Stock insuffisant'
            ]);
            exit();
        }
        
        // Update quantity if already in cart
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
    } else {
        // Add new item to cart
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
        $stmt->execute([$user_id, $product_id]);
    }

    // Get updated cart count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cartCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Commit the transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Produit ajouté au panier avec succès',
        'cartCount' => $cartCount
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