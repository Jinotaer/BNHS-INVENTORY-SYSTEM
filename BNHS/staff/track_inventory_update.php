<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

function sanitize($data) {
    return htmlspecialchars(trim($data));
}

// Helper function to get database value or default
function get_field_value($item, $field, $default = '') {
    return isset($item[$field]) && $item[$field] !== null ? $item[$field] : $default;
}

$tables = [
  'inspection_acceptance_reports',
  'inventory_custodian_slips',
  'requisition_and_issue_slips',
  'property_acknowledgment_receipts'
];

// Get parameters
$source_table = isset($_GET['table']) ? $_GET['table'] : '';
$record_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Validate
if (!in_array($source_table, $tables) || $record_id <= 0) {
    die("Invalid table or ID.");
}

// Fetch existing record
$id_column = 'id';
switch($source_table) {
    case 'inspection_acceptance_reports': $id_column = 'iar_id'; break;
    case 'inventory_custodian_slips': $id_column = 'ics_id'; break;
    case 'requisition_and_issue_slips': $id_column = 'ris_id'; break;
    case 'property_acknowledgment_receipts': $id_column = 'par_id'; break;
}

// Modified query with JOIN to entities table where applicable
if ($source_table == 'inspection_acceptance_reports') {
    $query = "SELECT t.*, e.entity_name, e.fund_cluster,
              i.item_id, i.quantity, i.unit_price, i.total_price,
              itm.item_description, itm.unit, itm.unit_cost, itm.stock_no,
              s.supplier_name, s.contact_info
              FROM `$source_table` t 
              JOIN entities e ON t.entity_id = e.entity_id 
              LEFT JOIN iar_items i ON t.iar_id = i.iar_id
              LEFT JOIN items itm ON i.item_id = itm.item_id
              LEFT JOIN suppliers s ON t.supplier_id = s.supplier_id
              WHERE t.$id_column = ?";
} else if ($source_table == 'inventory_custodian_slips') {
    $query = "SELECT t.*, e.entity_name, e.fund_cluster,
              i.item_id, i.quantity, i.inventory_item_no,
              itm.item_description, itm.unit, itm.unit_cost, itm.estimated_useful_life
              FROM `$source_table` t 
              JOIN entities e ON t.entity_id = e.entity_id 
              LEFT JOIN ics_items i ON t.ics_id = i.ics_id
              LEFT JOIN items itm ON i.item_id = itm.item_id
              WHERE t.$id_column = ?";
} else if ($source_table == 'requisition_and_issue_slips') {
    $query = "SELECT t.*, e.entity_name, e.fund_cluster,
              i.item_id, i.requested_qty, i.issued_qty, i.stock_available, i.remarks,
              itm.item_description, itm.unit, itm.unit_cost, itm.stock_no
              FROM `$source_table` t 
              JOIN entities e ON t.entity_id = e.entity_id 
              LEFT JOIN ris_items i ON t.ris_id = i.ris_id
              LEFT JOIN items itm ON i.item_id = itm.item_id
              WHERE t.$id_column = ?";
} else if ($source_table == 'property_acknowledgment_receipts') {
    $query = "SELECT t.*, e.entity_name, e.fund_cluster,
              i.item_id, i.quantity, i.property_number,
              itm.item_description, itm.unit, itm.unit_cost
              FROM `$source_table` t 
              JOIN entities e ON t.entity_id = e.entity_id 
              LEFT JOIN par_items i ON t.par_id = i.par_id
              LEFT JOIN items itm ON i.item_id = itm.item_id
              WHERE t.$id_column = ?";
} else {
    $query = "SELECT * FROM `$source_table` WHERE $id_column = ?";
}

