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
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    
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