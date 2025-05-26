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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="index.css">
    <style>
        .register-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .register-header h1 {
            color: #2c3e50;
            font-size: 2em;
            margin-bottom: 10px;
        }
        .register-form {
            padding: 20px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
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
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            font-size: 1em;
            transition: border-color 0.3s;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #3498db;
            outline: none;
        }
        .error-message {
            color: #e74c3c;
            background: #fdeaea;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 0.9em;
        }
        .register-btn {
            background: #3498db;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
            transition: background 0.3s;
            width: 100%;
        }
        .register-btn:hover {
            background: #2980b9;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #3498db;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        .back-to-home {
            text-align: center;
            margin-top: 20px;
        }
        .back-to-home a {
            color: #7f8c8d;
            text-decoration: none;
        }
        .back-to-home a:hover {
            color: #2c3e50;
        }
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }
    </style>
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