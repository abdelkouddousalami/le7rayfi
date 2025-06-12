<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$conn = getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = $_POST['name'];
                $description = $_POST['description'];
                $price = $_POST['price'];
                $stock = $_POST['stock'];
                $category = $_POST['category'];
                $storage = $_POST['storage'] ?? null;
                $ram = $_POST['ram'] ?? null;
                $processor = $_POST['processor'] ?? null;
                $camera = $_POST['camera'] ?? null;
                $battery = $_POST['battery'] ?? null;
                $discount = $_POST['discount'];
                $screen_size = $_POST['screen_size'] ?? null;
                $os = $_POST['os'] ?? null;
                $color = $_POST['color'] ?? null;
                $network = $_POST['network'] ?? null;
                $graphics_card = $_POST['graphics_card'] ?? null;
                $brand = $_POST['brand'] ?? null;
                $model = $_POST['model'] ?? null;

                $image_url = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $upload_dir = '../uploads/products/';
                    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $file_name = uniqid() . '.' . $file_extension;
                    $target_path = $upload_dir . $file_name;

                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                        $image_url = 'uploads/products/' . $file_name;
                    }
                }

                try {
                    $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, category_id, image_url, 
                                                        storage, ram, processor, camera, battery, screen_size, os, color, network, graphics_card, brand, model, discount) 
                                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $description, $price, $stock, $category, $image_url, 
                                                $storage, $ram, $processor, $camera, $battery, $screen_size, 
                                                $os, $color, $network, $graphics_card, $brand, $model, $discount]);
                    
                    $_SESSION['success_message'] = "Product added successfully!";
                } catch (PDOException $e) {
                    $_SESSION['error_message'] = "Error adding product: " . $e->getMessage();
                }
                break;

            case 'edit':
                $id = $_POST['product_id'];
                $name = $_POST['name'];
                $description = $_POST['description'];
                $price = $_POST['price'];
                $stock = $_POST['stock'];
                $category = $_POST['category'];
                $storage = $_POST['storage'] ?? null;
                $ram = $_POST['ram'] ?? null;
                $processor = $_POST['processor'] ?? null;
                $camera = $_POST['camera'] ?? null;
                $battery = $_POST['battery'] ?? null;
                $discount = $_POST['discount'];
                $screen_size = $_POST['screen_size'] ?? null;
                $os = $_POST['os'] ?? null;
                $color = $_POST['color'] ?? null;
                $network = $_POST['network'] ?? null;
                $graphics_card = $_POST['graphics_card'] ?? null;
                $brand = $_POST['brand'] ?? null;
                $model = $_POST['model'] ?? null;

                $update_fields = [
                    'name' => $name,
                    'description' => $description,
                    'price' => $price,
                    'stock' => $stock,
                    'category_id' => $category,
                    'storage' => $storage,
                    'ram' => $ram,
                    'processor' => $processor,
                    'camera' => $camera,
                    'battery' => $battery,
                    'screen_size' => $_POST['screen_size'] ?? null,
                    'os' => $_POST['os'] ?? null,
                    'color' => $_POST['color'] ?? null,
                    'network' => $_POST['network'] ?? null,
                    'graphics_card' => $_POST['graphics_card'] ?? null,
                    'brand' => $_POST['brand'] ?? null,
                    'model' => $_POST['model'] ?? null,
                    'discount' => $discount
                ];

                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $upload_dir = '../uploads/products/';
                    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $file_name = uniqid() . '.' . $file_extension;
                    $target_path = $upload_dir . $file_name;

                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                        $update_fields['image_url'] = 'uploads/products/' . $file_name;
                    }
                }

                try {
                    $sql = "UPDATE products SET " . implode(" = ?, ", array_keys($update_fields)) . " = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([...array_values($update_fields), $id]);
                    
                    $_SESSION['success_message'] = "Product updated successfully!";
                } catch (PDOException $e) {
                    $_SESSION['error_message'] = "Error updating product: " . $e->getMessage();
                }
                break;

            case 'delete':
                try {
                    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
                    $stmt->execute([$_POST['product_id']]);
                    $_SESSION['success_message'] = "Product deleted successfully!";
                } catch (PDOException $e) {
                    $_SESSION['error_message'] = "Error deleting product: " . $e->getMessage();
                }
                break;
        }
        header('Location: products.php');
        exit();
    }
}

