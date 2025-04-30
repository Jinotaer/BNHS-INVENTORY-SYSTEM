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
if (isset($_GET['delete']) && isset($_GET['table']) && isset($_GET['item_id'])) {
  $id = $_GET['delete']; // This is the main record ID (iar_id, ics_id, etc.)
  $table = $_GET['table']; // The main table name
  $item_id = $_GET['item_id']; // This is the specific item ID (iar_item_id, ics_item_id, etc.)
  
  // Map tables to their items tables
  $item_tables = [
    'inspection_acceptance_reports' => 'iar_items',
    'inventory_custodian_slips' => 'ics_items',
    'requisition_and_issue_slips' => 'ris_items',
    'property_acknowledgment_receipts' => 'par_items'
  ];
  
  // Map item tables to their ID columns
  $item_id_columns = [
    'iar_items' => 'iar_item_id',
    'ics_items' => 'ics_item_id',
    'ris_items' => 'ris_item_id',
    'par_items' => 'par_item_id'
  ];
  
  // Get the correct items table and ID column
  $items_table = isset($item_tables[$table]) ? $item_tables[$table] : '';
  $item_id_column = isset($item_id_columns[$items_table]) ? $item_id_columns[$items_table] : '';
  
  if (!empty($items_table) && !empty($item_id_column)) {
    // Debug information
    error_log("Deleting from table: $items_table where $item_id_column = $item_id");
    
    // Delete the specific item record
    $adn = "DELETE FROM `$items_table` WHERE `$item_id_column` = ?";
    $stmt = $mysqli->prepare($adn);
    if ($stmt) {
      $stmt->bind_param('i', $item_id);
      $result = $stmt->execute();
      $stmt->close();
      if ($result) {
        $success = "Item deleted successfully";
        header("refresh:1; url=track_inventory.php");
      } else {
        $err = "Error deleting item: " . $mysqli->error;
        error_log("MySQL Error: " . $mysqli->error);
        // header("refresh:1; url=track_inventory.php");
      }
    } else {
      $err = "Error preparing delete statement: " . $mysqli->error;
      error_log("MySQL Prepare Error: " . $mysqli->error);
      // header("refresh:1; url=track_inventory.php");
    }
  } else {
    $err = "Invalid table or item table";
    header("refresh:1; url=track_inventory.php");
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
    $sql = "SELECT DISTINCT m.*, i.item_description, i.unit_cost, ii.quantity, ii.iar_item_id,
           (ii.quantity * i.unit_cost) as total_amount,
           '$table' as source_table 
           FROM `$table` m
           JOIN {$config['items_table']} ii ON m.{$config['id_column']} = ii.{$config['id_column']}
           JOIN items i ON ii.item_id = i.item_id
           WHERE i.item_description LIKE CONCAT('%', ?, '%')
           OR m.receiver_name LIKE CONCAT('%', ?, '%')
           OR m.end_user_name LIKE CONCAT('%', ?, '%')
           OR m.received_by_name LIKE CONCAT('%', ?, '%')";
    
    // Special handling for requisition_and_issue_slips
    if ($table == 'requisition_and_issue_slips') {
      $sql = "SELECT DISTINCT m.*, i.item_description, i.unit_cost, ii.issued_qty as quantity, ii.ris_item_id,
             (ii.issued_qty * i.unit_cost) as total_amount,
             '$table' as source_table 
             FROM `$table` m
             JOIN {$config['items_table']} ii ON m.{$config['id_column']} = ii.{$config['id_column']}
             JOIN items i ON ii.item_id = i.item_id
             WHERE i.item_description LIKE CONCAT('%', ?, '%')
             OR m.receiver_name LIKE CONCAT('%', ?, '%')
             OR m.end_user_name LIKE CONCAT('%', ?, '%')
             OR m.received_by_name LIKE CONCAT('%', ?, '%')";
    }
    
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
      $stmt->bind_param('ssss', $search, $search, $search, $search);
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
              <form class="form-inline" method="GET" style="color: black; float: left; margin-top: 20px; margin-bottom: 20px;">
                <input id="search" class="form-control mr-sm-2" style="width: 500px; color: black;" type="search" name="item"
                  placeholder="Search by Item Description or End User" aria-label="Search"
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
                        <td><?php echo ucfirst(str_replace('_', ' ', $item->source_table)); ?></td>
                        <td><?php echo $item->item_description ?? 'N/A'; ?></td>
                        <td><?php echo $item->{$item->source_table . '_no'} ?? 'N/A'; ?></td>
                        <td><?php echo $item->receiver_name ?? $item->end_user_name ?? $item->received_by_name ?? 'N/A'; ?></td>
                        <td><?php echo $item->created_at ?? 'N/A'; ?></td>
                        <td><?php echo $item->unit_cost ?? '0.00'; ?></td>
                        <td><?php echo $item->quantity ?? '0'; ?></td>
                        <td><?php echo $item->total_amount ?? '0.00'; ?></td>
                        <td><?php echo $item->property_custodian ?? $item->custodian_name ?? $item->issued_by_name ?? 'N/A'; ?></td>
                        <td>
                          <a href="track_view.php?id=<?php echo $item->{$tables[$item->source_table]['id_column']}; ?>&table=<?php echo $item->source_table; ?>">
                            <button class="btn btn-sm btn-info"><i class="fas fa-eye"></i> View</button>
                          </a>
                          <!-- <a href="track_inventory.php?delete=<?php echo $item->{$tables[$item->source_table]['id_column']}; ?>&table=<?php echo $item->source_table; ?>&item_id=<?php 
                            // Get the items table item ID
                            $items_table = $tables[$item->source_table]['items_table'];
                            $item_id_field = "{$items_table}_id";
                            if ($item->source_table == 'requisition_and_issue_slips' && $items_table == 'ris_items') {
                                $item_id_field = "ris_item_id";
                            }elseif($item->source_table == 'property_acknowledgment_receipts' && $items_table == 'par_items'){
                              $item_id_field = "par_item_id";
                            }elseif($item->source_table == 'inventory_custodian_slips' && $items_table == 'ics_items'){
                              $item_id_field = "ics_item_id";
                            }elseif($item->source_table == 'inspection_acceptance_reports' && $items_table == 'iar_items'){
                              $item_id_field = "iar_item_id";
                            }

                            // Debug the field name
                            error_log("Looking for field: $item_id_field in item object");
                            echo isset($item->$item_id_field) ? $item->$item_id_field : '0';
                          ?>"
                            onclick="return confirm('Are you sure you want to delete this record?')">
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Delete</button>
                          </a>
                          <a href="track_inventory_update.php?id=<?php echo $item->{$tables[$item->source_table]['id_column']}; ?>&table=<?php echo $item->source_table; ?>">
                            <button class="btn btn-sm btn-primary"><i class="fas fa-user-edit"></i> Update</button>
                          </a> -->
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
                      $sql = "SELECT DISTINCT m.*, i.item_description, i.unit_cost, ii.quantity, ii.iar_item_id,
                             (ii.quantity * i.unit_cost) as total_amount,
                             '$table' as source_table
                             FROM `$table` m
                             JOIN {$config['items_table']} ii ON m.{$config['id_column']} = ii.{$config['id_column']}
                             JOIN items i ON ii.item_id = i.item_id
                             ORDER BY m.created_at DESC";

                      // Make special adjustment for the requisition_and_issue_slips table
                      if ($table == 'requisition_and_issue_slips') {
                        $sql = "SELECT DISTINCT m.*, i.item_description, i.unit_cost, ii.issued_qty as quantity, ii.ris_item_id,
                              (ii.issued_qty * i.unit_cost) as total_amount,
                              '$table' as source_table
                              FROM `$table` m
                              JOIN {$config['items_table']} ii ON m.{$config['id_column']} = ii.{$config['id_column']}
                              JOIN items i ON ii.item_id = i.item_id
                              ORDER BY m.created_at DESC";
                      }
                      elseif ($table == 'inventory_custodian_slips') {
                        $sql = "SELECT DISTINCT m.*, i.item_description, i.unit_cost, ii.quantity, ii.ics_item_id,
                              (ii.quantity * i.unit_cost) as total_amount,
                              '$table' as source_table
                              FROM `$table` m
                              JOIN {$config['items_table']} ii ON m.{$config['id_column']} = ii.{$config['id_column']}
                              JOIN items i ON ii.item_id = i.item_id
                              ORDER BY m.created_at DESC";
                      }
                      elseif ($table == 'property_acknowledgment_receipts') {
                        $sql = "SELECT DISTINCT m.*, i.item_description, i.unit_cost, ii.quantity, ii.par_item_id,
                              (ii.quantity * i.unit_cost) as total_amount,
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
                                  <button class="btn btn-sm btn-primary"><i class="fas fa-eye"></i> View</button>
                                </a>
                                <!-- <a href="track_inventory.php?delete=<?php echo $item->{$config['id_column']}; ?>&table=<?php echo $table; ?>&item_id=<?php 
                                  // Get the items table item ID
                                  $items_table = $config['items_table'];
                                  $item_id_field = "{$items_table}_id";
                                  if ($table == 'requisition_and_issue_slips' && $items_table == 'ris_items') {
                                      $item_id_field = "ris_item_id";
                                  }elseif($table == 'property_acknowledgment_receipts' && $items_table == 'par_items'){
                                      $item_id_field = "par_item_id";
                                  }elseif($table == 'inventory_custodian_slips' && $items_table == 'ics_items'){
                                      $item_id_field = "ics_item_id";
                                  }elseif($table == 'inspection_acceptance_reports' && $items_table == 'iar_items'){
                                      $item_id_field = "iar_item_id";
                                  }
                                  // Debug the field name
                                  error_log("Looking for field: $item_id_field in item object");
                                  echo isset($item->$item_id_field) ? $item->$item_id_field : '0';
                                ?>"
                                  onclick="return confirm('Are you sure you want to delete this record?')">
                                  <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Delete</button>
                                </a>
                                <a href="track_inventory_update.php?id=<?php echo $item->{$config['id_column']}; ?>&table=<?php echo $table; ?>">
                                  <button class="btn btn-sm btn-primary"><i class="fas fa-user-edit"></i> Update</button>
                                </a> -->
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