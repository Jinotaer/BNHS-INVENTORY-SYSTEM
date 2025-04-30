<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
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
    <div style="background-image: url(assets/img/theme/front.png); background-size: fill;"
      class="header  pb-8 pt-5 pt-md-8">
      <span class="mask bg-gradient-dark opacity-8"></span>
      <div class="container-fluid">
        <div class="header-body">
          <!-- Card stats -->
          <?php
          $ret = "SELECT COUNT(*) AS total_accounts FROM (SELECT id FROM inspection_acceptance_reports) AS combined_accounts";
          $stmt = $mysqli->prepare($ret);
          $stmt->execute();
          $res = $stmt->get_result();
          $user_count = $res->fetch_object()->total_accounts;
          ?>
          <div class="row">
            <div class="col-xl-3 col-lg-6">
              <div class="card card-stats mb-4 mb-xl-0">
                <div class="card-body">
                  <div class="row">
                    <div class="col">
                      <h5 class="card-title text-uppercase text-muted mb-0">Inspection Acceptance Reports</h5>
                      <span class="h2 font-weight-bold mb-0"><?php echo $user_count; ?></span>
                    </div>
                    <div class="col-auto">
                      <div class="icon icon-shape bg-danger text-white rounded-circle shadow">
                        <i class="material-icons-sharp">fact_check</i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <?php
            $ret = "SELECT COUNT(*) AS total_accounts FROM (SELECT id FROM inventory_custodian_slip) AS combined_accounts";
            $stmt = $mysqli->prepare($ret);
            $stmt->execute();
            $res = $stmt->get_result();
            $user_count = $res->fetch_object()->total_accounts;
            ?>
            <div class="col-xl-3 col-lg-6">
              <div class="card card-stats mb-4 mb-xl-0">
                <div class="card-body">
                  <div class="row">
                    <div class="col">
                      <h5 class="card-title text-uppercase text-muted mb-0">Inventory Custodian Slip</h5>
                      <span class="h2 font-weight-bold mb-0"><?php echo $user_count; ?></span>
                    </div>
                    <div class="col-auto">
                      <div class="icon icon-shape bg-primary text-white rounded-circle shadow">
                        <i class="material-icons-sharp">inventory_2</i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <?php
            $ret = "SELECT COUNT(*) AS total_accounts FROM (SELECT id FROM property_acknowledgment_receipt) AS combined_accounts";
            $stmt = $mysqli->prepare($ret);
            $stmt->execute();
            $res = $stmt->get_result();
            $user_count = $res->fetch_object()->total_accounts;
            ?>
            <div class="col-xl-3 col-lg-6">
              <div class="card card-stats mb-4 mb-xl-0">
                <div class="card-body">
                  <div class="row">
                    <div class="col">
                      <h5 class="card-title text-uppercase text-muted mb-0">Property Ack. Receipt</h5>
                      <span class="h2 font-weight-bold mb-0"><?php echo $user_count; ?></span>
                    </div>
                    <div class="col-auto">
                      <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                        <i class="material-icons-sharp">receipt_long</i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <?php
            $ret = "SELECT COUNT(*) AS total_accounts FROM (SELECT id FROM requisition_and_issue_slip ) AS combined_accounts";
            $stmt = $mysqli->prepare($ret);
            $stmt->execute();
            $res = $stmt->get_result();
            $user_count = $res->fetch_object()->total_accounts;
            ?>
            <div class="col-xl-3 col-lg-6">
              <div class="card card-stats mb-4 mb-xl-0">
                <div class="card-body">
                  <div class="row">
                    <div class="col">
                      <h5 class="card-title text-uppercase text-muted mb-0">Requisition and Issue Slip</h5>
                      <span class="h2 font-weight-bold mb-0"><?php echo $user_count; ?></span>
                    </div>
                    <div class="col-auto">
                      <div class="icon icon-shape bg-green text-white rounded-circle shadow">
                        <i class="material-icons-sharp">request_quote</i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Page content -->
    <div class="container-fluid mt--7">
      <div class="row mt-5">
        <div class="col-xl-12 mb-5 mb-xl-0">
          <div class="card shadow">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col">
                  <h3 class="mb-0">REPORT ON THE PHYSICAL COUNT OF PROPERTY, PLANT AND EQUIPMENT</h3>
                </div>
                <div class="col text-right">
                  <a href="dashboard_seeall_rpcppe.php" class="btn btn-sm btn-primary">See all</a>
                </div>
              </div>
            </div>
            <div class="table-responsive">
              <!-- Projects table -->
              <table class="table align-items-center table-flush">
                <thead class="thead-light">
                  <tr>
                    <th class="text-primary" scope="col">Entity Name</th>
                    <th scope="col">Fund Cluster</th>
                    <th class="text-primary" scope="col">PAR No.</th>
                    <th scope="col">Quantity</th>
                    <th class="text-primary" scope="col">Unit</th>
                    <th scope="col">Item Description</th>
                    <th class="text-primary" scope="col">Property Number</th>
                    <th scope="col">Data Acquired</th>
                    <th class="text-primary" scope="col">Unit Cost</th>
                    <th scope="col">Total Cost</th>
                    <th class="text-primary" scope="col">User Name</th>
                    <th scope="col">Position/Office</th>
                    <th class="text-primary" scope="col">Date</th>
                    <th scope="col">Property Custodian Name</th>
                    <th class="text-primary" scope="col">Position/Office</th>
                    <th scope="col">Date</th>
                    <th class="text-primary" scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $ret = "SELECT * FROM  property_acknowledgment_receipt  ORDER BY `property_acknowledgment_receipt`.`created_at` DESC ";
                  $stmt = $mysqli->prepare($ret);
                  $stmt->execute();
                  $res = $stmt->get_result();
                  while ($par = $res->fetch_object()) {
                    ?>
                    <tr>
                      <td class="text-primary"><?php echo $par->entity_name; ?></td>
                      <td><?php echo $par->fund_cluster; ?></td>
                      <td class="text-primary"><?php echo $par->par_no; ?></td>
                      <td><?php echo $par->quantity; ?></td>
                      <td class="text-primary"><?php echo $par->unit; ?></td>
                      <td><?php echo $par->item_description; ?></td>
                      <td class="text-primary"><?php echo $par->property_number; ?></td>
                      <td><?php echo $par->date_acquired; ?></td>
                      <td class="text-primary"><?php echo $par->unit_cost; ?></td>
                      <td><?php echo $par->total_amount; ?></td>
                      <td class="text-primary"><?php echo $par->end_user_name; ?></td>
                      <td><?php echo $par->receiver_position; ?></td>
                      <td class="text-primary"><?php echo $par->receiver_date; ?></td>
                      <td><?php echo $par->custodian_name; ?></td>
                      <td class="text-primary"><?php echo $par->custodian_position; ?></td>
                      <td><?php echo $par->custodian_date; ?></td>
                      <td>
                        <a href="display_par.php?delete=<?php echo $par->id; ?>">
                          <button class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i>
                            Delete
                          </button>
                        </a>

                        <a href="par_update.php?update=<?php echo $par->id; ?>">
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

      <div class="row mt-5">
        <div class="col-xl-12">
          <div class="card shadow">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col">
                  <h3 class="mb-0">REPORT ON THE PHYSICAL COUNT OF SEMI- EXPENDABLE PROPERTY</h3>
                </div>
                <div class="col text-right">
                  <a href="dashboard_seeall_rpcsp.php" class="btn btn-sm btn-primary">See all</a>
                </div>
              </div>
            </div>
            <div class="table-responsive">
              <!-- Projects table -->
              <table class="table align-items-center table-flush">
                <thead class="thead-light">
                  <tr>
                    <th class="text-primary" scope="col">Entity Name</th>
                    <th scope="col">Fund Cluster</th>
                    <th class="text-primary" scope="col">ICS No.</th>
                    <th scope="col">Quantity</th>
                    <th class="text-primary" scope="col">Unit</th>
                    <th scope="col">Unit Cost</th>
                    <th class="text-primary" scope="col">Total Amount</th>
                    <th scope="col">Item Description</th>
                    <th class="text-primary" scope="col">Inventory Item No.</th>
                    <th scope="col">Estimated Useful Life</th>
                    <th class="text-primary" scope="col">User Name</th>
                    <th scope="col">Position/Office</th>
                    <th class="text-primary" scope="col">Date Received(by User)</th>
                    <th scope="col">Property Custodian</th>
                    <th class="text-primary" scope="col">Position/Office</th>
                    <th scope="col">Date Received(by Custodian)</th>
                    <th class="text-primary" scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $ret = "SELECT * FROM  inventory_custodian_slip  ORDER BY `inventory_custodian_slip`.`created_at` DESC ";
                  $stmt = $mysqli->prepare($ret);
                  $stmt->execute();
                  $res = $stmt->get_result();
                  while ($ics = $res->fetch_object()) {
                    ?>
                    <tr>
                      <td class="text-primary" scope="rows"><?php echo $ics->entity_name; ?></td>
                      <td><?php echo $ics->fund_cluster; ?></td>
                      <td class="text-primary"><?php echo $ics->ics_no; ?></td>
                      <td><?php echo $ics->quantity; ?></td>
                      <td class="text-primary"><?php echo $ics->unit; ?></td>
                      <td><?php echo $ics->unit_cost; ?></td>
                      <td class="text-primary"><?php echo $ics->total_amount; ?></td>
                      <td><?php echo $ics->item_description; ?></td>
                      <td class="text-primary"><?php echo $ics->inventory_item_no; ?></td>
                      <td><?php echo $ics->estimated_life; ?></td>
                      <td><?php echo $ics->end_user_name; ?></td>
                      <td class="text-primary"><?php echo $ics->end_user_position; ?></td>
                      <td><?php echo $ics->date_received_user; ?></td>
                      <td><?php echo $ics->custodian_name; ?></td>
                      <td class="text-primary"><?php echo $ics->custodian_position; ?></td>
                      <td><?php echo $ics->date_received_custodian; ?></td>
                      <td>
                        <a href="display_ics.php?delete=<?php echo $ics->id; ?>">
                          <button class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i>
                            Delete
                          </button>
                        </a>

                        <a href="ics_update.php?update=<?php echo $ics->id; ?>">
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

<!-- <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.4.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script> -->
<!-- <script src="assets/js/custom.js"></script> -->

</html>