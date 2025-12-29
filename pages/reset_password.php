<?php
/**
 * Reset Password Page
 */

require_once __DIR__ . '/../includes/SessionManager.php';
require_once __DIR__ . '/../includes/Security.php';

$sessionManager = new SessionManager();
$sessionManager->requireGuest();

$security = new Security();
$csrfToken = $security->generateCSRFToken();

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error = "Invalid reset link.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($token)) {
    $postToken = $_POST['csrf_token'] ?? '';
    if (!$security->verifyCSRFToken($postToken)) {
        $error = "Invalid security token. Please try again.";
    } else {
        $newPassword = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($newPassword)) {
            $error = "Password is required.";
        } elseif ($newPassword !== $confirmPassword) {
            $error = "Passwords do not match.";
        } elseif (strlen($newPassword) < 8) {
            $error = "Password must be at least 8 characters.";
        } else {
            try {
                require_once __DIR__ . '/../includes/Auth.php';
                $auth = new Auth();
                $auth->resetPassword($token, $newPassword);
                
                $success = "Password reset successfully! Redirecting to login...";
                header("Refresh: 2; url=login.php");
            } catch (Exception $e) {
                $error = $e->getMessage();
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
    <title>Reset Password - Cursoft</title>
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
        
        .reset-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        
        .reset-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .reset-header h1 {
            color: #667eea;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-header">
            <h1>🔐 Reset Password</h1>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if (empty($error) && empty($success)): ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            
            <div class="form-group">
                <label for="password">New Password:</label>
                <input type="password" id="password" name="password" class="form-control" required minlength="8">
                <small style="color: #666;">Minimum 8 characters</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Reset Password</button>
        </form>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 20px;">
            <p><a href="login.php">Back to Login</a></p>
        </div>
    </div>
</body>
</html>

