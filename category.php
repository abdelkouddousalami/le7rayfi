<?php
session_start();
require_once 'config/db.php';
$conn = getConnection();
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
$categoryQuery = "SELECT * FROM categories WHERE slug = ?";
$stmt = $conn->prepare($categoryQuery);
$stmt->execute([$slug]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$category) {
    header('Location: index.php');
    exit();
}
$productsQuery = "SELECT * FROM products WHERE category_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($productsQuery);
$stmt->execute([$category['id']]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category['name']); ?> - HA GROUP</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/index.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="./assets/css/category.css?<?php echo time(); ?>">
</head>
<body>
    <div class="page-container">
        <div class="back-button">
            <a href="index.php"><i class="fas fa-arrow-left"></i> Retour</a>
        </div>
        <div class="category-header">
            <div class="container">
                <h1><i class="<?php echo htmlspecialchars($category['icon']); ?>"></i> <?php echo htmlspecialchars($category['name']); ?></h1>
                <p class="category-description">Découvrez notre sélection de <?php echo htmlspecialchars($category['name']); ?></p>
            </div>
        </div>
        <section class="products-section">
            <div class="products-grid">
                <?php if (empty($products)): ?>
                <div class="no-products">
                    <i class="fas fa-box-open"></i>
                    <p>Aucun produit n'est disponible dans cette catégorie pour le moment.</p>
                </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image-container">
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                class="product-image">
                        </div>
                        <div class="product-info">
                            <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                            <div class="product-price"><?php echo number_format($product['price'], 2); ?> DH</div>
                            <?php if ($product['stock'] > 0): ?>
                                <button class="product-button" onclick="addToCart(<?php echo $product['id']; ?>)">
                                    <i class="fas fa-shopping-cart"></i> Ajouter au panier
                                </button>
                            <?php else: ?>
                                <button class="product-button out-of-stock" disabled>
                                    <i class="fas fa-times"></i> Rupture de stock
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <div class="notification" id="notification" style="display: none;">
        <i class="fas fa-check-circle"></i>
        <span id="notification-message"></span>
    </div>

    <script>
    async function addToCart(productId) {
        try {
            const response = await fetch('update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=add&product_id=${productId}&quantity=1`
            });
            const data = await response.json();
            if (data.success) {
                showNotification('Produit ajouté au panier avec succès!');
            } else {
                showNotification(data.message || 'Erreur lors de l\'ajout au panier', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Une erreur s\'est produite', 'error');
        }
    }

    function showNotification(message, type = 'success') {
        const notification = document.getElementById('notification');
        const notificationMessage = document.getElementById('notification-message');
        notification.className = `notification ${type}`;
        notificationMessage.textContent = message;
        notification.style.display = 'flex';
        setTimeout(() => {
            notification.style.display = 'none';
        }, 3000);
    }
    </script>
</body>
</html>