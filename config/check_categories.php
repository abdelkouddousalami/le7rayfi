<?php
require_once 'db.php';

echo "Checking categories in database:\n\n";

$stmt = $conn->query("SELECT * FROM categories ORDER BY name");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$row['id']}, Name: {$row['name']}, Slug: {$row['slug']}, Icon: {$row['icon']}\n";
}

echo "\nChecking products with categories:\n\n";
$stmt = $conn->query("SELECT p.name as product_name, c.name as category_name 
                     FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Product: {$row['product_name']}, Category: {$row['category_name']}\n";
}
?>
