<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
//Delete Staff
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $adn = "DELETE FROM  requisition_and_issue_slip  WHERE  id = ?";
  $stmt = $mysqli->prepare($adn);
  $stmt->bind_param('s', $id);
  $stmt->execute();
  $stmt->close();
  if ($stmt) {
    $success = "Deleted" && header("refresh:1; url=display_ris.php");
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
                <h2 class="text-center mb-3 pt-3 text-uppercase">Requisition and Issue Slip</h2>
              </div>
              <div class="col text-right">
                <a href="print_ris_files.php" class="btn btn-sm btn-primary">
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
                    <th scope="col">Division</th>
                    <th scope="col">Office</th>
                    <th scope="col">Responsibility Center Code</th>
                    <th scope="col">RIS No.</th>
                    <th scope="col">Stock No.</th>
                    <th scope="col">unit</th>
                    <th scope="col">Item Description</th>
                    <th scope="col">Requested Quantity</th>
                    <th scope="col">Stock Available(YES/NO)</th>
                    <th scope="col">Issued Quantity</th>
                    <th scope="col">Remarks</th>
                    <th scope="col">Purpose</th>
                    <th scope="col">Name Requested</th>
                    <th scope="col">Designation</th>
                    <th scope="col">Date</th>
                    <th scope="col">Name Approved</th>
                    <th scope="col">Designation</th>
                    <th scope="col">Date</th>
                    <th scope="col">Name Issued</th>
                    <th scope="col">Designation</th>
                    <th scope="col">Date</th>
                    <th scope="col">Name Recieved</th>
                    <th scope="col">Designation</th>
                    <th scope="col">Date</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $ret = "SELECT 
                        r.*,
                        e.entity_name,
                        e.fund_cluster,
                        i.stock_no,
                        i.item_description,
                        i.unit,
                        ri.requested_qty,
                        ri.stock_available,
                        ri.issued_qty,
                        ri.remarks
                      FROM requisition_and_issue_slips r
                      JOIN entities e ON r.entity_id = e.entity_id
                      JOIN ris_items ri ON r.ris_id = ri.ris_id
                      JOIN items i ON ri.item_id = i.item_id
                      ORDER BY r.created_at DESC";
                  $stmt = $mysqli->prepare($ret);
                  $stmt->execute();
                  $res = $stmt->get_result();
                  while ($ris = $res->fetch_object()) {
                    ?>
                    <tr>
                      <td><?php echo $ris->entity_name; ?></td>
                      <td><?php echo $ris->fund_cluster; ?></td>
                      <td><?php echo $ris->division; ?></td>
                      <td><?php echo $ris->office; ?></td>
                      <td><?php echo $ris->responsibility_code; ?></td>
                      <td><?php echo $ris->ris_no; ?></td>
                      <td><?php echo $ris->stock_no; ?></td>
                      <td><?php echo $ris->unit; ?></td>
                      <td><?php echo $ris->item_description; ?></td>
                      <td><?php echo $ris->requested_qty; ?></td>
                      <td><?php echo $ris->stock_available; ?></td>
                      <td><?php echo $ris->issued_qty; ?></td>
                      <td><?php echo $ris->remarks; ?></td>
                      <td><?php echo $ris->purpose; ?></td>
                      <td><?php echo $ris->requested_by_name; ?></td>
                      <td><?php echo $ris->requested_by_designation; ?></td>
                      <td><?php echo $ris->requested_by_date; ?></td>
                      <td><?php echo $ris->approved_by_name; ?></td>
                      <td><?php echo $ris->approved_by_designation; ?></td>
                      <td><?php echo $ris->approved_by_date; ?></td>
                      <td><?php echo $ris->issued_by_name; ?></td>
                      <td><?php echo $ris->issued_by_designation; ?></td>
                      <td><?php echo $ris->issued_by_date; ?></td>
                      <td><?php echo $ris->received_by_name; ?></td>
                      <td><?php echo $ris->received_by_designation; ?></td>
                      <td><?php echo $ris->received_by_date; ?></td>
                      <td>
                        <a href="display_ris.php?delete=<?php echo $ris->id; ?>" class="btn btn-sm btn-danger">
                          <i class="fas fa-trash"></i> Delete
                        </a>

                        <a href="ris_update.php?update=<?php echo $ris->id; ?>" class="btn btn-sm btn-primary">
                          <i class="fas fa-user-edit"></i> Update
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