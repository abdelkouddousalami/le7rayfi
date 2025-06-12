<?php
require_once 'db.php';

$stmt = $conn->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL");
$existingCategories = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "Found these categories in products table: " . implode(", ", $existingCategories) . "\n";

$categoryMapping = [
    'pc' => 'laptops',
    'laptop' => 'laptops',
    'desktop' => 'desktops',
    'smartphone' => 'smartphones',
    'mobile' => 'smartphones',
    'phones' => 'smartphones',
    'tablet' => 'tablets',
    'audio' => 'audio',
    'accessory' => 'keyboards',
    'accessories' => 'keyboards',
    'printer' => 'printers',
    'component' => 'components',
    'gaming' => 'gaming',
    'security' => 'security'
];

foreach ($categoryMapping as $oldCategory => $newSlug) {
    $query = "UPDATE products p, categories c 
              SET p.category_id = c.id 
              WHERE c.slug = ? 
              AND (LOWER(p.category) = ? OR LOWER(p.category) = ?)";
    $stmt = $conn->prepare($query);
    $stmt->execute([$newSlug, strtolower($oldCategory), strtolower($newSlug)]);
}

$stmt = $conn->query("SELECT id, name, category FROM products WHERE category_id IS NULL");
$uncategorized = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($uncategorized) > 0) {
    echo "\nProducts still needing categorization:\n";
    foreach ($uncategorized as $product) {
        echo "ID: {$product['id']}, Name: {$product['name']}, Old Category: {$product['category']}\n";
    }
} else {
    echo "\nAll products have been categorized successfully!\n";
}

$stmt = $conn->query("SELECT c.name, COUNT(p.id) as count 
                     FROM categories c 
                     LEFT JOIN products p ON c.id = p.category_id 
                     GROUP BY c.id, c.name 
                     ORDER BY c.name");
$counts = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "\nProducts per category:\n";
foreach ($counts as $count) {
    echo "{$count['name']}: {$count['count']}\n";
}

echo "\nUpdate completed!\n";
?>