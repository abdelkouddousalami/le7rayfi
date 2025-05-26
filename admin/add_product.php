<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "Access denied. Admin privileges required.";
    header('Location: ../auth.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category'];
    
    $stmt = $conn->prepare("SELECT slug FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    $categorySlug = $category ? $category['slug'] : '';
    
    $ram = null;
    $storage = null;
    $processor = null;
    $camera = null;
    $battery = null;

    if (in_array($categorySlug, ['laptops', 'desktops', 'gaming', 'components'])) {
        $ram = $_POST['ram'] ?? null;
        $storage = $_POST['storage'] ?? null;
        $processor = $_POST['processor'] ?? null;
    } elseif (in_array($categorySlug, ['smartphones', 'tablets'])) {
        $storage = $_POST['phone_storage'] ?? null;
        $camera = $_POST['camera'] ?? null;
        $battery = $_POST['battery'] ?? null;
    }
    
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/products/';
        
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $file_extension;
        $target_path = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $image_url = 'uploads/products/' . $filename;
        }
    }
      try {
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, category_id, image_url, ram, storage, processor, camera, battery) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $stock, $category, $image_url, $ram, $storage, $processor, $camera, $battery]);
        
        $_SESSION['success_message'] = "Produit ajouté avec succès!";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur lors de l'ajout du produit: " . $e->getMessage();
    }
    
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Produit - Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Ajouter un Nouveau Produit</h2>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            <div class="form-group">
                <label for="name">Nom du Produit:</label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="description">Description:</label>
                <textarea name="description" id="description" class="form-control" required></textarea>
            </div>

            <div class="form-group">
                <label for="price">Prix (DH):</label>
                <input type="number" name="price" id="price" class="form-control" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="stock">Stock:</label>
                <input type="number" name="stock" id="stock" class="form-control" required>
            </div>            <div class="form-group">
                <label for="category">Catégorie:</label>
                <select name="category" id="category" class="form-control" required onchange="toggleSpecifications()">
                    <option value="">Sélectionner une catégorie</option>
                    <?php
                    $stmt = $conn->query("SELECT * FROM categories ORDER BY name");
                    while ($cat = $stmt->fetch(PDO::FETCH_ASSOC)):
                    ?>
                    <option value="<?php echo $cat['id']; ?>" 
                            data-slug="<?php echo htmlspecialchars($cat['slug']); ?>">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="image">Image:</label>
                <input type="file" name="image" id="image" class="form-control-file" accept="image/*" required>
            </div>  
            <div id="pcSpecs" class="specs-container">
                <h4 class="specs-title"><i class="fas fa-laptop"></i> Spécifications PC</h4>
                <div class="specs-grid">
                    <div class="form-group">
                        <label for="ram">
                            <i class="fas fa-memory"></i> RAM:
                        </label>
                        <select name="ram" id="ram" class="form-control form-field-animation">
                            <option value="">Sélectionner RAM</option>
                            <option value="8GB">8 GB</option>
                            <option value="16GB">16 GB</option>
                            <option value="32GB">32 GB</option>
                            <option value="64GB">64 GB</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="storage">
                            <i class="fas fa-hdd"></i> Stockage:
                        </label>
                        <select name="storage" id="storage" class="form-control form-field-animation">
                            <option value="">Sélectionner Stockage</option>
                            <option value="256GB">256 GB</option>
                            <option value="512GB">512 GB</option>
                            <option value="1TB">1 TB</option>
                            <option value="2TB">2 TB</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="processor">
                            <i class="fas fa-microchip"></i> Processeur:
                        </label>
                        <select name="processor" id="processor" class="form-control form-field-animation">
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
            </div>

            <!-- Mobile Specifications -->
            <div id="mobileSpecs" class="specs-container">
                <h4 class="specs-title"><i class="fas fa-mobile-alt"></i> Spécifications Mobile</h4>
                <div class="specs-grid">
                    <div class="form-group">
                        <label for="phone_storage">
                            <i class="fas fa-hdd"></i> Stockage:
                        </label>
                        <select name="phone_storage" id="phone_storage" class="form-control form-field-animation">
                            <option value="">Sélectionner Stockage</option>
                            <option value="128GB">128 GB</option>
                            <option value="256GB">256 GB</option>
                            <option value="512GB">512 GB</option>
                            <option value="1TB">1 TB</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="camera">
                            <i class="fas fa-camera"></i> Caméra:
                        </label>
                        <select name="camera" id="camera" class="form-control form-field-animation">
                            <option value="">Sélectionner Caméra</option>
                            <option value="12MP">12 MP</option>
                            <option value="48MP">48 MP</option>
                            <option value="50MP">50 MP</option>
                            <option value="108MP">108 MP</option>
                            <option value="200MP">200 MP</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="battery">
                            <i class="fas fa-battery-full"></i> Batterie:
                        </label>
                        <select name="battery" id="battery" class="form-control form-field-animation">
                            <option value="">Sélectionner Batterie</option>
                            <option value="3000mAh">3000 mAh</option>
                            <option value="4000mAh">4000 mAh</option>
                            <option value="4500mAh">4500 mAh</option>
                        <option value="5000mAh">5000 mAh</option>
                        <option value="5400mAh">5400 mAh</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Ajouter le Produit</button>
        </form>
    </div>    
    
    <script>
        function toggleSpecifications() {
            const categorySelect = document.getElementById('category');
            const selectedOption = categorySelect.options[categorySelect.selectedIndex];
            const categorySlug = selectedOption ? selectedOption.getAttribute('data-slug') : '';
            
            const pcSpecs = document.getElementById('pcSpecs');
            const mobileSpecs = document.getElementById('mobileSpecs');
            
            [pcSpecs, mobileSpecs].forEach(container => {
                if (container.classList.contains('show')) {
                    container.classList.remove('show');
                    setTimeout(() => {
                        container.style.display = 'none';
                    }, 300);
                }
            });
            
            const allFields = ['ram', 'storage', 'processor', 'phone_storage', 'camera', 'battery'];
            allFields.forEach(field => {
                const element = document.getElementById(field);
                if (element) {
                    element.required = false;
                    element.classList.remove('highlight');
                }
            });
            
            let targetSpecs = null;
            let requiredFields = [];
            
            if (['laptops', 'desktops', 'gaming', 'components'].includes(categorySlug)) {
                targetSpecs = pcSpecs;
                requiredFields = ['ram', 'storage', 'processor'];
            } else if (['smartphones', 'tablets'].includes(categorySlug)) {
                targetSpecs = mobileSpecs;
                requiredFields = ['phone_storage', 'camera', 'battery'];
            }
            
            if (targetSpecs) {
                setTimeout(() => {
                    targetSpecs.style.display = 'block';
                    void targetSpecs.offsetWidth;
                    targetSpecs.classList.add('show');
                    
                    requiredFields.forEach((field, index) => {
                        setTimeout(() => {
                            const element = document.getElementById(field);
                            element.required = true;
                            element.classList.add('highlight');
                            setTimeout(() => {
                                element.classList.remove('highlight');
                            }, 1000);
                        }, index * 200);
                    });
                }, 300);
            }
        }

        
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>