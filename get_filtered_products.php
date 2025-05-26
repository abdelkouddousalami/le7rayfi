<?php
header('Content-Type: application/json');
require_once 'config/db.php';

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
            'categories' => getUniqueValues($conn, 'category'),
            'pc' => [
                'ram' => getUniqueValues($conn, 'ram', 'pc'),
                'storage' => getUniqueValues($conn, 'storage', 'pc'),
                'processor' => getUniqueValues($conn, 'processor', 'pc')
            ],
            'mobile' => [
                'storage' => getUniqueValues($conn, 'storage', 'mobile'),
                'camera' => getUniqueValues($conn, 'camera', 'mobile'),
                'battery' => getUniqueValues($conn, 'battery', 'mobile')
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
}

try {
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

    if ($storage) {
        $conditions[] = "LOWER(storage) = LOWER(?)";
        $params[] = $storage;
    }

    if ($processor) {
        $conditions[] = "LOWER(processor) = LOWER(?)";
        $params[] = $processor;
    }

    if ($camera) {
        $conditions[] = "LOWER(camera) = LOWER(?)";
        $params[] = $camera;
    }

    if ($battery) {
        $conditions[] = "LOWER(battery) = LOWER(?)";
        $params[] = $battery;
    }

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

    $response = [
        'success' => true,
        'products' => $formattedProducts,
        'total' => count($formattedProducts)
    ];
} catch (PDOException $e) {
    $response = [
        'success' => false,
        'message' => 'Error fetching products: ' . $e->getMessage()
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
?>