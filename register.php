<?php
session_start();
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn = getConnection();
        
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("Invalid CSRF token");
        }

        // Basic input validation
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        
        $errors = [];

        // Validate required fields
        if (empty($username)) $errors[] = "Le nom d'utilisateur est requis";
        if (empty($email)) $errors[] = "L'email est requis";
        if (empty($password)) $errors[] = "Le mot de passe est requis";
        if (empty($full_name)) $errors[] = "Le nom complet est requis";

        // Validate username length and format
        if (strlen($username) < 3 || strlen($username) > 50) {
            $errors[] = "Le nom d'utilisateur doit contenir entre 3 et 50 caractères";
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = "Le nom d'utilisateur ne peut contenir que des lettres, des chiffres et des underscores";
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Format d'email invalide";
        }

        // Check if username exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            $errors[] = "Ce nom d'utilisateur est déjà pris";
        }

        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $errors[] = "Cet email est déjà utilisé";
        }

        // Validate password
        if (strlen($password) < 8) {
            $errors[] = "Le mot de passe doit contenir au moins 8 caractères";
        }
        if ($password !== $confirm_password) {
            $errors[] = "Les mots de passe ne correspondent pas";
        }

        if (empty($errors)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("
                INSERT INTO users (username, email, password, full_name, phone, address, role, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'user', TRUE)
            ");
            
            if ($stmt->execute([$username, $email, $hashed_password, $full_name, $phone, $address])) {
                $_SESSION['registration_success'] = true;
                $_SESSION['message'] = "Inscription réussie! Vous pouvez maintenant vous connecter.";
                header("Location: login.php");
                exit();
            } else {
                throw new Exception("Erreur lors de l'insertion dans la base de données");
            }
        }
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        $errors[] = "Une erreur est survenue lors de l'inscription. Veuillez réessayer plus tard.";
    }
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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
            <h1>Créer un compte</h1>
            <p>Rejoignez HA GROUP pour accéder à nos services</p>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" class="register-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur *</label>
                    <input type="text" id="username" name="username" required 
                           minlength="3" maxlength="50" pattern="[a-zA-Z0-9_]+"
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Mot de passe *</label>
                    <input type="password" id="password" name="password" required minlength="8">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                </div>
            </div>

            <div class="form-group">
                <label for="full_name">Nom complet *</label>
                <input type="text" id="full_name" name="full_name" required
                       value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="phone">Téléphone</label>
                <input type="tel" id="phone" name="phone"
                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="address">Adresse</label>
                <textarea id="address" name="address" rows="3"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
            </div>

            <button type="submit" class="register-btn">
                <i class="fas fa-user-plus"></i> S'inscrire
            </button>

            <div class="login-link">
                Vous avez déjà un compte? <a href="login.php">Connectez-vous ici</a>
            </div>
            
            <div class="back-to-home">
                <a href="index.php"><i class="fas fa-arrow-left"></i> Retour à l'accueil</a>
            </div>
        </form>
    </div>
    
    <script>
    document.getElementById('password').addEventListener('input', function() {
        var confirmPassword = document.getElementById('confirm_password');
        if (this.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity("Les mots de passe ne correspondent pas");
        } else {
            confirmPassword.setCustomValidity('');
        }
    });

    document.getElementById('confirm_password').addEventListener('input', function() {
        if (this.value !== document.getElementById('password').value) {
            this.setCustomValidity("Les mots de passe ne correspondent pas");
        } else {
            this.setCustomValidity('');
        }
    });
    </script>
</body>
</html>