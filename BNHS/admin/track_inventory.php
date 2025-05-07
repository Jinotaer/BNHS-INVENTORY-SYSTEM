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
           (ii.quantity * i.unit_cost) as total_amount, ii.remarks as item_remarks, i.remarks as general_remarks, i.updated_at,
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
             (ii.issued_qty * i.unit_cost) as total_amount, ii.remarks as item_remarks, i.remarks as general_remarks, i.updated_at,
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
              <div class="row align-items-center">
                <div class="col-8">
                  <h3 class="mb-0">Inventory Tracking</h3>
                </div>
                <div class="col-4 text-right">
                  <select id="entries-select" class="custom-select custom-select-sm w-auto mr-2">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                  </select>
                  <span class="mr-2">entries per page</span>
                </div>
              </div>
              <div class="row mt-3">
                <div class="col-md-6">
                  <form class="form-inline" method="GET">
                    <div class="input-group w-100">
                      <div class="input-group-prepend">
                        <span class="input-group-text">Search:</span>
                      </div>
                      <input style="color: #000000" id="search" class="form-control" type="search" name="item"
                        placeholder="Search by Item Description or End User"
                        value="<?php echo isset($_GET['item']) ? htmlspecialchars($_GET['item']) : ''; ?>">
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table align-items-center table-flush" id="inventoryTable">
                <thead class="thead-light">
                  <tr>
                    <th scope="col">Source</th>
                    <th scope="col">Item Description</th>
                    <th scope="col">Item No.</th>
                    <th scope="col">End User</th>
                    <th scope="col">Date Added</th>
                    <th scope="col">Unit Cost</th>
                    <th scope="col">Quantity</th>
                    <th scope="col">Total Cost</th>
                    <th scope="col">Custodian</th>
                    <th scope="col">Remarks</th>
                    <th scope="col">Date Updated</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  if (!empty($searchResults)) {
                    foreach ($searchResults as $item) {
                  ?>
                      <tr>
                        <td><?php echo ucfirst(str_replace('_', ' ', $item->source_table)); ?></td>
                        <td><?php echo $item->item_description ?? 'N/A'; ?></td>
                        <td><?php echo $item->{$item->source_table . '_no'} ?? 'N/A'; ?></td>
                        <td><?php echo $item->receiver_name ?? $item->end_user_name ?? $item->received_by_name ?? 'N/A'; ?></td>
                        <td><?php
                          if (!empty($item->created_at)) {
                              echo date('M j, Y g:i A', strtotime($item->created_at));
                          } else {
                              echo 'N/A';
                          }
                          ?></td>
                        <td><?php echo $item->unit_cost ?? '0.00'; ?></td>
                        <td><?php echo $item->quantity ?? '0'; ?></td>
                        <td><?php echo $item->total_amount ?? '0.00'; ?></td>
                        <td><?php echo $item->property_custodian ?? $item->custodian_name ?? $item->issued_by_name ?? 'N/A'; ?></td>
                        <td><?php echo $item->item_remarks ?? $item->general_remarks ?? 'N/A'; ?></td>
                        <td><?php
                          if (!empty($item->updated_at)) {
                              echo date('M j, Y g:i A', strtotime($item->updated_at));
                          } else {
                              echo 'N/A';
                          }
                          ?></td>
                        <td>
                          <a href="track_view.php?item_id=<?php 
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
                          ?>&table=<?php echo $item->source_table; ?>">
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
                             (ii.quantity * i.unit_cost) as total_amount, ii.remarks as item_remarks, i.remarks as general_remarks, i.updated_at,
                             '$table' as source_table
                             FROM `$table` m
                             JOIN {$config['items_table']} ii ON m.{$config['id_column']} = ii.{$config['id_column']}
                             JOIN items i ON ii.item_id = i.item_id
                             ORDER BY m.created_at DESC";

                      // Make special adjustment for the requisition_and_issue_slips table
                      if ($table == 'requisition_and_issue_slips') {
                        $sql = "SELECT DISTINCT m.*, i.item_description, i.unit_cost, ii.issued_qty as quantity, ii.ris_item_id,
                              (ii.issued_qty * i.unit_cost) as total_amount, ii.remarks as item_remarks, i.remarks as general_remarks, i.updated_at,
                              '$table' as source_table
                              FROM `$table` m
                              JOIN {$config['items_table']} ii ON m.{$config['id_column']} = ii.{$config['id_column']}
                              JOIN items i ON ii.item_id = i.item_id
                              ORDER BY m.created_at DESC";
                      }
                      elseif ($table == 'inventory_custodian_slips') {
                        $sql = "SELECT DISTINCT m.*, i.item_description, i.unit_cost, ii.quantity, ii.ics_item_id,
                              (ii.quantity * i.unit_cost) as total_amount, ii.remarks as item_remarks, i.remarks as general_remarks, i.updated_at,
                              '$table' as source_table
                              FROM `$table` m
                              JOIN {$config['items_table']} ii ON m.{$config['id_column']} = ii.{$config['id_column']}
                              JOIN items i ON ii.item_id = i.item_id
                              ORDER BY m.created_at DESC";
                      }
                      elseif ($table == 'property_acknowledgment_receipts') {
                        $sql = "SELECT DISTINCT m.*, i.item_description, i.unit_cost, ii.quantity, ii.par_item_id,
                              (ii.quantity * i.unit_cost) as total_amount, ii.remarks as item_remarks, i.remarks as general_remarks, i.updated_at,
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
                              <td><?php
                                if (!empty($item->created_at)) {
                                  echo date('M j, Y g:i A', strtotime($item->created_at));
                                } else {
                                  echo 'N/A';
                                }
                              ?></td>
                              <td><?php echo $item->unit_cost ?? '0.00'; ?></td>
                              <td><?php echo $item->quantity ?? '0'; ?></td>
                              <td><?php echo $item->total_amount ?? '0.00'; ?></td>
                              <td><?php echo $item->property_custodian ?? $item->custodian_name ?? $item->issued_by_name ?? 'N/A'; ?></td>
                              <td><?php echo $item->item_remarks ?? $item->general_remarks ?? 'N/A'; ?></td>
                              <td><?php
                                if (!empty($item->updated_at)) {
                                  echo date('M j, Y g:i A', strtotime($item->updated_at));
                                } else {
                                  echo 'N/A';
                                }
                              ?></td>
                              <td>
                                <a href="track_view.php?item_id=<?php 
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
                                ?>&table=<?php echo $table; ?>">
                                  <button class="btn btn-sm btn-info"><i class="fas fa-eye"></i> View</button>
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
                                <a href="track_inventory_update.php?id=<?php echo $item->{$config['id_column']}; ?>&table=<?php echo $table; ?>&item_id=<?php 
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
                                ?>">
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
                </tbody>
              </table>
              
              <div class="d-flex justify-content-between align-items-center mt-3 mb-3 px-3">
              
            </div>
          </div>
        </div>
      </div>
    
    </div>
  </div>
    <!-- Footer -->
  <?php
      require_once('partials/_mainfooter.php');
  ?>
  <!-- Argon Scripts -->
  <?php
  require_once('partials/_scripts.php');
  ?>
  
  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
  
  <!-- Custom DataTable Initialization -->
  <script>
    $(document).ready(function() {
      var table = $('#inventoryTable').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "dom": '<"top"fl>rt<"bottom"<"info-container"i><"pagination-container"p>><"clear">',
        "pagingType": "simple_numbers",
        "language": {
          "lengthMenu": "_MENU_ entries per page",
          "zeroRecords": "No matching records found",
          "info": "Showing _START_ to _END_ of _TOTAL_ entries",
          "infoEmpty": "Showing 0 to 0 of 0 entries",
          "infoFiltered": "(filtered from _MAX_ total entries)",
          "search": "Search:",
          "paginate": {
            "first": "«",
            "last": "»",
            "next": ">",
            "previous": "<"
          },
          "emptyTable": "No data available in table",
          "zeroRecords": "<div class='text-center p-4'>No matching records found</div>"
        },
        "initComplete": function() {
          // Replace the DataTables search input with our custom form
          $('.dataTables_filter').hide();
          $('#search').on('keyup', function() {
            table.search(this.value).draw();
          });
          
          // Replace the DataTables length menu with our select
          $('.dataTables_length').hide();
          $('#entries-select').on('change', function() {
            table.page.len($(this).val()).draw();
          });
          
          // Make sure pagination is on the right
          $('.dataTables_paginate').css('float', 'right');
          
          // Initialize search with current URL parameter if present
          var urlParams = new URLSearchParams(window.location.search);
          var searchParam = urlParams.get('item');
          if (searchParam) {
            $('#search').val(searchParam);
            table.search(searchParam).draw();
          }
        }
      });
    });
  </script>
  
  <!-- Custom Styles for DataTables -->
  <style>
    .pagination .page-link {
      color: #5e72e4;
      background-color: #fff;
      border: 1px solid #dee2e6;
      border-radius: 50%;
      width: 36px;
      height: 36px;
      padding: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 3px;
    }
    .pagination .page-item.active .page-link {
      z-index: 3;
      color: #fff;
      background-color: #5e72e4;
      border-color: #5e72e4;
    }
    .pagination .page-item.disabled .page-link {
      color: #6c757d;
      pointer-events: none;
      background-color: #fff;
      border-color: #dee2e6;
    }
    #inventoryTable th {
      cursor: pointer;
      position: relative;
    }
    #inventoryTable th:after {
      content: "";
      position: absolute;
      right: 8px;
      top: 50%;
      transform: translateY(-50%);
    }
    #inventoryTable th.sorting_asc:after {
      content: "▲";
      font-size: 10px;
    }
    #inventoryTable th.sorting_desc:after {
      content: "▼";
      font-size: 10px;
    }
    .dataTables_info {
      clear: both;
      padding-left: 15px;
      padding-top: 10px;
      display: flex;
      text-align: left;
      width: 100%;
      justify-content: left;
      margin-top: 10px;
    }
    .pagination {
      margin: 0;
    }
    .dataTables_paginate {
      padding-right: 15px;
      width: 100%;
      text-align: left;
      display: flex;
      justify-content: left;
    }
    
    /* Style for pagination buttons like in the image */
    .dataTables_wrapper .dataTables_paginate .paginate_button {
      border-radius: 50%;
      padding: 0;
      margin: 0 3px;
      min-width: 36px;
      height: 36px;
      line-height: 36px;
      text-align: center;
      display: inline-block;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
      background: #5e72e4 !important;
      color: white !important;
      border-color: #5e72e4 !important;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
      background: #f0f2ff !important;
      color: #5e72e4 !important;
      border-color: #dee2e6 !important;
    }
    
    /* Fix table header and prevent horizontal movement */
    .table-responsive {
      position: relative;
      overflow-x: auto;
    }
    table.dataTable thead th{
      position: sticky;
      top: 0;
      height: 45px;
      background-color: #f6f9fc;
      z-index: 10;
    }
    table.dataTable thead td {
      position: sticky;
      top: 0;
      height: 50px;
      padding:50px;
      background-color: #f6f9fc;
      z-index: 10;
    }
    .card-header {
      position: sticky;
      top: 0;
      background-color: white;
      z-index: 20;
    }
    
    /* Ensure the table container has a fixed width */
    .card.shadow {
      overflow: hidden;
    }
    
    /* Bottom container layout */
    .dataTables_wrapper .bottom {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      position: relative;
      border-top: 1px solid #e9ecef;
      padding-top: 5px;
      padding-left: 15px;
      margin-top: 0;
      background: white;
    }
    
    /* Container for pagination */
    .pagination-container {
      width: 100%;
      display: flex;
      justify-content: left;
      margin-top: 5px;
      margin-bottom: 0px;
    }
    
    /* Container for info */
    .info-container {
      width: 100%;
      display: flex;
      justify-content: left;
      margin-bottom: 10px;
    }
    
    /* Style the table rows like the image */
    #inventoryTable tbody tr {
      border-bottom: 1px solid #e9ecef;
    }
    
    #inventoryTable tbody tr:hover {
      background-color: #f8f9fa;
    }
    
    #inventoryTable td {
      padding: 15px 12px;
      padding-left: 25px;
      vertical-align: middle;
      color: #525f7f;
    }
    
    #inventoryTable thead th {
      background-color: #f8f9fc;
      color: #8898aa;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-size: 0.65rem;
      border-bottom: 1px solid #e9ecef;
      padding: 12px;
      padding-left: 25px;
      padding-right: 25px;
    }
    
    /* Alternating row color */
    /* #inventoryTable tbody tr:nth-of-type(odd) {
      background-color: rgba(0, 0, 0, 0.01);
    }
     */
    #inventoryTable tbody tr:last-child {
      border-bottom: none;
    }
    
    /* Fix the header appearance */
    table.dataTable thead td {
      position: sticky;
      top: 0;
      height: 50px;
      padding: 12px;
      padding-left: 25px;
      padding-right: 25px;
      background-color: #f8f9fc;
      z-index: 10;
      border-bottom: 1px solid #e9ecef;
    }

    /* First column specific padding */
    #inventoryTable tbody tr td:first-child,
    #inventoryTable thead th:first-child {
      padding-left: 25px;
    }
    
    /* Center the "No matching records found" message */
    .dataTables_empty {
      text-align: center !important;
      padding: 0px !important;
      font-weight: 500;
      color: #8898aa;
    }
    
    /* Footer styles */
    .footer {
      padding: 1.5rem 0;
      margin-top: 2rem;
      border-top: 1px solid #e9ecef;
      background-color: #f6f9fc;
    }
    
    .footer .copyright {
      font-size: 0.875rem;
      font-weight: 400;
      color: #8898aa;
    }
    
    .footer .nav-link {
      font-size: 0.875rem;
      font-weight: 500;
    }
  </style>
</body>

</html>