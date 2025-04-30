<?php
session_start();
include('config/config.php'); // Ensure this file contains a valid $mysqli connection

if (isset($_POST['submit'])) {
  $code = $_POST['codes'];

  // Ensure email was stored when the code was sent
  if (!isset($_SESSION['verify_email'])) {
    $err = "Email session not found. Please request a new code.";
  } else {
    $staff_email = $_SESSION['verify_email'];

    // Check if the code and email match in the database
    $checkQuery = "SELECT code, created_at FROM verification_codes WHERE email = ? AND code = ?";
    $checkStmt = $mysqli->prepare($checkQuery);

    if ($checkStmt) {
      $checkStmt->bind_param('si', $staff_email, $code);
      $checkStmt->execute();
      $checkStmt->store_result();

      if ($checkStmt->num_rows > 0) {
        $checkStmt->bind_result($stored_code, $created_at);
        $checkStmt->fetch();

        // Check if the code is still valid
        $time_limit = 10 * 60; // 10 minutes
        $code_time = strtotime($created_at);
        $current_time = time();
        $time_diff = $current_time - $code_time;

        if ($time_diff <= $time_limit) {
          // Success! Store email as verified for password change
          $_SESSION['verified_email'] = $staff_email;

          // Redirect to change password page
          header("Location: change_password.php");
          exit();
        } else {
          $err = "Verification code has expired. Please request a new one.";
        }
      } else {
        $err = "Invalid verification code or email.";
      }
      $checkStmt->close();
    } else {
      $err = "Database error: " . $mysqli->error;
    }
  }
}

require_once('partials/_inhead.php');
?>

<body>
  <div class="containers">
    <img src="assets/img/brand/bnhs.png" alt="This is a Logo" style="width: 150px; height: auto; margin-bottom: 40px">
    <form method="POST" rule="form">

      <div class="field">
        <div class="input-fields">
          <input type="number" placeholder="Enter Code" name="codes" required>
        </div>
      </div>

      <div class="input-field buttons">
        <button type="submit" name="submit" style="background-color: #29126d">SUBMIT</button>
      </div>
      <div class="links">
        <p>Didn't receive a code? <a href="resend_code.php">Resend Code</a></p>
      </div>
    </form>
  </div>

</body>
<footer class="text-muted fixed-bottom mb-5">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-6 text-left text-md-start">
        &copy; 2020 - <?php echo date('Y'); ?> - Developed By SOVATECH Company
      </div>
      <div class="col-md-6 text-right text-md-end">
        <a href="#" class="nav-link" target="_blank">BNHS INVENTORY SYSTEM</a>
      </div>
    </div>
  </div>
</footer>