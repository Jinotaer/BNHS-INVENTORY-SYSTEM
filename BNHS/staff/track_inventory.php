<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

// Check for success message in URL
if (isset($_GET['success'])) {
  $success = $_GET['success'];
}

// Handle delete
if (isset($_GET['delete']) && isset($_GET['table'])) {
  $id = $_GET['delete'];
  $table = $_GET['table'];
  
  // Special handling for inventory_custodian_slips
  if ($table === 'inventory_custodian_slips') {
    // First delete related items in ics_items
    $adn = "DELETE FROM ics_items WHERE ics_id = ?";
    $stmt = $mysqli->prepare($adn);
    if ($stmt) {
      $stmt->bind_param('i', $id);
      $stmt->execute();
      $stmt->close();
    }
  }
  
  // Now delete the main record
  $adn = "DELETE FROM `$table` WHERE " . ($table === 'inventory_custodian_slips' ? 'ics_id' : 'id') . " = ?";
  $stmt = $mysqli->prepare($adn);
  if ($stmt) {
    $stmt->bind_param('i', $id);
    $result = $stmt->execute();
    $stmt->close();
    if ($result) {
      $success = "Deleted";
      header("refresh:1; url=track_inventory.php");
    } else {
      $err = "Error executing delete statement: " . $mysqli->error;
    }
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
    'inspection_acceptance_reports' => ['items_table' => 'iar_items', 'id_column' => 'iar_id', 'no_column' => 'iar_no'],
    'inventory_custodian_slips' => ['items_table' => 'ics_items', 'id_column' => 'ics_id', 'no_column' => 'ics_no'],
    'requisition_and_issue_slips' => ['items_table' => 'ris_items', 'id_column' => 'ris_id', 'no_column' => 'ris_no'],
    'property_acknowledgment_receipts' => ['items_table' => 'par_items', 'id_column' => 'par_id', 'no_column' => 'par_no']
  ];
  
  foreach ($tables as $table => $config) {
    $sql = "SELECT DISTINCT m.*, i.item_description, i.unit_cost, ii.quantity, 
           (ii.quantity * i.unit_cost) as total_amount,
           '$table' as source_table 
           FROM `$table` m
           JOIN {$config['items_table']} ii ON m.{$config['id_column']} = ii.{$config['id_column']}
           JOIN items i ON ii.item_id = i.item_id
           WHERE i.item_description LIKE CONCAT('%', ?, '%')";
    
    // Special handling for requisition_and_issue_slips
    if ($table == 'requisition_and_issue_slips') {
      $sql = "SELECT DISTINCT m.*, i.item_description, i.unit_cost, ii.issued_qty as quantity, 
             (ii.issued_qty * i.unit_cost) as total_amount,
             '$table' as source_table 
             FROM `$table` m
             JOIN {$config['items_table']} ii ON m.{$config['id_column']} = ii.{$config['id_column']}
             JOIN items i ON ii.item_id = i.item_id
             WHERE i.item_description LIKE CONCAT('%', ?, '%')";
    }
    
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
                        <td><?php echo $item->{$item->source_table . '_no'} ?? 'N/A'; ?></td>
                        <td><?php echo $item->receiver_name ?? $item->end_user_name ?? $item->received_by_name ?? 'N/A'; ?></td>
                        <td><?php echo $item->created_at ?? 'N/A'; ?></td>
                        <td><?php echo $item->unit_cost ?? '0.00'; ?></td>
                        <td><?php echo $item->quantity ?? '0'; ?></td>
                        <td><?php echo $item->total_amount ?? '0.00'; ?></td>
                        <td><?php echo $item->property_custodian ?? $item->custodian_name ?? $item->issued_by_name ?? 'N/A'; ?></td>
                        <td>
                          <a href="track_view.php?id=<?php echo $item->{$item->source_table . '_id'}; ?>&table=<?php echo $item->source_table; ?>">
                            <button class="btn btn-sm btn-info"><i class="fas fa-eye"></i> View</button>
                          </a>
                          <a href="track_inventory.php?delete=<?php echo $item->{$item->source_table . '_id'}; ?>&table=<?php echo $item->source_table; ?>"
                             onclick="return confirm('Are you sure you want to delete this record?')">
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Delete</button>
                          </a>
                          <a href="track_inventory_update.php?id=<?php echo $item->{$item->source_table . '_id'}; ?>&table=<?php echo $item->source_table; ?>">
                            <button class="btn btn-sm btn-primary"><i class="fas fa-user-edit"></i> Update</button>
                          </a>
                        </td>
                      </tr>
                      <?php
                    }
                  } else {
                    // Display all inventory items if no search query
                    $tables = [
                      'inspection_acceptance_reports' => ['items_table' => 'iar_items', 'id_column' => 'iar_id', 'no_column' => 'iar_no'],
                      'inventory_custodian_slips' => ['items_table' => 'ics_items', 'id_column' => 'ics_id', 'no_column' => 'ics_no'],
                      'requisition_and_issue_slips' => ['items_table' => 'ris_items', 'id_column' => 'ris_id', 'no_column' => 'ris_no'],
                      'property_acknowledgment_receipts' => ['items_table' => 'par_items', 'id_column' => 'par_id', 'no_column' => 'par_no']
                    ];
                    
                    foreach ($tables as $table => $config) {
                      $sql = "SELECT DISTINCT m.*, i.item_description, i.unit_cost, ii.quantity,
                             (ii.quantity * i.unit_cost) as total_amount,
                             '$table' as source_table
                             FROM `$table` m
                             JOIN {$config['items_table']} ii ON m.{$config['id_column']} = ii.{$config['id_column']}
                             JOIN items i ON ii.item_id = i.item_id
                             ORDER BY m.created_at DESC";
                      
                      // Make special adjustment for the requisition_and_issue_slips table
                      if ($table == 'requisition_and_issue_slips') {
                        $sql = "SELECT DISTINCT m.*, i.item_description, i.unit_cost, ii.issued_qty as quantity,
                              (ii.issued_qty * i.unit_cost) as total_amount,
                              '$table' as source_table
                              FROM `$table` m
                              JOIN {$config['items_table']} ii ON m.{$config['id_column']} = ii.{$config['id_column']}
                              JOIN items i ON ii.item_id = i.item_id
                              ORDER BY m.created_at DESC";
                      }
                      
                      // Add debugging
                      error_log("Executing query for table $table: " . $sql);
                      
                      $stmt = $mysqli->prepare($sql);
                      if ($stmt) {
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        // Add debugging
                        error_log("Query for $table returned " . ($result ? $result->num_rows : 0) . " rows");
                        
                        if ($result && $result->num_rows > 0) {
                          while ($item = $result->fetch_object()) {
                            ?>
                            <tr>
                              <td><?php echo ucfirst(str_replace('_', ' ', $table)); ?></td>
                              <td><?php echo $item->item_description ?? 'N/A'; ?></td>
                              <td><?php echo $item->{$config['no_column']} ?? 'N/A'; ?></td>
                              <td><?php echo $item->receiver_name ?? $item->end_user_name ?? $item->received_by_name ?? 'N/A'; ?></td>
                              <td><?php echo $item->created_at ?? 'N/A'; ?></td>
                              <td><?php echo $item->unit_cost ?? '0.00'; ?></td>
                              <td><?php echo $item->quantity ?? '0'; ?></td>
                              <td><?php echo $item->total_amount ?? '0.00'; ?></td>
                              <td><?php echo $item->property_custodian ?? $item->custodian_name ?? $item->issued_by_name ?? 'N/A'; ?></td>
                              <td>
                                <a href="track_view.php?id=<?php echo $item->{$config['id_column']}; ?>&table=<?php echo $table; ?>">
                                  <button class="btn btn-sm btn-info"><i class="fas fa-eye"></i> View</button>
                                </a>
                                <a href="track_inventory.php?delete=<?php echo $item->{$config['id_column']}; ?>&table=<?php echo $table; ?>"
                                   onclick="return confirm('Are you sure you want to delete this record?')">
                                  <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Delete</button>
                                </a>
                                <a href="track_inventory_update.php?id=<?php echo $item->{$config['id_column']}; ?>&table=<?php echo $table; ?>">
                                  <button class="btn btn-sm btn-primary"><i class="fas fa-user-edit"></i> Update</button>
                                </a>
                              </td>
                            </tr>
                            <?php
                          }
                        }
                        $stmt->close();
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