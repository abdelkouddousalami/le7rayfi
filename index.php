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
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HA GROUP - Vente des pc à Marrakech</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="./assets/css/index.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="./assets/css/search-hover.css?<?php echo time(); ?>">
    <script src="./assets/js/search-hover.js?<?php echo time(); ?>" defer></script>
</head>

<body>
    <div class="search-container">
        <div class="floating-search-btn">
            <i class="fas fa-search"></i>
        </div>

        <div class="search-side-panel">
            <div class="search-panel-header">
                <h3><i class="fas fa-search"></i> Recherche Rapide</h3>
                <button class="close-search">
                    <i class="fas fa-times"></i>
                </button>
            </div>            
            <div class="search-panel-content">
                <div class="search-input-wrapper">
                    <input type="text" id="quickSearch" class="search-input" placeholder="Rechercher un produit...">
                    <i class="fas fa-search search-icon"></i>
                </div>
    
                <div class="deep-search-link">
                    <a href="deep_search.php" class="ai-search-link">
                        <i class="fas fa-robot"></i>
                        Recherche IA Avancée
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <div class="category-filter">
                    <h4>Catégorie</h4>
                    <select id="quickSearchCategory">
                        <option value="">Toutes les catégories</option>
                        <!-- Les options seront peuplées dynamiquement par JavaScript -->
                    </select>
                </div>

                <!-- NOUVEAU: Conteneur pour les filtres dynamiques -->
                <div class="dynamic-filters-container" id="dynamicFiltersContainer">
                    <!-- Les filtres spécifiques apparaîtront ici selon la catégorie sélectionnée -->
                </div>
                <!-- NOUVEAU: Affichage des filtres actifs -->
                <div class="active-filters" id="activeFilters">
                    <!-- Les tags de filtres actifs apparaîtront ici -->
                </div>

                <div class="search-results" id="searchResults"></div>
            </div>
        </div>
    </div>

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
                    <a href="wishlist.php"><span class="text">Favoris</span></a>
                </button>
                <a href="add_to_cart.php" class="icon-btn">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="badge"><?php echo $cartCount; ?></span>
                    <span class="text">Panier</span>
                </a>
            </div>
        </div>
        <nav class="nav-bar">
            <ul class="nav-list">                <li><a href="index.php" class="active"><i class="fas fa-home"></i>Accueil</a></li>
                <li><a href="boutique.php"><i class="fas fa-store"></i>Boutique</a></li>
                <li><a href="#"><i class="fas fa-laptop"></i>Ordinateurs</a></li>
                <li><a href="#"><i class="fas fa-keyboard"></i>Accessoires</a></li>
                <li><a href="#"><i class="fas fa-cogs"></i>Services</a></li>
                <li><a href="contact.php"><i class="fas fa-envelope"></i>Contact</a></li>
                <li><a href="#" class="vente-flash"><i class="fas fa-bolt"></i>Vente Flash</a></li>
            </ul>
        </nav>
    </header>

    <section class="hero">
        <div class="hero-sidebar">
            <h3 class="sidebar-title">Catégories</h3>
            <ul class="category-list"> <?php
                                        $conn = getConnection();
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
            <button class="filter-btn" data-category="laptops">Ordinateurs Portables</button>
            <button class="filter-btn" data-category="desktops">Ordinateurs Fixes</button>
            <button class="filter-btn" data-category="smartphones">Smartphones</button>
            <button class="filter-btn" data-category="tablets">Tablettes</button>
            <button class="filter-btn" data-category="keyboards">Claviers & Souris</button>
            <button class="filter-btn" data-category="audio">Audio & Casques</button>
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
        </div>
    </footer>

    <a href="https://wa.me/212500000000" class="whatsapp-button">
        <i class="fab fa-whatsapp"></i>
    </a>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
    const categorySelect = document.getElementById('quickSearchCategory');
    const filtersContainer = document.getElementById('dynamicFiltersContainer');
    const resultsDiv = document.getElementById('searchResults');

    // Map catégorie frontend <-> slug côté backend (à adapter selon ta BDD)
    // Par exemple si pc = laptops côté PHP, mobile = smartphones, accessory = accessoires etc.
    const categorySlugMap = {
        pc: 'laptops',
        mobile: 'smartphones',
        accessory: 'accessories' // adapter si tu as cette catégorie dans ta BDD
    };

    // Quand on change la catégorie, on charge les filtres dynamiques
    categorySelect.addEventListener('change', async () => {
        filtersContainer.innerHTML = '';
        resultsDiv.innerHTML = '';

        const catKey = categorySelect.value;
        if (!catKey) return;

        const categorySlug = categorySlugMap[catKey];
        if (!categorySlug) return;

        try {
            const res = await fetch(`search.php?action=getFeatures&category=${encodeURIComponent(categorySlug)}`);
            const data = await res.json();

            if (!data.success) {
                filtersContainer.innerHTML = `<p style="color:red;">${data.message}</p>`;
                return;
            }

            // Créer un formulaire (ou juste des selects) dans filtersContainer
            for (const [feature, values] of Object.entries(data.features)) {
                if (values.length === 0) continue;

                const label = document.createElement('label');
                label.textContent = `Filtrer par ${feature.charAt(0).toUpperCase() + feature.slice(1)}`;
                label.style.display = 'block';
                label.style.marginTop = '8px';

                const select = document.createElement('select');
                select.name = feature;
                select.style.width = '100%';
                select.style.padding = '5px';
                select.style.marginTop = '4px';

                const emptyOption = document.createElement('option');
                emptyOption.value = '';
                emptyOption.textContent = '-- Aucun filtre --';
                select.appendChild(emptyOption);

                values.forEach(val => {
                    const opt = document.createElement('option');
                    opt.value = val;
                    opt.textContent = val;
                    select.appendChild(opt);
                });

                filtersContainer.appendChild(label);
                filtersContainer.appendChild(select);
            }
        } catch (err) {
            filtersContainer.innerHTML = `<p style="color:red;">Erreur lors du chargement des filtres.</p>`;
        }
    });

    // Recherche quand on change un filtre ou appuie sur entrée dans quickSearch input
    const quickSearchInput = document.getElementById('quickSearch');

    async function performSearch() {
        resultsDiv.innerHTML = 'Recherche en cours...';

        const catKey = categorySelect.value;
        if (!catKey) {
            resultsDiv.innerHTML = '<p>Veuillez sélectionner une catégorie.</p>';
            return;
        }

        const categorySlug = categorySlugMap[catKey];
        if (!categorySlug) {
            resultsDiv.innerHTML = '<p>Catégorie non prise en charge.</p>';
            return;
        }

        // Récupérer tous les filtres dynamiques sélectionnés
        const filters = {};
        filters.category = categorySlug;

        // Ajouter valeurs des selects dynamiques
        const selects = filtersContainer.querySelectorAll('select');
        selects.forEach(sel => {
            if (sel.value) filters[sel.name] = sel.value;
        });

        // Ajouter terme de recherche libre
        const searchTerm = quickSearchInput.value.trim();
        if (searchTerm) {
            filters.search = searchTerm;
        }

        // Construire query string
        const params = new URLSearchParams(filters);

        try {
            const res = await fetch(`search.php?action=search&${params.toString()}`);
            const data = await res.json();

            if (!data.success) {
                resultsDiv.innerHTML = `<p style="color:red;">${data.message}</p>`;
                return;
            }

            if (data.products.length === 0) {
                resultsDiv.innerHTML = '<p>Aucun produit trouvé.</p>';
                return;
            }

            // Affichage simple des résultats
            resultsDiv.innerHTML = '';
            data.products.forEach(prod => {
                const div = document.createElement('div');
                div.className = 'product-result';
                div.style.border = '1px solid #ccc';
                div.style.padding = '10px';
                div.style.marginBottom = '8px';

                div.innerHTML = `
                    <strong>${prod.name}</strong> (${prod.category_name})<br>
                    Prix: ${prod.price} €<br>
                    RAM: ${prod.ram || '-'}<br>
                    Storage: ${prod.storage || '-'}<br>
                    Processor: ${prod.processor || '-'}<br>
                    Camera: ${prod.camera || '-'}<br>
                    Battery: ${prod.battery || '-'}
                `;

                resultsDiv.appendChild(div);
            });

        } catch (err) {
            resultsDiv.innerHTML = `<p style="color:red;">Erreur lors de la recherche.</p>`;
        }
    }

    // Recherche quand on change un filtre dynamique
    filtersContainer.addEventListener('change', performSearch);

    // Recherche quand on change la catégorie (après chargement filtres)
    categorySelect.addEventListener('change', () => {
        // on attend 300ms car le fetch de filtres est async, 
        // mais on peut mieux gérer ça si besoin
        setTimeout(performSearch, 350);
    });

    // Recherche quand on tape dans l'input + debounce 300ms
    let debounceTimer = null;
    quickSearchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(performSearch, 300);
    });
});

    </script>
    </aside>
    <script src="/assets/js/index.js"></script>
   
</body>

</html>