<?php
/**
 * Login Page
 */

require_once __DIR__ . '/../includes/SessionManager.php';
require_once __DIR__ . '/../includes/Security.php';

$sessionManager = new SessionManager();
$sessionManager->requireGuest(); // Redirect if already logged in

$security = new Security();
$csrfToken = $security->generateCSRFToken();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    $token = $_POST['csrf_token'] ?? '';
    if (!$security->verifyCSRFToken($token)) {
        $error = "Invalid security token. Please try again.";
        $security->logSecurityEvent('csrf_token_invalid', ['page' => 'login']);
    } else {
        // Rate limiting
        if (!$security->checkRateLimit('login', 5, 300)) { // 5 attempts per 5 minutes
            $error = "Too many login attempts. Please try again later.";
        } else {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $rememberMe = isset($_POST['remember_me']);
            
            if (empty($email) || empty($password)) {
                $error = "Please enter both email and password.";
            } else {
                try {
                    require_once __DIR__ . '/../includes/Auth.php';
                    $auth = new Auth();
                    $auth->login($email, $password, $rememberMe);
                    
                    // Redirect to dashboard or return URL
                    $redirect = $_GET['redirect'] ?? '/cursoft/pages/dashboard.php';
                    header('Location: ' . $redirect);
                    exit;
                } catch (Exception $e) {
                    $error = $e->getMessage();
                    $security->logSecurityEvent('login_failed', ['email' => $email]);
                }
            }
        }
    }
}

// Check for signup success
if (isset($_GET['signup']) && $_GET['signup'] === 'success') {
    $success = "Account created successfully! Please login.";
}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cursoft</title>
    <link rel="stylesheet" href="/cursoft/public/css/main.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: #666;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🚀 Cursoft</h1>
            <p>AI-Powered Development Platform</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" class="form-control" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="remember_me">
                    Remember me
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
        </form>
        
        <div style="text-align: center; margin-top: 20px;">
            <p>Don't have an account? <a href="signup.php">Sign up</a></p>
            <p><a href="forgot_password.php">Forgot password?</a></p>
        </div>
    </div>
</body>
</html>

