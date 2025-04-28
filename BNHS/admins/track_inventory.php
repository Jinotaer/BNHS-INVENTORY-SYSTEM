<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

// Delete item
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $table = $_GET['table'];
  $adn = "DELETE FROM `$table` WHERE id = ?";
  $stmt = $mysqli->prepare($adn);
  if ($stmt) {
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    $success = "Deleted" && header("refresh:1; url=track_inventory.php");
  } else {
    $err = "Error preparing delete statement: " . $mysqli->error;
  }
}

// Check if a search item is submitted
$searchResults = [];
if (isset($_GET['item']) && !empty(trim($_GET['item']))) {
  $search = $mysqli->real_escape_string(trim($_GET['item']));
  
  // Search across all inventory tables
  $tables = [
    'inspection_acceptance_reports',
    'inventory_custodian_slip',
    'requisition_and_issue_slip',
    'property_acknowledgment_receipt'
  ];
  
  foreach ($tables as $table) {
    $sql = "SELECT *, '$table' as source_table FROM `$table` WHERE item_description LIKE CONCAT('%', ?, '%')";
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
      $stmt->bind_param('s', $search);
      $stmt->execute();
      $result = $stmt->get_result();
      
      if ($result) {
        while ($row = $result->fetch_object()) {
          $searchResults[] = $row;
        }
      }
      $stmt->close();
    } else {
      // Log error but continue with other tables
      error_log("Error preparing search statement for table $table: " . $mysqli->error);
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
              <form class="form-inline" method="GET" style="float: left; margin-top: 20px; margin-bottom: 20px;">
                <input id="search" class="form-control mr-sm-2" style="width: 500px;" type="search" name="item"
                  placeholder="Search Item by Description" aria-label="Search"
                  value="<?php echo isset($_GET['item']) ? htmlspecialchars($_GET['item']) : ''; ?>">
              </form>
            </div>
            <div class="table-responsive">
              <table class="table align-items-center table-flush">
                <thead class="thead-light">
                  <tr>
                    <th scope="col">Source</th>
                    <th scope="col">Item Description</th>
                    <th scope="col">Item No.</th>
                    <th scope="col">End User</th>
                    <th scope="col">Date</th>
                    <th scope="col">Unit Cost</th>
                    <th scope="col">Quantity</th>
                    <th scope="col">Total Cost</th>
                    <th scope="col">Custodian</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody class="list" id="userTableBody">
                  <?php
                  if (!empty($searchResults)) {
                    foreach ($searchResults as $item) {
                      ?>
                      <tr>
                        <td ><?php echo ucfirst(str_replace('_', ' ', $item->source_table)); ?></td>
                        <td><?php echo $item->item_description ?? 'N/A'; ?></td>
                        <td><?php echo $item->iar_no ?? $item->ics_no ?? $item->ris_no ?? $item->par_no ?? 'N/A'; ?></td>
                        <td><?php echo $item->receiver_name ?? $item->end_user_name ?? $item->received_by_name ?? 'N/A'; ?></td>
                        <td><?php echo $item->created_at ?? 'N/A'; ?></td>
                        <td><?php echo $item->unit_price ?? $item->unit_cost ?? $item->unit ?? '0.00'; ?></td>
                        <td><?php echo $item->quantity ?? '0'; ?></td>
                        <td><?php echo $item->total_price ?? $item->total_amount ?? '0.00'; ?></td>
                        <td><?php echo $item->property_custodian ?? $item->custodian_name ?? $item->issued_by_name ?? 'N/A'; ?></td>
                        <td>
                          <a href="view_item.php?id=<?php echo $item->id; ?>&table=<?php echo $item->source_table; ?>">
                            <button class="btn btn-sm btn-info"><i class="fas fa-eye"></i> View</button>
                          </a>
                          <a href="track_inventory.php?delete=<?php echo $item->id; ?>&table=<?php echo $item->source_table; ?>">
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Delete</button>
                          </a>
                          <a href="track_inventory_update.php?id=<?php echo $item->id; ?>&table=<?php echo $item->source_table; ?>">
                            <button class="btn btn-sm btn-primary"><i class="fas fa-user-edit"></i> Update</button>
                          </a>
                        </td>
                      </tr>
                      <?php
                    }
                  } else {
                    // Display all inventory items if no search query
                    $tables = [
                      'inspection_acceptance_reports',
                      'inventory_custodian_slip',
                      'requisition_and_issue_slip',
                      'property_acknowledgment_receipt'
                    ];
                    
                    foreach ($tables as $table) {
                      $sql = "SELECT *, '$table' as source_table FROM `$table` ORDER BY created_at DESC";
                      $stmt = $mysqli->prepare($sql);
                      if ($stmt) {
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result && $result->num_rows > 0) {
                          while ($item = $result->fetch_object()) {
                            ?>
                            <tr>
                              <td><?php echo ucfirst(str_replace('_', ' ', $table)); ?></td>
                              <td><?php echo $item->item_description ?? 'N/A'; ?></td>
                              <td><?php echo $item->iar_no ?? $item->ics_no ?? $item->ris_no ?? $item->par_no ?? 'N/A'; ?></td>
                              <td><?php echo $item->receiver_name ?? $item->end_user_name ?? $item->received_by_name ?? 'N/A'; ?></td>
                              <td><?php echo $item->created_at ?? 'N/A'; ?></td>
                              <td><?php echo $item->unit_price ?? $item->unit_cost ?? $item->unit ?? '0.00'; ?></td>
                              <td><?php echo $item->quantity ?? '0'; ?></td>
                              <td><?php echo $item->total_price ?? $item->total_amount ?? '0.00'; ?></td>
                              <td><?php echo $item->property_custodian ?? $item->custodian_name ?? $item->issued_by_name ?? 'N/A'; ?></td>
                              <td>
                                <a href="track_view.php?id=<?php echo $item->id; ?>&table=<?php echo $table; ?>">
                                  <button class="btn btn-sm btn-info"><i class="fas fa-eye"></i> View</button>
                                </a>
                                <a href="track_inventory.php?delete=<?php echo $item->id; ?>&table=<?php echo $table; ?>">
                                  <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Delete</button>
                                </a>
                                <a href="track_inventory_update.php?id=<?php echo $item->id; ?>&table=<?php echo $table; ?>">
                                  <button class="btn btn-sm btn-primary"><i class="fas fa-user-edit"></i> Update</button>
                                </a>
                              </td>
                            </tr>
                            <?php
                          }
                        }
                        $stmt->close();
                      } else {
                        // Log error but continue with other tables
                        error_log("Error preparing statement for table $table: " . $mysqli->error);
                      }
                    }
                  }
                  ?>
                  <tr id="noResults" style="display: none;">
                    <td colspan="10" class="text-center">No inventory items found.</td>
                  </tr>
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