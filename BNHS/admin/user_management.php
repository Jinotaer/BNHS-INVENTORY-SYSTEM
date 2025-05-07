<?php
session_start();
include('config/config.php');
// include('config/checklogin.php');
// check_login();
//Delete Staff
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $adn = "DELETE FROM bnhs_staff WHERE staff_id = ?";
  $stmt = $mysqli->prepare($adn);
  $stmt->bind_param('s', $id);
  $stmt->execute();
  
  if ($stmt->affected_rows > 0) {
    $success = "Staff Deleted Successfully";
    header("refresh:1; url=user_management.php");
  } else {
    $err = "Failed to delete staff. Please try again.";
  }
  $stmt->close();
}
require_once('partials/_head.php');
?>

<body>
  <!-- Sidenav -->
  <?php
  require_once('partials/_sidebar.php');
  ?>
  <!-- Main content -->
  <div class="main-content">
    <!-- Top navbar -->
    <?php
    require_once('partials/_topnav.php');
    ?>
    <!-- Header -->
    <div style="background-image: url(assets/img/theme/bnhsfront.jpg); background-size: cover;" class="header  pb-8 pt-5 pt-md-8">
    <span class="mask bg-gradient-dark opacity-8"></span>
      <div class="container-fluid">
        <div class="header-body">
        </div>
      </div>
    </div>
    <!-- Page content -->
    <div class="container-fluid mt--8">
      <!-- Table -->
      <div class="row">
        <div class="col">
          <div class="card shadow">
            <div class="card-header border-0">
              <a href="add_staff.php" class="btn btn-outline-primary">
                <i class="fas fa-user-plus"></i>
                Add New Staff
              </a>
            </div>
            <div class="table-responsive">
              <table class="table align-items-center table-flush">
                <thead class="thead-light">
                  <tr>
                    <th scope="col">Profile</th>
                    <th scope="col">ID</th>
                    <th scope="col">Full Name</th>
                    <th scope="col">Email</th>
                    <!-- <th></th> -->
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $ret = "SELECT * FROM  bnhs_staff  ORDER BY `bnhs_staff`.`created_at` DESC ";
                  $stmt = $mysqli->prepare($ret);
                  $stmt->execute();
                  $res = $stmt->get_result();
                  while ($staff = $res->fetch_object()) {
                  ?>
                    <tr>
                      <td><img src="assets/img/theme/user-a-min.png" alt="Profile" class="img-fluid rounded-circle" style="width: 40px; height: 40px;"></td>
                      <td><?php echo $staff->staff_id; ?></td>
                      <td><?php echo $staff->staff_name; ?></td>
                      <td><?php echo $staff->staff_email; ?></td>
                      <td>
                        <a href="user_management.php?delete=<?php echo $staff->staff_id; ?>">
                          <button class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i>
                            Delete
                          </button>
                        </a>

                          <!-- <a href="update_staff.php?update=<?php echo $staff->staff_id; ?>">
                            <button class="btn btn-sm btn-primary">
                              <i class="fas fa-user-edit"></i>
                              Update
                            </button>
                          </a> -->
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
       <!-- Footer -->
       <?php
      require_once('partials/_mainfooter.php');
      ?>
    </div>
  </div>
  <!-- Argon Scripts -->
  <?php
  require_once('partials/_scripts.php');
  ?>
</body>

</html>