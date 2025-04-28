<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
//Delete Staff
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $adn = "DELETE FROM  inspection_acceptance_reports  WHERE  id = ?";
  $stmt = $mysqli->prepare($adn);
  $stmt->bind_param('s', $id);
  $stmt->execute();
  $stmt->close();
  if ($stmt) {
    $success = "Deleted" && header("refresh:1; url=display_iar.php");
  } else {
    $err = "Try Again Later";
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
              <div class="col">
                <h2 class="text-center mb-3 pt-3 text-uppercase">Inspection and Acceptance Report</h2>
              </div>
              <div class="col text-right">
                <i></i>
                <a target="_blank" href="print_iar_files.php" class="btn btn-sm btn-primary">
                  <i class="material-icons-sharp text-primary"></i>
                  Print files</a>
              </div>
            </div>

            <div class="table-responsive">
              <table class="table align-items-center table-flush">
                <thead class="thead-light">
                  <tr>
                    <th scope="col">Entity Name</th>
                    <th scope="col">Fund Cluster</th>
                    <th scope="col">Supplier</th>
                    <th scope="col">PO No. / Date</th>
                    <th scope="col">Requisitioning Office/Dept.</th>
                    <th scope="col">Responsibility Center</th>
                    <th scope="col">IAR No.</th>
                    <th scope="col">IAR Date</th>
                    <th scope="col">Unit Cost</th>
                    <th scope="col">Invoice No. / Date</th>
                    <th scope="col">Stock / Property No.</th>
                    <th scope="col">Remarks</th>
                    <th scope="col">Unit</th>
                    <th scope="col">Quantity</th>
                    <th scope="col">Unit Price</th>
                    <th scope="col">Total Price</th>
                    <th scope="col">Receiver Name</th>
                    <th scope="col">Teacher's ID</th>
                    <th scope="col">Position</th>
                    <th scope="col">Date Inspected</th>
                    <th scope="col">Inspection Team</th>
                    <th scope="col">PTA Observer</th>
                    <th scope="col">Date Received</th>
                    <th scope="col">Property Custodian</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $ret = "SELECT * FROM  inspection_acceptance_reports  ORDER BY `inspection_acceptance_reports`.`created_at` DESC ";
                  $stmt = $mysqli->prepare($ret);
                  $stmt->execute();
                  $res = $stmt->get_result();
                  while ($iar = $res->fetch_object()) {
                    ?>
                    <tr>
                      <td><?php echo $iar->entity_name; ?></td>
                      <td><?php echo $iar->fund_cluster; ?></td>
                      <td><?php echo $iar->supplier; ?></td>
                      <td><?php echo $iar->po_no_date; ?></td>
                      <td><?php echo $iar->req_office; ?></td>
                      <td><?php echo $iar->responsibility_center; ?></td>
                      <td><?php echo $iar->iar_no; ?></td>
                      <td><?php echo $iar->iar_date; ?></td>
                      <td><?php echo $iar->stock_no; ?></td>
                      <td><?php echo $iar->remarks; ?></td>
                      <td><?php echo $iar->item_description; ?></td>
                      <td><?php echo $iar->unit; ?></td>
                      <td><?php echo $iar->quantity; ?></td>
                      <td><?php echo $iar->unit_price; ?></td>
                      <td><?php echo $iar->total_price; ?></td>
                      <td><?php echo $iar->receiver_name; ?></td>
                      <td><?php echo $iar->teacher_id; ?></td>
                      <td><?php echo $iar->position; ?></td>
                      <td><?php echo $iar->date_inspected; ?></td>
                      <td><?php echo $iar->inspectors; ?></td>
                      <td><?php echo $iar->barangay_councilor; ?></td>
                      <td><?php echo $iar->pta_observer; ?></td>
                      <td><?php echo $iar->date_received; ?></td>
                      <td><?php echo $iar->property_custodian; ?></td>
                      <td>
                        <a href="display_iar.php?delete=<?php echo $iar->id; ?>">
                          <button class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i>
                            Delete
                          </button>
                        </a>

                        <a href="iar_update.php?update=<?php echo $iar  ->id; ?>">
                          <button class="btn btn-sm btn-primary">
                            <i class="fas fa-user-edit"></i>
                            Update
                          </button>
                        </a>
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