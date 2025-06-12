<?php
session_start();
require_once 'config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Veuillez vous connecter pour modifier le panier'
    ]);
    exit();
}

if (!isset($_POST['cart_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID du panier manquant'
    ]);
    exit();
}

$cart_id = (int)$_POST['cart_id'];

try {
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $result = $stmt->execute([$cart_id, $_SESSION['user_id']]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Article supprimé du panier'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Article non trouvé dans le panier'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Une erreur est survenue: ' . $e->getMessage()
    ]);
}
?>