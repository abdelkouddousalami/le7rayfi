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

function getUniqueValues($conn, $field, $categorySlug = null) {
    $query = "SELECT DISTINCT p.$field as value, COUNT(*) as count 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.$field IS NOT NULL AND p.$field != '' AND p.$field != 'NULL'
              AND TRIM(p.$field) != ''";
    
    $params = [];
    if ($categorySlug && $categorySlug !== 'all') {
        $query .= " AND c.slug = ?";
        $params[] = $categorySlug;
    }
    
    $query .= " GROUP BY p.$field ORDER BY count DESC, p.$field ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return array_map(function($row) {
        return $row['value'] . ' (' . $row['count'] . ')';
    }, $results);
}

if (isset($_GET['get_options'])) {
    try {
        $dynamicFilters = [
            'laptops' => ['brand', 'model', 'ram', 'storage', 'processor', 'graphics_card', 'screen_size', 'os', 'color'],
            'desktops' => ['brand', 'model', 'ram', 'storage', 'processor', 'graphics_card', 'os'],
            'smartphones' => ['brand', 'model', 'storage', 'camera', 'battery', 'screen_size', 'os', 'color', 'network'],
            'tablets' => ['brand', 'model', 'storage', 'camera', 'battery', 'screen_size', 'os', 'color', 'network'],
            'audio' => ['brand', 'model', 'color', 'battery'],
            'keyboards' => ['brand', 'model', 'color', 'battery'],
            'printers' => ['brand', 'model', 'color'],
            'components' => ['brand', 'model'],
            'gaming' => ['brand', 'model', 'color', 'battery'],
            'security' => ['brand', 'model']
        ];
        
        $filterOptions = [
            'categories' => $conn->query("SELECT slug, name FROM categories")->fetchAll(PDO::FETCH_ASSOC),
            'price' => [
                'min' => $conn->query("SELECT MIN(price) FROM products")->fetchColumn(),
                'max' => $conn->query("SELECT MAX(price) FROM products")->fetchColumn()
            ],
            'dynamic_structure' => $dynamicFilters  
        ];
        
        foreach ($dynamicFilters as $categorySlug => $fields) {
            $filterOptions[$categorySlug] = [];
            foreach ($fields as $field) {
                $filterOptions[$categorySlug][$field] = getUniqueValues($conn, $field, $categorySlug);
            }
        }
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
}
try {
    error_log('Search parameters: ' . json_encode($_GET));
    error_log('Cleaned parameters will be logged below...');
    
    $query = "SELECT p.*, c.name as category_name, c.slug as category_slug,
              p.created_at, p.image_url, p.description, p.stock,
              p.ram, p.storage, p.processor, p.camera, p.battery,
              p.brand, p.model, p.graphics_card, p.screen_size, p.os, p.color, p.network
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE 1=1";
    $params = [];

    if (isset($_GET['category']) && $_GET['category'] !== '' && $_GET['category'] !== 'all') {
        $query .= " AND c.slug = ?";
        $params[] = $_GET['category'];
    }

    if (isset($_GET['priceMin']) && is_numeric($_GET['priceMin'])) {
        $query .= " AND p.price >= ?";
        $params[] = $_GET['priceMin'];
    }
    if (isset($_GET['priceMax']) && is_numeric($_GET['priceMax'])) {
        $query .= " AND p.price <= ?";
        $params[] = $_GET['priceMax'];
    }

    if (isset($_GET['ram']) && $_GET['ram'] !== '') {
        $ramValue = preg_replace('/\s*\(\d+\)\s*$/', '', $_GET['ram']);
        $query .= " AND p.ram = ?";
        $params[] = $ramValue;
    }
    if (isset($_GET['storage']) && $_GET['storage'] !== '') {
        $storageValue = preg_replace('/\s*\(\d+\)\s*$/', '', $_GET['storage']);
        $query .= " AND p.storage = ?";
        $params[] = $storageValue;
    }
    if (isset($_GET['processor']) && $_GET['processor'] !== '') {
        $processorValue = preg_replace('/\s*\(\d+\)\s*$/', '', $_GET['processor']);
        $query .= " AND p.processor = ?";
        $params[] = $processorValue;
    }

    if (isset($_GET['camera']) && $_GET['camera'] !== '') {
        $cameraValue = preg_replace('/\s*\(\d+\)\s*$/', '', $_GET['camera']);
        $query .= " AND p.camera = ?";
        $params[] = $cameraValue;
    }
    if (isset($_GET['battery']) && $_GET['battery'] !== '') {
        $batteryValue = preg_replace('/\s*\(\d+\)\s*$/', '', $_GET['battery']);
        $query .= " AND p.battery = ?";
        $params[] = $batteryValue;
    }

    if (isset($_GET['brand']) && $_GET['brand'] !== '') {
        $brandValue = preg_replace('/\s*\(\d+\)\s*$/', '', $_GET['brand']);
        $query .= " AND p.brand = ?";
        $params[] = $brandValue;
    }
    
    if (isset($_GET['model']) && $_GET['model'] !== '') {
        $modelValue = preg_replace('/\s*\(\d+\)\s*$/', '', $_GET['model']);
        $query .= " AND p.model = ?";
        $params[] = $modelValue;
    }
    
    if (isset($_GET['graphics_card']) && $_GET['graphics_card'] !== '') {
        $graphicsValue = preg_replace('/\s*\(\d+\)\s*$/', '', $_GET['graphics_card']);
        $query .= " AND p.graphics_card = ?";
        $params[] = $graphicsValue;
    }
    
    if (isset($_GET['screen_size']) && $_GET['screen_size'] !== '') {
        $screenValue = preg_replace('/\s*\(\d+\)\s*$/', '', $_GET['screen_size']);
        $query .= " AND p.screen_size = ?";
        $params[] = $screenValue;
    }
    
    if (isset($_GET['os']) && $_GET['os'] !== '') {
        $osValue = preg_replace('/\s*\(\d+\)\s*$/', '', $_GET['os']);
        $query .= " AND p.os = ?";
        $params[] = $osValue;
    }
    
    if (isset($_GET['color']) && $_GET['color'] !== '') {
        $colorValue = preg_replace('/\s*\(\d+\)\s*$/', '', $_GET['color']);
        $query .= " AND p.color = ?";
        $params[] = $colorValue;
    }
    
    if (isset($_GET['network']) && $_GET['network'] !== '') {
        $networkValue = preg_replace('/\s*\(\d+\)\s*$/', '', $_GET['network']);
        $query .= " AND p.network = ?";
        $params[] = $networkValue;
    }

    if (isset($_GET['search']) && trim($_GET['search']) !== '') {
        $searchTerm = '%' . trim($_GET['search']) . '%';
        $query .= " AND (p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    $query .= " ORDER BY p.created_at DESC";
    
    if (isset($_GET['quickSearch']) && $_GET['quickSearch'] === '1') {
        $query .= " LIMIT 5";
    }

    error_log('Final query: ' . $query);
    error_log('Parameters: ' . json_encode($params));

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

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