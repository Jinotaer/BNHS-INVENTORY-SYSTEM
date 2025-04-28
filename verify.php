<?php
session_start();
require_once __DIR__ . "/BNHS/staff/assets/vendor/autoload.php";

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
try {
    $dotenv->load();
} catch (Exception $e) {
    die('Error loading .env file. Please ensure it exists and is readable.');
}

$sitekey = $_ENV['RECAPTCHA_SITE_KEY'] ?? '';

// Handle reCAPTCHA verification via AJAX
if (isset($_POST['g-recaptcha-response'])) {
    $recaptcha_response = $_POST['g-recaptcha-response'];
    $secret_key = $_ENV['RECAPTCHA_SECRET_KEY'] ?? '';

    // Verify reCAPTCHA
    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secret_key}&response={$recaptcha_response}");
    $captcha_success = json_decode($verify);

    if ($captcha_success->success == true) {
        // Set session variable to indicate verification success
        $_SESSION['recaptcha_verified'] = true;
        echo "success";
        exit();
    }
    echo "failed";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify - BNHS Inventory System</title>
    <link rel="icon" type="image/png" sizes="32x32" href="bnhs1.png">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,400,600" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to bottom right, #d9f0ff, #ffffff);
            font-family: 'Nunito', sans-serif;
            margin: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .verify-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        .logo {
            margin-bottom: 2rem;
        }
        .logo img {
            width: 200px;
            height: auto;
        }
        h1 {
            color: #29126b;
            margin-bottom: 1.5rem;
        }
        .g-recaptcha{
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <div class="logo">
            <img src="bnhs1.png" alt="BNHS Logo">
        </div>
        <h1>Welcome to BNHS Inventory System</h1>
        <p>Please verify that you are human to continue</p>
        <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($sitekey) ?>" data-callback="onRecaptchaSuccess"></div>
    </div>

    <script src="https://www.google.com/recaptcha/api.js"></script>
    <script>
        function onRecaptchaSuccess(token) {
            // Send the token to the server for verification
            fetch('verify.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'g-recaptcha-response=' + token
            })
            .then(response => response.text())
            .then(result => {
                if (result === 'success') {
                    window.location.href = 'index.php';
                }
            });
        }
        
    </script>
</body>
</html> 