$stmt = $conn->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->query("SELECT p.*, c.name as category_name 
                     FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     ORDER BY p.created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 d-none d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="products.php">
                                <i class="fas fa-box"></i> Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../index.php">
                                <i class="fas fa-store"></i> View Store
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Manage Products</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="fas fa-plus"></i> Add New Product
                    </button>
                </div>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success">
                        <?php 
                        echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger">
                        <?php 
                        echo $_SESSION['error_message'];
                        unset($_SESSION['error_message']);
                        ?>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Discount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <img src="../<?php echo $product['image_url']; ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                         style="width: 50px; height: 50px; object-fit: cover;">
                                </td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                <td><?php echo number_format($product['price'], 2); ?> DH</td>
                                <td><?php echo $product['stock']; ?></td>
                                <td><?php echo $product['discount']; ?>%</td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="products.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required-field">Name</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required-field">Category</label>
                                <select class="form-select" name="category" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required-field">Price (DH)</label>
                                <input type="number" class="form-control" name="price" required min="0" step="0.01">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required-field">Stock</label>
                                <input type="number" class="form-control" name="stock" required min="0">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3" data-field="brand">
                                <label class="form-label">Marque</label>
                                <input type="text" class="form-control" name="brand" placeholder="ex: Apple, Samsung, Toshiba, MSI...">
                            </div>
                            <div class="col-md-6 mb-3" data-field="model">
                                <label class="form-label">Modèle</label>
                                <input type="text" class="form-control" name="model" placeholder="ex: MacBook Pro M2, Galaxy S24...">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3 spec-field" data-field="ram" style="display: none;">
                                <label class="form-label">RAM</label>
                                <input type="text" class="form-control" name="ram" placeholder="ex: 8GB, 16GB, 32GB...">
                            </div>
                            <div class="col-md-6 mb-3 spec-field" data-field="storage" style="display: none;">
                                <label class="form-label">Stockage</label>
                                <input type="text" class="form-control" name="storage" placeholder="ex: 512GB SSD, 1TB HDD...">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3 spec-field" data-field="processor" style="display: none;">
                                <label class="form-label">Processeur</label>
                                <input type="text" class="form-control" name="processor" placeholder="ex: Intel i7-12700H, AMD Ryzen 7...">
                            </div>
                            <div class="col-md-6 mb-3 spec-field" data-field="graphics_card" style="display: none;">
                                <label class="form-label">Carte Graphique</label>
                                <input type="text" class="form-control" name="graphics_card" placeholder="ex: NVIDIA RTX 4060, Intégrée...">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3 spec-field" data-field="camera" style="display: none;">
                                <label class="form-label">Appareil Photo</label>
                                <input type="text" class="form-control" name="camera" placeholder="ex: 50MP, Triple 50MP+12MP+8MP...">
                            </div>
                            <div class="col-md-6 mb-3 spec-field" data-field="battery" style="display: none;">
                                <label class="form-label">Batterie</label>
                                <input type="text" class="form-control" name="battery" placeholder="ex: 5000mAh, 4500mAh...">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3 spec-field" data-field="screen_size" style="display: none;">
                                <label class="form-label">Taille Écran</label>
                                <input type="text" class="form-control" name="screen_size" placeholder="ex: 6.1 pouces, 15.6 pouces...">
                            </div>
                            <div class="col-md-6 mb-3 spec-field" data-field="os" style="display: none;">
                                <label class="form-label">Système d'exploitation</label>
                                <input type="text" class="form-control" name="os" placeholder="ex: Windows 11, iOS 17, Android 14...">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3 spec-field" data-field="color" style="display: none;">
                                <label class="form-label">Couleur</label>
                                <input type="text" class="form-control" name="color" placeholder="ex: Noir, Blanc, Bleu Océan...">
                            </div>
                            <div class="col-md-6 mb-3 spec-field" data-field="network" style="display: none;">
                                <label class="form-label">Réseau</label>
                                <input type="text" class="form-control" name="network" placeholder="ex: 5G, 4G, WiFi seulement...">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Discount (%)</label>
                                <input type="number" class="form-control" name="discount" value="0" min="0" max="100">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required-field">Product Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="products.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="product_id" id="edit_product_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required-field">Name</label>
                                <input type="text" class="form-control" name="name" id="edit_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required-field">Category</label>
                                <select class="form-select" name="category" id="edit_category" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required-field">Price (DH)</label>
                                <input type="number" class="form-control" name="price" id="edit_price" required min="0" step="0.01">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required-field">Stock</label>
                                <input type="number" class="form-control" name="stock" id="edit_stock" required min="0">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3" data-field="brand">
                                <label class="form-label">Marque</label>
                                <input type="text" class="form-control" name="brand" id="edit_brand" placeholder="ex: Apple, Samsung, Toshiba, MSI...">
                            </div>
                            <div class="col-md-6 mb-3" data-field="model">
                                <label class="form-label">Modèle</label>
                                <input type="text" class="form-control" name="model" id="edit_model" placeholder="ex: MacBook Pro M2, Galaxy S24...">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3 spec-field" data-field="ram" style="display: none;">
                                <label class="form-label">RAM</label>
                                <input type="text" class="form-control" name="ram" id="edit_ram" placeholder="ex: 8GB, 16GB, 32GB...">
                            </div>
                            <div class="col-md-6 mb-3 spec-field" data-field="storage" style="display: none;">
                                <label class="form-label">Stockage</label>
                                <input type="text" class="form-control" name="storage" id="edit_storage" placeholder="ex: 512GB SSD, 1TB HDD...">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3 spec-field" data-field="processor" style="display: none;">
                                <label class="form-label">Processeur</label>
                                <input type="text" class="form-control" name="processor" id="edit_processor" placeholder="ex: Intel i7-12700H, AMD Ryzen 7...">
                            </div>
                            <div class="col-md-6 mb-3 spec-field" data-field="graphics_card" style="display: none;">
                                <label class="form-label">Carte Graphique</label>
                                <input type="text" class="form-control" name="graphics_card" id="edit_graphics_card" placeholder="ex: NVIDIA RTX 4060, Intégrée...">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3 spec-field" data-field="camera" style="display: none;">
                                <label class="form-label">Appareil Photo</label>
                                <input type="text" class="form-control" name="camera" id="edit_camera" placeholder="ex: 50MP, Triple 50MP+12MP+8MP...">
                            </div>
                            <div class="col-md-6 mb-3 spec-field" data-field="battery" style="display: none;">
                                <label class="form-label">Batterie</label>
                                <input type="text" class="form-control" name="battery" id="edit_battery" placeholder="ex: 5000mAh, 4500mAh...">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3 spec-field" data-field="screen_size" style="display: none;">
                                <label class="form-label">Taille Écran</label>
                                <input type="text" class="form-control" name="screen_size" id="edit_screen_size" placeholder="ex: 6.1 pouces, 15.6 pouces...">
                            </div>
                            <div class="col-md-6 mb-3 spec-field" data-field="os" style="display: none;">
                                <label class="form-label">Système d'exploitation</label>
                                <input type="text" class="form-control" name="os" id="edit_os" placeholder="ex: Windows 11, iOS 17, Android 14...">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3 spec-field" data-field="color" style="display: none;">
                                <label class="form-label">Couleur</label>
                                <input type="text" class="form-control" name="color" id="edit_color" placeholder="ex: Noir, Blanc, Bleu Océan...">
                            </div>
                            <div class="col-md-6 mb-3 spec-field" data-field="network" style="display: none;">
                                <label class="form-label">Réseau</label>
                                <input type="text" class="form-control" name="network" id="edit_network" placeholder="ex: 5G, 4G, WiFi seulement...">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Discount (%)</label>
                                <input type="number" class="form-control" name="discount" id="edit_discount" min="0" max="100">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Product Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                            <small class="form-text text-muted">Leave empty to keep the current image</small>
                        </div>
                        <div id="current_image" class="mb-3 text-center">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editProduct(product) {
            document.getElementById('edit_product_id').value = product.id;
            document.getElementById('edit_name').value = product.name;
            document.getElementById('edit_category').value = product.category;
            document.getElementById('edit_description').value = product.description;
            document.getElementById('edit_price').value = product.price;
            document.getElementById('edit_stock').value = product.stock;
            document.getElementById('edit_storage').value = product.storage;
            document.getElementById('edit_ram').value = product.ram;
            document.getElementById('edit_processor').value = product.processor;
            document.getElementById('edit_camera').value = product.camera;
            document.getElementById('edit_battery').value = product.battery;
            document.getElementById('edit_discount').value = product.discount;

            const currentImageDiv = document.getElementById('current_image');
            if (product.image_url) {
                currentImageDiv.innerHTML = `
                    <img src="../${product.image_url}" alt="${product.name}" style="max-height: 200px;">
                    <p class="mt-2">Current image</p>
                `;
            } else {
                currentImageDiv.innerHTML = '<p>No current image</p>';
            }
            
            const modal = new bootstrap.Modal(document.getElementById('editProductModal'));
            modal.show();
        }
    </script>


    <script>
        const categoryFields = {
            'smartphones': ['camera', 'battery', 'screen_size', 'os', 'color', 'network','storage'],
            'tablets': ['camera', 'battery', 'screen_size', 'os', 'color', 'network', 'storage'],
            'mobile': ['camera', 'battery', 'screen_size', 'os', 'color', 'network','storage'],
            'tablettes': ['camera', 'battery', 'screen_size', 'os', 'color', 'network', 'storage'],
            
            'laptops': ['ram', 'storage', 'processor', 'graphics_card', 'screen_size', 'os', 'color'],
            'ordinateurs': ['ram', 'storage', 'processor', 'graphics_card', 'screen_size', 'os', 'color'],
            'pc': ['ram', 'storage', 'processor', 'graphics_card', 'os'],
            'desktop': ['ram', 'storage', 'processor', 'graphics_card', 'os'],
            'bureautique': ['ram', 'storage', 'processor', 'graphics_card', 'os'],
            
            'gaming': ['ram', 'storage', 'processor', 'graphics_card', 'os'],
            'games': ['ram', 'storage', 'processor', 'graphics_card', 'os'],
            'jeux': ['ram', 'storage', 'processor', 'graphics_card', 'os'],
            
            'accessories': ['color'],
            'accessoires': ['color'],
            'casques': ['color'],
            'ecouteurs': ['color', 'battery'],
            
            'monitors': ['screen_size', 'color'],
            'ecrans': ['screen_size', 'color'],
            'tv': ['screen_size', 'color', 'os']
        };

        function toggleFields(categoryName, modalType = 'add') {
            const prefix = modalType === 'edit' ? '#editProductModal ' : '#addProductModal ';
            const allSpecFields = document.querySelectorAll(prefix + '.spec-field');
            
            allSpecFields.forEach(field => {
                field.style.display = 'none';
                const input = field.querySelector('input');
                if (input && modalType === 'add') {
                    input.value = '';
                }
            });

            if (!categoryName) return;

            const categoryKey = categoryName.toLowerCase();
            let fieldsToShow = [];
            
            for (const [key, fields] of Object.entries(categoryFields)) {
                if (categoryKey.includes(key) || key.includes(categoryKey)) {
                    fieldsToShow = fields;
                    break;
                }
            }

            fieldsToShow.forEach(fieldName => {
                const field = document.querySelector(prefix + `.spec-field[data-field="${fieldName}"]`);
                if (field) {
                    field.style.display = 'block';
                }
            });

            console.log(`Catégorie: ${categoryName} → Champs affichés: ${fieldsToShow.join(', ')}`);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const addCategorySelect = document.querySelector('#addProductModal select[name="category"]');
            if (addCategorySelect) {
                addCategorySelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const categoryName = selectedOption.text;
                    toggleFields(categoryName, 'add');
                });
            }

            const editCategorySelect = document.querySelector('#editProductModal select[name="category"]');
            if (editCategorySelect) {
                editCategorySelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const categoryName = selectedOption.text;
                    toggleFields(categoryName, 'edit');
                });
            }
        });

        function editProduct(product) {
            document.getElementById('edit_product_id').value = product.id;
            document.getElementById('edit_name').value = product.name;
            document.getElementById('edit_category').value = product.category_id;
            document.getElementById('edit_description').value = product.description;
            document.getElementById('edit_price').value = product.price;
            document.getElementById('edit_stock').value = product.stock;
            document.getElementById('edit_storage').value = product.storage || '';
            document.getElementById('edit_ram').value = product.ram || '';
            document.getElementById('edit_processor').value = product.processor || '';
            document.getElementById('edit_camera').value = product.camera || '';
            document.getElementById('edit_battery').value = product.battery || '';
            document.getElementById('edit_discount').value = product.discount;

            const editFields = ['screen_size', 'os', 'color', 'network', 'graphics_card', 'brand', 'model'];
            editFields.forEach(field => {
                const element = document.getElementById(`edit_${field}`);
                if (element) {
                    element.value = product[field] || '';
                }
            });

            const categorySelect = document.getElementById('edit_category');
            const selectedOption = categorySelect.options[categorySelect.selectedIndex];
            if (selectedOption && selectedOption.text) {
                setTimeout(() => {
                    toggleFields(selectedOption.text, 'edit');
                }, 100);
            }

            const currentImageDiv = document.getElementById('current_image');
            if (product.image_url) {
                currentImageDiv.innerHTML = `
                    <img src="../${product.image_url}" alt="${product.name}" style="max-height: 200px;">
                    <p class="mt-2">Current image</p>
                `;
            } else {
                currentImageDiv.innerHTML = '<p>No current image</p>';
            }
            
            const modal = new bootstrap.Modal(document.getElementById('editProductModal'));
            modal.show();
        }
    </script>

</body>
</html>