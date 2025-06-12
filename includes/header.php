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

            <div class="category-filter">
                <h4>Catégorie</h4>
                <select id="quickSearchCategory">
                    <option value="">Toutes les catégories</option>
                    <option value="pc">Ordinateurs</option>
                    <option value="mobile">Mobiles</option>
                    <option value="accessory">Accessoires</option>
                </select>
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
        <ul class="nav-list">
            <li><a href="index.php" <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="active"' : ''; ?>><i class="fas fa-home"></i>Accueil</a></li>
            <li><a href="boutique.php" <?php echo basename($_SERVER['PHP_SELF']) == 'boutique.php' ? 'class="active"' : ''; ?>><i class="fas fa-store"></i>Boutique</a></li>
            <li><a href="#"><i class="fas fa-laptop"></i>Ordinateurs</a></li>
            <li><a href="#"><i class="fas fa-keyboard"></i>Accessoires</a></li>
            <li><a href="#"><i class="fas fa-cogs"></i>Services</a></li>
            <li><a href="contact.php" <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'class="active"' : ''; ?>><i class="fas fa-envelope"></i>Contact</a></li>
            <li><a href="#" class="vente-flash"><i class="fas fa-bolt"></i>Vente Flash</a></li>
        </ul>
    </nav>
</header>