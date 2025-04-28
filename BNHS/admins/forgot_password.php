<?php
session_start();
include('config/config.php'); // Ensure this file contains a valid $mysqli connection

if (isset($_POST['add'])) {
    // Prevent Posting Blank Values
    if (empty($_POST["admin_phoneno"]) || empty($_POST["admin_id"]) || empty($_POST["admin_name"]) || empty($_POST['admin_email']) || empty($_POST['admin_password'])) {
        $err = "Blank Values Not Accepted";
    } else {
        $admin_name = $_POST['admin_name'];
        $admin_phoneno = $_POST['admin_phoneno'];
        $admin_email = $_POST['admin_email'];
        $admin_password = sha1(md5($_POST['admin_password'])); // Hash This
        $admin_id = $_POST['admin_id'];

        // Insert Captured Information into the Database Table
        $postQuery = "INSERT INTO bnhs_admin (admin_id, admin_name, admin_phoneno, admin_email, admin_password) VALUES(?,?,?,?,?)";
        $postStmt = $mysqli->prepare($postQuery);

        if ($postStmt) {
            // Bind Parameters
            $rc = $postStmt->bind_param('sssss', $admin_id, $admin_name, $admin_phoneno, $admin_email, $admin_password);

            // Execute the Query
            if ($postStmt->execute()) {
                $success = "Admin Account Created Successfully";
                header("refresh:1; url=index.php");
            } else {
                $err = "Error: " . $postStmt->error; // Debugging: Show SQL error
            }
        } else {
            $err = "Error: " . $mysqli->error; // Debugging: Show SQL preparation error
        }
    }
}
require_once('partials/_inhead.php');
require_once('config/code-generator.php');
?>
<body>
  <div class="containers">
   <img src="assets/img/brand/bnhs.png" alt="This is a Logo" style="width: 150px; height: auto; margin-bottom: 40px">
   <form method="POST" rule="form">
    <div class="field">
      <div class="input-fields">
        <input type="email" placeholder="Email" name="admin_email" required>
      </div>
    </div>
  
    <div class="input-field buttons">
      <button type="submit" name="add" style="background-color: #29126d">Submit</button>
    </div>
 
   
</body>
<footer class="text-muted fixed-bottom mb-5">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-6 text-left text-md-start">
        &copy; 2024 - <?php echo date('Y'); ?> - Developed By SOVATECH Company
      </div>
      <div class="col-md-6 text-right text-md-end">
        <a href="#" class="nav-link" target="_blank"> BNHS INVENTORY SYSTEM</a>
      </div>
    </div>
  </div>
</footer>


</html>