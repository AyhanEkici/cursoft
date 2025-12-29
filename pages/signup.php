<?php
/**
 * Signup Page
 */

require_once __DIR__ . '/../includes/SessionManager.php';
require_once __DIR__ . '/../includes/Security.php';

$sessionManager = new SessionManager();
$sessionManager->requireGuest();

$security = new Security();
$csrfToken = $security->generateCSRFToken();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    $token = $_POST['csrf_token'] ?? '';
    if (!$security->verifyCSRFToken($token)) {
        $error = "Invalid security token. Please try again.";
        $security->logSecurityEvent('csrf_token_invalid', ['page' => 'signup']);
    } else {
        // Rate limiting
        if (!$security->checkRateLimit('signup', 3, 3600)) { // 3 attempts per hour
            $error = "Too many signup attempts. Please try again later.";
        } else {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (empty($name) || empty($email) || empty($password)) {
                $error = "All fields are required.";
            } elseif ($password !== $confirmPassword) {
                $error = "Passwords do not match.";
            } elseif (strlen($password) < 8) {
                $error = "Password must be at least 8 characters.";
            } else {
                try {
                    require_once __DIR__ . '/../includes/Auth.php';
                    $auth = new Auth();
                    $auth->register($email, $password, $name);
                    
                    header('Location: login.php?signup=success');
                    exit;
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Cursoft</title>
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
        
        .signup-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            width: 100%;
            max-width: 450px;
        }
        
        .signup-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .signup-header h1 {
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .signup-header p {
            color: #666;
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <div class="signup-header">
            <h1>🚀 Create Account</h1>
            <p>Join Cursoft today</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <div class="form-group">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" class="form-control" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" class="form-control" required minlength="8">
                <small style="color: #666;">Minimum 8 characters</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Create Account</button>
        </form>
        
        <div style="text-align: center; margin-top: 20px;">
            <p>Already have an account? <a href="login.php">Login</a></p>
        </div>
    </div>
</body>
</html>