$stmt = $mysqli->prepare($query);
if ($stmt === false) {
    die("Error preparing statement: " . $mysqli->error);
}
$stmt->bind_param("i", $record_id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

if (!$item) {
    die("Record not found.");
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    // Gather input based on table type
    $fields = [];
    $values = [];
    $updates = [];

    // Manually calculate totals based on form type
    if ($source_table == 'inspection_acceptance_reports' && isset($_POST['quantity']) && isset($_POST['unit_price'])) {
        $quantity = intval($_POST['quantity']);
        $unit_price = floatval($_POST['unit_price']);
        $_POST['total_price'] = $quantity * $unit_price;
    } 
    else if ($source_table == 'inventory_custodian_slips' && isset($_POST['quantity']) && isset($_POST['unit_cost'])) {
        $quantity = intval($_POST['quantity']);
        $unit_cost = floatval($_POST['unit_cost']);
        $_POST['total_amount'] = $quantity * $unit_cost;
    }
    else if ($source_table == 'property_acknowledgment_receipts' && isset($_POST['quantity']) && isset($_POST['unit_cost'])) {
        $quantity = intval($_POST['quantity']);
        $unit_cost = floatval($_POST['unit_cost']);
        $_POST['total_amount'] = $quantity * $unit_cost;
    }
    else if ($source_table == 'requisition_and_issue_slips' && isset($_POST['requested_qty']) && isset($_POST['unit_cost'])) {
        $quantity = intval($_POST['requested_qty']);
        $unit_cost = floatval($_POST['unit_cost']);
        $_POST['total_cost'] = $quantity * $unit_cost;
    }

    switch($source_table) {
        case 'inspection_acceptance_reports':
            $fields = [
                'entity_id', 'iar_no', 'property_number', 'date_acquired', 'receiver_name',
                'receiver_position', 'receiver_date', 'property_custodian', 'custodian_position',
                'custodian_date', 'supplier_id', 'po_no_date', 'req_office', 'responsibility_center',
                'iar_date', 'invoice_no_date', 'remarks', 'teacher_id', 'position', 'date_inspected', 
                'inspectors', 'barangay_councilor', 'pta_observer', 'date_received'
            ];
            break;
        case 'inventory_custodian_slips':
            $fields = [
                'entity_id', 'ics_no', 'end_user_name', 'end_user_position', 
                'end_user_date', 'custodian_name', 'custodian_position', 'custodian_date'
            ];
            break;
        case 'requisition_and_issue_slips':
            $fields = [
                'entity_id', 'ris_no', 'date_acquired', 'requested_by_name',
                'requested_by_position', 'requested_by_date', 'issued_by_name', 'issued_by_position',
                'issued_by_date', 'division', 'office', 'responsibility_code', 'purpose', 
                'requested_by_designation', 'approved_by_name', 'approved_by_designation', 
                'approved_by_date', 'issued_by_designation', 'received_by_name', 
                'received_by_designation', 'received_by_date'
            ];
            break;
        case 'property_acknowledgment_receipts':
            $fields = [
                'entity_id', 'par_no', 'date_acquired',
                'end_user_name', 'receiver_position', 'receiver_date',
                'custodian_name', 'custodian_position', 'custodian_date'
            ];
            break;
    }

    // Get form data for entity and supplier updates
    $entity_name = isset($_POST['entity_name']) ? sanitize($_POST['entity_name']) : '';
    $fund_cluster = isset($_POST['fund_cluster']) ? sanitize($_POST['fund_cluster']) : '';
    $entity_id = isset($_POST['entity_id']) ? intval($_POST['entity_id']) : 0;
    $supplier_name = isset($_POST['supplier_name']) ? sanitize($_POST['supplier_name']) : '';
    $supplier_id = isset($_POST['supplier_id']) ? intval($_POST['supplier_id']) : 0;

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $updates[] = "`$field` = ?";
            $values[] = sanitize($_POST[$field]);
        }
    }

    if (!empty($updates)) {
        $setClause = implode(', ', $updates);
        $sql = "UPDATE `$source_table` SET $setClause WHERE $id_column = ?";
        
        // Debug information
        error_log("SQL Query: " . $sql);
        error_log("Table: " . $source_table);
        error_log("ID Column: " . $id_column);
        error_log("Record ID: " . $record_id);
        error_log("Updates: " . print_r($updates, true));
        error_log("Values: " . print_r($values, true));
        
        try {
            $stmt = $mysqli->prepare($sql);
            
            if ($stmt === false) {
                $err = "Error preparing statement: " . $mysqli->error;
                error_log("MySQL Error: " . $mysqli->error);
            } else {
                // Create an array of references for bind_param
                $types = str_repeat('s', count($values)) . 'i';
                $values[] = $record_id;
                
                // Fix for PHP 7+ bind_param with references
                $params = array($types);
                foreach($values as $key => $value) {
                    $params[] = &$values[$key];
                }
                
                try {
                    // Call bind_param with the array of references
                    call_user_func_array(array($stmt, 'bind_param'), $params);
    
                    if ($stmt->execute()) {
                        // Handle entity_name and fund_cluster updates if they've changed
                        if (!empty($entity_name) && !empty($fund_cluster) && $entity_id > 0) {
                            // Update the entities table
                            $entity_sql = "UPDATE entities SET entity_name = ?, fund_cluster = ? WHERE entity_id = ?";
                            $entity_stmt = $mysqli->prepare($entity_sql);
                            if ($entity_stmt) {
                                $entity_stmt->bind_param("ssi", $entity_name, $fund_cluster, $entity_id);
                                $entity_stmt->execute();
                                $entity_stmt->close();
                            }
                        }
                        
                        // Handle supplier_name updates for IAR
                        if ($source_table == 'inspection_acceptance_reports' && !empty($supplier_name) && $supplier_id > 0) {
                            // Update the suppliers table
                            $supplier_sql = "UPDATE suppliers SET supplier_name = ? WHERE supplier_id = ?";
                            $supplier_stmt = $mysqli->prepare($supplier_sql);
                            if ($supplier_stmt) {
                                $supplier_stmt->bind_param("si", $supplier_name, $supplier_id);
                                $supplier_stmt->execute();
                                $supplier_stmt->close();
                            }
                        }
                        
                        // Update the item-related tables based on the source table
                        if ($source_table == 'inspection_acceptance_reports' && isset($_POST['quantity']) && isset($_POST['unit_price'])) {
                            $quantity = intval($_POST['quantity']);
                            $unit_price = floatval($_POST['unit_price']);
                            $total_price = $quantity * $unit_price;
                            
                            // Find the first iar_item_id for this IAR
                            $find_sql = "SELECT iar_item_id FROM iar_items WHERE iar_id = ? LIMIT 1";
                            $find_stmt = $mysqli->prepare($find_sql);
                            if ($find_stmt) {
                                $find_stmt->bind_param('i', $record_id);
                                $find_stmt->execute();
                                $find_result = $find_stmt->get_result();
                                if ($row = $find_result->fetch_assoc()) {
                                    $iar_item_id = $row['iar_item_id'];
                                    
                                    // Update the iar_items table
                                    $update_sql = "UPDATE iar_items SET quantity = ?, unit_price = ?, total_price = ? WHERE iar_item_id = ?";
                                    $update_stmt = $mysqli->prepare($update_sql);
                                    if ($update_stmt) {
                                        $update_stmt->bind_param('iddi', $quantity, $unit_price, $total_price, $iar_item_id);
                                        $update_stmt->execute();
                                        $update_stmt->close();
                                    }
                                }
                                $find_stmt->close();
                            }
                        } 
                        else if ($source_table == 'inventory_custodian_slips' && isset($_POST['quantity']) && isset($_POST['unit_cost'])) {
                            $quantity = intval($_POST['quantity']);
                            $unit_cost = floatval($_POST['unit_cost']);
                            
                            // Find the first ics_item_id for this ICS
                            $find_sql = "SELECT ics_item_id, item_id FROM ics_items WHERE ics_id = ? LIMIT 1";
                            $find_stmt = $mysqli->prepare($find_sql);
                            if ($find_stmt) {
                                $find_stmt->bind_param('i', $record_id);
                                $find_stmt->execute();
                                $find_result = $find_stmt->get_result();
                                if ($row = $find_result->fetch_assoc()) {
                                    $ics_item_id = $row['ics_item_id'];
                                    $item_id = $row['item_id'];
                                    
                                    // Update the ics_items table
                                    $update_sql = "UPDATE ics_items SET quantity = ? WHERE ics_item_id = ?";
                                    $update_stmt = $mysqli->prepare($update_sql);
                                    if ($update_stmt) {
                                        $update_stmt->bind_param('ii', $quantity, $ics_item_id);
                                        $update_stmt->execute();
                                        $update_stmt->close();
                                    }
                                    
                                    // Update the items table with new unit_cost
                                    $update_items_sql = "UPDATE items SET unit_cost = ? WHERE item_id = ?";
                                    $update_items_stmt = $mysqli->prepare($update_items_sql);
                                    if ($update_items_stmt) {
                                        $update_items_stmt->bind_param('di', $unit_cost, $item_id);
                                        $update_items_stmt->execute();
                                        $update_items_stmt->close();
                                    }
                                }
                                $find_stmt->close();
                            }
                        } 
                        else if ($source_table == 'requisition_and_issue_slips' && isset($_POST['requested_qty']) && isset($_POST['unit_cost'])) {
                            $requested_qty = intval($_POST['requested_qty']);
                            $unit_cost = floatval($_POST['unit_cost']);
                            
                            // Find the first ris_item_id for this RIS
                            $find_sql = "SELECT ris_item_id, item_id FROM ris_items WHERE ris_id = ? LIMIT 1";
                            $find_stmt = $mysqli->prepare($find_sql);
                            if ($find_stmt) {
                                $find_stmt->bind_param('i', $record_id);
                                $find_stmt->execute();
                                $find_result = $find_stmt->get_result();
                                if ($row = $find_result->fetch_assoc()) {
                                    $ris_item_id = $row['ris_item_id'];
                                    $item_id = $row['item_id'];
                                    
                                    // Update the ris_items table
                                    $update_sql = "UPDATE ris_items SET requested_qty = ?, stock_available = ? WHERE ris_item_id = ?";
                                    $update_stmt = $mysqli->prepare($update_sql);
                                    if ($update_stmt) {
                                        $stock_available = isset($_POST['stock_available']) ? $_POST['stock_available'] : 'no';
                                        $update_stmt->bind_param('isi', $requested_qty, $stock_available, $ris_item_id);
                                        $update_stmt->execute();
                                        $update_stmt->close();
                                    }
                                    
                                    // Update the items table with new unit_cost
                                    $update_items_sql = "UPDATE items SET unit_cost = ? WHERE item_id = ?";
                                    $update_items_stmt = $mysqli->prepare($update_items_sql);
                                    if ($update_items_stmt) {   
                                        $update_items_stmt->bind_param('di', $unit_cost, $item_id);
                                        $update_items_stmt->execute();
                                        $update_items_stmt->close();
                                    }
                                }
                                $find_stmt->close();
                            }
                        } 
                        else if ($source_table == 'property_acknowledgment_receipts' && isset($_POST['quantity']) && isset($_POST['unit_cost'])) {
                            $quantity = intval($_POST['quantity']);
                            $unit_cost = floatval($_POST['unit_cost']);
                            
                            // Find the first par_item_id for this PAR
                            $find_sql = "SELECT par_item_id, item_id FROM par_items WHERE par_id = ? LIMIT 1";
                            $find_stmt = $mysqli->prepare($find_sql);
                            if ($find_stmt) {
                                $find_stmt->bind_param('i', $record_id);
                                $find_stmt->execute();
                                $find_result = $find_stmt->get_result();
                                if ($row = $find_result->fetch_assoc()) {
                                    $par_item_id = $row['par_item_id'];
                                    $item_id = $row['item_id'];
                                    
                                    // Update the par_items table
                                    $update_sql = "UPDATE par_items SET quantity = ? WHERE par_item_id = ?";
                                    $update_stmt = $mysqli->prepare($update_sql);
                                    if ($update_stmt) {
                                        $update_stmt->bind_param('ii', $quantity, $par_item_id);
                                        $update_stmt->execute();
                                        $update_stmt->close();
                                    }
                                    
                                    // Update the items table with new unit_cost
                                    $update_items_sql = "UPDATE items SET unit_cost = ? WHERE item_id = ?";
                                    $update_items_stmt = $mysqli->prepare($update_items_sql);
                                    if ($update_items_stmt) {
                                        $update_items_stmt->bind_param('di', $unit_cost, $item_id);
                                        $update_items_stmt->execute();
                                        $update_items_stmt->close();
                                    }
                                }
                                $find_stmt->close();
                            }
                        }
                        
                        $success = "Record updated successfully.";
                        // Redirect to the same page to prevent form resubmission
                        header("Location: track_inventory.php?");
                        exit();
                    } else {
                        $err = "Update failed: " . $stmt->error;
                        error_log("Execute Error: " . $stmt->error);
                    }
                } catch (Exception $e) {
                    $err = "Binding parameters failed: " . $e->getMessage();
                    error_log("Binding Error: " . $e->getMessage());
                }
                $stmt->close();
            }
        } catch (Exception $e) {
            $err = "Database operation failed: " . $e->getMessage();
            error_log("Exception: " . $e->getMessage());
        }
    } else {
        $err = "No fields were updated.";
        error_log("No updates specified in the form submission.");
    }
}

