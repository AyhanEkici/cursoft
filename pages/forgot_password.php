<?php
/**
 * Forgot Password Page
 */

require_once __DIR__ . '/../includes/SessionManager.php';
require_once __DIR__ . '/../includes/Security.php';

$sessionManager = new SessionManager();
$sessionManager->requireGuest();

$security = new Security();
$csrfToken = $security->generateCSRFToken();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!$security->verifyCSRFToken($token)) {
        $error = "Invalid security token. Please try again.";
    } else {
        // Rate limiting
        if (!$security->checkRateLimit('password_reset', 3, 3600)) {
            $error = "Too many requests. Please try again later.";
        } else {
            $email = $security->sanitizeInput($_POST['email'] ?? '', 'email');
            
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Please enter a valid email address.";
            } else {
                try {
                    require_once __DIR__ . '/../includes/Auth.php';
                    $auth = new Auth();
                    $result = $auth->requestPasswordReset($email);
                    
                    // In production, email would be sent
                    // For development, show token (REMOVE IN PRODUCTION!)
                    $success = "Password reset link sent! (Dev mode: Token: " . $result['token'] . ")";
                    $success .= "<br><a href='reset_password.php?token=" . $result['token'] . "'>Click here to reset</a>";
                } catch (Exception $e) {
                    // Don't reveal if email exists
                    $success = "If that email exists, a reset link has been sent.";
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
    <title>Forgot Password - Cursoft</title>
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
        
        .forgot-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        
        .forgot-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .forgot-header h1 {
            color: #667eea;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-header">
            <h1>🔐 Forgot Password</h1>
            <p>Enter your email to reset</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" class="form-control" required autofocus>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Send Reset Link</button>
        </form>
        
        <div style="text-align: center; margin-top: 20px;">
            <p><a href="login.php">Back to Login</a></p>
        </div>
    </div>
</body>
</html>

