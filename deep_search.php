<?php
session_start();
require_once 'config/db.php';

function analyzeUserPrompt($prompt, $apiKey) {
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $apiKey;
    
    $data = [
        'contents' => [
            [
                'parts' => [
                    [
                        'text' => "Analyze this product requirement and extract key features: " . $prompt
                    ]
                ]
            ]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

function findMatchingProducts($features) {
    $conn = getConnection();
    
    $query = "SELECT p.*, c.name as category_name FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE 1=1";
    
    foreach ($features as $feature => $value) {
        $query .= " AND (
            p.name LIKE :$feature 
            OR p.description LIKE :$feature 
            OR p.specs LIKE :$feature
        )";
    }
    
    $stmt = $conn->prepare($query);
    
    foreach ($features as $feature => $value) {
        $stmt->bindValue(":$feature", "%$value%");
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$apiKey = 'AIzaSyC0WjH3pT7wxhFoN-rQcJ5wppwZ-vfzqd0';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['prompt'])) {
    $userPrompt = $_POST['prompt'];
    $analysis = analyzeUserPrompt($userPrompt, $apiKey);
    
    $features = [];
    
    $matchingProducts = findMatchingProducts($features);
    
    header('Content-Type: application/json');
    echo json_encode([
        'analysis' => $analysis,
        'products' => $matchingProducts
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deep Search - AI-Powered Product Search</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="./assets/css/index.css">
    <link rel="stylesheet" href="./assets/css/deep-search.css">
    <style>
    .header {
        transition: transform 0.3s ease;
    }
    .header.sticky {
        position: fixed;
        top: 0;
        width: 100%;
        background: rgba(255, 255, 255, 0.98);
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        animation: slideDown 0.3s ease-out;
    }
    .mobile-menu-btn {
        display: none;
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #333;
        cursor: pointer;
        padding: 0.5rem;
    }
    @media (max-width: 1024px) {
        .mobile-menu-btn {
            display: block;
        }
        .nav-list {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            padding: 1rem 0;
            border-top: 1px solid rgba(0,0,0,0.1);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .nav-list.show {
            display: block;
        }
        .nav-list li {
            display: block;
            margin: 0;
        }
        .nav-list a {
            padding: 1rem 2rem;
            display: block;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
    }
    @keyframes slideDown {
        from { transform: translateY(-100%); }
        to { transform: translateY(0); }
    }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-top">
            <div class="logo-container">
                <a href="index.php" class="logo">Le7rayfi</a>
                <span class="slogan">L'expertise à votre service</span>
            </div>
            
            <div class="header-icons">
                <?php if(isset($_SESSION['user'])): ?>
                    <div class="user-menu">
                        <button class="icon-btn" id="userMenuBtn">
                            <i class="fas fa-user"></i>
                            <span class="text"><?php echo $_SESSION['user']['fullname']; ?></span>
                        </button>
                        <div class="user-dropdown" id="userDropdown">
                            <a href="profile.php"><i class="fas fa-user-circle"></i>Mon Profil</a>
                            <a href="orders.php"><i class="fas fa-shopping-bag"></i>Mes Commandes</a>
                            <a href="wishlist.php"><i class="fas fa-heart"></i>Ma Liste</a>
                            <a href="logout.php"><i class="fas fa-sign-out-alt"></i>Déconnexion</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="icon-btn">
                        <i class="fas fa-user"></i>
                        <span class="text">Se connecter</span>
                    </a>
                <?php endif; ?>
                
                <a href="cart.php" class="icon-btn">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="text">Panier</span>
                    <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                        <span class="badge"><?php echo count($_SESSION['cart']); ?></span>
                    <?php endif; ?>
                </a>
                  <a href="wishlist.php" class="icon-btn">
                    <i class="fas fa-heart"></i>
                    <span class="text">Favoris</span>
                </a>
                
                <a href="deep_search.php" class="icon-btn" title="Recherche Avancée">
                    <i class="fas fa-search-plus"></i>
                    <span class="text">Recherche IA</span>
                </a>
            </div>
        </div>
        
        <nav class="nav-bar">
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
            <ul class="nav-list" id="navList">
                <li><a href="index.php"><i class="fas fa-home"></i>Accueil</a></li>
                <li><a href="categories.php"><i class="fas fa-th-large"></i>Catégories</a></li>
                <li><a href="artisans.php"><i class="fas fa-users"></i>Artisans</a></li>
                <li><a href="products.php"><i class="fas fa-box-open"></i>Produits</a></li>
                <li><a href="services.php"><i class="fas fa-cogs"></i>Services</a></li>
                <li><a href="about.php"><i class="fas fa-info-circle"></i>À propos</a></li>
                <li><a href="#" class="vente-flash"><i class="fas fa-bolt"></i>Vente Flash</a></li>
            </ul>
        </nav>
    </header>

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
                    </select>
                </div>

                <div class="dynamic-filters-container" id="dynamicFiltersContainer">
                </div>

                <div class="active-filters" id="activeFilters">
                </div>

                <div class="search-results" id="searchResults"></div>
            </div>
        </div>
    </div>

    <div class="ai-search-container">
        <h1>AI-Powered Deep Search</h1>
        <p>Describe the product you're looking for in detail, and our AI will help find the perfect match.</p>
        
        <textarea class="ai-search-input" placeholder="Describe what you're looking for... (e.g., 'I need a laptop with 16GB RAM, good for gaming, with a large screen')" rows="4"></textarea>
        
        <button id="searchButton" class="cta-button">
            <i class="fas fa-search"></i> Search with AI
        </button>

        <div class="loading">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Analyzing your requirements...</p>
        </div>

        <div class="ai-suggestions"></div>
        <div class="product-recommendations"></div>
    </div>

    <script>
        document.getElementById('searchButton').addEventListener('click', async function() {
            const prompt = document.querySelector('.ai-search-input').value;
            const loading = document.querySelector('.loading');
            const suggestions = document.querySelector('.ai-suggestions');
            const recommendations = document.querySelector('.product-recommendations');

            if (!prompt) {
                alert('Please enter your requirements');
                return;
            }

            loading.style.display = 'block';
            suggestions.innerHTML = '';
            recommendations.innerHTML = '';

            try {
                const response = await fetch('deep_search.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'prompt=' + encodeURIComponent(prompt)
                });

                const data = await response.json();
                
                loading.style.display = 'none';

                suggestions.innerHTML = `
                    <h2>AI Analysis</h2>
                    <div>${data.analysis}</div>
                `;

                data.products.forEach(product => {
                    recommendations.innerHTML += `
                        <div class="product-card">
                            <div class="product-image-container">
                                <img src="${product.image_url}" alt="${product.name}" class="product-image">
                            </div>
                            <div class="product-info">
                                <h3 class="product-title">${product.name}</h3>
                                <p class="product-description">${product.description}</p>
                                <div class="product-price">${product.price} DH</div>
                            </div>
                            <div class="product-actions">
                                <a href="product_details.php?id=${product.id}" class="product-button view-details">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                    `;
                });
            } catch (error) {
                loading.style.display = 'none';
                suggestions.innerHTML = '<p class="error">An error occurred while processing your request.</p>';
            }
        });
    </script>
    <script src="./assets/js/search-hover.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.querySelector('.header');
            let lastScroll = 0;
            
            window.addEventListener('scroll', () => {
                const currentScroll = window.pageYOffset;
                
                if (currentScroll > 100) {
                    header.classList.add('sticky');
                    
                    if (currentScroll > lastScroll) {
                        header.style.transform = 'translateY(-100%)';
                    } else {
                        header.style.transform = 'translateY(0)';
                    }
                } else {
                    header.classList.remove('sticky');
                    header.style.transform = 'translateY(0)';
                }
                
                lastScroll = currentScroll;
            });

            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const navList = document.getElementById('navList');
            
            mobileMenuBtn.addEventListener('click', () => {
                navList.classList.toggle('show');
                mobileMenuBtn.querySelector('i').classList.toggle('fa-bars');
                mobileMenuBtn.querySelector('i').classList.toggle('fa-times');
            });

            const userMenuBtn = document.getElementById('userMenuBtn');
            const userDropdown = document.getElementById('userDropdown');
            
            if (userMenuBtn && userDropdown) {
                userMenuBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    userDropdown.classList.toggle('show');
                });

                document.addEventListener('click', (e) => {
                    if (!userDropdown.contains(e.target) && !userMenuBtn.contains(e.target)) {
                        userDropdown.classList.remove('show');
                    }
                });
            }
        });
    </script>
</body>
</html>