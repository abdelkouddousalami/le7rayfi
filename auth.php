<?php
session_start();
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn = getConnection();
        
        if (isset($_POST['action']) && $_POST['action'] === 'login') {
            $username = trim($_POST['username']);
            $password = $_POST['password'];
            
            if (empty($username) || empty($password)) {
                echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
                exit();
            }
            
            $stmt = $conn->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND status = TRUE");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                
                echo json_encode(['success' => true, 'redirect' => $user['role'] === 'admin' ? 'admin/dashboard.php' : 'index.php']);
                exit();
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid username/email or password']);
                exit();
            }
        } elseif (isset($_POST['action']) && $_POST['action'] === 'register') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            $full_name = trim($_POST['full_name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');
            
            $errors = [];
            
            // Validate required fields
            if (empty($username)) $errors[] = "Username is required";
            if (empty($email)) $errors[] = "Email is required";
            if (empty($password)) $errors[] = "Password is required";
            if (empty($full_name)) $errors[] = "Full name is required";
            
            // Validate username format
            if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
                $errors[] = "Username must be between 3-50 characters and can only contain letters, numbers, and underscores";
            }
            
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email format";
            }
            
            // Check existing username
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->rowCount() > 0) {
                $errors[] = "Username already taken";
            }
            
            // Check existing email
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $errors[] = "Email already in use";
            }
            
            // Validate password
            if (strlen($password) < 8) {
                $errors[] = "Password must be at least 8 characters long";
            }
            if ($password !== $confirm_password) {                $errors[] = "Passwords do not match";
            }
            
            if (empty($errors)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                try {
                    $stmt = $conn->prepare("
                        INSERT INTO users (username, email, password, full_name, phone, address, role, status) 
                        VALUES (?, ?, ?, ?, ?, ?, 'user', TRUE)
                    ");
                    if ($stmt->execute([$username, $email, $hashed_password, $full_name, $phone, $address])) {
                        echo json_encode([
                            'success' => true, 
                            'message' => 'Registration successful! Please login.',
                            'redirect' => 'login.php'
                        ]);
                        exit();
                    } else {
                        throw new Exception("Database insert failed");
                    }
                } catch (Exception $e) {
                    error_log("Registration error: " . $e->getMessage());
                    echo json_encode([
                        'success' => false, 
                        'message' => 'An error occurred during registration. Please try again.'
                    ]);
                    exit();
                }
            } else {
                echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
                exit();
            }
        }
    } catch (Exception $e) {
        error_log("Auth error: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'An error occurred. Please try again later.'
        ]);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication - HA GROUP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="index.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            height: 100vh;
            overflow: hidden;
        }

        .nav-container {
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 60px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .nav-brand {
            font-size: 1.5rem;
            color: #2c3e50;
            text-decoration: none;
            font-weight: bold;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
        }

        .nav-link {
            color: #2c3e50;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-link:hover {
            color: #3498db;
        }

        .auth-wrapper {
            height: 100vh;
            padding-top: 60px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #3498db, #2c3e50);
        }

        .auth-container {
            width: 90%;
            max-width: 1000px;
            height: 600px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            display: flex;
            overflow: hidden;
            position: relative;
        }

        .form-container {
            width: 60%;
            padding: 2rem 4rem;
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
            height: 100%;
            box-sizing: border-box;
        }

        .form-container::-webkit-scrollbar {
            width: 6px;
        }

        .form-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .form-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        .auth-container.register-mode .side-content {
            transform: translateX(-150%);
        }

        .auth-container.register-mode .form-container {
            transform: translateX(66.666%);
        }

        .form-title {
            font-size: 2em;
            margin-bottom: 2rem;
            color: #2c3e50;
            text-align: center;
            opacity: 0;
            animation: fadeInDown 0.6s forwards;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            box-sizing: border-box;
        }

        .form-group {
            margin-bottom: 1.5rem;
            opacity: 0;
            animation: fadeInUp 0.6s forwards;
            box-sizing: border-box;
        }

        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .form-group:nth-child(3) { animation-delay: 0.3s; }
        .form-group:nth-child(4) { animation-delay: 0.4s; }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
            background: #f8f9fa;
            box-sizing: border-box;
        }

        .form-group input:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            outline: none;
            background: #fff;
        }

        /* Specific form layouts */
        .login-form {
            max-width: 400px;
            margin: 0 auto;
            padding: 0 1rem;
            box-sizing: border-box;
        }

        .register-form {
            max-width: 100%;
            box-sizing: border-box;
        }

        .register-form .form-row {
            margin-bottom: 1rem;
        }

        /* Error and success messages */
        .error-message, .success-message {
            width: 100%;
            box-sizing: border-box;
            margin-bottom: 1.5rem;
            padding: 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            display: none;
        }

        .error-message {
            background: #fff3f3;
            color: #e74c3c;
            border-left: 4px solid #e74c3c;
        }

        .success-message {
            background: #f0fff4;
            color: #2ecc71;
            border-left: 4px solid #2ecc71;
        }

        /* Submit button styling */
        .submit-btn {
            background: #3498db;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            width: 100%;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
            box-sizing: border-box;
            opacity: 0;
            animation: fadeInUp 0.6s 0.5s forwards;
        }

        .submit-btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }

        /* Side content and switch button */
        .side-content {
            width: 40%;
            background: linear-gradient(135deg, #3498db, #2c3e50);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem;
            text-align: center;
            position: absolute;
            right: 0;
            height: 100%;
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            box-sizing: border-box;
        }

        .switch-form-btn {
            background: transparent;
            border: 2px solid white;
            color: white;
            padding: 14px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            opacity: 0;
            animation: fadeInUp 0.6s 0.4s forwards;
            box-sizing: border-box;
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .auth-container {
                width: 95%;
                height: auto;
                min-height: 600px;
                margin: 1rem auto;
                flex-direction: column-reverse;
            }

            .side-content {
                position: static;
                width: 100%;
                padding: 2rem;
                min-height: 200px;
            }

            .form-container {
                width: 100%;
                padding: 2rem;
                height: auto;
                max-height: calc(100vh - 280px);
                overflow-y: auto;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .login-form {
                padding: 0;
            }

            .auth-container.register-mode .side-content,
            .auth-container.register-mode .form-container {
                transform: none;
            }

            .auth-wrapper {
                padding: 80px 1rem 20px;
                height: auto;
                min-height: 100vh;
            }

            .form-group {
                margin-bottom: 1rem;
            }

            .submit-btn {
                margin-top: 0.5rem;
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <nav class="nav-container">
        <a href="index.php" class="nav-brand">
            HA GROUP
        </a>
        <div class="nav-links">
            <a href="index.php" class="nav-link">Accueil</a>
            <a href="#" class="nav-link">Produits</a>
            <a href="#" class="nav-link">Contact</a>
        </div>
    </nav>

    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="side-content">
                <div class="login-side">
                    <h2>Bienvenue!</h2>
                    <p>Pour rester connecté avec nous, veuillez vous connecter avec vos informations personnelles</p>
                    <button class="switch-form-btn" onclick="switchToRegister()">
                        <i class="fas fa-user-plus"></i> S'inscrire
                    </button>
                </div>
                <div class="register-side" style="display: none;">
                    <h2>Bonjour!</h2>
                    <p>Entrez vos informations personnelles et commencez votre voyage avec nous</p>
                    <button class="switch-form-btn" onclick="switchToLogin()">
                        <i class="fas fa-sign-in-alt"></i> Se connecter
                    </button>
                </div>
            </div>
            
            <div class="form-container">
                <div class="login-form">
                    <h2 class="form-title">Connexion</h2>
                    <div class="error-message" id="login-error"></div>
                    <form id="loginForm" onsubmit="submitLogin(event)">
                        <input type="hidden" name="action" value="login">
                        <div class="form-group">
                            <label for="login-username">Nom d'utilisateur ou Email</label>
                            <input type="text" id="login-username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="login-password">Mot de passe</label>
                            <input type="password" id="login-password" name="password" required>
                        </div>
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-sign-in-alt"></i> Se connecter
                        </button>
                    </form>
                </div>

                <div class="register-form" style="display: none;">
                    <h2 class="form-title">Créer un compte</h2>
                    <div class="error-message" id="register-error"></div>
                    <div class="success-message" id="register-success"></div>
                    <form id="registerForm" onsubmit="submitRegister(event)">
                        <input type="hidden" name="action" value="register">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username">Nom d'utilisateur</label>
                                <input type="text" id="username" name="username" required 
                                       pattern="[a-zA-Z0-9_]{3,50}" 
                                       title="3 à 50 caractères, lettres, chiffres et underscore uniquement">
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="password">Mot de passe</label>
                                <input type="password" id="password" name="password" required 
                                       minlength="8" 
                                       title="Au moins 8 caractères">
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirmer le mot de passe</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="full_name">Nom complet</label>
                            <input type="text" id="full_name" name="full_name" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Téléphone</label>
                                <input type="tel" id="phone" name="phone" 
                                       pattern="[0-9+\s-]{8,}" 
                                       title="Numéro de téléphone valide">
                            </div>
                            <div class="form-group">
                                <label for="address">Adresse</label>
                                <input type="text" id="address" name="address">
                            </div>
                        </div>
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-user-plus"></i> S'inscrire
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function switchToRegister() {
            document.querySelector('.auth-container').classList.add('register-mode');
            document.querySelector('.login-form').style.display = 'none';
            document.querySelector('.register-form').style.display = 'block';
            document.querySelector('.login-side').style.display = 'none';
            document.querySelector('.register-side').style.display = 'block';
        }

        function switchToLogin() {
            document.querySelector('.auth-container').classList.remove('register-mode');
            document.querySelector('.login-form').style.display = 'block';
            document.querySelector('.register-form').style.display = 'none';
            document.querySelector('.login-side').style.display = 'block';
            document.querySelector('.register-side').style.display = 'none';
        }

        async function submitLogin(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);

            try {
                const response = await fetch('auth.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    const errorDiv = document.getElementById('login-error');
                    errorDiv.textContent = data.message;
                    errorDiv.style.display = 'block';
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        async function submitRegister(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);

            try {
                const response = await fetch('auth.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                
                if (data.success) {
                    const successDiv = document.getElementById('register-success');
                    successDiv.textContent = data.message;
                    successDiv.style.display = 'block';
                    document.getElementById('register-error').style.display = 'none';
                    form.reset();
                    setTimeout(() => {
                        switchToLogin();
                    }, 2000);
                } else {
                    const errorDiv = document.getElementById('register-error');
                    errorDiv.textContent = data.message;
                    errorDiv.style.display = 'block';
                    document.getElementById('register-success').style.display = 'none';
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        // Password confirmation validation
        document.getElementById('password')?.addEventListener('input', function() {
            var confirmPassword = document.getElementById('confirm_password');
            if (confirmPassword && this.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity("Les mots de passe ne correspondent pas");
            } else if (confirmPassword) {
                confirmPassword.setCustomValidity('');
            }
        });

        document.getElementById('confirm_password')?.addEventListener('input', function() {
            if (this.value !== document.getElementById('password').value) {
                this.setCustomValidity("Les mots de passe ne correspondent pas");
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>