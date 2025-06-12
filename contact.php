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
    <title>Contactez-nous - Le7rayfi</title>
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/contact.css">
    <link rel="stylesheet" href="assets/css/search-hover.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                <li><a href="index.php" class="active"><i class="fas fa-home"></i>Accueil</a></li>                <li><a href="boutique.php"><i class="fas fa-store"></i>Boutique</a></li>
                <li><a href="#"><i class="fas fa-laptop"></i>Ordinateurs</a></li>
                <li><a href="#"><i class="fas fa-keyboard"></i>Accessoires</a></li>
                <li><a href="#"><i class="fas fa-cogs"></i>Services</a></li>
                <li><a href="contact.php"><i class="fas fa-envelope"></i>Contact</a></li>
                <li><a href="#" class="vente-flash"><i class="fas fa-bolt"></i>Vente Flash</a></li>
            </ul>
        </nav>
    </header>

    <main class="contact-container">
        <div class="contact-wrapper">
            <div class="contact-info">
                <h2 class="section-title">Contactez-nous</h2>
                <div class="info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <h3>Notre adresse</h3>
                        <p>123 Rue du Commerce, Casablanca, Maroc</p>
                    </div>
                </div>
                <div class="info-item">
                    <i class="fas fa-phone"></i>
                    <div>
                        <h3>Téléphone</h3>
                        <p>+212 5XX-XXXXXX</p>
                    </div>
                </div>
                <div class="info-item">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <h3>Email</h3>
                        <p>contact@le7rayfi.com</p>
                    </div>
                </div>
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>            <form class="contact-form" id="contactForm">
                <div class="form-group">
                    <input type="text" id="name" name="name" required>
                    <label for="name">Votre nom</label>
                </div>
                <div class="form-group">
                    <input type="email" id="email" name="email" required>
                    <label for="email">Votre email</label>
                </div>
                <div class="form-group">
                    <input type="text" id="subject" name="subject" required>
                    <label for="subject">Sujet</label>
                </div>
                <div class="form-group">
                    <textarea id="message" name="message" required></textarea>
                    <label for="message">Votre message</label>
                </div>
                <button type="submit" class="submit-btn">
                    Envoyer le message
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>

        <div class="map-container">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3323.846447851791!2d-7.589843785106444!3d33.57214048073471!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMzPCsDM0JzE5LjciTiA3wrAzNScyMy40Ilc!5e0!3m2!1sfr!2sma!4v1620821234567!5m2!1sfr!2sma" 
                width="100%" 
                height="450" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy">
            </iframe>        </div>
    </main>
    
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
    <script src="./assets/js/search-hover.js"></script>
    <script src="assets/js/contact.js"></script>
</body>
</html>