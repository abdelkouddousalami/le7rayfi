<?php
session_start();
require_once 'config/db.php';
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
    <link rel="stylesheet" href="https:
    <link rel="stylesheet" href="./assets/css/index.css?<?php echo time(); ?>">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="category-header">
        <div class="container">
            <h1><i class="<?php echo htmlspecialchars($category['icon']); ?>"></i> <?php echo htmlspecialchars($category['name']); ?></h1>
            <p>Découvrez notre sélection de <?php echo htmlspecialchars($category['name']); ?></p>
        </div>
    </div>
    <section class="products-section">
        <div class="products-grid">
            <?php if (empty($products)): ?>
            <div class="no-products">
                <p>Aucun produit n'est disponible dans cette catégorie pour le moment.</p>
            </div>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="product-image">
                    <div class="product-info">
                        <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
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
    <?php include 'includes/footer.php'; ?>
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
                alert('Produit ajouté au panier avec succès!');
                location.reload();
            } else {
                alert(data.message || 'Erreur lors de l\'ajout au panier');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Une erreur s\'est produite');
        }
    }
    </script>
</body>
</html>
