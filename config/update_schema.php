<?php
require_once 'db.php';

try {
    $conn = getConnection();
    
    $stmt = $conn->query("SHOW COLUMNS FROM products LIKE 'discount'");
    if ($stmt->rowCount() == 0) {
        $conn->exec("ALTER TABLE products ADD COLUMN discount INT DEFAULT 0");
        echo "Successfully added discount column to products table.";
    } else {
        echo "Discount column already exists.";
    }
} catch(PDOException $e) {
    die("Error updating database schema: " . $e->getMessage());
}
?>