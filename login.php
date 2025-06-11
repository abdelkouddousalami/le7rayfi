<?php
session_start();
require_once 'config/db.php';

if (isset($_SESSION['user_id'])) {
    // If user is already logged in, redirect them
    header("Location: " . ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'index.php'));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn = getConnection();
        
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = "Veuillez remplir tous les champs requis";
        } else {
            $stmt = $conn->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND status = TRUE");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                
                header("Location: " . ($user['role'] === 'admin' ? 'admin/dashboard.php' : 'index.php'));
                exit();
            } else {
                $error = "Nom d'utilisateur/email ou mot de passe invalide";
            }
        }
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        $error = "Une erreur est survenue. Veuillez réessayer plus tard.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HA GROUP</title>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="/assets/css/auth.css">
    
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Connexion</h1>
            <p>Connectez-vous à votre compte</p>
        </div>
        <?php if (isset($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['registration_success'])): ?>
            <div class="success-message">
                Inscription réussie! Vous pouvez maintenant vous connecter.
            </div>
            <?php unset($_SESSION['registration_success']); ?>
        <?php endif; ?>
        
        <form class="login-form" method="POST" action="">
            <div class="form-group">
                <label for="username">Nom d'utilisateur ou Email *</label>
                <input type="text" id="username" name="username" required 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="password">Mot de passe *</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Se connecter
            </button>
        </form>
        <div class="register-link">
            Pas encore de compte? <a href="register.php">S'inscrire</a>
        </div>
        <div class="back-to-home">
            <a href="index.php"><i class="fas fa-arrow-left"></i> Retour à l'accueil</a>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Clear the form fields if the user just registered successfully
        if (document.querySelector('.success-message')) {
            document.getElementById('username').value = '';
            document.getElementById('password').value = '';
        }
    });
    </script>
</body>
</html>