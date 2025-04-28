<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
//Delete Staff
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $adn = "DELETE FROM  bnhs_staff  WHERE  staff_id = ?";
  $stmt = $mysqli->prepare($adn);
  $stmt->bind_param('s', $id);
  $stmt->execute();
  $stmt->close();
  if ($stmt) {
    $success = "Deleted" && header("refresh:1; url=user_management.php");
  } else {
    $err = "Try Again Later";
  }
}
// Check if a search query is submitted
$searchResults = [];
if (isset($_GET['query'])) {
  $search = $mysqli->real_escape_string($_GET['query']); // Sanitize input to prevent SQL injection
  $sql = "SELECT * FROM bnhs_staff WHERE staff_name LIKE '%$search%' OR staff_id LIKE '%$search%'";
  $result = $mysqli->query($sql);

  if ($result) {
    while ($row = $result->fetch_object()) {
      $searchResults[] = $row; // Store results in an array
    }
  }
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
    <div style="background-image: url(assets/img/theme/bnhsfront.jpg); background-size: cover;"
      class="header  pb-8 pt-5 pt-md-8">
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
              <form class="form-inline" method="GET" action="search_staff.php"
                style="float: left; margin-top: 20px; margin-bottom: 20px;">
                <input class="form-control mr-sm-2" style="width: 500px;" type="search" name="query"
                  placeholder="Search Item" aria-label="Search">
                <button class="btn btn-outline-success my-2 my-sm-0" type="submit">
                  <i class="fas fa-search"></i>
                  Search
                </button>
              </form>
            </div>
            <div class="table-responsive">
              <table class="table align-items-center table-flush">
                <thead class="thead-light">
                  <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Full Name</th>
                    <th scope="col">Contact Number</th>
                    <th scope="col">Email</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($searchResults)) { ?>
                    <?php foreach ($searchResults as $cust) { ?>
                      <tr>
                        <td><?php echo $cust->staff_id; ?></td>
                        <td><?php echo $cust->staff_name; ?></td>
                        <td><?php echo $cust->staff_phoneno; ?></td>
                        <td><?php echo $cust->staff_email; ?></td>
                        <td>
                          <a href="view_item.php?id=<?php echo $cust->staff_id; ?>">
                            <button class="btn btn-sm btn-info">
                              <i class="fas fa-eye"></i> View
                            </button>
                          </a>
                          <a href="user_management.php?delete=<?php echo $cust->staff_id; ?>">
                          <button class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i>
                            Delete
                          </button>
                        </a>
                        <a href="update_staff.php?update=<?php echo $cust->staff_id; ?>">
                          <button class="btn btn-sm btn-primary">
                            <i class="fas fa-user-edit"></i>
                            Update
                          </button>
                        </a>
                        </td>
                      </tr>
                    <?php } ?>
                  <?php } else { ?>
                    <tr>
                      <td colspan="5" class="text-center">No items found matching your search.</td>
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