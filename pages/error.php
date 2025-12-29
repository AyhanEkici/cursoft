<?php
/**
 * Error Page Handler
 */

$errorCode = $_GET['code'] ?? '500';
$errorMessages = [
    '400' => 'Bad Request',
    '401' => 'Unauthorized',
    '403' => 'Forbidden',
    '404' => 'Page Not Found',
    '500' => 'Internal Server Error',
    '503' => 'Service Unavailable'
];

$errorTitle = $errorMessages[$errorCode] ?? 'Error';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $errorCode; ?> - <?php echo $errorTitle; ?></title>
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
        
        .error-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 60px 40px;
            text-align: center;
            max-width: 500px;
        }
        
        .error-code {
            font-size: 120px;
            font-weight: bold;
            color: #667eea;
            margin: 0;
            line-height: 1;
        }
        
        .error-title {
            font-size: 32px;
            color: #333;
            margin: 20px 0;
        }
        
        .error-message {
            color: #666;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code"><?php echo $errorCode; ?></div>
        <h1 class="error-title"><?php echo $errorTitle; ?></h1>
        <p class="error-message">
            <?php
            switch ($errorCode) {
                case '404':
                    echo "The page you're looking for doesn't exist.";
                    break;
                case '403':
                    echo "You don't have permission to access this resource.";
                    break;
                case '401':
                    echo "Please login to access this page.";
                    break;
                case '500':
                    echo "Something went wrong on our end. We're working on it.";
                    break;
                default:
                    echo "An error occurred. Please try again later.";
            }
            ?>
        </p>
        <a href="/cursoft/pages/dashboard.php" class="btn btn-primary">Go to Dashboard</a>
    </div>
</body>
</html>

