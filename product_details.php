<?php
session_start();
require_once 'config/db.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: index.php');
    exit();
}

$stmt = $conn->prepare("
    SELECT * FROM products 
    WHERE category_id = ? AND id != ? 
    LIMIT 4
");
$stmt->execute([$product['category_id'], $product_id]);
$related_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - HA GROUP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="./assets/css/index.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="./assets/css/product-details.css?<?php echo time(); ?>">
</head>

<body>

    <main class="product-details-container">
        <nav class="breadcrumb">
            <a href="index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="category.php?slug=<?php echo htmlspecialchars($product['category_slug']); ?>">
                <?php echo htmlspecialchars($product['category_name']); ?>
            </a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo htmlspecialchars($product['name']); ?></span>
        </nav>

        <div class="product-details">
            <div class="product-gallery">
                <div class="main-image">
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>"
                        alt="<?php echo htmlspecialchars($product['name']); ?>"
                        id="mainImage">
                    <?php if ($product['stock'] < 5 && $product['stock'] > 0): ?>
                        <span class="stock-warning">Plus que <?php echo $product['stock']; ?> en stock!</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="product-info">
                <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>

                <div class="product-meta">
                    <span class="category">
                        <i class="fas fa-tag"></i>
                        <?php echo htmlspecialchars($product['category_name']); ?>
                    </span>
                    <span class="stock-status <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                        <i class="fas <?php echo $product['stock'] > 0 ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                        <?php echo $product['stock'] > 0 ? 'En stock' : 'Rupture de stock'; ?>
                    </span>
                </div>

                <div class="product-price">
                    <span class="price"><?php echo number_format($product['price'], 2); ?> DH</span>
                    <?php if (isset($product['old_price']) && $product['old_price'] > $product['price']): ?>
                        <span class="old-price"><?php echo number_format($product['old_price'], 2); ?> DH</span>
                        <span class="discount">
                            -<?php echo round((($product['old_price'] - $product['price']) / $product['old_price']) * 100); ?>%
                        </span>
                    <?php endif; ?>
                </div>

                <div class="product-description">
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </div> <?php if ($product['category_slug'] === 'laptops' || $product['category_slug'] === 'desktops'): ?>
                    <div class="product-specs">
                        <div class="specs-grid">
                            <?php if ($product['ram']): ?>
                                <div class="spec-item">
                                    <div class="spec-icon">
                                        <i class="fas fa-memory"></i>
                                    </div>
                                    <div class="spec-content">
                                        <span class="spec-label">Mémoire RAM</span>
                                        <span class="spec-value"><?php echo htmlspecialchars($product['ram']); ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($product['storage']): ?>
                                <div class="spec-item">
                                    <div class="spec-icon">
                                        <i class="fas fa-hdd"></i>
                                    </div>
                                    <div class="spec-content">
                                        <span class="spec-label">Stockage</span>
                                        <span class="spec-value"><?php echo htmlspecialchars($product['storage']); ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($product['processor']): ?>
                                <div class="spec-item">
                                    <div class="spec-icon">
                                        <i class="fas fa-microchip"></i>
                                    </div>
                                    <div class="spec-content">
                                        <span class="spec-label">Processeur</span>
                                        <span class="spec-value"><?php echo htmlspecialchars($product['processor']); ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?> <?php if ($product['category_slug'] === 'smartphones' || $product['category_slug'] === 'tablets'): ?>
                    <div class="product-specs">
                        <div class="specs-grid">
                            <?php if ($product['storage']): ?>
                                <div class="spec-item">
                                    <div class="spec-icon">
                                        <i class="fas fa-hdd"></i>
                                    </div>
                                    <div class="spec-content">
                                        <span class="spec-label">Stockage</span>
                                        <span class="spec-value"><?php echo htmlspecialchars($product['storage']); ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($product['camera']): ?>
                                <div class="spec-item">
                                    <div class="spec-icon">
                                        <i class="fas fa-camera"></i>
                                    </div>
                                    <div class="spec-content">
                                        <span class="spec-label">Appareil Photo</span>
                                        <span class="spec-value"><?php echo htmlspecialchars($product['camera']); ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($product['battery']): ?>
                                <div class="spec-item">
                                    <div class="spec-icon">
                                        <i class="fas fa-battery-full"></i>
                                    </div>
                                    <div class="spec-content">
                                        <span class="spec-label">Batterie</span>
                                        <span class="spec-value"><?php echo htmlspecialchars($product['battery']); ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="product-actions">
                    <?php if ($product['stock'] > 0): ?>
                        <div class="quantity-selector">
                            <button class="quantity-btn" onclick="updateQuantity(-1)">-</button>
                            <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                            <button class="quantity-btn" onclick="updateQuantity(1)">+</button>
                        </div>
                        <button class="add-to-cart-btn" onclick="addToCart(<?php echo $product['id']; ?>, getQuantity())">
                            <i class="fas fa-shopping-cart"></i>
                            Ajouter au panier
                        </button>
                    <?php else: ?>
                        <button class="add-to-cart-btn out-of-stock" disabled>
                            <i class="fas fa-times"></i>
                            Rupture de stock
                        </button>
                    <?php endif; ?>

                    <button class="wishlist-btn">
                        <i class="fas fa-heart"></i>
                        Ajouter aux favoris
                    </button>
                </div>
            </div>
        </div>

        <?php if (!empty($related_products)): ?>
            <section class="related-products">
                <h2>Produits Similaires</h2>
                <div class="products-grid">
                    <?php foreach ($related_products as $related): ?>
                        <div class="product-card">
                            <img src="<?php echo htmlspecialchars($related['image_url']); ?>"
                                alt="<?php echo htmlspecialchars($related['name']); ?>"
                                class="product-image">
                            <div class="product-info">
                                <h3 class="product-title"><?php echo htmlspecialchars($related['name']); ?></h3>
                                <p><?php echo htmlspecialchars($related['description']); ?></p>
                                <div class="product-price"><?php echo number_format($related['price'], 2); ?> DH</div>
                                <a href="product_details.php?id=<?php echo $related['id']; ?>" class="view-details-btn">
                                    Voir les détails
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <script>
        function updateQuantity(change) {
            const input = document.getElementById('quantity');
            const newValue = Math.max(1, Math.min(<?php echo $product['stock']; ?>, parseInt(input.value) + change));
            input.value = newValue;
        }
    </script>
    <script src="/assets/js/details.js"></script>

</body>

</html>