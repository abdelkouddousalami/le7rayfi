<?php
session_start();
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            echo json_encode(['success' => true, 'redirect' => $user['role'] === 'admin' ? 'admin/dashboard.php' : 'index.php']);
            exit();
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid username/email or password']);
            exit();
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'register') {
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
            $errors[] = "Username already taken";
        }
        
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $errors[] = "Email already in use";
        }
        
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        }
        
        if (empty($errors)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, phone, address, role) VALUES (?, ?, ?, ?, ?, ?, 'user')");
            if ($stmt->execute([$username, $email, $hashed_password, $full_name, $phone, $address])) {
                echo json_encode(['success' => true, 'message' => 'Registration successful! Please login.']);
                exit();
            } else {
                echo json_encode(['success' => false, 'message' => 'An error occurred during registration']);
                exit();
            }
        } else {
            echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
            exit();
        }
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
        .nav-container {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            padding: 1rem 2rem;
        }

        .nav-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5em;
            font-weight: bold;
            color: #3498db;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo i {
            font-size: 1.2em;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
        }

        .nav-link {
            color: #2c3e50;
            text-decoration: none;
            position: relative;
            padding: 5px 0;
            transition: color 0.3s;
        }

        .nav-link:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: #3498db;
            transition: width 0.3s;
        }

        .nav-link:hover {
            color: #3498db;
        }

        .nav-link:hover:after {
            width: 100%;
        }

        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(120deg, #2980b9, #8e44ad);
            padding: 80px 20px 20px;
        }

        .auth-container {
            width: 70%;
            max-width: 1000px;
            display: flex;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3);
            position: relative;
            transform-origin: center;
            animation: container-appear 0.6s ease-out;
        }

        @keyframes container-appear {
            0% {
                opacity: 0;
                transform: scale(0.9);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        .side-content {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: white;
            background: linear-gradient(135deg, #3498db, #2c3e50);
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 1;
        }

        .form-container {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            background: white;
        }

        .auth-container.register-mode .side-content {
            transform: translateX(100%);
        }

        .auth-container.register-mode .form-container {
            transform: translateX(-100%);
        }

        .form-title {
            font-size: 2em;
            margin-bottom: 30px;
            color: #2c3e50;
            text-align: center;
            opacity: 0;
            animation: fade-in 0.6s ease-out forwards 0.3s;
        }

        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-group {
            margin-bottom: 20px;
            opacity: 0;
            animation: slide-up 0.5s ease-out forwards;
        }

        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .form-group:nth-child(3) { animation-delay: 0.3s; }
        .form-group:nth-child(4) { animation-delay: 0.4s; }

        @keyframes slide-up {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1em;
            transition: all 0.3s;
            background: white;
        }

        .form-group input:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            outline: none;
        }

        .submit-btn {
            background: #3498db;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            width: 100%;
            font-size: 1em;
            cursor: pointer;
            transition: all 0.3s;
            opacity: 0;
            animation: fade-in 0.5s ease-out forwards 0.5s;
        }

        .submit-btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }

        .switch-form-btn {
            background: transparent;
            border: 2px solid white;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s;
            opacity: 0;
            animation: fade-in 0.5s ease-out forwards 0.6s;
        }

        .switch-form-btn:hover {
            background: white;
            color: #3498db;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 255, 255, 0.3);
        }

        .side-content h2 {
            font-size: 2.5em;
            margin-bottom: 20px;
            opacity: 0;
            animation: fade-in 0.5s ease-out forwards;
        }

        .side-content p {
            margin-bottom: 30px;
            font-size: 1.1em;
            opacity: 0;
            animation: fade-in 0.5s ease-out forwards 0.2s;
        }

        .error-message, .success-message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
            animation: slide-down 0.3s ease-out;
        }

        .error-message {
            background: #ff6b6b;
            color: white;
        }

        .success-message {
            background: #51cf66;
            color: white;
        }

        @keyframes slide-down {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .auth-container {
                width: 90%;
                flex-direction: column;
            }

            .side-content, .form-container {
                width: 100%;
            }

            .auth-container.register-mode .side-content {
                transform: translateY(100%);
            }

            .auth-container.register-mode .form-container {
                transform: translateY(-100%);
            }
        }
    </style>
</head>
<body>
    <nav class="nav-container">
        <div class="nav-content">
            <a href="index.php" class="logo">
                <i class="fas fa-laptop"></i>
                HA GROUP
            </a>
            <div class="nav-links">
                <a href="index.php" class="nav-link">Accueil</a>
                <a href="#" class="nav-link">Produits</a>
                <a href="#" class="nav-link">Contact</a>
            </div>
        </div>
    </nav>

    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="side-content">
                <div class="login-side">
                    <h2>Welcome Back!</h2>
                    <p>To keep connected with us please login with your personal information</p>
                    <button class="switch-form-btn" onclick="switchToRegister()">
                        <i class="fas fa-user-plus"></i> Sign Up
                    </button>
                </div>
                <div class="register-side" style="display: none;">
                    <h2>Hello, Friend!</h2>
                    <p>Enter your personal details and start your journey with us</p>
                    <button class="switch-form-btn" onclick="switchToLogin()">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </div>
            </div>
            
            <div class="form-container">
                <div class="login-form">
                    <h2 class="form-title">Sign In</h2>
                    <div class="error-message" id="login-error"></div>
                    <form id="loginForm" onsubmit="submitLogin(event)">
                        <input type="hidden" name="action" value="login">
                        <div class="form-group">
                            <label for="login-username">Username or Email</label>
                            <input type="text" id="login-username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="login-password">Password</label>
                            <input type="password" id="login-password" name="password" required>
                        </div>
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-sign-in-alt"></i> Sign In
                        </button>
                    </form>
                </div>

                <div class="register-form" style="display: none;">
                    <h2 class="form-title">Create Account</h2>
                    <div class="error-message" id="register-error"></div>
                    <div class="success-message" id="register-success"></div>
                    <form id="registerForm" onsubmit="submitRegister(event)">
                        <input type="hidden" name="action" value="register">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" required>
                        </div>
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-user-plus"></i> Sign Up
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
    </script>
</body>
</html>