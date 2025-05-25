<?php
// filepath: config/setup_categories.php
require_once 'db.php';

// Create categories table if it doesn't exist
$createCategoriesTable = "CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(50) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$conn->exec($createCategoriesTable);

// Add category_id to products table if it doesn't exist
$addCategoryColumn = "ALTER TABLE products ADD COLUMN IF NOT EXISTS category_id INT,
                     ADD CONSTRAINT fk_category FOREIGN KEY (category_id) REFERENCES categories(id)";
try {
    $conn->exec($addCategoryColumn);
} catch (PDOException $e) {
    // Column might already exist
}

// Sample categories data
$categories = [
    ['name' => 'Ordinateurs Portables', 'icon' => 'fas fa-laptop', 'slug' => 'laptops'],
    ['name' => 'Ordinateurs Fixes', 'icon' => 'fas fa-desktop', 'slug' => 'desktops'],
    ['name' => 'Smartphones', 'icon' => 'fas fa-mobile-alt', 'slug' => 'smartphones'],
    ['name' => 'Tablettes', 'icon' => 'fas fa-tablet-alt', 'slug' => 'tablets'],
    ['name' => 'Audio & Casques', 'icon' => 'fas fa-headphones', 'slug' => 'audio'],
    ['name' => 'Claviers & Souris', 'icon' => 'fas fa-keyboard', 'slug' => 'keyboards'],
    ['name' => 'Imprimantes', 'icon' => 'fas fa-print', 'slug' => 'printers'],
    ['name' => 'Composants PC', 'icon' => 'fas fa-microchip', 'slug' => 'components'],
    ['name' => 'Gaming', 'icon' => 'fas fa-gamepad', 'slug' => 'gaming'],
    ['name' => 'Sécurité', 'icon' => 'fas fa-shield-alt', 'slug' => 'security']
];

// Insert categories
$stmt = $conn->prepare("INSERT IGNORE INTO categories (name, icon, slug) VALUES (?, ?, ?)");
foreach ($categories as $category) {
    $stmt->execute([$category['name'], $category['icon'], $category['slug']]);
}

echo "Categories setup completed successfully!\n";
?>