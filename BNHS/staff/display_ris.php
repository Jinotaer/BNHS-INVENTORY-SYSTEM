<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

//Delete individual item
if (isset($_GET['delete_item'])) {
  $ris_item_id = $_GET['delete_item'];

  // Start transaction
  $mysqli->begin_transaction();

  try {
    // First get the item_id from ris_items
    $stmt = $mysqli->prepare("SELECT item_id FROM ris_items WHERE ris_item_id = ?");
    $stmt->bind_param('i', $ris_item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item_id = $result->fetch_object()->item_id;

    // Delete from ris_items
    $stmt = $mysqli->prepare("DELETE FROM ris_items WHERE ris_item_id = ?");
    $stmt->bind_param('i', $ris_item_id);
    $stmt->execute();

    // Delete from items
    $stmt = $mysqli->prepare("DELETE FROM items WHERE item_id = ?");
    $stmt->bind_param('i', $item_id);
    $stmt->execute();

    // Commit transaction
    $mysqli->commit();
    $success = "Item Deleted Successfully";
    header("refresh:1; url=display_ris.php");
  } catch (Exception $e) {
    // Rollback transaction on error
    $mysqli->rollback();
    $err = "Error: " . $e->getMessage();
    header("refresh:1; url=display_ris.php");
  }
}

//Delete RIS
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];

  // Start transaction
  $mysqli->begin_transaction();

  try {
    // Get all item_ids from ris_items for this RIS
    $stmt = $mysqli->prepare("SELECT item_id FROM ris_items WHERE ris_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Delete from ris_items first
    $stmt = $mysqli->prepare("DELETE FROM ris_items WHERE ris_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    // Delete corresponding items
    while ($row = $result->fetch_object()) {
      $stmt = $mysqli->prepare("DELETE FROM items WHERE item_id = ?");
      $stmt->bind_param('i', $row->item_id);
      $stmt->execute();
    }

    // Delete from requisition_and_issue_slips
    $stmt = $mysqli->prepare("DELETE FROM requisition_and_issue_slips WHERE ris_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    // Commit transaction
    $mysqli->commit();
    $success = "Record Deleted Successfully";
    header("refresh:1; url=display_ris.php");
  } catch (Exception $e) {
    // Rollback transaction on error
    $mysqli->rollback();
    $err = "Error: " . $e->getMessage();
    header("refresh:1; url=display_ris.php");
  }
}

require_once('partials/_head.php');
?>

<body>
  <!-- Sidenav -->
  <?php require_once('partials/_sidebar.php'); ?>

  <!-- Main content -->
  <div class="main-content">
    <!-- Top navbar -->
    <?php require_once('partials/_topnav.php'); ?>

    <!-- Header -->
    <div style="background-image: url(assets/img/theme/bnhsfront.jpg); background-size: cover;" class="header pb-8 pt-5 pt-md-8">
      <span class="mask bg-gradient-dark opacity-8"></span>
      <div class="container-fluid">
        <div class="header-body"></div>
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
              <!-- <div class="col text-right">
                <a href="print_all_ris_files.php" class="btn btn-sm btn-primary" target="_blank">
                  <i class="fas fa-print"></i> Print All RIS
                </a>
              </div> -->
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
                    <!-- <th scope="col">Stock No.</th>
                    <th scope="col">Unit</th> -->
                    <!-- <th scope="col">Item Description</th> -->
                    <!-- <th scope="col">Requested Quantity</th>
                    <th scope="col">Stock Available</th>
                    <th scope="col">Issued Quantity</th>
                    <th scope="col">Remarks</th>
                    <th scope="col">Purpose</th> -->
                    <th scope="col">Name Requested</th>
                    <th scope="col">Designation</th>
                    <th scope="col">Date</th>
                    <th scope="col">Name Approved</th>
                    <th scope="col">Designation</th>
                    <th scope="col">Date</th>
                    <th scope="col">Name Issued</th>
                    <th scope="col">Designation</th>
                    <th scope="col">Date</th>
                    <th scope="col">Name Received</th>
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
                        e.fund_cluster
                      FROM requisition_and_issue_slips r
                      JOIN entities e ON r.entity_id = e.entity_id
                      GROUP BY r.ris_no
                      ORDER BY r.created_at DESC, r.ris_id";
                  $stmt = $mysqli->prepare($ret);
                  $stmt->execute();
                  $res = $stmt->get_result();
                  while ($ris = $res->fetch_object()) {
                    // Get one item from this RIS to display as an example
                    $item_query = "SELECT 
                        i.stock_no,
                        -- i.item_description,
                        i.unit,
                        i.item_id,
                        ri.ris_item_id,
                        ri.requested_qty,
                        ri.stock_available,
                        ri.issued_qty,
                        ri.remarks
                      FROM ris_items ri
                      JOIN items i ON ri.item_id = i.item_id
                      WHERE ri.ris_id = ?
                      LIMIT 1";
                    $item_stmt = $mysqli->prepare($item_query);
                    $item_stmt->bind_param('i', $ris->ris_id);
                    $item_stmt->execute();
                    $item_res = $item_stmt->get_result();

                    // Check if we have an item for this RIS
                    if ($item_res->num_rows > 0) {
                      $item = $item_res->fetch_object();

                      // Safe access to item properties
                      $stock_no = $item->stock_no ?? '';
                      $unit = $item->unit ?? '';
                      // $item_description = $item->item_description ?? '';
                      $requested_qty = $item->requested_qty ?? 0;
                      $stock_available = $item->stock_available ?? 0;
                      $issued_qty = $item->issued_qty ?? 0;
                      $remarks = $item->remarks ?? '';
                    } else {
                      // Set default values if no item is found
                      $stock_no = '';
                      $unit = '';
                      // $item_description = '';
                      $requested_qty = 0;
                      $stock_available = 0;
                      $issued_qty = 0;
                      $remarks = '';
                    }
                  ?>
                    <tr>
                      <td><?php echo htmlspecialchars($ris->entity_name); ?></td>
                      <td><?php echo htmlspecialchars($ris->fund_cluster); ?></td>
                      <td><?php echo htmlspecialchars($ris->division); ?></td>
                      <td><?php echo htmlspecialchars($ris->office); ?></td>
                      <td><?php echo htmlspecialchars($ris->responsibility_code); ?></td>
                      <td><?php echo htmlspecialchars($ris->ris_no); ?></td>
                      <!-- <td><?php echo htmlspecialchars($stock_no); ?></td>
                      <td><?php echo htmlspecialchars($unit); ?></td> -->
                      <!-- <td><?php // echo htmlspecialchars($item_description); 
                                ?></td> -->
                      <!-- <td><?php echo htmlspecialchars($requested_qty); ?></td>
                      <td><?php echo htmlspecialchars($stock_available); ?></td>
                      <td><?php echo htmlspecialchars($issued_qty); ?></td>
                      <td><?php echo htmlspecialchars($remarks); ?></td> -->
                      <!-- s<td><?php echo htmlspecialchars($ris->purpose); ?></td> -->
                      <td><?php echo htmlspecialchars($ris->requested_by_name); ?></td>
                      <td><?php echo htmlspecialchars($ris->requested_by_designation); ?></td>
                      <td><?php echo !empty($ris->requested_by_date) ? date('M d, Y', strtotime($ris->requested_by_date)) : ''; ?></td>
                      <td><?php echo htmlspecialchars($ris->approved_by_name); ?></td>
                      <td><?php echo htmlspecialchars($ris->approved_by_designation); ?></td>
                      <td><?php echo !empty($ris->approved_by_date) ? date('M d, Y', strtotime($ris->approved_by_date)) : ''; ?></td>
                      <td><?php echo htmlspecialchars($ris->issued_by_name); ?></td>
                      <td><?php echo htmlspecialchars($ris->issued_by_designation); ?></td>
                      <td><?php echo !empty($ris->issued_by_date) ? date('M d, Y', strtotime($ris->issued_by_date)) : ''; ?></td>
                      <td><?php echo htmlspecialchars($ris->received_by_name); ?></td>
                      <td><?php echo htmlspecialchars($ris->received_by_designation); ?></td>
                      <td><?php echo !empty($ris->received_by_date) ? date('M d, Y', strtotime($ris->received_by_date)) : ''; ?></td>
                      <td>
                        <!-- <a href="display_ris.php?delete=<?php echo $ris->ris_id; ?>" >
                          <button class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i>
                            Delete All
                          </button>
                        </a> -->

                        <a href="ris_update.php?update=<?php echo $ris->ris_id; ?>">
                          <button class="btn btn-sm btn-primary">
                            <i class="fas fa-user-edit"></i>
                            Update
                          </button>
                        </a>

                        <a href="print_ris_files.php?ris_id=<?php echo $ris->ris_id; ?>" target="_blank">
                          <button class="btn btn-sm btn-info">
                            <i class="fas fa-print"></i>
                            Print File
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
      <?php require_once('partials/_mainfooter.php'); ?>
    </div>
  </div>
  <!-- Argon Scripts -->
  <?php require_once('partials/_scripts.php'); ?>

  <style>
    .table-responsive {
      max-height: 500px;
      overflow-y: auto;
    }

    .btn-group {
      display: flex;
      gap: 5px;
    }
  </style>
</body>

</html>