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
$item_id = isset($_GET['item_id']) ? intval($_GET['item_id']) : 0;

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

// Validate
if (!in_array($source_table, $tables) || ($record_id <= 0 && $item_id <= 0)) {
    die("Invalid table or ID.");
}

// Get the correct items table and ID column
$items_table = isset($item_tables[$source_table]) ? $item_tables[$source_table] : '';
$item_id_column = isset($item_id_columns[$items_table]) ? $item_id_columns[$items_table] : '';

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
    if ($item_id > 0 && !empty($item_id_column)) {
        $query = "SELECT t.*, e.entity_name, e.fund_cluster,
                  i.item_id, i.quantity, i.unit_price, i.total_price, i.remarks, i.iar_item_id,
                  itm.item_description, itm.unit, itm.unit_cost, itm.stock_no,
                  s.supplier_name, s.contact_info
                  FROM `$source_table` t 
                  JOIN entities e ON t.entity_id = e.entity_id 
                  JOIN iar_items i ON t.iar_id = i.iar_id
                  JOIN items itm ON i.item_id = itm.item_id
                  LEFT JOIN suppliers s ON t.supplier_id = s.supplier_id
                  WHERE i.iar_item_id = ?";
    } else {
        $query = "SELECT t.*, e.entity_name, e.fund_cluster,
                  i.item_id, i.quantity, i.unit_price, i.total_price, i.remarks, i.iar_item_id,
                  itm.item_description, itm.unit, itm.unit_cost, itm.stock_no,
                  s.supplier_name, s.contact_info
                  FROM `$source_table` t 
                  JOIN entities e ON t.entity_id = e.entity_id 
                  LEFT JOIN iar_items i ON t.iar_id = i.iar_id
                  LEFT JOIN items itm ON i.item_id = itm.item_id
                  LEFT JOIN suppliers s ON t.supplier_id = s.supplier_id
                  WHERE t.$id_column = ?";
    }
} else if ($source_table == 'inventory_custodian_slips') {
    if ($item_id > 0 && !empty($item_id_column)) {
        $query = "SELECT t.*, e.entity_name, e.fund_cluster,
                  i.item_id, i.quantity, i.inventory_item_no, i.ics_item_id, i.remarks,
                  itm.item_description, itm.unit, itm.unit_cost, itm.estimated_useful_life
                  FROM `$source_table` t 
                  JOIN entities e ON t.entity_id = e.entity_id 
                  JOIN ics_items i ON t.ics_id = i.ics_id
                  JOIN items itm ON i.item_id = itm.item_id
                  WHERE i.ics_item_id = ?";
    } else {
        $query = "SELECT t.*, e.entity_name, e.fund_cluster,
                  i.item_id, i.quantity, i.inventory_item_no, i.ics_item_id, i.remarks,
                  itm.item_description, itm.unit, itm.unit_cost, itm.estimated_useful_life
                  FROM `$source_table` t 
                  JOIN entities e ON t.entity_id = e.entity_id 
                  LEFT JOIN ics_items i ON t.ics_id = i.ics_id
                  LEFT JOIN items itm ON i.item_id = itm.item_id
                  WHERE t.$id_column = ?";
    }
} else if ($source_table == 'requisition_and_issue_slips') {
    if ($item_id > 0 && !empty($item_id_column)) {
        $query = "SELECT t.*, e.entity_name, e.fund_cluster,
                  i.item_id, i.requested_qty, i.issued_qty, i.stock_available, i.remarks, i.ris_item_id,
                  itm.item_description, itm.unit, itm.unit_cost, itm.stock_no
                  FROM `$source_table` t 
                  JOIN entities e ON t.entity_id = e.entity_id 
                  JOIN ris_items i ON t.ris_id = i.ris_id
                  JOIN items itm ON i.item_id = itm.item_id
                  WHERE i.ris_item_id = ?";
    } else {
        $query = "SELECT t.*, e.entity_name, e.fund_cluster,
                  i.item_id, i.requested_qty, i.issued_qty, i.stock_available, i.remarks, i.ris_item_id,
                  itm.item_description, itm.unit, itm.unit_cost, itm.stock_no
                  FROM `$source_table` t 
                  JOIN entities e ON t.entity_id = e.entity_id 
                  LEFT JOIN ris_items i ON t.ris_id = i.ris_id
                  LEFT JOIN items itm ON i.item_id = itm.item_id
                  WHERE t.$id_column = ?";
    }
} else if ($source_table == 'property_acknowledgment_receipts') {
    if ($item_id > 0 && !empty($item_id_column)) {
        $query = "SELECT t.*, e.entity_name, e.fund_cluster,
                  i.item_id, i.quantity, i.property_number, i.par_item_id,i.remarks,
                  itm.item_description, itm.unit, itm.unit_cost
                  FROM `$source_table` t 
                  JOIN entities e ON t.entity_id = e.entity_id 
                  JOIN par_items i ON t.par_id = i.par_id
                  JOIN items itm ON i.item_id = itm.item_id
                  WHERE i.par_item_id = ?";
    } else {
        $query = "SELECT t.*, e.entity_name, e.fund_cluster,
                  i.item_id, i.quantity, i.property_number, i.par_item_id,i.remarks,
                  itm.item_description, itm.unit, itm.unit_cost
                  FROM `$source_table` t 
                  JOIN entities e ON t.entity_id = e.entity_id 
                  LEFT JOIN par_items i ON t.par_id = i.par_id
                  LEFT JOIN items itm ON i.item_id = itm.item_id
                  WHERE t.$id_column = ?";
    }
} else {
    $query = "SELECT * FROM `$source_table` WHERE $id_column = ?";
}

