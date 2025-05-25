<?php
session_start();
require_once 'config/db.php';

$cartCount = 0;
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $cartQuery = "SELECT COUNT(*) as count FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($cartQuery);
    $stmt->execute([$userId]);
    $cartCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HA GROUP - Vente des pc à Marrakech</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">    <link rel="stylesheet" href="./assets/css/index.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="./assets/css/search-extension.css?<?php echo time(); ?>">
    <script src="./assets/js/search-extension.js?<?php echo time(); ?>" defer></script>
</head>
<body>
    <header class="header">
        <div class="header-top">
            <div class="logo-container">
                <div class="logo">HA GROUP</div>
                <div class="slogan">Vente des pc à Marrakech</div>
            </div>
            <div class="header-icons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="user-menu">
                        <button class="icon-btn" id="userMenuBtn">
                            <i class="fas fa-user"></i>
                            <span class="text">Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </button>
                        <div class="user-dropdown" id="userDropdown">
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <a href="admin/dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard Admin</a>
                            <?php endif; ?>
                            <a href="profile.php"><i class="fas fa-user-circle"></i>Mon Profil</a>
                            <a href="orders.php"><i class="fas fa-shopping-bag"></i>Mes Commandes</a>
                            <a href="logout.php"><i class="fas fa-sign-out-alt"></i>Déconnexion</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="auth.php" class="icon-btn">
                        <i class="fas fa-user"></i>
                        <span class="text">Connexion</span>
                    </a>
                    <a href="auth.php" class="icon-btn" onclick="event.preventDefault(); window.location.href='auth.php?mode=register';">
                        <i class="fas fa-user-plus"></i>
                        <span class="text">S'inscrire</span>
                    </a>
                <?php endif; ?>
                <button class="icon-btn">
                    <i class="fas fa-heart"></i>
                    <span class="badge">0</span>
                    <span class="text">Favoris</span>
                </button>
                <a href="cart.php" class="icon-btn">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="badge"><?php echo $cartCount; ?></span>
                    <span class="text">Panier</span>
                </a>
            </div>
        </div>
        <nav class="nav-bar">
            <ul class="nav-list">
                <li><a href="#" class="active"><i class="fas fa-home"></i>Accueil</a></li>
                <li><a href="#"><i class="fas fa-store"></i>Boutique</a></li>
                <li><a href="#"><i class="fas fa-laptop"></i>Ordinateurs</a></li>
                <li><a href="#"><i class="fas fa-keyboard"></i>Accessoires</a></li>
                <li><a href="#"><i class="fas fa-cogs"></i>Services</a></li>
                <li><a href="#"><i class="fas fa-envelope"></i>Contact</a></li>
                <li><a href="#" class="vente-flash"><i class="fas fa-bolt"></i>Vente Flash</a></li>
            </ul>
        </nav>
    </header>

    <section class="hero">        <div class="hero-sidebar">
            <h3 class="sidebar-title">Catégories</h3>
            <ul class="category-list">
                <?php
                // Get categories with product counts
                $categoryQuery = "SELECT c.*, COUNT(p.id) as product_count 
                                FROM categories c 
                                LEFT JOIN products p ON p.category_id = c.id 
                                GROUP BY c.id 
                                ORDER BY c.name";
                $stmt = $conn->query($categoryQuery);
                while ($category = $stmt->fetch(PDO::FETCH_ASSOC)):
                ?>
                <li>
                    <a href="category.php?slug=<?php echo htmlspecialchars($category['slug']); ?>">
                        <i class="<?php echo htmlspecialchars($category['icon']); ?>"></i>
                        <?php echo htmlspecialchars($category['name']); ?> 
                        <span class="cat-count"><?php echo (int)$category['product_count']; ?></span>
                    </a>
                </li>
                <?php endwhile; ?>
                <li><a href="categories.php" class="view-all">Voir toutes les catégories <i class="fas fa-arrow-right"></i></a></li>
            </ul>
        </div>
        <div class="hero-main">
            <img src="img/laptop.jpg" alt="Hero Image" class="hero-image">
            <div class="hero-content">
                <h1 class="hero-title">Engagé Pour Satisfaire Vos Besoins Et Vos Envies</h1>
                <p class="hero-description">Découvrez notre large gamme de produits informatiques et high-tech. Des solutions innovantes pour particuliers et professionnels au meilleur prix.</p>
                <a href="#" class="cta-button">
                    Découvrir nos produits
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <section class="products-section">
        <h2 class="section-title">Offres Spéciales</h2>
        <div class="announcement-grid">
            <div class="announcement-card">
                <img src="img/best-laptops-20240516-medium.jpg" alt="Promo MacBooks" class="announcement-image">
                <div class="announcement-overlay">
                    <h3 class="announcement-title">Promo MacBook Pro</h3>
                    <p class="announcement-text">Jusqu'à -15% sur toute la gamme MacBook Pro</p>
                    <a href="#" class="announcement-link">
                        Découvrir <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <div class="announcement-card">
                <img src="img/laptop.jpg" alt="Gaming Week" class="announcement-image">
                <div class="announcement-overlay">
                    <h3 class="announcement-title">Gaming Week</h3>
                    <p class="announcement-text">Les meilleurs PC gaming à prix imbattables</p>
                    <a href="#" class="announcement-link">
                        Voir les offres <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <div class="announcement-card">
                <img src="img/phone.png" alt="Smartphones" class="announcement-image">
                <div class="announcement-overlay">
                    <h3 class="announcement-title">Smartphones 5G</h3>
                    <p class="announcement-text">Les derniers modèles disponibles</p>
                    <a href="#" class="announcement-link">
                        Explorer <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="products-section">
        <h2>Nos Produits Populaires</h2>
        <div class="products-filter">
            <button class="filter-btn active" data-category="all">Tous</button>
            <button class="filter-btn" data-category="laptop">Ordinateurs</button>
            <button class="filter-btn" data-category="smartphone">Smartphones</button>
            <button class="filter-btn" data-category="accessory">Accessoires</button>
        </div>
        <div class="products-grid">            <?php
            $stmt = $conn->query("SELECT p.*, c.name as category_name FROM products p 
                                LEFT JOIN categories c ON p.category_id = c.id 
                                ORDER BY p.created_at DESC");
            while ($product = $stmt->fetch(PDO::FETCH_ASSOC)):
            ?>
            <div class="product-card" data-category="<?php echo htmlspecialchars($product['category']); ?>">
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
                    
                    <?php if ($product['category'] === 'pc' || $product['category'] === 'laptop'): ?>
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
                    <?php elseif ($product['category'] === 'mobile' || $product['category'] === 'smartphone'): ?>
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
                                <?php echo number_format($product['price'] * (1 - $product['discount']/100), 2); ?> DH
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
                        
                            <button class="product-button out-of-stock" disabled>
                                <i class="fas fa-times"></i> Rupture de stock
                            </button>
                            <button class="product-button notify-stock">
                                <i class="fas fa-bell"></i> Notifier disponibilité
                            </button>
                        
                    </div>
                    <a href="product_details.php?id=<?php echo $product['id']; ?>" class="product-button view-details">
                        <i class="fas fa-eye"></i> Voir détails
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </section>
    <section class="best-sellers">
        <h2 class="section-title">Meilleures Ventes</h2>
        <div class="best-seller-grid">
            <div class="best-seller-card">
                <div class="best-seller-image-container">
                    <img src="img/best-laptops-20240516-medium.jpg" alt="MacBook Pro" class="best-seller-image">
                    <span class="best-seller-badge">Best Seller</span>
                    <span class="rank-badge">1</span>
                    <span class="discount-badge">-15%</span>
                </div>
                <div class="best-seller-info">
                    <span class="best-seller-category">Ordinateurs Portables</span>
                    <h3 class="best-seller-title">MacBook Pro M2</h3>
                    <p class="best-seller-description">16GB RAM, 512GB SSD, 14-inch Liquid Retina XDR display</p>
                    <div class="best-seller-price-container">
                        <div class="best-seller-price">25,999 DH</div>
                        <div class="original-price">29,999 DH</div>
                    </div>
                    <div class="best-seller-stats">
                        <div class="stat">
                            <i class="fas fa-star star-rating"></i>
                            <i class="fas fa-star star-rating"></i>
                            <i class="fas fa-star star-rating"></i>
                            <i class="fas fa-star star-rating"></i>
                            <i class="fas fa-star-half-alt star-rating"></i>
                            <span>(4.8)</span>
                        </div>
                        <div class="stat">
                            <i class="fas fa-shopping-cart"></i>
                            120 vendus
                        </div>
                    </div>
                    <div class="best-seller-actions">
                        <a href="#" class="best-seller-button add-to-cart">
                            <i class="fas fa-shopping-cart"></i>
                            Ajouter au panier
                        </a>
                        <a href="#" class="best-seller-button add-to-wishlist">
                            <i class="fas fa-heart"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="best-seller-card">
                <div class="best-seller-image-container">
                    <img src="img/laptop.jpg" alt="MSI Gaming" class="best-seller-image">
                    <span class="best-seller-badge">Best Seller</span>
                    <span class="rank-badge">2</span>
                    <span class="discount-badge">-10%</span>
                </div>
                <div class="best-seller-info">
                    <span class="best-seller-category">PC Gaming</span>
                    <h3 class="best-seller-title">MSI Gaming Laptop</h3>
                    <p class="best-seller-description">RTX 4060, 16GB RAM, 1TB SSD, écran 144Hz</p>
                    <div class="best-seller-price-container">
                        <div class="best-seller-price">15,499 DH</div>
                        <div class="original-price">16,999 DH</div>
                    </div>
                    <div class="best-seller-stats">
                        <div class="stat">
                            <i class="fas fa-star star-rating"></i>
                            <i class="fas fa-star star-rating"></i>
                            <i class="fas fa-star star-rating"></i>
                            <i class="fas fa-star star-rating"></i>
                            <i class="fas fa-star-half-alt star-rating"></i>
                            <span>(4.7)</span>
                        </div>
                        <div class="stat">
                            <i class="fas fa-shopping-cart"></i>
                            98 vendus
                        </div>
                    </div>
                    <div class="best-seller-actions">
                        <a href="#" class="best-seller-button add-to-cart">
                            <i class="fas fa-shopping-cart"></i>
                            Ajouter au panier
                        </a>
                        <a href="#" class="best-seller-button add-to-wishlist">
                            <i class="fas fa-heart"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="best-seller-card">
                <div class="best-seller-image-container">
                    <img src="img/phone.png" alt="iPhone 15 Pro" class="best-seller-image">
                    <span class="best-seller-badge">Best Seller</span>
                    <span class="rank-badge">3</span>
                    <span class="discount-badge">New</span>
                </div>
                <div class="best-seller-info">
                    <span class="best-seller-category">Smartphones</span>
                    <h3 class="best-seller-title">iPhone 15 Pro</h3>
                    <p class="best-seller-description">256GB, Titanium, 5G, Caméra 48MP</p>
                    <div class="best-seller-price-container">
                        <div class="best-seller-price">13,999 DH</div>
                    </div>
                    <div class="best-seller-stats">
                        <div class="stat">
                            <i class="fas fa-star star-rating"></i>
                            <i class="fas fa-star star-rating"></i>
                            <i class="fas fa-star star-rating"></i>
                            <i class="fas fa-star star-rating"></i>
                            <i class="fas fa-star star-rating"></i>
                            <span>(4.9)</span>
                        </div>
                        <div class="stat">
                            <i class="fas fa-shopping-cart"></i>
                            150 vendus
                        </div>
                    </div>
                    <div class="best-seller-actions">
                        <a href="#" class="best-seller-button add-to-cart">
                            <i class="fas fa-shopping-cart"></i>
                            Ajouter au panier
                        </a>
                        <a href="#" class="best-seller-button add-to-wishlist">
                            <i class="fas fa-heart"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <footer class="footer">
    <div class="footer-wave"></div>
    <div class="footer-container">
        <div class="footer-grid">
            <div class="footer-links">
                <h3>About Us</h3>
                <p style="color: #ecf0f1; line-height: 1.6;">We provide high-quality electronics and accessories to enhance your digital lifestyle. Trust us for the latest tech solutions.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>

            <div class="footer-links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#"><i class="fas fa-chevron-right"></i>Home</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i>Products</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i>About Us</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i>Contact</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i>Privacy Policy</a></li>
                </ul>
            </div>

            <div class="footer-links">
                <h3>Our Services</h3>
                <ul>
                    <li><a href="#"><i class="fas fa-chevron-right"></i>Phones & Tablets</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i>Laptops</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i>Accessories</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i>Repair Services</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i>Support</a></li>
                </ul>
            </div>

            <div class="footer-links">
                <h3>Contact Info</h3>
                <ul class="contact-info">
                    <li><i class="fas fa-map-marker-alt"></i>123 Tech Street, Digital City</li>
                    <li><i class="fas fa-phone"></i>+1 234 567 8900</li>
                    <li><i class="fas fa-envelope"></i>info@techstore.com</li>
                    <li><i class="fas fa-clock"></i>Mon - Sat: 9:00 AM - 8:00 PM</li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="copyright">
                © 2025 Tech Store. All rights reserved.
            </div>
            <div class="payment-methods">
                <i class="fab fa-cc-visa"></i>
                <i class="fab fa-cc-mastercard"></i>
                <i class="fab fa-cc-paypal"></i>
                <i class="fab fa-cc-apple-pay"></i>
            </div>
        </div>
    </div>
</footer>

    <!-- WhatsApp Button -->
    <a href="https://wa.me/212500000000" class="whatsapp-button">
        <i class="fab fa-whatsapp"></i>
    </a>    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // User menu functionality
            const userMenuBtn = document.getElementById('userMenuBtn');
            const userDropdown = document.getElementById('userDropdown');
            
            if (userMenuBtn && userDropdown) {
                userMenuBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userDropdown.classList.toggle('show');
                });

                document.addEventListener('click', function(e) {
                    if (!userDropdown.contains(e.target) && !userMenuBtn.contains(e.target)) {
                        userDropdown.classList.remove('show');
                    }
                });
            }
        });
    </script>    <aside class="filter-aside">
        <div class="filter-toggle">
            <i class="fas fa-filter"></i>
        </div>
        <h3 class="filter-title">
            <i class="fas fa-sliders-h"></i>
            Filtrer et Rechercher
        </h3>
        <div class="filter-container">
            <div class="search-wrapper">
                <input type="text" id="productSearch" class="search-input" placeholder="Rechercher un produit...">
                <i class="fas fa-search search-icon"></i>
            </div>

            <div class="filter-section">
                <h4 class="filter-section-title">Catégorie</h4>
                <select id="categoryFilter" class="filter-dropdown">
                    <option value="">Toutes les catégories</option>
                    <option value="pc">Ordinateurs</option>
                    <option value="mobile">Mobiles</option>
                    <option value="accessory">Accessoires</option>
                </select>
            </div>

            <div class="filter-section">
                <h4 class="filter-section-title">Prix</h4>
                <div class="price-range">
                    <input type="number" id="priceMin" placeholder="Min" class="price-input">
                    <span>-</span>
                    <input type="number" id="priceMax" placeholder="Max" class="price-input">
                </div>
            </div>

            <div id="pc-filters" class="filter-section" style="display: none;">
                <h4 class="filter-section-title">Spécifications PC</h4>
                <select id="ramFilter" class="filter-dropdown">
                    <option value="">RAM</option>
                    <option value="8GB">8GB</option>
                    <option value="16GB">16GB</option>
                    <option value="32GB">32GB</option>
                    <option value="64GB">64GB</option>
                </select>

                <select id="ssdFilter" class="filter-dropdown">
                    <option value="">Stockage</option>
                    <option value="256GB">256GB</option>
                    <option value="512GB">512GB</option>
                    <option value="1TB">1TB</option>
                    <option value="2TB">2TB</option>
                </select>

                <select id="processorFilter" class="filter-dropdown">
                    <option value="">Processeur</option>
                    <option value="Intel Core i5">Intel Core i5</option>
                    <option value="Intel Core i7">Intel Core i7</option>
                    <option value="Intel Core i9">Intel Core i9</option>
                    <option value="AMD Ryzen 5">AMD Ryzen 5</option>
                    <option value="AMD Ryzen 7">AMD Ryzen 7</option>
                    <option value="AMD Ryzen 9">AMD Ryzen 9</option>
                    <option value="Apple M1">Apple M1</option>
                    <option value="Apple M2">Apple M2</option>
                </select>
            </div>

            <div id="mobile-filters" class="filter-section" style="display: none;">
                <h4 class="filter-section-title">Spécifications Mobile</h4>
                <select id="cameraFilter" class="filter-dropdown">
                    <option value="">Appareil photo</option>
                    <option value="12MP">12MP</option>
                    <option value="48MP">48MP</option>
                    <option value="50MP">50MP</option>
                    <option value="108MP">108MP</option>
                    <option value="200MP">200MP</option>
                </select>

                <select id="batteryFilter" class="filter-dropdown">
                    <option value="">Batterie</option>
                    <option value="3000mAh">3000mAh</option>
                    <option value="4000mAh">4000mAh</option>
                    <option value="4500mAh">4500mAh</option>
                    <option value="5000mAh">5000mAh</option>
                    <option value="5400mAh">5400mAh</option>
                </select>

                <select id="storageFilter" class="filter-dropdown">
                    <option value="">Stockage</option>
                    <option value="64GB">64GB</option>
                    <option value="128GB">128GB</option>
                    <option value="256GB">256GB</option>
                    <option value="512GB">512GB</option>
                    <option value="1TB">1TB</option>
                </select>
            </div>

            <div class="active-filters" id="activeFilters">
                <!-- Active filters will be displayed here -->
            </div>

            <button class="filter-reset" id="resetFilters">
                <i class="fas fa-undo-alt"></i>
                Réinitialiser les filtres
            </button>
        </div>
    </aside>    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elements
            const productsGrid = document.querySelector('.products-grid');
            const filterAside = document.querySelector('.filter-aside');
            const filterToggle = document.querySelector('.filter-toggle');
            
            // Get all available filter options from the server
            function loadFilterOptions() {
                fetch('get_filtered_products.php?get_options=1')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            updateFilterOptions(data.options);
                        }
                    })
                    .catch(error => console.error('Error loading filter options:', error));
            }

            // Update filter dropdowns with available options
            function updateFilterOptions(options) {
                // Update category filter
                const categoryFilter = document.getElementById('categoryFilter');
                categoryFilter.innerHTML = `
                    <option value="">Toutes les catégories</option>
                    ${options.categories.map(category => `
                        <option value="${category}">${
                            category === 'pc' ? 'Ordinateurs' :
                            category === 'mobile' ? 'Mobiles' :
                            category === 'accessory' ? 'Accessoires' : category
                        }</option>
                    `).join('')}
                `;

                // Update PC filters
                if (options.pc) {
                    const ramSelect = document.getElementById('ramFilter');
                    const storageSelect = document.getElementById('ssdFilter');
                    const processorSelect = document.getElementById('processorFilter');

                    if (ramSelect) {
                        ramSelect.innerHTML = `
                            <option value="">RAM</option>
                            ${options.pc.ram.sort().map(ram => 
                                `<option value="${ram}">${ram}</option>`
                            ).join('')}
                        `;
                    }

                    if (storageSelect) {
                        storageSelect.innerHTML = `
                            <option value="">Stockage</option>
                            ${options.pc.storage.sort().map(storage => 
                                `<option value="${storage}">${storage}</option>`
                            ).join('')}
                        `;
                    }

                    if (processorSelect) {
                        processorSelect.innerHTML = `
                            <option value="">Processeur</option>
                            ${options.pc.processor.sort().map(processor => 
                                `<option value="${processor}">${processor}</option>`
                            ).join('')}
                        `;
                    }
                }

                // Update Mobile filters
                if (options.mobile) {
                    const cameraSelect = document.getElementById('cameraFilter');
                    const batterySelect = document.getElementById('batteryFilter');
                    const storageSelect = document.getElementById('storageFilter');

                    if (cameraSelect) {
                        cameraSelect.innerHTML = `
                            <option value="">Appareil photo</option>
                            ${options.mobile.camera.sort().map(camera => 
                                `<option value="${camera}">${camera}</option>`
                            ).join('')}
                        `;
                    }

                    if (batterySelect) {
                        batterySelect.innerHTML = `
                            <option value="">Batterie</option>
                            ${options.mobile.battery.sort().map(battery => 
                                `<option value="${battery}">${battery}</option>`
                            ).join('')}
                        `;
                    }

                    if (storageSelect) {
                        storageSelect.innerHTML = `
                            <option value="">Stockage</option>
                            ${options.mobile.storage.sort().map(storage => 
                                `<option value="${storage}">${storage}</option>`
                            ).join('')}
                        `;
                    }
                }

                // Update price range inputs
                if (options.price) {
                    const priceMin = document.getElementById('priceMin');
                    const priceMax = document.getElementById('priceMax');
                    
                    if (priceMin && priceMax) {
                        priceMin.placeholder = `Min (${Math.floor(options.price.min)} DH)`;
                        priceMax.placeholder = `Max (${Math.ceil(options.price.max)} DH)`;
                        priceMin.min = Math.floor(options.price.min);
                        priceMax.max = Math.ceil(options.price.max);
                    }
                }
            }
            const searchInput = document.getElementById('productSearch');
            const categoryFilter = document.getElementById('categoryFilter');
            const pcFilters = document.getElementById('pc-filters');
            const mobileFilters = document.getElementById('mobile-filters');
            const resetFilters = document.getElementById('resetFilters');
            const activeFilters = document.getElementById('activeFilters');
            const priceMin = document.getElementById('priceMin');
            const priceMax = document.getElementById('priceMax');
            
            let currentFilters = {};
            let searchTimeout;

            // Initialize loading state
            const loadingSpinner = document.createElement('div');
            loadingSpinner.className = 'loading-spinner';
            loadingSpinner.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            // Toggle filter panel
            filterToggle.addEventListener('click', () => {
                filterAside.classList.toggle('active');
            });

            // Search with debounce
            searchInput.addEventListener('input', debounce(function(e) {
                currentFilters.search = e.target.value;
                updateActiveFilters();
                fetchFilteredProducts();
            }, 500));            // Load filter options when page loads
            loadFilterOptions();

            // Category filter
            categoryFilter.addEventListener('change', function() {
                currentFilters.category = this.value;
                pcFilters.style.display = this.value === 'pc' ? 'block' : 'none';
                mobileFilters.style.display = this.value === 'mobile' ? 'block' : 'none';
                
                // Clear other filters when category changes
                if (this.value === 'pc') {
                    ['cameraFilter', 'batteryFilter', 'storageFilter'].forEach(id => {
                        const element = document.getElementById(id);
                        if (element) element.value = '';
                        delete currentFilters[id];
                    });
                } else if (this.value === 'mobile') {
                    ['ramFilter', 'ssdFilter', 'processorFilter'].forEach(id => {
                        const element = document.getElementById(id);
                        if (element) element.value = '';
                        delete currentFilters[id];
                    });
                }
                
                updateActiveFilters();
                fetchFilteredProducts();
            });

            // Price range filters
            [priceMin, priceMax].forEach(input => {
                input.addEventListener('change', function() {
                    currentFilters.priceMin = priceMin.value || null;
                    currentFilters.priceMax = priceMax.value || null;
                    updateActiveFilters();
                    fetchFilteredProducts();
                });
            });

            // PC Specification filters
            ['ramFilter', 'ssdFilter', 'processorFilter'].forEach(filterId => {
                document.getElementById(filterId)?.addEventListener('change', function() {
                    currentFilters[filterId] = this.value;
                    updateActiveFilters();
                    fetchFilteredProducts();
                });
            });

            // Mobile Specification filters
            ['cameraFilter', 'batteryFilter', 'storageFilter'].forEach(filterId => {
                document.getElementById(filterId)?.addEventListener('change', function() {
                    currentFilters[filterId] = this.value;
                    updateActiveFilters();
                    fetchFilteredProducts();
                });
            });            // Reset filters
            resetFilters.addEventListener('click', function() {
                // Reset all form elements
                searchInput.value = '';
                categoryFilter.value = '';
                priceMin.value = '';
                priceMax.value = '';
                document.querySelectorAll('.filter-dropdown').forEach(select => select.value = '');
                
                // Reset filter states
                pcFilters.style.display = 'none';
                mobileFilters.style.display = 'none';
                currentFilters = {};
                
                // Visual feedback
                resetFilters.classList.add('spinning');
                setTimeout(() => resetFilters.classList.remove('spinning'), 500);
                
                // Reload filter options and update UI
                loadFilterOptions();
                updateActiveFilters();
                fetchFilteredProducts();
            });

            // Update active filters display
            function updateActiveFilters() {
                activeFilters.innerHTML = '';
                
                Object.entries(currentFilters).forEach(([key, value]) => {
                    if (value && value !== '') {
                        const badge = document.createElement('span');
                        badge.className = 'filter-badge';
                        badge.innerHTML = `
                            ${getFilterLabel(key)}: ${value}
                            <i class="fas fa-times" data-filter="${key}"></i>
                        `;
                        activeFilters.appendChild(badge);

                        // Add click handler to remove filter
                        badge.querySelector('i').addEventListener('click', function() {
                            const filterKey = this.dataset.filter;
                            delete currentFilters[filterKey];
                            
                            // Reset corresponding form element
                            const element = document.getElementById(filterKey);
                            if (element) element.value = '';
                            
                            updateActiveFilters();
                            fetchFilteredProducts();
                        });
                    }
                });
            }

            // Helper function to get filter labels
            function getFilterLabel(key) {
                const labels = {
                    search: 'Recherche',
                    category: 'Catégorie',
                    priceMin: 'Prix min',
                    priceMax: 'Prix max',
                    ramFilter: 'RAM',
                    ssdFilter: 'Stockage PC',
                    processorFilter: 'Processeur',
                    cameraFilter: 'Appareil photo',
                    batteryFilter: 'Batterie',
                    storageFilter: 'Stockage Mobile'
                };
                return labels[key] || key;
            }

            // Debounce helper function
            function debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }

            // Fetch filtered products
            function fetchFilteredProducts() {
                // Show loading state
                productsGrid.style.opacity = '0.5';
                productsGrid.appendChild(loadingSpinner);

                const queryParams = new URLSearchParams();
                Object.entries(currentFilters).forEach(([key, value]) => {
                    if (value) queryParams.append(key, value);
                });

                fetch(`get_filtered_products.php?${queryParams.toString()}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            updateProductsGrid(data.products);
                        } else {
                            console.error('Error:', data.message);
                        }
                    })
                    .catch(error => console.error('Error:', error))
                    .finally(() => {
                        productsGrid.removeChild(loadingSpinner);
                        productsGrid.style.opacity = '1';
                    });
            }

            // Update products grid
            function updateProductsGrid(products) {
                productsGrid.style.opacity = '0';
                
                setTimeout(() => {
                    if (products.length === 0) {
                        productsGrid.innerHTML = `
                            <div class="no-results">
                                <i class="fas fa-search"></i>
                                <p>Aucun produit ne correspond à vos critères de recherche</p>
                            </div>
                        `;
                    } else {
                        productsGrid.innerHTML = products.map(product => `
                            <div class="product-card" data-category="${product.category}">
                                <div class="product-image-container">
                                    <img src="${product.image_url}" 
                                         alt="${product.name}" 
                                         class="product-image">
                                    ${product.stock < 5 && product.stock > 0 ? 
                                        `<span class="stock-warning">Plus que ${product.stock} en stock!</span>` : ''}
                                </div>
                                <div class="product-info">
                                    <h3 class="product-title">${product.name}</h3>
                                    <p class="product-description">${product.description}</p>
                                    <div class="product-specs">
                                        ${product.specs.map(spec => `
                                            <span class="spec-badge">
                                                <i class="fas fa-${spec.includes('RAM') ? 'memory' : 
                                                                   spec.includes('Storage') ? 'hdd' :
                                                                   spec.includes('Camera') ? 'camera' :
                                                                   spec.includes('Battery') ? 'battery-full' :
                                                                   'microchip'}"></i>
                                                ${spec}
                                            </span>
                                        `).join('')}
                                    </div>
                                    <div class="product-price">${Number(product.price).toFixed(2)} DH</div>
                                    ${product.stock > 0 ? 
                                        `<a href="#" class="product-button" onclick="addToCart(${product.id})">
                                            <i class="fas fa-shopping-cart"></i> Ajouter au panier
                                        </a>` :
                                        `<button class="product-button out-of-stock" disabled>
                                            <i class="fas fa-times"></i> Rupture de stock
                                        </button>`
                                    }
                                </div>
                            </div>
                        `).join('');
                    }
                    productsGrid.style.opacity = '1';
                }, 300);
            }

            // Initial load
            fetchFilteredProducts();
        });
    </script>    <!-- Search Extension -->
    <div class="search-wrapper">
        <div class="search-toggle">
            <i class="fas fa-search"></i>
        </div>
        
        <div class="search-extension">
            <div class="search-header">
                <h3><i class="fas fa-search"></i> Recherche Rapide</h3>
            </div>
            
            <div class="category-filter">
                <select id="quickSearchCategory">
                    <option value="">Toutes les catégories</option>
                    <option value="pc">Ordinateurs</option>
                    <option value="mobile">Mobiles</option>
                    <option value="accessory">Accessoires</option>
                </select>
            </div>
            
            <div class="search-input-wrapper">
                <input type="text" id="quickSearch" class="search-input" placeholder="Rechercher un produit...">
                <i class="fas fa-search search-icon"></i>
            </div>
            
            <div class="search-results" id="searchResults"></div>
        </div>
    </div>

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        // Search extension functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchToggle = document.getElementById('searchToggle');
            const searchExtension = document.getElementById('searchExtension');
            const quickSearch = document.getElementById('quickSearch');
            const quickSearchCategory = document.getElementById('quickSearchCategory');
            const searchResults = document.getElementById('searchResults');

            // Toggle search extension
            searchToggle.addEventListener('click', function() {
                searchExtension.classList.toggle('active');
                quickSearch.focus();
            });

            // Close search extension when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchExtension.contains(e.target) && !searchToggle.contains(e.target)) {
                    searchExtension.classList.remove('active');
                }
            });

            // Fetch and display search results
            quickSearch.addEventListener('input', debounce(function() {
                const query = this.value;
                const category = quickSearchCategory.value;

                if (query.length < 3) {
                    searchResults.innerHTML = '';
                    return;
                }

                // Simulate server request
                setTimeout(() => {
                    // For demo, we use static data
                    const products = [
                        { id: 1, name: 'MacBook Pro M2', category: 'pc', image_url: 'img/best-laptops-20240516-medium.jpg', price: 25999 },
                        { id: 2, name: 'MSI Gaming Laptop', category: 'pc', image_url: 'img/laptop.jpg', price: 15499 },
                        { id: 3, name: 'iPhone 15 Pro', category: 'mobile', image_url: 'img/phone.png', price: 13999 },
                        // Add more products as needed
                    ];

                    const filteredProducts = products.filter(product => {
                        const inCategory = category === '' || product.category === category;
                        const inSearch = product.name.toLowerCase().includes(query.toLowerCase());
                        return inCategory && inSearch;
                    });

                    displaySearchResults(filteredProducts);
                }, 300);
            }, 300));

            // Display search results
            function displaySearchResults(products) {
                searchResults.innerHTML = '';

                if (products.length === 0) {
                    searchResults.innerHTML = '<div class="no-results">Aucun résultat trouvé</div>';
                    return;
                }

                products.forEach(product => {
                    const div = document.createElement('div');
                    div.className = 'search-result';
                    div.innerHTML = `
                        <img src="${product.image_url}" alt="${product.name}" class="result-image">
                        <div class="result-info">
                            <h4 class="result-title">${product.name}</h4>
                            <div class="result-price">${product.price} DH</div>
                        </div>
                    `;
                    div.addEventListener('click', () => {
                        window.location.href = `product.php?id=${product.id}`;
                    });
                    searchResults.appendChild(div);
                });
            }
        });
    </script>
</body>
</html>