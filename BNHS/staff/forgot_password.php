<?php
session_start();
include('config/config.php'); // Ensure this file contains a valid $mysqli connection

if (isset($_POST['add'])) {
    // Prevent Posting Blank Values
    if (empty($_POST["staff_phoneno"]) || empty($_POST["staff_id"]) || empty($_POST["staff_name"]) || empty($_POST['staff_email']) || empty($_POST['staff_password'])) {
        $err = "Blank Values Not Accepted";
    } else {
        $staff_name = $_POST['staff_name'];
        $staff_phoneno = $_POST['staff_phoneno'];
        $staff_email = $_POST['staff_email'];
        $staff_password = sha1(md5($_POST['staff_password'])); // Hash This
        $staff_id = $_POST['staff_id'];

        // Insert Captured Information into the Database Table
        $postQuery = "INSERT INTO bnhs_staff (staff_id, staff_name, staff_phoneno, staff_email, staff_password) VALUES(?,?,?,?,?)";
        $postStmt = $mysqli->prepare($postQuery);

        if ($postStmt) {
            // Bind Parameters
            $rc = $postStmt->bind_param('sssss', $staff_id, $staff_name, $staff_phoneno, $staff_email, $staff_password);

            // Execute the Query
            if ($postStmt->execute()) {
                $success = "staff Account Created Successfully";
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
        <input type="email" placeholder="Email" name="staff_email" required>
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