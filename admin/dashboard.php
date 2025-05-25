<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "Access denied. Admin privileges required.";
    header('Location: ../login.php');
    exit();
}
$stmt = $conn->query("SELECT COUNT(*) as total_products FROM products");
$total_products = $stmt->fetch()['total_products'];
$stmt = $conn->query("SELECT COUNT(*) as total_orders FROM orders");
$total_orders = $stmt->fetch()['total_orders'];
$stmt = $conn->query("SELECT SUM(total_amount) as total_revenue FROM orders WHERE status = 'completed'");
$total_revenue = $stmt->fetch()['total_revenue'] ?? 0;
$stmt = $conn->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
           SUM(total_amount) as revenue
    FROM orders 
    WHERE status = 'completed'
    GROUP BY month 
    ORDER BY month DESC 
    LIMIT 6
");
$monthly_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - HA GROUP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --warning-color: #f72585;
            --info-color: #4895ef;
            --dark-color: #1e2a3a;
            --light-color: #f8f9fa;
            --transition: all 0.3s ease;
            --border-radius: 15px;
            --box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            --gradient-primary: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            --gradient-light: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
            --card-border: 1px solid rgba(67, 97, 238, 0.1);
            --backdrop-blur: blur(10px);
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            color: var(--dark-color);
            line-height: 1.6;
            min-height: 100vh;
        }
        .dashboard-container {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }
        .dashboard-header {
            background: var(--gradient-light);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: slideDown 0.5s ease-out;
            border: var(--card-border);
            backdrop-filter: var(--backdrop-blur);
            -webkit-backdrop-filter: var(--backdrop-blur);
        }
        .welcome-text {
            font-size: 2rem;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
            font-weight: 700;
            background: var(--gradient-primary);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        .date-time {
            color: #6c757d;
            font-size: 1rem;
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        .home-btn {
            background: var(--gradient-primary);
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
            font-weight: 500;
            border: none;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.2);
        }
        .home-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.3);
            filter: brightness(1.1);
        }
        .chart-container {
            background: var(--gradient-light);
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
            border: var(--card-border);
            animation: fadeIn 0.5s ease-out;
            backdrop-filter: var(--backdrop-blur);
            -webkit-backdrop-filter: var(--backdrop-blur);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: var(--gradient-light);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            display: flex;
            align-items: center;
            gap: 1.5rem;
            transition: var(--transition);
            border: var(--card-border);
            animation: fadeIn 0.5s ease-out;
            position: relative;
            overflow: hidden;
            backdrop-filter: var(--backdrop-blur);
            -webkit-backdrop-filter: var(--backdrop-blur);
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.05) 0%, rgba(67, 97, 238, 0) 100%);
            z-index: 1;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.2);
        }
        .stat-icon {
            width: 64px;
            height: 64px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.1) 0%, rgba(67, 97, 238, 0.05) 100%);
            z-index: 2;
            position: relative;
            transition: var(--transition);
        }
        .stat-card:hover .stat-icon {
            transform: scale(1.1) rotate(5deg);
        }
        .stat-info {
            z-index: 2;
            position: relative;
        }
        .stat-info h3 {
            font-size: 1rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        .stat-info p {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0;
            background: var(--gradient-primary);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .products-section {
            background: var(--gradient-light);
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: var(--card-border);
            animation: fadeIn 0.5s ease-out;
            backdrop-filter: var(--backdrop-blur);
            -webkit-backdrop-filter: var(--backdrop-blur);
        }
        .products-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(67, 97, 238, 0.1);
        }
        .products-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            background: var(--gradient-primary);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .add-product-btn {
            background: linear-gradient(135deg, var(--success-color) 0%, #3da8d9 100%);
            color: white;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
            font-weight: 500;
            box-shadow: 0 4px 15px rgba(76, 201, 240, 0.2);
        }
        .add-product-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(76, 201, 240, 0.3);
            filter: brightness(1.1);
        }
        .products-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 1rem;
        }
        .products-table th,
        .products-table td {
            padding: 1.2rem;
            text-align: left;
        }
        .products-table th {
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.1) 0%, rgba(67, 97, 238, 0.05) 100%);
            font-weight: 600;
            color: var(--dark-color);
            position: sticky;
            top: 0;
            border-bottom: 2px solid rgba(67, 97, 238, 0.1);
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
            backdrop-filter: var(--backdrop-blur);
            -webkit-backdrop-filter: var(--backdrop-blur);
        }
        .products-table tr {
            transition: var(--transition);
            border-bottom: 1px solid rgba(67, 97, 238, 0.05);
        }
        .products-table tr:hover {
            background: rgba(67, 97, 238, 0.02);
            transform: translateX(5px);
            cursor: pointer;
        }
        .spec-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.1) 0%, rgba(67, 97, 238, 0.05) 100%);
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            margin: 0.2rem;
            color: var(--dark-color);
            white-space: nowrap;
            border: var(--card-border);
            transition: var(--transition);
            cursor: default;
            backdrop-filter: var(--backdrop-blur);
            -webkit-backdrop-filter: var(--backdrop-blur);
        }
        .spec-badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.15);
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.15) 0%, rgba(67, 97, 238, 0.1) 100%);
        }
        .spec-badge i {
            color: var(--primary-color);
            font-size: 0.9rem;
        }
        .specs-column {
            max-width: 300px;
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            align-items: center;
        }
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
            transition: var(--transition);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .product-image:hover {
            transform: scale(1.15) rotate(3deg);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            opacity: 0.7;
            transition: var(--transition);
        }
        tr:hover .action-buttons {
            opacity: 1;
        }
        .action-btn {
            padding: 0.6rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .edit-btn {
            background: linear-gradient(135deg, var(--info-color) 0%, #3d7ec9 100%);
            color: white;
        }
        .delete-btn {
            background: linear-gradient(135deg, var(--warning-color) 0%, #d91a6b 100%);
            color: white;
        }
        .edit-btn:hover,
        .delete-btn:hover {
            transform: translateY(-2px) scale(1.1);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            filter: brightness(1.1);
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .form-group {
            position: relative;
            margin-bottom: 1.2rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
            font-weight: 500;
            font-size: 0.9rem;
            transition: var(--transition);
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid rgba(67, 97, 238, 0.15);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
            transition: var(--transition);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            color: var(--dark-color);
        }
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.1);
            outline: none;
            background: white;
        }
        .form-group input:hover,
        .form-group select:hover,
        .form-group textarea:hover {
            border-color: rgba(67, 97, 238, 0.3);
            background: white;
        }
        .form-group select {
            appearance: none;
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1rem;
            padding-right: 2.5rem;
        }
        .form-group input[type="file"] {
            padding: 0.6rem;
            font-size: 0.9rem;
            cursor: pointer;
        }
        .form-group input[type="file"]::file-selector-button {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: none;
            background: var(--gradient-primary);
            color: white;
            margin-right: 1rem;
            transition: var(--transition);
            cursor: pointer;
        }
        .form-group input[type="file"]::file-selector-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.2);
        }
        .specs-container {
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.05) 0%, rgba(67, 97, 238, 0.02) 100%);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin-top: 1rem;
            border: var(--card-border);
            animation: fadeIn 0.5s ease-out;
        }
        .specs-container h2 {
            font-size: 1.1rem;
            color: var(--dark-color);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(67, 97, 238, 0.1);
        }
        .product-form {
            background: var(--gradient-light);
            padding: 2rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            border: var(--card-border);
            display: none;
            animation: slideDown 0.3s ease-out;
            backdrop-filter: var(--backdrop-blur);
            -webkit-backdrop-filter: var(--backdrop-blur);
        }
        .product-form.active {
            display: block;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.98) translateY(10px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
        @keyframes slideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem;
            }
            .dashboard-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            .welcome-text {
                font-size: 1.5rem;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .products-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            .products-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
                -webkit-overflow-scrolling: touch;
            }
            .products-table td,
            .products-table th {
                padding: 0.8rem;
            }
            .stat-card {
                padding: 1rem;
            }
            .stat-icon {
                width: 50px;
                height: 50px;
                font-size: 1.4rem;
            }
            .stat-info p {
                font-size: 1.5rem;
            }
            .add-product-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div>
                <h1 class="welcome-text">Tableau de Bord Admin</h1>
                <p class="date-time"><?php echo date('l, d F Y'); ?></p>
            </div>
            <a href="../index.php" class="home-btn">
                <i class="fas fa-home"></i> Retour au site
            </a>
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Produits</h3>
                    <p><?php echo $total_products; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Commandes</h3>
                    <p><?php echo $total_orders; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-info">
                    <h3>Revenu Total</h3>
                    <p><?php echo number_format($total_revenue, 2); ?> DH</p>
                </div>
            </div>
        </div>
        <div class="chart-container">
            <canvas id="salesChart"></canvas>
        </div>
        <div class="products-section">
            <div class="products-header">
                <h2>Gestion des Produits</h2>
                <button class="add-product-btn" onclick="toggleProductForm()">
                    <i class="fas fa-plus"></i> Ajouter un Produit
                </button>
            </div>
            <div class="product-form" id="productForm">
                <form action="add_product.php" method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name">Nom du Produit</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="category">Catégorie</label>
                            <select id="category" name="category" required>
                                <option value="">Sélectionner une catégorie</option>
                                <option value="laptop">Ordinateurs Portables</option>
                                <option value="desktop">Ordinateurs Fixes</option>
                                <option value="smartphone">Smartphones</option>
                                <option value="tablet">Tablettes</option>
                                <option value="accessory">Accessoires</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="price">Prix (DH)</label>
                            <input type="number" id="price" name="price" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="stock">Stock</label>
                            <input type="number" id="stock" name="stock" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="4" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="image">Image</label>
                            <input type="file" id="image" name="image" accept="image/*" required>
                        </div>
                        <!-- PC/Laptop Specifications -->
                        <div id="pcSpecs" class="specs-container" style="display: none;">
                            <div class="form-group">
                                <label for="ram">RAM</label>
                                <select name="ram" id="ram">
                                    <option value="">Sélectionner RAM</option>
                                    <option value="8GB">8 GB</option>
                                    <option value="16GB">16 GB</option>
                                    <option value="32GB">32 GB</option>
                                    <option value="64GB">64 GB</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="storage">Stockage</label>
                                <select name="storage" id="storage">
                                    <option value="">Sélectionner Stockage</option>
                                    <option value="256GB">256 GB</option>
                                    <option value="512GB">512 GB</option>
                                    <option value="1TB">1 TB</option>
                                    <option value="2TB">2 TB</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="processor">Processeur</label>
                                <select name="processor" id="processor">
                                    <option value="">Sélectionner Processeur</option>
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
                        </div>
                        <!-- Mobile Specifications -->
                        <div id="mobileSpecs" class="specs-container" style="display: none;">
                            <div class="form-group">
                                <label for="phone_storage">Stockage</label>
                                <select name="phone_storage" id="phone_storage">
                                    <option value="">Sélectionner Stockage</option>
                                    <option value="128GB">128 GB</option>
                                    <option value="256GB">256 GB</option>
                                    <option value="512GB">512 GB</option>
                                    <option value="1TB">1 TB</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="camera">Caméra</label>
                                <select name="camera" id="camera">
                                    <option value="">Sélectionner Caméra</option>
                                    <option value="12MP">12 MP</option>
                                    <option value="48MP">48 MP</option>
                                    <option value="50MP">50 MP</option>
                                    <option value="108MP">108 MP</option>
                                    <option value="200MP">200 MP</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="battery">Batterie</label>
                                <select name="battery" id="battery">
                                    <option value="">Sélectionner Batterie</option>
                                    <option value="3000mAh">3000 mAh</option>
                                    <option value="4000mAh">4000 mAh</option>
                                    <option value="4500mAh">4500 mAh</option>
                                    <option value="5000mAh">5000 mAh</option>
                                    <option value="5400mAh">5400 mAh</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="add-product-btn">
                        <i class="fas fa-save"></i> Enregistrer le Produit
                    </button>
                </form>
            </div>
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Nom</th>
                        <th>Catégorie</th>
                        <th>Prix</th>
                        <th>Stock</th>
                        <th>Spécifications</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
                    while ($product = $stmt->fetch(PDO::FETCH_ASSOC)):
                    ?>
                    <tr>
                        <td>
                            <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 class="product-image">
                        </td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['category']); ?></td>
                        <td><?php echo number_format($product['price'], 2); ?> DH</td>
                        <td><?php echo $product['stock']; ?></td>
                        <td class="specs-column">
                            <?php if ($product['category'] === 'pc' || $product['category'] === 'laptop'): ?>
                                <?php if ($product['ram']): ?>
                                    <span class="spec-badge">
                                        <i class="fas fa-memory"></i> <?php echo htmlspecialchars($product['ram']); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($product['storage']): ?>
                                    <span class="spec-badge">
                                        <i class="fas fa-hdd"></i> <?php echo htmlspecialchars($product['storage']); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($product['processor']): ?>
                                    <span class="spec-badge">
                                        <i class="fas fa-microchip"></i> <?php echo htmlspecialchars($product['processor']); ?>
                                    </span>
                                <?php endif; ?>
                            <?php elseif ($product['category'] === 'mobile' || $product['category'] === 'smartphone'): ?>
                                <?php if ($product['storage']): ?>
                                    <span class="spec-badge">
                                        <i class="fas fa-hdd"></i> <?php echo htmlspecialchars($product['storage']); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($product['camera']): ?>
                                    <span class="spec-badge">
                                        <i class="fas fa-camera"></i> <?php echo htmlspecialchars($product['camera']); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($product['battery']): ?>
                                    <span class="spec-badge">
                                        <i class="fas fa-battery-full"></i> <?php echo htmlspecialchars($product['battery']); ?>
                                    </span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn edit-btn" onclick="editProduct(<?php echo $product['id']; ?>)" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn delete-btn" onclick="deleteProduct(<?php echo $product['id']; ?>)" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        const salesData = <?php echo json_encode(array_reverse($monthly_sales)); ?>;
        const months = salesData.map(item => item.month);
        const revenue = salesData.map(item => parseFloat(item.revenue));
        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Revenus Mensuels (DH)',
                    data: revenue,
                    borderColor: '#4361ee',
                    backgroundColor: 'rgba(67, 97, 238, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#4361ee',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Évolution des Ventes sur 6 Mois',
                        font: {
                            size: 16,
                            weight: '600'
                        },
                        padding: 20
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            font: {
                                size: 12
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value + ' DH';
                            },
                            font: {
                                size: 12
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                animation: {
                    duration: 1000
                }
            }
        });
        const categorySelect = document.getElementById('category');
        const pcSpecs = document.getElementById('pcSpecs');
        const mobileSpecs = document.getElementById('mobileSpecs');
        function handleCategoryChange() {
            const category = categorySelect.value;
            pcSpecs.style.display = 'none';
            mobileSpecs.style.display = 'none';
            if (category === 'laptop' || category === 'pc' || category === 'desktop') {
                pcSpecs.style.display = 'block';
                document.getElementById('ram').required = true;
                document.getElementById('storage').required = true;
                document.getElementById('processor').required = true;
                document.getElementById('phone_storage').required = false;
                document.getElementById('camera').required = false;
                document.getElementById('battery').required = false;
            } else if (category === 'mobile' || category === 'smartphone' || category === 'tablet') {
                mobileSpecs.style.display = 'block';
                document.getElementById('phone_storage').required = true;
                document.getElementById('camera').required = true;
                document.getElementById('battery').required = true;
                document.getElementById('ram').required = false;
                document.getElementById('storage').required = false;
                document.getElementById('processor').required = false;
            }
        }
        categorySelect.addEventListener('change', handleCategoryChange);
        handleCategoryChange();
        function toggleProductForm() {
            const form = document.getElementById('productForm');
            form.classList.toggle('active');
        }
        function editProduct(id) {
            window.location.href = `edit_product.php?id=${id}`;
        }
        function deleteProduct(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')) {
                window.location.href = `delete_product.php?id=${id}`;
            }
        }
    </script>
</body>
</html>