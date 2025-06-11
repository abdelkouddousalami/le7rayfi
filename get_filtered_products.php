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

function getUniqueValues($conn, $field, $category = null) {
    $query = "SELECT DISTINCT $field FROM products WHERE $field IS NOT NULL";
    if ($category) {
        $query .= " AND category = ?";
    }
    $stmt = $conn->prepare($query);
    if ($category) {
        $stmt->execute([$category]);
    } else {
        $stmt->execute();
    }
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

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
    $conditions = ["1=1"];  

    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $category = isset($_GET['category']) ? trim($_GET['category']) : '';
    $ram = isset($_GET['ramFilter']) ? trim($_GET['ramFilter']) : '';
    $storage = isset($_GET['ssdFilter']) ? trim($_GET['ssdFilter']) : '';
    $phoneStorage = isset($_GET['storageFilter']) ? trim($_GET['storageFilter']) : '';
    $processor = isset($_GET['processorFilter']) ? trim($_GET['processorFilter']) : '';
    $camera = isset($_GET['cameraFilter']) ? trim($_GET['cameraFilter']) : '';
    $battery = isset($_GET['batteryFilter']) ? trim($_GET['batteryFilter']) : '';

    $priceMin = isset($_GET['priceMin']) && $_GET['priceMin'] !== '' ? floatval($_GET['priceMin']) : null;
    $priceMax = isset($_GET['priceMax']) && $_GET['priceMax'] !== '' ? floatval($_GET['priceMax']) : null;

    if ($priceMin !== null && $priceMax !== null && $priceMin > $priceMax) {
        echo json_encode([
            'success' => false,
            'message' => 'Le prix minimum ne peut pas être supérieur au prix maximum'
        ]);
        exit;
    }

    if ($priceMin !== null) {
        $conditions[] = "price >= ?";
        $params[] = $priceMin;
    }
    if ($priceMax !== null) {
        $conditions[] = "price <= ?";
        $params[] = $priceMax;
    }

    if ($search) {
        $searchTerms = explode(' ', trim($search));
        $searchConditions = [];
        
        foreach ($searchTerms as $term) {
            if (is_numeric($term)) {
                $searchConditions[] = "
                    (price = ? OR 
                    ram = ? OR 
                    storage = ? OR 
                    processor = ? OR 
                    camera = ? OR 
                    battery = ? OR
                    name LIKE ?)";

                $term = trim($term);
                $params[] = $term;
                $params[] = $term; 
                $params[] = $term; 
                $params[] = $term;
                $params[] = $term; 
                $params[] = $term; 
                $params[] = "%{$term}%"; 
            } else {
                $specSearchTerm = mb_strtolower($term);
                $searchConditions[] = "
                    (LOWER(name) LIKE ? OR 
                    LOWER(description) LIKE ? OR 
                    LOWER(ram) = ? OR 
                    LOWER(storage) = ? OR 
                    LOWER(processor) = ? OR 
                    LOWER(camera) = ? OR 
                    LOWER(battery) = ?)";
                $params[] = "%{$specSearchTerm}%"; 
                $params[] = "%{$specSearchTerm}%"; 
                $params[] = $specSearchTerm; 
                $params[] = $specSearchTerm; 
                $params[] = $specSearchTerm; 
                $params[] = $specSearchTerm; 
                $params[] = $specSearchTerm;
            }
        }
        
        if (!empty($searchConditions)) {
            $conditions[] = "(" . implode(" AND ", $searchConditions) . ")";
        }
    }

    if ($category && $category !== 'all') {
        $conditions[] = "LOWER(category) = LOWER(?)";
        $params[] = $category;
    }

    if ($ram) {
        $conditions[] = "LOWER(ram) = LOWER(?)";
        $params[] = $ram;
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

    if ($camera) {
        $conditions[] = "LOWER(camera) = LOWER(?)";
        $params[] = $camera;
    }

    // Log the final query and parameters for debugging
    error_log('Final query: ' . $query);
    error_log('Parameters: ' . json_encode($params));

    if ($phoneStorage) {
        $conditions[] = "LOWER(storage) = LOWER(?)";
        $params[] = $phoneStorage;
    }

    $query = "SELECT * FROM products";
    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }
    $query .= " ORDER BY price ASC";

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $formattedProducts = array_map(function($product) {
        $specs = [];
        
        if ($product['category'] === 'pc' || $product['category'] === 'laptop') {
            if ($product['ram']) $specs[] = $product['ram'] . ' RAM';
            if ($product['storage']) $specs[] = $product['storage'] . ' Storage';
            if ($product['processor']) $specs[] = $product['processor'];
        } elseif ($product['category'] === 'mobile' || $product['category'] === 'smartphone') {
            if ($product['camera']) $specs[] = $product['camera'] . ' Camera';
            if ($product['battery']) $specs[] = $product['battery'] . ' Battery';
            if ($product['storage']) $specs[] = $product['storage'] . ' Storage';
        }
        
        return [
            'id' => $product['id'],
            'name' => $product['name'],
            'description' => $product['description'],
            'price' => $product['price'],
            'image_url' => $product['image_url'],
            'stock' => $product['stock'],
            'category' => $product['category'],
            'specs' => $specs,
            'ram' => $product['ram'],
            'storage' => $product['storage'],
            'processor' => $product['processor'],
            'camera' => $product['camera'],
            'battery' => $product['battery']
        ];
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