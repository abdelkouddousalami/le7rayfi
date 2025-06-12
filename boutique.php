<?php
session_start();
require_once 'config/db.php';

$cartCount = 0;
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $conn = getConnection();
    $cartQuery = "SELECT COUNT(*) as count FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($cartQuery);
    $stmt->execute([$userId]);
    $cartCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
}

// Fetch all products
$conn = getConnection();
$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          ORDER BY p.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all categories for filter
$categoryQuery = "SELECT * FROM categories ORDER BY name";
$stmt = $conn->prepare($categoryQuery);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boutique - Le7rayfi</title>
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/boutique.css">
    <link rel="stylesheet" href="assets/css/search-hover.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
             .header {
            position: sticky !important;
            width: 100%;
            top: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="boutique-container">
        </div>
            <div class="products-grid"> <?php
                                    $conn = getConnection();
                                    $stmt = $conn->query("SELECT p.*, c.name as category_name, c.slug as category_slug FROM products p 
                                LEFT JOIN categories c ON p.category_id = c.id 
                                ORDER BY p.created_at DESC");
                                    while ($product = $stmt->fetch(PDO::FETCH_ASSOC)):
                                    ?>
                <div class="product-card" data-category="<?php echo htmlspecialchars($product['category_slug']); ?>">
                    <div class="product-image-container">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                            class="product-image">
                        <?php if ($product['stock'] < 5 && $product['stock'] > 0): ?>
                            <span class="badge stock-badge">Plus que <?php echo $product['stock']; ?> en stock!</span>
                        <?php endif; ?>
                        <?php if (strtotime($product['created_at']) > strtotime('-7 days')): ?>
                            <span class="badge new-badge">Nouveau</span>
                        <?php endif; ?>
                        <?php if (isset($product['discount']) && $product['discount'] > 0): ?>
                            <span class="badge discount-badge">-<?php echo $product['discount']; ?>%</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <span class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></span>
                        <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>

                        <?php if ($product['category_slug'] === 'laptops' || $product['category_slug'] === 'desktops'): ?>
                            <div class="product-specs">
                                <?php if ($product['ram']): ?>
                                    <span><i class="fas fa-memory" title="RAM"></i> <?php echo htmlspecialchars($product['ram']); ?></span>
                                <?php endif; ?>
                                <?php if ($product['storage']): ?>
                                    <span><i class="fas fa-hdd" title="Storage"></i> <?php echo htmlspecialchars($product['storage']); ?></span>
                                <?php endif; ?>
                                <?php if ($product['processor']): ?>
                                    <span><i class="fas fa-microchip" title="Processor"></i> <?php echo htmlspecialchars($product['processor']); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php elseif ($product['category_slug'] === 'smartphones' || $product['category_slug'] === 'tablets'): ?>
                            <div class="product-specs">
                                <?php if ($product['storage']): ?>
                                    <span><i class="fas fa-hdd" title="Storage"></i> <?php echo htmlspecialchars($product['storage']); ?></span>
                                <?php endif; ?>
                                <?php if ($product['camera']): ?>
                                    <span><i class="fas fa-camera" title="Camera"></i> <?php echo htmlspecialchars($product['camera']); ?></span>
                                <?php endif; ?>
                                <?php if ($product['battery']): ?>
                                    <span><i class="fas fa-battery-full" title="Battery"></i> <?php echo htmlspecialchars($product['battery']); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="product-description">
                            <p><?php echo htmlspecialchars($product['description']); ?></p>
                        </div>

                        <div class="product-stats">
                            <div class="stat">
                                <i class="fas fa-star star-rating"></i>
                                <i class="fas fa-star star-rating"></i>
                                <i class="fas fa-star star-rating"></i>
                                <i class="fas fa-star star-rating"></i>
                                <i class="fas fa-star-half-alt star-rating"></i>
                                <span>(4.5)</span>
                            </div>
                            <div class="stat">
                                <i class="fas fa-shopping-cart"></i>
                                <?php echo rand(50, 200); ?> vendus
                            </div>
                        </div>

                        <div class="product-price-container">
                            <?php if (isset($product['discount']) && $product['discount'] > 0): ?>
                                <div class="original-price"><?php echo number_format($product['price'], 2); ?> DH</div>
                                <div class="discounted-price">
                                    <?php echo number_format($product['price'] * (1 - $product['discount'] / 100), 2); ?> DH
                                </div>
                            <?php else: ?>
                                <div class="product-price"><?php echo number_format($product['price'], 2); ?> DH</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="product-actions">
                        <div class="action-buttons">

                            <button class="product-button add-to-cart" onclick="addToCart(<?php echo $product['id']; ?>)">
                                <i class="fas fa-shopping-cart"></i> Ajouter au panier
                            </button>
                            <button class="product-button add-to-wishlist" onclick="addToWishlist(<?php echo $product['id']; ?>)">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>
                        <a href="product_details.php?id=<?php echo $product['id']; ?>" class="product-button view-details">
                            <i class="fas fa-eye"></i> Voir détails
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/boutique.js"></script>
</body>
</html>