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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="index.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 60px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            color: #2c3e50;
            font-size: 2em;
            margin-bottom: 10px;
        }
        .login-form {
            padding: 20px;
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
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            font-size: 1em;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            border-color: #3498db;
            outline: none;
        }
        .login-btn {
            background: #3498db;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            width: 100%;
            font-size: 1em;
            cursor: pointer;
            transition: background 0.3s;
        }
        .login-btn:hover {
            background: #2980b9;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
        }
        .register-link a {
            color: #3498db;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
        .error-message {
            background: #ff6b6b;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .success-message {
            background: #2ecc71;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
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
    </style>
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