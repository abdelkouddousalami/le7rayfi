<?php
session_start();
require_once 'config/db.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $errors = [];
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Ce nom d'utilisateur est déjà pris";
    }
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Cet email est déjà utilisé";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Les mots de passe ne correspondent pas";
    }
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, phone, address, role) VALUES (?, ?, ?, ?, ?, ?, 'user')");
        if ($stmt->execute([$username, $email, $hashed_password, $full_name, $phone, $address])) {
            $_SESSION['registration_success'] = true;
            header("Location: login.php");
            exit();
        } else {
            $errors[] = "Une erreur est survenue lors de l'inscription";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - HA GROUP</title>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1>Inscription</h1>
            <p>Créez votre compte pour accéder à toutes nos fonctionnalités</p>
        </div>
        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $error): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <form class="register-form" method="POST" action="">
            <div class="form-row">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur*</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="email">Email*</label>
                    <input type="email" id="email" name="email" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Mot de passe*</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe*</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
            </div>
            <div class="form-group">
                <label for="full_name">Nom complet</label>
                <input type="text" id="full_name" name="full_name">
            </div>
            <div class="form-group">
                <label for="phone">Téléphone</label>
                <input type="tel" id="phone" name="phone">
            </div>
            <div class="form-group">
                <label for="address">Adresse</label>
                <textarea id="address" name="address" rows="3"></textarea>
            </div>
            <button type="submit" class="register-btn">
                <i class="fas fa-user-plus"></i> S'inscrire
            </button>
        </form>
        <div class="login-link">
            Déjà inscrit? <a href="login.php">Se connecter</a>
        </div>
        <div class="back-to-home">
            <a href="index.php"><i class="fas fa-arrow-left"></i> Retour à l'accueil</a>
        </div>
    </div>
</body>
</html>