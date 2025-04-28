<?php
session_start();
include('config/config.php'); // Ensure this file contains a valid $mysqli connection
//check if already logged in move to home page
//login 
if (isset($_POST['login'])) {
  $admin_email = $_POST['admin_email'];
  $admin_password = sha1(md5($_POST['admin_password'])); //double encrypt to increase security
  $stmt = $mysqli->prepare("SELECT admin_email, admin_password, admin_id  FROM   bnhs_admin WHERE (admin_email =? AND admin_password =?)"); //sql to log in user
  $stmt->bind_param('ss', $admin_email, $admin_password); //bind fetched parameters
  $stmt->execute(); //execute bind 
  $stmt->bind_result($admin_email, $admin_password, $admin_id); //bind result
  $rs = $stmt->fetch();
  $_SESSION['admin_id'] = $admin_id;
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
          <input type="text" placeholder="Email" name="admin_email" required />
        </div>
      </div>
      <div class="field create-password">
        <div class="input-field">
          <input class="username" type="password" placeholder="Password" name="admin_password" required />
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