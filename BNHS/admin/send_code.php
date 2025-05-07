<?php
session_start();
include('config/config.php'); // Ensure this file contains a valid $mysqli connection
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'assets\vendor\autoload.php'; // Adjusted path to vendor/autoload.php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $admin_email = $_POST['admin_email'];

  // Check if email exists
  $stmt = $mysqli->prepare("SELECT * FROM bnhs_admin WHERE admin_email = ?");
  $stmt->bind_param('s', $admin_email);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();
  
  if ($user) {
    // Generate reset code
    $reset_code = rand(100000, 999999);

    // Update user with reset code and timestamp
    $update = $mysqli->prepare("UPDATE bnhs_admin SET resetcode = ?, created_at = NOW() WHERE admin_email = ?");
    $update->bind_param('ss', $reset_code, $admin_email);
    $update->execute();

    $_SESSION['verify_email'] = $admin_email;

    // Send email with reset code
    $mail = new PHPMailer(true);

    try {
      // Server settings
      $mail->isSMTP();
      $mail->Host = 'smtp.gmail.com';
      $mail->SMTPAuth = true;
      $mail->Username = 'jjane0248@gmail.com';
      $mail->Password = 'cwbf hstm kdfr hxrd';
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port = 587;

      // Recipients
      $mail->setFrom('no-reply@bnhs.com', 'BNHS Inventory System');
      $mail->addAddress($admin_email);

      // Content
      $mail->isHTML(true);
      $mail->Subject = 'BNHS Inventory System - Password Reset Code';
      $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px;'>
            <div style='text-align: center; margin-bottom: 20px;'>
                  <img src='https://media.licdn.com/dms/image/v2/C560BAQFKp73CoeDSeA/company-logo_200_200/company-logo_200_200/0/1632664744758?e=2147483647&v=beta&t=ZA_N5ziKoP489erbycdGhsJtI5oDRSwSzxhcfoeXc-I' alt='BNHS Logo' style='width: 150px; height: auto;'>
            </div>
            <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;'>
                <h2 style='color: #29126d; margin-bottom: 15px; text-align: center;'>Password Reset Request</h2>
                <p style='color: #333; font-size: 16px; line-height: 1.5;'>Hello,</p>
                <p style='color: #333; font-size: 16px; line-height: 1.5;'>We received a request to reset your password for the BNHS Inventory System. Please use the following verification code:</p>
                <div style='background-color: #29126d; color: white; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px; margin: 20px 0; border-radius: 5px;'>
                    $reset_code
                </div>
                <p style='color: #dc3545; font-size: 14px; text-align: center;'><strong>This code will expire in 10 minutes.</strong></p>
            </div>
            <div style='text-align: center; color: #666; font-size: 14px;'>
                <p>If you didn't request this password reset, please ignore this email or contact support if you have concerns.</p>
                <p style='margin-top: 20px;'>© " . date('Y') . " BNHS Inventory System. All rights reserved.</p>
            </div>
        </div>
      ";
      $mail->AltBody = "Hello,\n\nWe received a request to reset your password for the BNHS Inventory System. Please use the following verification code:\n\n$reset_code\n\nThis code will expire in 10 minutes.\n\nIf you didn't request this password reset, please ignore this email or contact support if you have concerns.\n\n© " . date('Y') . " BNHS Inventory System. All rights reserved.";

      $mail->send();
      // $err = "A verification code has been sent to your email";
      header('Location: verify_code.php');
      exit();
    } catch (Exception $e) {
      $err = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
  } else {
    $err = "No user found with that email";
  }
}

require_once('partials/_inhead.php');
?>

<body>
  <div class="containers">
    <img src="assets/img/brand/bnhs.png" alt="This is a Logo" style="width: 150px; height: auto; margin-bottom: 40px">
    <!-- <?php if(isset($err)): ?>
    <p style="color: red; margin-bottom: 10px; font-size: 13px; text-align: center;">
      <?php echo $err; ?>
    </p>
    <?php endif; ?> -->
    <form method="POST" rule="form">
      <div class="field">
        <div class="input-fields">
          <input type="email" placeholder="Email" name="admin_email" required>
        </div>
      </div>

      <div class="input-field buttons">
        <button type="submit" name="send_code" style="background-color: #29126d">SEND CODE</button>
      </div>
      <div class="links">
        <p style="display: flex; justify-content: center;">Back to<a href="index.php" style="margin-left: 5px">Login</a></p>
      </div>
    </form>
</body>
<footer class="text-muted fixed-bottom mb-5">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-6 text-left text-md-start">
        &copy; 2024 - <?php echo date('Y'); ?> - Developed By SOVATECH Company
      </div>
      <div class="col-md-6 text-right text-md-end">
        <a href="#" class="nav-link" target="_blank">BNHS INVENTORY SYSTEM</a>
      </div>
    </div>
  </div>
</footer>