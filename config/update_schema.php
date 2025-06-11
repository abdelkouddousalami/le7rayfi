<?php
require_once 'db.php';

try {
    $conn = getConnection();
    
    // Check if discount column exists
    $stmt = $conn->query("SHOW COLUMNS FROM products LIKE 'discount'");
    if ($stmt->rowCount() == 0) {
        // Add discount column if it doesn't exist
        $conn->exec("ALTER TABLE products ADD COLUMN discount INT DEFAULT 0");
        echo "Successfully added discount column to products table.";
    } else {
        echo "Discount column already exists.";
    }
} catch(PDOException $e) {
    die("Error updating database schema: " . $e->getMessage());
}
?>
