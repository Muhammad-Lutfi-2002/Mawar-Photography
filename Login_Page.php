<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

// Check if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: Dashboard.php');
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error_message = "Please enter both username and password.";
    } else {
        if (login($username, $password)) {
            $_SESSION['logged_in'] = true;
            header('Location: Dashboard.php');
            exit();
        } else {
            $error_message = "Invalid username or password.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mawar Photography</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;700&display=swap');

        :root {
            --primary-color: #333;
            --secondary-color: #f4f4f4;
            --accent-color: #d4af37;
            --text-color: #333;
            --error-color: #dc3545;
            --success-color: #28a745;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--secondary-color);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            line-height: 1.6;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 1000px;
            display: flex;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }

        .login-image {
            flex: 1;
            position: relative;
            background-image: url('https://images.pexels.com/photos/2959192/pexels-photo-2959192.jpeg');
            background-size: cover;
            background-position: center;
            min-height: 500px;
        }

        .login-image::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.3);
        }

        .login-form {
            flex: 1;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 30px;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        p {
            color: #666;
            margin-bottom: 40px;
            font-weight: 300;
        }

        .input-group {
            margin-bottom: 25px;
            position: relative;
        }

        .input-group input {
            width: 100%;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            font-family: 'Montserrat', sans-serif;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(212,175,55,0.1);
        }

        .login-btn {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Montserrat', sans-serif;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .login-btn:hover {
            background-color: #b8960c;
            transform: translateY(-2px);
        }

        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            color: var(--accent-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .back-btn i {
            margin-right: 8px;
        }

        .back-btn:hover {
            color: #b8960c;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            opacity: 0;
            transform: translateY(-20px);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .notification.show {
            opacity: 1;
            transform: translateY(0);
        }

        .notification.success {
            background-color: var(--success-color);
        }

        .notification.error {
            background-color: var(--error-color);
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }

            .login-image {
                min-height: 300px;
            }

            .login-form {
                padding: 40px 30px;
            }

            h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <a href="Home_Page.php" class="back-btn">
        <i class="fas fa-arrow-left"></i>
        Back to Home
    </a>

    <div class="login-container">
        <div class="login-image"></div>
        <div class="login-form">
            <div class="logo">Mawar Photography</div>
            <h1>Welcome Back</h1>
            <p>Please sign in to access your admin dashboard</p>
            
            <?php if (!empty($error_message)): ?>
                <div class="notification error show">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="input-group">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" class="login-btn">Sign In </button>
            </form>
        </div>
    </div>
</body>
</html>
    <div id="notification" class="notification"></div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            // Mock authentication
            if (username === 'admin' && password === 'password') {
                showNotification('Login successful! Redirecting...', 'success');
                setTimeout(() => window.location.href = 'Dashboard.php', 2000);
            } else {
                showNotification('Invalid username or password', 'error');
            }
        });

        function showNotification(message, type) {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = `notification ${type} show`;

            setTimeout(() => {
                notification.className = 'notification';
            }, 3000);
        }
    </script>
</body>
</html>