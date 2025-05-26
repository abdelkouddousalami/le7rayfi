<?php
// Add proper headers
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle database connection errors gracefully
try {
    require_once 'config/db.php';
    $conn = getConnection(); // Get the database connection
    if (!$conn) {
        throw new Exception('Failed to establish database connection');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error: ' . $e->getMessage()
    ]);
    exit;
}

// Function to get unique values for a field
function getUniqueValues($conn, $field, $categorySlug = null) {
    $query = "SELECT DISTINCT p.$field FROM products p";
    if ($categorySlug) {
        $query .= " LEFT JOIN categories c ON p.category_id = c.id WHERE p.$field IS NOT NULL AND c.slug = ?";
    } else {
        $query .= " WHERE p.$field IS NOT NULL";
    }
    $stmt = $conn->prepare($query);
    if ($categorySlug) {
        $stmt->execute([$categorySlug]);
    } else {
        $stmt->execute();
    }
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Get filter options if requested
if (isset($_GET['get_options'])) {
    try {
        $filterOptions = [
            'categories' => $conn->query("SELECT slug, name FROM categories")->fetchAll(PDO::FETCH_ASSOC),
            'laptops' => [
                'ram' => getUniqueValues($conn, 'ram', 'laptops'),
                'storage' => getUniqueValues($conn, 'storage', 'laptops'),
                'processor' => getUniqueValues($conn, 'processor', 'laptops')
            ],
            'smartphones' => [
                'storage' => getUniqueValues($conn, 'storage', 'smartphones'),
                'camera' => getUniqueValues($conn, 'camera', 'smartphones'),
                'battery' => getUniqueValues($conn, 'battery', 'smartphones')
            ],
            'price' => [
                'min' => $conn->query("SELECT MIN(price) FROM products")->fetchColumn(),
                'max' => $conn->query("SELECT MAX(price) FROM products")->fetchColumn()
            ]
        ];

        echo json_encode([
            'success' => true,
            'options' => $filterOptions
        ]);
        exit;
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching filter options: ' . $e->getMessage()
        ]);
        exit;
    }
}    // Build the query for filtered products
try {
    // Log the incoming request parameters for debugging
    error_log('Search parameters: ' . json_encode($_GET));
    
    $query = "SELECT p.*, c.name as category_name, c.slug as category_slug,
              p.created_at, p.image_url, p.description, p.stock,
              p.ram, p.storage, p.processor, p.camera, p.battery
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE 1=1";
    $params = [];

    // Category filter
    if (isset($_GET['category']) && $_GET['category'] !== '' && $_GET['category'] !== 'all') {
        $query .= " AND c.slug = ?";
        $params[] = $_GET['category'];
    }

    // Price filter
    if (isset($_GET['priceMin']) && is_numeric($_GET['priceMin'])) {
        $query .= " AND p.price >= ?";
        $params[] = $_GET['priceMin'];
    }
    if (isset($_GET['priceMax']) && is_numeric($_GET['priceMax'])) {
        $query .= " AND p.price <= ?";
        $params[] = $_GET['priceMax'];
    }

    // Specification filters for laptops/desktops
    if (isset($_GET['ram'])) {
        $query .= " AND p.ram = ?";
        $params[] = $_GET['ram'];
    }
    if (isset($_GET['storage'])) {
        $query .= " AND p.storage = ?";
        $params[] = $_GET['storage'];
    }
    if (isset($_GET['processor'])) {
        $query .= " AND p.processor = ?";
        $params[] = $_GET['processor'];
    }

    // Specification filters for smartphones/tablets
    if (isset($_GET['camera'])) {
        $query .= " AND p.camera = ?";
        $params[] = $_GET['camera'];
    }
    if (isset($_GET['battery'])) {
        $query .= " AND p.battery = ?";
        $params[] = $_GET['battery'];
    }    // Search filter
    if (isset($_GET['search']) && trim($_GET['search']) !== '') {
        $searchTerm = '%' . trim($_GET['search']) . '%';
        $query .= " AND (p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    // Order by and limit
    $query .= " ORDER BY p.created_at DESC";
    
    // Add limit for quick search after ORDER BY
    if (isset($_GET['quickSearch']) && $_GET['quickSearch'] === '1') {
        $query .= " LIMIT 5";
    }

    // Log the final query and parameters for debugging
    error_log('Final query: ' . $query);
    error_log('Parameters: ' . json_encode($params));

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process products to ensure all required fields are present
    $processedProducts = array_map(function($product) {
        return array_merge([
            'id' => null,
            'name' => '',
            'price' => 0,
            'category_name' => '',
            'category_slug' => '',
            'image_url' => '',
            'description' => '',
            'stock' => 0,
            'created_at' => null,
            'specifications' => []
        ], $product);
    }, $products);

    echo json_encode([
        'success' => true,
        'products' => $processedProducts,
        'debug' => [
            'total_products' => count($products),
            'filters_applied' => array_keys($_GET)
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching products: ' . $e->getMessage()
    ]);
}
?>