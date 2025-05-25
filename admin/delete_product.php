<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "Access denied. Admin privileges required.";
    header('Location: ../auth.php');
    exit();
}
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
    $stmt = $conn->prepare("SELECT image_url FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    try {
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        if ($product && $product['image_url'] && file_exists('../' . $product['image_url'])) {
            unlink('../' . $product['image_url']);
        }
        $_SESSION['success_message'] = "Produit supprimé avec succès!";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur lors de la suppression du produit: " . $e->getMessage();
    }
}
header('Location: dashboard.php');
exit();