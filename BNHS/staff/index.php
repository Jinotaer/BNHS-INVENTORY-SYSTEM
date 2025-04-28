<?php
session_start();
include('config/config.php'); // Ensure this file contains a valid $mysqli connection
//check if already logged in move to home page
//login 
if (isset($_POST['login'])) {
  $staff_email = $_POST['staff_email'];
  $staff_password = sha1(md5($_POST['staff_password'])); //double encrypt to increase security
  $stmt = $mysqli->prepare("SELECT staff_email, staff_password, staff_id  FROM   bnhs_staff WHERE (staff_email =? AND staff_password =?)"); //sql to log in user
  $stmt->bind_param('ss', $staff_email, $staff_password); //bind fetched parameters
  $stmt->execute(); //execute bind 
  $stmt->bind_result($staff_email, $staff_password, $staff_id); //bind result
  $rs = $stmt->fetch();
  $_SESSION['staff_id'] = $staff_id;
  if ($rs) {
    //if its sucessfull
    header("location:dashboard.php");
  } else {
    $err = "Incorrect Authentication Credentials ";
  }
}
require_once('partials/_inhead.php');
?>

<body>
  <div class="containers">
    <img src="assets/img/brand/bnhs.png" alt="This is a Logo" style="width: 150px; height: auto;" />
    <form method="POST" rule="form">
      <div class="field email-field">
        <div class="input-field">
          <input type="text" placeholder="Email" name="staff_email" required />
        </div>
      </div>
      <div class="field create-password">
        <div class="input-field">
          <input class="username" type="password" placeholder="Password" name="staff_password" required />
        </div>
      </div>
      <div class="links" style="text-align: end;">
        <a class="password" href="send_code.php">Forgot Password</a>
      </div>
      <div class="input-field buttons">
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
<script src=""></script>

</html>