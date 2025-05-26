<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "Access denied. Admin privileges required.";
    header('Location: ../auth.php');
    exit();
}
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = $_POST['category'];
    $stmt = $conn->prepare("SELECT image_url FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    $image_url = $product['image_url'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/products/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        if ($image_url && file_exists('../' . $image_url)) {
            unlink('../' . $image_url);
        }
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $file_extension;
        $target_path = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $image_url = 'uploads/products/' . $filename;
        }
    }
    try {
        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category = ?, image_url = ? WHERE id = ?");
        $stmt->execute([$name, $description, $price, $stock, $category, $image_url, $id]);
        $_SESSION['success_message'] = "Produit mis à jour avec succès!";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur lors de la mise à jour du produit: " . $e->getMessage();
    }
    header('Location: dashboard.php');
    exit();
}
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Produit - HA GROUP</title>
    <link rel="stylesheet" href="../index.css">
    <style>
        .edit-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .edit-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .back-btn {
            background: #7f8c8d;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .current-image {
            max-width: 200px;
            margin: 10px 0;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .form-group textarea {
            height: 120px;
        }
        .submit-btn {
            background: #3498db;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .submit-btn:hover {
            background: #2980b9;
        }
        .specs-container {
            grid-column: span 2;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .specs-container h2 {
            margin-bottom: 15px;
            font-size: 1.5em;
            color: #34495e;
        }
        .form-group.pc-specs,
        .form-group.mobile-specs {
            display: none;
            grid-column: span 2;
        }
    </style>
</head>
<body>
    <div class="edit-container">
        <div class="edit-header">
            <h1>Modifier Produit</h1>
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Nom du Produit</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="category">Catégorie</label>
                    <select id="category" name="category" required>
                        <option value="">Sélectionner une catégorie</option>
                        <option value="laptop" <?php echo $product['category'] === 'laptop' ? 'selected' : ''; ?>>Ordinateurs Portables</option>
                        <option value="desktop" <?php echo $product['category'] === 'desktop' ? 'selected' : ''; ?>>Ordinateurs Fixes</option>
                        <option value="smartphone" <?php echo $product['category'] === 'smartphone' ? 'selected' : ''; ?>>Smartphones</option>
                        <option value="tablet" <?php echo $product['category'] === 'tablet' ? 'selected' : ''; ?>>Tablettes</option>
                        <option value="accessory" <?php echo $product['category'] === 'accessory' ? 'selected' : ''; ?>>Accessoires</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="price">Prix (DH)</label>
                    <input type="number" id="price" name="price" step="0.01" value="<?php echo $product['price']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="stock">Stock</label>
                    <input type="number" id="stock" name="stock" value="<?php echo $product['stock']; ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="image">Image Actuelle</label>
                <?php if ($product['image_url']): ?>
                    <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" alt="Current product image" class="current-image">
                <?php endif; ?>
                <input type="file" id="image" name="image" accept="image/*">
                <small>Laissez vide pour conserver l'image actuelle</small>
            </div>
            <!-- Add this inside your form, before the submit button -->
            <div class="form-group pc-specs" style="display: none;">
                <label for="ram">RAM:</label>
                <select name="ram" id="ram" class="form-control">
                    <option value="">Select RAM</option>
                    <option value="8GB" <?php echo $product['ram'] === '8GB' ? 'selected' : ''; ?>>8GB</option>
                    <option value="16GB" <?php echo $product['ram'] === '16GB' ? 'selected' : ''; ?>>16GB</option>
                    <option value="32GB" <?php echo $product['ram'] === '32GB' ? 'selected' : ''; ?>>32GB</option>
                    <option value="64GB" <?php echo $product['ram'] === '64GB' ? 'selected' : ''; ?>>64GB</option>
                </select>
                <label for="storage">Storage:</label>
                <select name="storage" id="storage" class="form-control">
                    <option value="">Select Storage</option>
                    <option value="256GB" <?php echo $product['storage'] === '256GB' ? 'selected' : ''; ?>>256GB</option>
                    <option value="512GB" <?php echo $product['storage'] === '512GB' ? 'selected' : ''; ?>>512GB</option>
                    <option value="1TB" <?php echo $product['storage'] === '1TB' ? 'selected' : ''; ?>>1TB</option>
                    <option value="2TB" <?php echo $product['storage'] === '2TB' ? 'selected' : ''; ?>>2TB</option>
                </select>
                <label for="processor">Processor:</label>
                <select name="processor" id="processor" class="form-control">
                    <option value="">Select Processor</option>
                    <option value="Intel Core i5" <?php echo $product['processor'] === 'Intel Core i5' ? 'selected' : ''; ?>>Intel Core i5</option>
                    <option value="Intel Core i7" <?php echo $product['processor'] === 'Intel Core i7' ? 'selected' : ''; ?>>Intel Core i7</option>
                    <option value="Intel Core i9" <?php echo $product['processor'] === 'Intel Core i9' ? 'selected' : ''; ?>>Intel Core i9</option>
                    <option value="AMD Ryzen 5" <?php echo $product['processor'] === 'AMD Ryzen 5' ? 'selected' : ''; ?>>AMD Ryzen 5</option>
                    <option value="AMD Ryzen 7" <?php echo $product['processor'] === 'AMD Ryzen 7' ? 'selected' : ''; ?>>AMD Ryzen 7</option>
                    <option value="AMD Ryzen 9" <?php echo $product['processor'] === 'AMD Ryzen 9' ? 'selected' : ''; ?>>AMD Ryzen 9</option>
                    <option value="Apple M1" <?php echo $product['processor'] === 'Apple M1' ? 'selected' : ''; ?>>Apple M1</option>
                    <option value="Apple M2" <?php echo $product['processor'] === 'Apple M2' ? 'selected' : ''; ?>>Apple M2</option>
                </select>
            </div>
            <div class="form-group mobile-specs" style="display: none;">
                <label for="camera">Camera:</label>
                <select name="camera" id="camera" class="form-control">
                    <option value="">Select Camera</option>
                    <option value="12MP" <?php echo $product['camera'] === '12MP' ? 'selected' : ''; ?>>12MP</option>
                    <option value="48MP" <?php echo $product['camera'] === '48MP' ? 'selected' : ''; ?>>48MP</option>
                    <option value="50MP" <?php echo $product['camera'] === '50MP' ? 'selected' : ''; ?>>50MP</option>
                    <option value="108MP" <?php echo $product['camera'] === '108MP' ? 'selected' : ''; ?>>108MP</option>
                    <option value="200MP" <?php echo $product['camera'] === '200MP' ? 'selected' : ''; ?>>200MP</option>
                </select>
                <label for="battery">Battery:</label>
                <select name="battery" id="battery" class="form-control">
                    <option value="">Select Battery</option>
                    <option value="3000mAh" <?php echo $product['battery'] === '3000mAh' ? 'selected' : ''; ?>>3000mAh</option>
                    <option value="4000mAh" <?php echo $product['battery'] === '4000mAh' ? 'selected' : ''; ?>>4000mAh</option>
                    <option value="4500mAh" <?php echo $product['battery'] === '4500mAh' ? 'selected' : ''; ?>>4500mAh</option>
                    <option value="5000mAh" <?php echo $product['battery'] === '5000mAh' ? 'selected' : ''; ?>>5000mAh</option>
                    <option value="5400mAh" <?php echo $product['battery'] === '5400mAh' ? 'selected' : ''; ?>>5400mAh</option>
                </select>
                <label for="phone_storage">Storage:</label>
                <select name="phone_storage" id="phone_storage" class="form-control">
                    <option value="">Select Storage</option>
                    <option value="64GB" <?php echo $product['storage'] === '64GB' ? 'selected' : ''; ?>>64GB</option>
                    <option value="128GB" <?php echo $product['storage'] === '128GB' ? 'selected' : ''; ?>>128GB</option>
                    <option value="256GB" <?php echo $product['storage'] === '256GB' ? 'selected' : ''; ?>>256GB</option>
                    <option value="512GB" <?php echo $product['storage'] === '512GB' ? 'selected' : ''; ?>>512GB</option>
                    <option value="1TB" <?php echo $product['storage'] === '1TB' ? 'selected' : ''; ?>>1TB</option>
                </select>
            </div>
            <button type="submit" class="submit-btn">
                <i class="fas fa-save"></i> Enregistrer les modifications
            </button>
        </form