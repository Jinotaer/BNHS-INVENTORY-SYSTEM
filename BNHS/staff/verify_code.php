<?php
session_start();
include('config/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $enteredCode = trim($_POST['codes']);
  $email = $_SESSION['verify_email'];

  if (!isset($_SESSION['verify_email'])) {
    header('Location: send_code.php');
    exit();
  }

  $stmt = $mysqli->prepare("SELECT resetcode, created_at FROM bnhs_staff WHERE staff_email = ?");
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();

  if ($user) {
    // Check if the code is still valid (10 minutes time limit)
    $time_limit = 10 * 60; // 10 minutes in seconds
    $code_time = strtotime($user['created_at']);
    $current_time = time();
    $time_diff = $current_time - $code_time;

    if ($time_diff > $time_limit) {
      $err = "Verification code has expired. Please request a new one.";
      header('Location: send_code.php');
      exit();
    }

    if ($enteredCode === trim($user['resetcode'])) {
      $_SESSION['verified_email'] = $email;
      $_SESSION['reset_code_verified'] = true;

      // Clear the reset code after successful verification
      $update = $mysqli->prepare("UPDATE bnhs_staff SET resetcode = NULL WHERE staff_email = ?");
      $update->bind_param('s', $email);
      $update->execute();
      
      header('Location: change_password.php');
      exit();
    } else {
      $err = "Invalid verification code. Please try again.";
    }
  } else {
    $err = "No user found with that email address.";
    header('Location: send_code.php');
    exit();
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
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
      <p style="color: green; margin-bottom: 10px; font-size: 13px; text-align: center;">
        <?php 
          echo $_SESSION['success'];
          unset($_SESSION['success']); 
        ?>
      </p>
    <?php endif; ?> -->
    <form method="POST">
      <div class="field">
        <div class="input-fields">
          <input type="number" placeholder="Enter Code" name="codes" required>
        </div>
      </div>

      <div class="input-field buttons">
        <button type="submit" name="submit" style="background-color: #29126d">SUBMIT</button>
      </div>
      <div class="links">
        <p>Didn't receive a code? <a href="send_code.php">Resend Code</a></p>
      </div>
    </form>
  </div>
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