require_once('partials/_head.php');
?>

<body>
<?php require_once('partials/_sidebar.php'); ?>
<div class="main-content">
<?php require_once('partials/_topnav.php'); ?>

<div style="background-image: url(assets/img/theme/bnhsfront.jpg); background-size: cover;" class="header pb-8 pt-5 pt-md-8">
  <span class="mask bg-gradient-dark opacity-8"></span>
</div>

<div class="container-fluid mt--8">
  <div class="row">
    <div class="col">
      <div class="card shadow">
        <div class="card-body">
          <?php if(isset($err)): ?>
          <div class="alert alert-danger">
            <strong>Error:</strong> <?php echo $err; ?>
          </div>
          
          <div class="card mb-4">
            <div class="card-header">
              <h4>Debug Information</h4>
            </div>
            <div class="card-body">
              <p><strong>Table:</strong> <?php echo $source_table; ?></p>
              <p><strong>ID Column:</strong> <?php echo $id_column; ?></p>
              <p><strong>Record ID:</strong> <?php echo $record_id; ?></p>
              <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
              <p><strong>MySQL Version:</strong> <?php echo $mysqli->server_info; ?></p>
            </div>
          </div>
          <?php endif; ?>
          
          <?php if(isset($success)): ?>
          <div class="alert alert-success">
            <strong>Success:</strong> <?php echo $success; ?>
          </div>
          <?php endif; ?>
          
          <form method="POST" class="border border-light p-4 rounded">
            <div class="container mt-4">
        
              <?php if ($source_table === 'property_acknowledgment_receipts'): ?>
                <h2 class="text-center mb-4 text-uppercase">Purchase Acceptance Report</h2>
                <!-- Entity Info -->
                <div class="row mt-3 mb-3">
                    <div class="col-md-4">
                        <label>Entity Name</label>
                        <input style="color: #000000;" type="text" class="form-control" name="entity_name" value="<?php echo get_field_value($item, 'entity_name'); ?>" required>
                        <input type="hidden" name="entity_id" value="<?php echo get_field_value($item, 'entity_id'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Fund Cluster</label>
                        <input style="color: #000000;" type="text" class="form-control" name="fund_cluster" value="<?php echo get_field_value($item, 'fund_cluster'); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label>PAR No.</label>
                        <input style="color: #000000;" type="text" class="form-control" name="par_no" value="<?php echo get_field_value($item, 'par_no'); ?>" required>
                    </div>
                </div>
                <!-- Item Info -->
                <div class="row mb-3">
                    <div class="col-md-2">
                        <label>Quantity</label>
                        <input style="color: #000000;" type="number" class="form-control" name="quantity" value="<?php echo get_field_value($item, 'quantity'); ?>">
                    </div>
                    <div class="col-md-2">
                        <label>Unit</label>
                        <input style="color: #000000;" type="text" class="form-control" name="unit" value="<?php echo get_field_value($item, 'unit'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Description</label>
                        <input style="color: #000000;" type="text" class="form-control" name="item_description" value="<?php echo get_field_value($item, 'item_description'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Property Number</label>
                        <input style="color: #000000;" type="text" class="form-control" name="property_number" value="<?php echo get_field_value($item, 'property_number'); ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Date Acquired</label>
                        <input style="color: #000000;" type="date" class="form-control" name="date_acquired" value="<?php echo get_field_value($item, 'date_acquired'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Unit Cost</label>
                        <input style="color: #000000;" type="text" class="form-control" name="unit_cost" value="<?php echo get_field_value($item, 'unit_cost'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Total Amount</label>
                        <input style="color: #000000;" type="text" class="form-control" name="total_amount" value="<?php echo get_field_value($item, 'total_amount'); ?>">
                    </div>
                </div>
                <!-- Receiver Section -->
                <div class="sub-section receiver-section">Receiver</div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>End User Name</label>
                        <input style="color: #000000;" type="text" class="form-control" name="end_user_name" value="<?php echo get_field_value($item, 'end_user_name'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Position/Office</label>
                        <input style="color: #000000;" type="text" class="form-control" name="receiver_position" value="<?php echo get_field_value($item, 'receiver_position'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Date</label>
                        <input style="color: #000000;" type="date" class="form-control" name="receiver_date" value="<?php echo get_field_value($item, 'receiver_date'); ?>">
                    </div>
                </div>
                <!-- Issue Section -->
                <div class="sub-section issue-section">Issue</div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Property Custodian Name</label>
                        <input style="color: #000000;" type="text" class="form-control" name="custodian_name" value="<?php echo get_field_value($item, 'custodian_name'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Position/Office</label>
                        <input style="color: #000000;" type="text" class="form-control" name="custodian_position" value="<?php echo get_field_value($item, 'custodian_position'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Date</label>
                        <input style="color: #000000;" type="date" class="form-control" name="custodian_date" value="<?php echo get_field_value($item, 'custodian_date'); ?>">
                    </div>
                </div>

              <?php elseif ($source_table === 'requisition_and_issue_slips'): ?>
                <h2 class="text-center mb-4 text-uppercase">Requisition and Issue Slip</h2>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Entity Name</label>
                        <input style="color: #000000;" type="text" class="form-control" name="entity_name" value="<?php echo get_field_value($item, 'entity_name'); ?>" required>
                        <input type="hidden" name="entity_id" value="<?php echo get_field_value($item, 'entity_id'); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fund Cluster</label>
                        <input style="color: #000000;" type="text" class="form-control" name="fund_cluster" value="<?php echo get_field_value($item, 'fund_cluster'); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Division</label>
                        <input style="color: #000000;" type="text" class="form-control" name="division" value="<?php echo get_field_value($item, 'division'); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Office</label>
                        <input style="color: #000000;" type="text" class="form-control" name="office" value="<?php echo get_field_value($item, 'office'); ?>" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Responsibility Center Code</label>
                        <input style="color: #000000;" type="text" class="form-control" name="responsibility_code" value="<?php echo get_field_value($item, 'responsibility_code'); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">RIS No.</label>
                        <input style="color: #000000;" type="text" class="form-control" name="ris_no" value="<?php echo get_field_value($item, 'ris_no'); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Stock No.</label>
                        <input style="color: #000000;" type="text" class="form-control" name="stock_no" value="<?php echo get_field_value($item, 'stock_no'); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Unit</label>
                        <input style="color: #000000;" type="text" class="form-control" name="unit" value="<?php echo get_field_value($item, 'unit'); ?>" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Item Description</label>
                        <input style="color: #000000;" type="text" style="color: #000000;" class="form-control" name="item_description" value="<?php echo get_field_value($item, 'item_description'); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Requested Quantity</label>
                        <input style="color: #000000;" type="number" style="color: #000000;" class="form-control" name="requested_qty" value="<?php echo get_field_value($item, 'requested_qty'); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Stock Available</label>
                        <select style="color: #000000;" class="form-control" name="stock_available" required>
                            <option value="yes" <?php echo get_field_value($item, 'stock_available') === 'yes' ? 'selected' : ''; ?>>Yes</option>
                            <option value="no" <?php echo get_field_value($item, 'stock_available') === 'no' ? 'selected' : ''; ?>>No</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Issued Quantity</label>
                        <input style="color: #000000;" type="number" style="color: #000000;" class="form-control" name="issued_qty" value="<?php echo get_field_value($item, 'issued_qty'); ?>" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Remarks</label>
                        <input style="color: #000000;" type="text" style="color: #000000;" class="form-control" name="remarks" value="<?php echo get_field_value($item, 'remarks'); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Purpose</label>
                        <input style="color: #000000;" type="text" style="color: #000000;" class="form-control" name="purpose" value="<?php echo get_field_value($item, 'purpose'); ?>" required>
                    </div>
                </div>
                <h5 class="mt-4">Requested By</h5>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Name</label>
                        <input type="text" style="color: #000000;" class="form-control" name="requested_by_name" value="<?php echo get_field_value($item, 'requested_by_name'); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Designation</label>
                        <input type="text" style="color: #000000;" class="form-control" name="requested_by_designation" value="<?php echo get_field_value($item, 'requested_by_designation'); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" style="color: #000000;" class="form-control" name="requested_by_date" value="<?php echo get_field_value($item, 'requested_by_date'); ?>" required>
                    </div>
                </div>
                <h5>Approved By</h5>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Name</label>
                        <input type="text" style="color: #000000;" class="form-control" name="approved_by_name" value="<?php echo get_field_value($item, 'approved_by_name'); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Designation</label>
                        <input type="text" style="color: #000000;" class="form-control" name="approved_by_designation" value="<?php echo get_field_value($item, 'approved_by_designation'); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" style="color: #000000;" class="form-control" name="approved_by_date" value="<?php echo $item['approved_by_date'] ?? ''; ?>" required>
                    </div>
                </div>
                <h5>Issued By</h5>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Name</label>
                        <input type="text" style="color: #000000;" class="form-control" name="issued_by_name" value="<?php echo get_field_value($item, 'issued_by_name'); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Designation</label>
                        <input type="text" style="color: #000000;" class="form-control" name="issued_by_designation" value="<?php echo get_field_value($item, 'issued_by_designation'); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" style="color: #000000;" class="form-control" name="issued_by_date" value="<?php echo get_field_value($item, 'issued_by_date'); ?>" required>
                    </div>
                </div>
                <h5>Received By</h5>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Name</label>
                        <input type="text" style="color: #000000;" class="form-control" name="received_by_name" value="<?php echo get_field_value($item, 'received_by_name'); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Designation</label>
                        <input type="text" style="color: #000000;" class="form-control" name="received_by_designation" value="<?php echo get_field_value($item, 'received_by_designation'); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" style="color: #000000;" class="form-control" name="received_by_date" value="<?php echo get_field_value($item, 'received_by_date'); ?>" required>
                    </div>
                </div>

              <?php elseif ($source_table === 'inventory_custodian_slips'): ?>
                <h2 class="text-center mb-4 text-uppercase">Inventory Custodian Slip</h2>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Entity Name</label>
                        <input style="color: #000000;" type="text" class="form-control" name="entity_name" value="<?php echo get_field_value($item, 'entity_name'); ?>" required>
                        <input type="hidden" name="entity_id" value="<?php echo get_field_value($item, 'entity_id'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fund Cluster</label>
                        <input style="color: #000000;" type="text" class="form-control" name="fund_cluster" value="<?php echo get_field_value($item, 'fund_cluster'); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ICS No.</label>
                        <input style="color: #000000;" type="text" class="form-control" name="ics_no" value="<?php echo get_field_value($item, 'ics_no'); ?>" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-2">
                        <label class="form-label">Quantity</label>
                        <input style="color: #000000;" type="number" class="form-control" name="quantity" value="<?php echo get_field_value($item, 'quantity'); ?>" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Unit</label>
                        <input style="color: #000000;" type="text" class="form-control" name="unit" value="<?php echo get_field_value($item, 'unit'); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Unit Cost</label>
                        <input style="color: #000000;" type="text" class="form-control" name="unit_cost" value="<?php echo get_field_value($item, 'unit_cost'); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Total Amount</label>
                        <input style="color: #000000;" type="text" class="form-control" name="total_amount" value="<?php echo get_field_value($item, 'total_amount'); ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Item Description</label>
                        <input style="color: #000000;" type="text" class="form-control" name="item_description" value="<?php echo get_field_value($item, 'item_description'); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Inventory Item No.</label>
                        <input style="color: #000000;" type="text" class="form-control" name="inventory_item_no" value="<?php echo get_field_value($item, 'inventory_item_no'); ?>" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Estimated Useful Life</label>
                        <input style="color: #000000;" type="text" class="form-control" name="estimated_useful_life" value="<?php echo get_field_value($item, 'estimated_useful_life'); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">End User Name</label>
                        <input style="color: #000000;" type="text" class="form-control" name="end_user_name" value="<?php echo get_field_value($item, 'end_user_name'); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Position / Office</label>
                        <input style="color: #000000;" type="text" class="form-control" name="end_user_position" value="<?php echo get_field_value($item, 'end_user_position'); ?>" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Date Received (by End User)</label>
                        <input style="color: #000000;" type="date" class="form-control" name="end_user_date" value="<?php echo get_field_value($item, 'end_user_date'); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Property Custodian Name</label>
                        <input style="color: #000000;" type="text" class="form-control" name="custodian_name" value="<?php echo get_field_value($item, 'custodian_name'); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Position / Office (Custodian)</label>
                        <input style="color: #000000;" type="text" class="form-control" name="custodian_position" value="<?php echo get_field_value($item, 'custodian_position'); ?>" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Date Received (by Custodian)</label>
                        <input style="color: #000000;" type="date" class="form-control" name="custodian_date" value="<?php echo get_field_value($item, 'custodian_date'); ?>" required>
                    </div>
                </div>

              <?php elseif ($source_table === 'inspection_acceptance_reports'): ?>
                <h2 class="text-center mb-4 text-uppercase">Inspection and Acceptance Report</h2>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Entity Name</label>
                        <input style="color: #000000;" type="text" class="form-control" name="entity_name" value="<?php echo get_field_value($item, 'entity_name'); ?>" required>
                        <input type="hidden" name="entity_id" value="<?php echo get_field_value($item, 'entity_id'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fund Cluster</label>
                        <input style="color: #000000;" type="text" class="form-control" name="fund_cluster" value="<?php echo get_field_value($item, 'fund_cluster'); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Supplier</label>
                        <input style="color: #000000;" type="text" class="form-control" name="supplier_name" value="<?php echo get_field_value($item, 'supplier_name'); ?>" required>
                        <input type="hidden" name="supplier_id" value="<?php echo get_field_value($item, 'supplier_id'); ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">PO No. / Date</label>
                        <input style="color: #000000;" type="text" class="form-control" name="po_no_date" value="<?php echo get_field_value($item, 'po_no_date'); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Requisitioning Office/Dept.</label>
                        <input style="color: #000000;" type="text" class="form-control" name="req_office" value="<?php echo get_field_value($item, 'req_office'); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Responsibility Center</label>
                        <input style="color: #000000;" type="text" class="form-control" name="responsibility_center" value="<?php echo get_field_value($item, 'responsibility_center'); ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">IAR No.</label>
                        <input style="color: #000000;" type="text" class="form-control" name="iar_no" value="<?php echo get_field_value($item, 'iar_no'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">IAR Date</label>
                        <input style="color: #000000;" type="date" class="form-control" name="iar_date" value="<?php echo get_field_value($item, 'iar_date'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Invoice No. / Date</label>
                        <input style="color: #000000;" type="text" class="form-control" name="invoice_no_date" value="<?php echo get_field_value($item, 'invoice_no_date'); ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Stock / Property No.</label>
                        <input style="color: #000000;" type="text" class="form-control" name="stock_no" value="<?php echo get_field_value($item, 'stock_no'); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Remarks</label>
                        <input style="color: #000000;" type="text" class="form-control" name="remarks" value="<?php echo get_field_value($item, 'remarks'); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Item Description</label>
                        <input style="color: #000000;" type="text" class="form-control" name="item_description" value="<?php echo get_field_value($item, 'item_description'); ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-2">
                        <label class="form-label">Unit</label>
                        <input style="color: #000000;" type="text" class="form-control" name="unit" value="<?php echo get_field_value($item, 'unit'); ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Qty</label>
                        <input style="color: #000000;" type="number" class="form-control" name="quantity" value="<?php echo get_field_value($item, 'quantity'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Unit Price</label>
                        <input style="color: #000000;" type="number" step="0.01" class="form-control" name="unit_price" value="<?php echo get_field_value($item, 'unit_price'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Total Price</label>
                        <input style="color: #000000;" type="text" class="form-control" name="total_price" value="<?php echo get_field_value($item, 'total_price'); ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Receiver Name</label>
                        <input style="color: #000000;" type="text" class="form-control" name="receiver_name" value="<?php echo get_field_value($item, 'receiver_name'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Teacher's ID</label>
                        <input style="color: #000000;" type="text" class="form-control" name="teacher_id" value="<?php echo get_field_value($item, 'teacher_id'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Position</label>
                        <input style="color: #000000;" type="text" class="form-control" name="position" value="<?php echo get_field_value($item, 'position'); ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Date Inspected</label>
                        <input style="color: #000000;" type="date" class="form-control" name="date_inspected" value="<?php echo get_field_value($item, 'date_inspected'); ?>">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Inspection Team</label>
                        <input style="color: #000000;" type="text" class="form-control" name="inspectors" value="<?php echo get_field_value($item, 'inspectors'); ?>" placeholder="e.g., Joan Savage, Nelson British, Bles Sings">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Barangay Councilor</label>
                        <input style="color: #000000;" type="text" class="form-control" name="barangay_councilor" value="<?php echo get_field_value($item, 'barangay_councilor'); ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">PTA Observer</label>
                        <input style="color: #000000;" type="text" class="form-control" name="pta_observer" value="<?php echo get_field_value($item, 'pta_observer'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date Received</label>
                        <input style="color: #000000;" type="date" class="form-control" name="date_received" value="<?php echo get_field_value($item, 'date_received'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Property Custodian</label>
                        <input style="color: #000000;" type="text" class="form-control" name="property_custodian" value="<?php echo get_field_value($item, 'property_custodian'); ?>">
                    </div>
                </div>
              <?php endif; ?>

              <div class="text-end mt-3">
                <a href="track_inventory.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Record</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <?php require_once('partials/_mainfooter.php'); ?>
</div>
<?php require_once('partials/_scripts.php'); ?>

<!-- <script>
  function toggleDebug() {
    var debugInfo = document.getElementById('debugInfo');
    if (debugInfo.style.display === 'none') {
      debugInfo.style.display = 'block';
    } else {
      debugInfo.style.display = 'none';
    }
  }
</script> -->
</body>
</html>