$stmt = $mysqli->prepare($query);
if ($stmt === false) {
    die("Error preparing statement: " . $mysqli->error);
}

// Decide which parameter to bind based on what's available
$param_id = ($item_id > 0) ? $item_id : $record_id;
$stmt->bind_param("i", $param_id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

if (!$item) {
    die("Record not found.");
}

// Calculate total amounts for each form type
if ($source_table === 'inspection_acceptance_reports' && isset($item['quantity']) && isset($item['unit_price'])) {
    $item['total_price'] = $item['quantity'] * $item['unit_price'];
} 
else if ($source_table === 'inventory_custodian_slips' && isset($item['quantity']) && isset($item['unit_cost'])) {
    $item['total_amount'] = $item['quantity'] * $item['unit_cost'];
}
else if ($source_table === 'property_acknowledgment_receipts' && isset($item['quantity']) && isset($item['unit_cost'])) {
    $item['total_amount'] = $item['quantity'] * $item['unit_cost'];
}
else if ($source_table === 'requisition_and_issue_slips' && isset($item['requested_qty']) && isset($item['unit_cost'])) {
    $item['total_cost'] = $item['requested_qty'] * $item['unit_cost'];
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    // Only allow updating remarks field
    if (isset($_POST['remarks'])) {
        $remarks = sanitize($_POST['remarks']);
        
        // Update the item-specific tables instead of the main tables
        if ($source_table == 'inspection_acceptance_reports' && $item_id > 0) {
            $sql = "UPDATE iar_items SET remarks = ? WHERE iar_item_id = ?";
            try {
                $stmt = $mysqli->prepare($sql);
                if ($stmt === false) {
                    $err = "Error preparing statement: " . $mysqli->error;
                    error_log("MySQL Error: " . $mysqli->error);
                } else {
                    $stmt->bind_param("si", $remarks, $item_id);
                    if ($stmt->execute()) {
                        $success = "Remarks updated successfully.";
                        // Redirect to prevent form resubmission
                        header("Location: track_inventory.php?success=Remarks updated successfully.");
                        exit();
                    } else {
                        $err = "Update failed: " . $stmt->error;
                        error_log("Execute Error: " . $stmt->error);
                    }
                    $stmt->close();
                }
            } catch (Exception $e) {
                $err = "Database operation failed: " . $e->getMessage();
                error_log("Exception: " . $e->getMessage());
            }
        } 
        else if ($source_table == 'inventory_custodian_slips' && $item_id > 0) {
            $sql = "UPDATE ics_items SET remarks = ? WHERE ics_item_id = ?";
            try {
                $stmt = $mysqli->prepare($sql);
                if ($stmt === false) {
                    $err = "Error preparing statement: " . $mysqli->error;
                    error_log("MySQL Error: " . $mysqli->error);
                } else {
                    $stmt->bind_param("si", $remarks, $item_id);
                    if ($stmt->execute()) {
                        $success = "Remarks updated successfully.";
                        // Redirect to prevent form resubmission
                        header("Location: track_inventory.php?success=Remarks updated successfully.");
                        exit();
                    } else {
                        $err = "Update failed: " . $stmt->error;
                        error_log("Execute Error: " . $stmt->error);
                    }
                    $stmt->close();
                }
            } catch (Exception $e) {
                $err = "Database operation failed: " . $e->getMessage();
                error_log("Exception: " . $e->getMessage());
            }
        }
        else if ($source_table == 'property_acknowledgment_receipts' && $item_id > 0) {
            $sql = "UPDATE par_items SET remarks = ? WHERE par_item_id = ?";
            try {
                $stmt = $mysqli->prepare($sql);
                if ($stmt === false) {
                    $err = "Error preparing statement: " . $mysqli->error;
                    error_log("MySQL Error: " . $mysqli->error);
                } else {
                    $stmt->bind_param("si", $remarks, $item_id);
                    if ($stmt->execute()) {
                        $success = "Remarks updated successfully.";
                        // Redirect to prevent form resubmission
                        header("Location: track_inventory.php?success=Remarks updated successfully.");
                        exit();
                    } else {
                        $err = "Update failed: " . $stmt->error;
                        error_log("Execute Error: " . $stmt->error);
                    }
                    $stmt->close();
                }
            } catch (Exception $e) {
                $err = "Database operation failed: " . $e->getMessage();
                error_log("Exception: " . $e->getMessage());
            }
        }
        else if ($source_table == 'requisition_and_issue_slips' && $item_id > 0) {
            $sql = "UPDATE ris_items SET remarks = ? WHERE ris_item_id = ?";
            try {
                $stmt = $mysqli->prepare($sql);
                if ($stmt === false) {
                    $err = "Error preparing statement: " . $mysqli->error;
                    error_log("MySQL Error: " . $mysqli->error);
                } else {
                    $stmt->bind_param("si", $remarks, $item_id);
                    if ($stmt->execute()) {
                        $success = "Remarks updated successfully.";
                        // Redirect to prevent form resubmission
                        header("Location: track_inventory.php?success=Remarks updated successfully.");
                        exit();
                    } else {
                        $err = "Update failed: " . $stmt->error;
                        error_log("Execute Error: " . $stmt->error);
                    }
                    $stmt->close();
                }
            } catch (Exception $e) {
                $err = "Database operation failed: " . $e->getMessage();
                error_log("Exception: " . $e->getMessage());
            }
        } else {
            $err = "Invalid item or form type.";
            error_log("Invalid item ID or source table for remarks update.");
        }
    } else {
        $err = "Remarks field is required.";
        error_log("Remarks field not provided in form submission.");
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
                        <input style="color: #000000;" type="text" class="form-control" name="entity_name" value="<?php echo get_field_value($item, 'entity_name'); ?>" readonly>
                        <input type="hidden" name="entity_id" value="<?php echo get_field_value($item, 'entity_id'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Fund Cluster</label>
                        <input style="color: #000000;" type="text" class="form-control" name="fund_cluster" value="<?php echo get_field_value($item, 'fund_cluster'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label>PAR No.</label>
                        <input style="color: #000000;" type="text" class="form-control" name="par_no" value="<?php echo get_field_value($item, 'par_no'); ?>" readonly>
                    </div>
                </div>
                <!-- Item Info -->
                <div class="row mb-3">
                    <div class="col-md-2">
                        <label>Quantity</label>
                        <input style="color: #000000;" type="number" class="form-control" name="quantity" value="<?php echo get_field_value($item, 'quantity'); ?>" readonly>
                    </div>
                    <div class="col-md-2">
                        <label>Unit</label>
                        <input style="color: #000000;" type="text" class="form-control" name="unit" value="<?php echo get_field_value($item, 'unit'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label>Description</label>
                        <input style="color: #000000;" type="text" class="form-control" name="item_description" value="<?php echo get_field_value($item, 'item_description'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label>Property Number</label>
                        <input style="color: #000000;" type="text" class="form-control" name="property_number" value="<?php echo get_field_value($item, 'property_number'); ?>" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Date Acquired</label>
                        <input style="color: #000000;" type="date" class="form-control" name="date_acquired" value="<?php echo get_field_value($item, 'date_acquired'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label>Unit Cost</label>
                        <input style="color: #000000;" type="text" class="form-control" name="unit_cost" value="<?php echo get_field_value($item, 'unit_cost'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Total Amount</label>
                        <input style="color: #000000;" type="text" class="form-control" name="total_amount" value="<?php echo get_field_value($item, 'total_amount'); ?>" readonly>
                    </div>
                </div>
                <!-- Receiver Section -->
                <div class="sub-section receiver-section">Receiver</div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>End User Name</label>
                        <input style="color: #000000;" type="text" class="form-control" name="end_user_name" value="<?php echo get_field_value($item, 'end_user_name'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label>Position/Office</label>
                        <input style="color: #000000;" type="text" class="form-control" name="receiver_position" value="<?php echo get_field_value($item, 'receiver_position'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label>Date</label>
                        <input style="color: #000000;" type="date" class="form-control" name="receiver_date" value="<?php echo get_field_value($item, 'receiver_date'); ?>" readonly>
                    </div>
                </div>
                <!-- Issue Section -->
                <div class="sub-section issue-section">Issue</div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Property Custodian Name</label>
                        <input style="color: #000000;" type="text" class="form-control" name="custodian_name" value="<?php echo get_field_value($item, 'custodian_name'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label>Position/Office</label>
                        <input style="color: #000000;" type="text" class="form-control" name="custodian_position" value="<?php echo get_field_value($item, 'custodian_position'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label>Date</label>
                        <input style="color: #000000;" type="date" class="form-control" name="custodian_date" value="<?php echo get_field_value($item, 'custodian_date'); ?>" readonly>
                    </div>
                </div>
                
                <div style="margin-bottom: 20px;"><strong>Edit:</strong></div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label>Remarks</label>
                        <textarea style="color: #000000;" class="form-control" name="remarks"><?php echo isset($item['remarks']) ? $item['remarks'] : ''; ?></textarea>
                    </div>
                </div>

              <?php elseif ($source_table === 'requisition_and_issue_slips'): ?>
                <h2 class="text-center mb-4 text-uppercase">Requisition and Issue Slip</h2>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Entity Name</label>
                        <input style="color: #000000;" type="text" class="form-control" name="entity_name" value="<?php echo get_field_value($item, 'entity_name'); ?>" readonly>
                        <input type="hidden" name="entity_id" value="<?php echo get_field_value($item, 'entity_id'); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fund Cluster</label>
                        <input style="color: #000000;" type="text" class="form-control" name="fund_cluster" value="<?php echo get_field_value($item, 'fund_cluster'); ?>" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Division</label>
                        <input style="color: #000000;" type="text" class="form-control" name="division" value="<?php echo get_field_value($item, 'division'); ?>" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Office</label>
                        <input style="color: #000000;" type="text" class="form-control" name="office" value="<?php echo get_field_value($item, 'office'); ?>" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Responsibility Center Code</label>
                        <input style="color: #000000;" type="text" class="form-control" name="responsibility_code" value="<?php echo get_field_value($item, 'responsibility_code'); ?>" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">RIS No.</label>
                        <input style="color: #000000;" type="text" class="form-control" name="ris_no" value="<?php echo get_field_value($item, 'ris_no'); ?>" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Stock No.</label>
                        <input style="color: #000000;" type="text" class="form-control" name="stock_no" value="<?php echo get_field_value($item, 'stock_no'); ?>" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Unit</label>
                        <input style="color: #000000;" type="text" class="form-control" name="unit" value="<?php echo get_field_value($item, 'unit'); ?>" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Item Description</label>
                        <input style="color: #000000;" type="text" style="color: #000000;" class="form-control" name="item_description" value="<?php echo get_field_value($item, 'item_description'); ?>" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Requested Quantity</label>
                        <input style="color: #000000;" type="number" style="color: #000000;" class="form-control" name="requested_qty" value="<?php echo get_field_value($item, 'requested_qty'); ?>" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Stock Available</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo get_field_value($item, 'stock_available'); ?>" readonly>
                        <input type="hidden" name="stock_available" value="<?php echo get_field_value($item, 'stock_available'); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Issued Quantity</label>
                        <input style="color: #000000;" type="number" style="color: #000000;" class="form-control" name="issued_qty" value="<?php echo get_field_value($item, 'issued_qty'); ?>" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <!-- <div class="col-md-6">
                        <label class="form-label">Remarks</label>
                        <textarea style="color: #000000;" class="form-control" name="remarks"><?php echo get_field_value($item, 'remarks'); ?></textarea>
                    </div> -->
                    <div class="col-md-6">
                        <label class="form-label">Purpose</label>
                        <input style="color: #000000;" type="text" style="color: #000000;" class="form-control" name="purpose" value="<?php echo get_field_value($item, 'purpose'); ?>" readonly>
                    </div>
                </div>
                <h5 class="mt-4">Requested By</h5>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Name</label>
                        <input type="text" style="color: #000000;" class="form-control" name="requested_by_name" value="<?php echo get_field_value($item, 'requested_by_name'); ?>" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Designation</label>
                        <input type="text" style="color: #000000;" class="form-control" name="requested_by_designation" value="<?php echo get_field_value($item, 'requested_by_designation'); ?>" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" style="color: #000000;" class="form-control" name="requested_by_date" value="<?php echo get_field_value($item, 'requested_by_date'); ?>" readonly>
                    </div>
                </div>
                <h5>Approved By</h5>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Name</label>
                        <input type="text" style="color: #000000;" class="form-control" name="approved_by_name" value="<?php echo get_field_value($item, 'approved_by_name'); ?>" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Designation</label>
                        <input type="text" style="color: #000000;" class="form-control" name="approved_by_designation" value="<?php echo get_field_value($item, 'approved_by_designation'); ?>" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" style="color: #000000;" class="form-control" name="approved_by_date" value="<?php echo $item['approved_by_date'] ?? ''; ?>" readonly>
                    </div>
                </div>
                <h5>Issued By</h5>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Name</label>
                        <input type="text" style="color: #000000;" class="form-control" name="issued_by_name" value="<?php echo get_field_value($item, 'issued_by_name'); ?>" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Designation</label>
                        <input type="text" style="color: #000000;" class="form-control" name="issued_by_designation" value="<?php echo get_field_value($item, 'issued_by_designation'); ?>" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" style="color: #000000;" class="form-control" name="issued_by_date" value="<?php echo get_field_value($item, 'issued_by_date'); ?>" readonly>
                    </div>
                </div>
                <h5>Received By</h5>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Name</label>
                        <input type="text" style="color: #000000;" class="form-control" name="received_by_name" value="<?php echo get_field_value($item, 'received_by_name'); ?>" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Designation</label>
                        <input type="text" style="color: #000000;" class="form-control" name="received_by_designation" value="<?php echo get_field_value($item, 'received_by_designation'); ?>" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" style="color: #000000;" class="form-control" name="received_by_date" value="<?php echo get_field_value($item, 'received_by_date'); ?>" readonly>
                    </div>
                </div>

                <div style="margin-bottom: 10px; margin-top: 25px;"><strong>Edit:</strong></div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label>Remarks</label>
                        <textarea style="color: #000000;" class="form-control" name="remarks"><?php echo isset($item['remarks']) ? $item['remarks'] : ''; ?></textarea>
                    </div>
                </div>

              <?php elseif ($source_table === 'inventory_custodian_slips'): ?>
                <h2 class="text-center mb-4 text-uppercase">Inventory Custodian Slip</h2>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Entity Name</label>
                        <input style="color: #000000;" type="text" class="form-control" name="entity_name" value="<?php echo get_field_value($item, 'entity_name'); ?>" readonly>
                        <input type="hidden" name="entity_id" value="<?php echo get_field_value($item, 'entity_id'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fund Cluster</label>
                        <input style="color: #000000;" type="text" class="form-control" name="fund_cluster" value="<?php echo get_field_value($item, 'fund_cluster'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ICS No.</label>
                        <input style="color: #000000;" type="text" class="form-control" name="ics_no" value="<?php echo get_field_value($item, 'ics_no'); ?>" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-2">
                        <label class="form-label">Quantity</label>
                        <input style="color: #000000;" type="number" class="form-control" name="quantity" value="<?php echo get_field_value($item, 'quantity'); ?>" readonly>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Unit</label>
                        <input style="color: #000000;" type="text" class="form-control" name="unit" value="<?php echo get_field_value($item, 'unit'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Unit Cost</label>
                        <input style="color: #000000;" type="text" class="form-control" name="unit_cost" value="<?php echo get_field_value($item, 'unit_cost'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Total Amount</label>
                        <input style="color: #000000;" type="text" class="form-control" name="total_amount" value="<?php echo get_field_value($item, 'total_amount'); ?>" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Item Description</label>
                        <input style="color: #000000;" type="text" class="form-control" name="item_description" value="<?php echo get_field_value($item, 'item_description'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Inventory Item No.</label>
                        <input style="color: #000000;" type="text" class="form-control" name="inventory_item_no" value="<?php echo get_field_value($item, 'inventory_item_no'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Estimated Useful Life</label>
                        <input style="color: #000000;" type="text" class="form-control" name="estimated_useful_life" value="<?php echo get_field_value($item, 'estimated_useful_life'); ?>" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">End User Name</label>
                        <input style="color: #000000;" type="text" class="form-control" name="end_user_name" value="<?php echo get_field_value($item, 'end_user_name'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Position / Office</label>
                        <input style="color: #000000;" type="text" class="form-control" name="end_user_position" value="<?php echo get_field_value($item, 'end_user_position'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date Received (by End User)</label>
                        <input style="color: #000000;" type="date" class="form-control" name="end_user_date" value="<?php echo get_field_value($item, 'end_user_date'); ?>" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Property Custodian Name</label>
                        <input style="color: #000000;" type="text" class="form-control" name="custodian_name" value="<?php echo get_field_value($item, 'custodian_name'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Position / Office (Custodian)</label>
                        <input style="color: #000000;" type="text" class="form-control" name="custodian_position" value="<?php echo get_field_value($item, 'custodian_position'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date Received (by Custodian)</label>
                        <input style="color: #000000;" type="date" class="form-control" name="custodian_date" value="<?php echo get_field_value($item, 'custodian_date'); ?>" readonly>
                    </div>
                </div>
                <!-- <div class="row mb-3">
                    <div class="col-md-8">
                        <label class="form-label">Remarks</label>
                        <textarea style="color: #000000;" class="form-control" name="remarks"><?php echo get_field_value($item, 'remarks'); ?></textarea>
                    </div>
                </div> -->
                <div style="margin-bottom: 10px; margin-top: 25px;"><strong>Edit:</strong></div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label>Remarks</label>
                        <textarea style="color: #000000;" class="form-control" name="remarks"><?php echo isset($item['remarks']) ? $item['remarks'] : ''; ?></textarea>
                    </div>
                </div>

              <?php elseif ($source_table === 'inspection_acceptance_reports'): ?>
                <h2 class="text-center mb-4 text-uppercase">Inspection and Acceptance Report</h2>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Entity Name</label>
                        <input style="color: #000000;" type="text" class="form-control" name="entity_name" value="<?php echo get_field_value($item, 'entity_name'); ?>" readonly>
                        <input type="hidden" name="entity_id" value="<?php echo get_field_value($item, 'entity_id'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fund Cluster</label>
                        <input style="color: #000000;" type="text" class="form-control" name="fund_cluster" value="<?php echo get_field_value($item, 'fund_cluster'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Supplier</label>
                        <input style="color: #000000;" type="text" class="form-control" name="supplier_name" value="<?php echo get_field_value($item, 'supplier_name'); ?>" readonly>
                        <input type="hidden" name="supplier_id" value="<?php echo get_field_value($item, 'supplier_id'); ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">PO No. / Date</label>
                        <input style="color: #000000;" type="text" class="form-control" name="po_no_date" value="<?php echo get_field_value($item, 'po_no_date'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Requisitioning Office/Dept.</label>
                        <input style="color: #000000;" type="text" class="form-control" name="req_office" value="<?php echo get_field_value($item, 'req_office'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Responsibility Center</label>
                        <input style="color: #000000;" type="text" class="form-control" name="responsibility_center" value="<?php echo get_field_value($item, 'responsibility_center'); ?>" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">IAR No.</label>
                        <input style="color: #000000;" type="text" class="form-control" name="iar_no" value="<?php echo get_field_value($item, 'iar_no'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">IAR Date</label>
                        <input style="color: #000000;" type="date" class="form-control" name="iar_date" value="<?php echo get_field_value($item, 'iar_date'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Invoice No. / Date</label>
                        <input style="color: #000000;" type="text" class="form-control" name="invoice_no_date" value="<?php echo get_field_value($item, 'invoice_no_date'); ?>" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Stock / Property No.</label>
                        <input style="color: #000000;" type="text" class="form-control" name="stock_no" value="<?php echo get_field_value($item, 'stock_no'); ?>" readonly>
                    </div>
                    <!-- <div class="col-md-3">
                        <label class="form-label">Remarks</label>
                        <textarea style="color: #000000;" class="form-control" name="remarks"><?php echo get_field_value($item, 'remarks'); ?></textarea>
                    </div> -->
                    <div class="col-md-6">
                        <label class="form-label">Item Description</label>
                        <input style="color: #000000;" type="text" class="form-control" name="item_description" value="<?php echo get_field_value($item, 'item_description'); ?>" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-2">
                        <label class="form-label">Unit</label>
                        <input style="color: #000000;" type="text" class="form-control" name="unit" value="<?php echo get_field_value($item, 'unit'); ?>" readonly>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Qty</label>
                        <input style="color: #000000;" type="number" class="form-control" name="quantity" value="<?php echo get_field_value($item, 'quantity'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Unit Price</label>
                        <input style="color: #000000;" type="number" step="0.01" class="form-control" name="unit_price" value="<?php echo get_field_value($item, 'unit_price'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Total Price</label>
                        <input style="color: #000000;" type="text" class="form-control" name="total_price" value="<?php echo get_field_value($item, 'total_price'); ?>" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Receiver Name</label>
                        <input style="color: #000000;" type="text" class="form-control" name="receiver_name" value="<?php echo get_field_value($item, 'receiver_name'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Teacher's ID</label>
                        <input style="color: #000000;" type="text" class="form-control" name="teacher_id" value="<?php echo get_field_value($item, 'teacher_id'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Position</label>
                        <input style="color: #000000;" type="text" class="form-control" name="position" value="<?php echo get_field_value($item, 'position'); ?>" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Date Inspected</label>
                        <input style="color: #000000;" type="date" class="form-control" name="date_inspected" value="<?php echo get_field_value($item, 'date_inspected'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Inspection Team</label>
                        <input style="color: #000000;" type="text" class="form-control" name="inspectors" value="<?php echo get_field_value($item, 'inspectors'); ?>" placeholder="e.g., Joan Savage, Nelson British, Bles Sings" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Barangay Councilor</label>
                        <input style="color: #000000;" type="text" class="form-control" name="barangay_councilor" value="<?php echo get_field_value($item, 'barangay_councilor'); ?>" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">PTA Observer</label>
                        <input style="color: #000000;" type="text" class="form-control" name="pta_observer" value="<?php echo get_field_value($item, 'pta_observer'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date Received</label>
                        <input style="color: #000000;" type="date" class="form-control" name="date_received" value="<?php echo get_field_value($item, 'date_received'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Property Custodian</label>
                        <input style="color: #000000;" type="text" class="form-control" name="property_custodian" value="<?php echo get_field_value($item, 'property_custodian'); ?>" readonly>
                    </div>
                </div>
                <div style="margin-bottom: 10px; margin-top: 25px;"><strong>Edit:</strong></div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label>Remarks</label>
                        <textarea style="color: #000000;" class="form-control" name="remarks"><?php echo isset($item['remarks']) ? $item['remarks'] : ''; ?></textarea>
                    </div>
                </div>
              <?php endif; ?>

              <div class="text-end mt-3">
                <a href="track_inventory.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Remarks</button>
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
