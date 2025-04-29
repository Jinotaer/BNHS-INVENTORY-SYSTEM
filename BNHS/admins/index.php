<?php
session_start();
include('config/config.php'); // Ensure this file contains a valid $mysqli connection

// Load composer autoloader
require_once __DIR__ . "/assets/vendor/autoload.php";

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/assets");
try {
    $dotenv->load();
} catch (Exception $e) {
    die('Error loading .env file. Please ensure it exists in the assets directory.');
}

$sitekey = $_ENV['RECAPTCHA_SITE_KEY'] ?? '';

//login 
if (isset($_POST['login'])) {
    $secretkey = $_ENV['RECAPTCHA_SECRET_KEY'] ?? ''; 
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
    
    if (empty($recaptcha_response)) {
        $err = "Please complete the reCAPTCHA verification";
    } else {
        $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretkey}&response={$recaptcha_response}");
        $response = json_decode($verify);
        
        if ($response->success) {
            $admin_email = $_POST['admin_email'];
            $admin_password = sha1(md5($_POST['admin_password'])); //double encrypt to increase security
            $stmt = $mysqli->prepare("SELECT admin_email, admin_password, admin_id FROM bnhs_admin WHERE (admin_email =? AND admin_password =?)");
            $stmt->bind_param('ss', $admin_email, $admin_password);
            $stmt->execute();
            $stmt->bind_result($admin_email, $admin_password, $admin_id);
            $rs = $stmt->fetch();
            $_SESSION['admin_id'] = $admin_id;
            if ($rs) {
                header("location:dashboard.php");
            } else {
                $err = "Incorrect Authentication Credentials";
            }
        } else {
            $err = "reCAPTCHA verification failed. Please try again.";
        }
    }
}
require_once('partials/_inhead.php');
?>

<body>
  <div class="containers">
    <img src="assets/img/brand/bnhs.png" alt="This is a Logo" style="width: 130px; height: auto;" />
    <form method="POST" rule="form">
      <div class="field email-field">
        <div class="input-field">
          <input type="text" placeholder="Email" name="admin_email" required />
        </div>
      </div>
      <div class="field create-password">
        <div class="input-field">
          <input class="username" type="password" placeholder="Password" name="admin_password" required />
        </div>
      </div>
      <!-- <div class="field">
       
      </div> -->
      <div class="links" style="text-align: end;">
        <a class="password" href="send_code.php">Forgot Password</a>
      </div>
      <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($sitekey); ?>"></div>
      <div class="input-field buttons" style="margin-top: 10px;">
        <button type="submit" name="login" style="background-color: #29126d;">LOGIN</button>
      </div>
 
      <div class="links">
        <p style=>Don't have an account? <a href="create_account.php">Signup</a></p>
      </div>
    </form>
  </div>

  <!-- Footer -->
  <?php
  require_once('partials/_footer.php');
  ?>
  <
</body>

<!-- Core -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

</html>