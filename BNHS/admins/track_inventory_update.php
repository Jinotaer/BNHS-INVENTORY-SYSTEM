<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

function sanitize($data) {
    return htmlspecialchars(trim($data));
}

$tables = [
  'inspection_acceptance_reports',
  'inventory_custodian_slip',
  'requisition_and_issue_slip',
  'property_acknowledgment_receipt'
];

// Get parameters
$source_table = isset($_GET['table']) ? $_GET['table'] : '';
$record_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Validate
if (!in_array($source_table, $tables) || $record_id <= 0) {
    die("Invalid table or ID.");
}

// Fetch existing record
$query = "SELECT * FROM `$source_table` WHERE id = ?";
$stmt = $mysqli->prepare($query);
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

    switch($source_table) {
        case 'inspection_acceptance_reports':
            $fields = [
                'entity_name', 'fund_cluster', 'iar_no', 'quantity', 'unit', 'item_description',
                'property_number', 'date_acquired', 'unit_cost', 'total_cost', 'receiver_name',
                'receiver_position', 'receiver_date', 'property_custodian', 'custodian_position',
                'custodian_date'
            ];
            break;
        case 'inventory_custodian_slip':
            $fields = [
                'entity_name', 'fund_cluster', 'ics_no', 'quantity', 'unit', 'item_description',
                'inventory_item_no', 'date_acquired', 'unit_cost', 'total_cost', 'end_user_name',
                'end_user_position', 'end_user_date', 'custodian_name', 'custodian_position',
                'custodian_date'
            ];
            break;
        case 'requisition_and_issue_slip':
            $fields = [
                'entity_name', 'fund_cluster', 'ris_no', 'quantity', 'unit', 'item_description',
                'stock_no', 'date_acquired', 'unit_cost', 'total_cost', 'requested_by_name',
                'requested_by_position', 'requested_by_date', 'issued_by_name', 'issued_by_position',
                'issued_by_date'
            ];
            break;
        case 'property_acknowledgment_receipt':
            $fields = [
                'entity_name', 'fund_cluster', 'par_no', 'quantity', 'unit', 'item_description',
                'property_number', 'date_acquired', 'unit_cost', 'total_cost', 'received_by_name',
                'received_by_position', 'received_by_date', 'issued_by_name', 'issued_by_position',
                'issued_by_date'
            ];
            break;
    }

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $updates[] = "`$field` = ?";
            $values[] = sanitize($_POST[$field]);
        }
    }

    if (!empty($updates)) {
        $setClause = implode(', ', $updates);
        $sql = "UPDATE `$source_table` SET $setClause WHERE id = ?";
        
        // Debug information
        error_log("SQL Query: " . $sql);
        error_log("Table: " . $source_table);
        error_log("Updates: " . print_r($updates, true));
        error_log("Values: " . print_r($values, true));
        
        $stmt = $mysqli->prepare($sql);
        
        if ($stmt === false) {
            $err = "Error preparing statement: " . $mysqli->error;
            error_log("MySQL Error: " . $mysqli->error);
        } else {
            // Create an array of references for bind_param
            $types = str_repeat('s', count($values)) . 'i';
            $values[] = $record_id;
            
            // Bind parameters directly
            $stmt->bind_param($types, ...$values);

            if ($stmt->execute()) {
                $success = "Record updated successfully.";
                header("Location: track_inventory.php");
                exit();
            } else {
                $err = "Update failed: " . $stmt->error;
                error_log("Execute Error: " . $stmt->error);
            }
            $stmt->close();
        }
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
          <form method="POST" class="border border-light p-4 rounded">
            <div class="container mt-4">
              <?php if ($source_table === 'property_acknowledgment_receipt'): ?>
                <h2 class="text-center mb-4 text-uppercase">Purchase Acceptance Report</h2>
                <!-- Entity Info -->
                <div class="row mt-3 mb-3">
                    <div class="col-md-4">
                        <label>Entity Name</label>
                        <input style="color: #000000;" type="text" class="form-control" name="entity_name" value="<?php echo $item['entity_name'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label>Fund Cluster</label>
                        <input style="color: #000000;" type="text" class="form-control" name="fund_cluster" value="<?php echo $item['fund_cluster'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label>PAR No.</label>
                        <input style="color: #000000;" type="text" class="form-control" name="par_no" value="<?php echo $item['par_no'] ?? ''; ?>" required>
                    </div>
                </div>
                <!-- Item Info -->
                <div class="row mb-3">
                    <div class="col-md-2">
                        <label>Quantity</label>
                        <input style="color: #000000;" type="number" class="form-control" name="quantity" value="<?php echo $item['quantity'] ?? ''; ?>">
                    </div>
                    <div class="col-md-2">
                        <label>Unit</label>
                        <input style="color: #000000;" type="text" class="form-control" name="unit" value="<?php echo $item['unit'] ?? ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Description</label>
                        <input style="color: #000000;" type="text" class="form-control" name="item_description" value="<?php echo $item['item_description'] ?? ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Property Number</label>
                        <input style="color: #000000;" type="text" class="form-control" name="property_number" value="<?php echo $item['property_number'] ?? ''; ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Date Acquired</label>
                        <input style="color: #000000;" type="date" class="form-control" name="date_acquired" value="<?php echo $item['date_acquired'] ?? ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Unit Cost</label>
                        <input style="color: #000000;" type="text" class="form-control" name="unit_cost" value="<?php echo $item['unit_cost'] ?? ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Total Amount</label>
                        <input style="color: #000000; background-color: white;" type="text" class="form-control" name="total_amount" value="<?php echo $item['total_amount'] ?? ''; ?>" readonly>
                    </div>
                </div>
                <!-- Receiver Section -->
                <div class="sub-section receiver-section">Receiver</div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>End User Name</label>
                        <input style="color: #000000;" type="text" class="form-control" name="end_user_name" value="<?php echo $item['end_user_name'] ?? ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Position/Office</label>
                        <input style="color: #000000;" type="text" class="form-control" name="receiver_position" value="<?php echo $item['receiver_position'] ?? ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Date</label>
                        <input style="color: #000000;" type="date" class="form-control" name="receiver_date" value="<?php echo $item['receiver_date'] ?? ''; ?>">
                    </div>
                </div>
                <!-- Issue Section -->
                <div class="sub-section issue-section">Issue</div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Property Custodian Name</label>
                        <input style="color: #000000;" type="text" class="form-control" name="custodian_name" value="<?php echo $item['custodian_name'] ?? ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Position/Office</label>
                        <input style="color: #000000;" type="text" class="form-control" name="custodian_position" value="<?php echo $item['custodian_position'] ?? ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Date</label>
                        <input style="color: #000000;" type="date" class="form-control" name="custodian_date" value="<?php echo $item['custodian_date'] ?? ''; ?>">
                    </div>
                </div>

              <?php elseif ($source_table === 'requisition_and_issue_slip'): ?>
                <h2 class="text-center mb-4 text-uppercase">Requisition and Issue Slip</h2>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Entity Name</label>
                        <input style="color: #000000;" type="text" class="form-control" name="entity_name" value="<?php echo $item['entity_name'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fund Cluster</label>
                        <input type="text" class="form-control" name="fund_cluster" value="<?php echo $item['fund_cluster'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Division</label>
                        <input type="text" class="form-control" name="division" value="<?php echo $item['division'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Office</label>
                        <input type="text" class="form-control" name="office" value="<?php echo $item['office'] ?? ''; ?>" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Responsibility Center Code</label>
                        <input type="text" class="form-control" name="responsibility_code" value="<?php echo $item['responsibility_code'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">RIS No.</label>
                        <input type="text" class="form-control" name="ris_no" value="<?php echo $item['ris_no'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Stock No.</label>
                        <input type="text" class="form-control" name="stock_no" value="<?php echo $item['stock_no'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Unit</label>
                        <input type="text" class="form-control" name="unit" value="<?php echo $item['unit'] ?? ''; ?>" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Item Description</label>
                        <input type="text" style="color: #000000;" class="form-control" name="item_description" value="<?php echo $item['item_description'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Requested Quantity</label>
                        <input type="number" style="color: #000000;" class="form-control" name="requested_qty" value="<?php echo $item['requested_qty'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Stock Available (Yes/No)</label>
                        <select style="color: #000000;" class="form-control" name="stock_available" required>
                            <option value="yes" <?php echo ($item['stock_available'] ?? '') === 'yes' ? 'selected' : ''; ?>>Yes</option>
                            <option value="no" <?php echo ($item['stock_available'] ?? '') === 'no' ? 'selected' : ''; ?>>No</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Issued Quantity</label>
                        <input type="number" style="color: #000000;" class="form-control" name="issued_qty" value="<?php echo $item['issued_qty'] ?? ''; ?>" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Remarks</label>
                        <input type="text" style="color: #000000;" class="form-control" name="remarks" value="<?php echo $item['remarks'] ?? ''; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Purpose</label>
                        <input type="text" style="color: #000000;" class="form-control" name="purpose" value="<?php echo $item['purpose'] ?? ''; ?>" required>
                    </div>
                </div>
                <h5 class="mt-4">Requested By</h5>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Name</label>
                        <input type="text" style="color: #000000;" class="form-control" name="requested_by_name" value="<?php echo $item['requested_by_name'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Designation</label>
                        <input type="text" style="color: #000000;" class="form-control" name="requested_by_designation" value="<?php echo $item['requested_by_designation'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" style="color: #000000;" class="form-control" name="requested_by_date" value="<?php echo $item['requested_by_date'] ?? ''; ?>" required>
                    </div>
                </div>
                <h5>Approved By</h5>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Name</label>
                        <input type="text" style="color: #000000;" class="form-control" name="approved_by_name" value="<?php echo $item['approved_by_name'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Designation</label>
                        <input type="text" style="color: #000000;" class="form-control" name="approved_by_designation" value="<?php echo $item['approved_by_designation'] ?? ''; ?>" required>
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
                        <input type="text" style="color: #000000;" class="form-control" name="issued_by_name" value="<?php echo $item['issued_by_name'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Designation</label>
                        <input type="text" style="color: #000000;" class="form-control" name="issued_by_designation" value="<?php echo $item['issued_by_designation'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" style="color: #000000;" class="form-control" name="issued_by_date" value="<?php echo $item['issued_by_date'] ?? ''; ?>" required>
                    </div>
                </div>
                <h5>Received By</h5>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Name</label>
                        <input type="text" style="color: #000000;" class="form-control" name="received_by_name" value="<?php echo $item['received_by_name'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Designation</label>
                        <input type="text" style="color: #000000;" class="form-control" name="received_by_designation" value="<?php echo $item['received_by_designation'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" style="color: #000000;" class="form-control" name="received_by_date" value="<?php echo $item['received_by_date'] ?? ''; ?>" required>
                    </div>
                </div>

              <?php elseif ($source_table === 'inventory_custodian_slip'): ?>
                <h2 class="text-center mb-4 text-uppercase">Inventory Custodian Slip</h2>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Entity Name</label>
                        <input style="color: #000000;" type="text" class="form-control" name="entity_name" value="<?php echo $item['entity_name'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fund Cluster</label>
                        <input style="color: #000000;" type="text" class="form-control" name="fund_cluster" value="<?php echo $item['fund_cluster'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ICS No.</label>
                        <input style="color: #000000;" type="text" class="form-control" name="ics_no" value="<?php echo $item['ics_no'] ?? ''; ?>" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-2">
                        <label class="form-label">Quantity</label>
                        <input style="color: #000000;" type="number" class="form-control" name="quantity" value="<?php echo $item['quantity'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Unit</label>
                        <input style="color: #000000;" type="text" class="form-control" name="unit" value="<?php echo $item['unit'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Unit Cost</label>
                        <input style="color: #000000;" type="text" class="form-control" name="unit_cost" value="<?php echo $item['unit_cost'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Total Amount</label>
                        <input style="color: #000000; background-color: white;" type="text" class="form-control" name="total_amount" value="<?php echo $item['total_amount'] ?? ''; ?>" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Item Description</label>
                        <input style="color: #000000;" type="text" class="form-control" name="item_description" value="<?php echo $item['item_description'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Inventory Item No.</label>
                        <input style="color: #000000;" type="text" class="form-control" name="inventory_item_no" value="<?php echo $item['inventory_item_no'] ?? ''; ?>" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Estimated Useful Life</label>
                        <input style="color: #000000;" type="text" class="form-control" name="estimated_life" value="<?php echo $item['estimated_life'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">End User Name</label>
                        <input style="color: #000000;" type="text" class="form-control" name="end_user_name" value="<?php echo $item['end_user_name'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Position / Office</label>
                        <input style="color: #000000;" type="text" class="form-control" name="end_user_position" value="<?php echo $item['end_user_position'] ?? ''; ?>" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Date Received (by End User)</label>
                        <input style="color: #000000;" type="date" class="form-control" name="date_received_user" value="<?php echo $item['date_received_user'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Property Custodian Name</label>
                        <input style="color: #000000;" type="text" class="form-control" name="custodian_name" value="<?php echo $item['custodian_name'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Position / Office (Custodian)</label>
                        <input style="color: #000000;" type="text" class="form-control" name="custodian_position" value="<?php echo $item['custodian_position'] ?? ''; ?>" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                           <label class="form-label">Date Received (by Custodian)</label>
                        <input style="color: #000000;" type="date" class="form-control" name="date_received_custodian" value="<?php echo $item['date_received_custodian'] ?? ''; ?>" required>
                    </div>
                </div>

              <?php elseif ($source_table === 'inspection_acceptance_reports'): ?>
                <h2 class="text-center mb-4 text-uppercase">Inspection and Acceptance Report</h2>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Entity Name</label>
                        <input style="color: #000000;" type="text" class="form-control" name="entity_name" value="<?php echo $item['entity_name'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fund Cluster</label>
                        <input style="color: #000000;" type="text" class="form-control" name="fund_cluster" value="<?php echo $item['fund_cluster'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Supplier</label>
                        <input style="color: #000000;" type="text" class="form-control" name="supplier" value="<?php echo $item['supplier'] ?? ''; ?>" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">PO No. / Date</label>
                        <input style="color: #000000;" type="text" class="form-control" name="po_no_date" value="<?php echo $item['po_no_date'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Requisitioning Office/Dept.</label>
                        <input style="color: #000000;" type="text" class="form-control" name="req_office" value="<?php echo $item['req_office'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Responsibility Center</label>
                        <input style="color: #000000;" type="text" class="form-control" name="responsibility_center" value="<?php echo $item['responsibility_center'] ?? ''; ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">IAR No.</label>
                        <input style="color: #000000;" type="text" class="form-control" name="iar_no" value="<?php echo $item['iar_no'] ?? ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">IAR Date</label>
                        <input style="color: #000000;" type="date" class="form-control" name="iar_date" value="<?php echo $item['iar_date'] ?? ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Invoice No. / Date</label>
                        <input style="color: #000000;" type="text" class="form-control" name="invoice_no_date" value="<?php echo $item['invoice_no_date'] ?? ''; ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Stock / Property No.</label>
                        <input style="color: #000000;" type="text" class="form-control" name="stock_no" value="<?php echo $item['stock_no'] ?? ''; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Remarks</label>
                        <input style="color: #000000;" type="text" class="form-control" name="remarks" value="<?php echo $item['remarks'] ?? ''; ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Item Description</label>
                        <input style="color: #000000;" type="text" class="form-control" name="item_description" value="<?php echo $item['item_description'] ?? ''; ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-2">
                        <label class="form-label">Unit</label>
                        <input style="color: #000000;" type="text" class="form-control" name="unit" value="<?php echo $item['unit'] ?? ''; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Qty</label>
                        <input style="color: #000000;" type="number" class="form-control" name="quantity" value="<?php echo $item['quantity'] ?? ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Unit Price</label>
                        <input style="color: #000000;" type="number" step="0.01" class="form-control" name="unit_price" value="<?php echo $item['unit_price'] ?? ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Total Price</label>
                        <input style="color: #000000; background-color: white;" type="text" class="form-control" name="total_price" value="<?php echo $item['total_price'] ?? ''; ?>" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Receiver Name</label>
                        <input style="color: #000000;" type="text" class="form-control" name="receiver_name" value="<?php echo $item['receiver_name'] ?? ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Teacher's ID</label>
                        <input style="color: #000000;" type="text" class="form-control" name="teacher_id" value="<?php echo $item['teacher_id'] ?? ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Position</label>
                        <input style="color: #000000;" type="text" class="form-control" name="position" value="<?php echo $item['position'] ?? ''; ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Date Inspected</label>
                        <input style="color: #000000;" type="date" class="form-control" name="date_inspected" value="<?php echo $item['date_inspected'] ?? ''; ?>">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Inspection Team</label>
                        <input style="color: #000000;" type="text" class="form-control" name="inspectors" value="<?php echo $item['inspectors'] ?? ''; ?>" placeholder="e.g., Joan Savage, Nelson British, Bles Sings">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Barangay Councilor</label>
                        <input style="color: #000000;" type="text" class="form-control" name="barangay_councilor" value="<?php echo $item['barangay_councilor'] ?? ''; ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">PTA Observer</label>
                        <input style="color: #000000;" type="text" class="form-control" name="pta_observer" value="<?php echo $item['pta_observer'] ?? ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date Received</label>
                        <input style="color: #000000;" type="date" class="form-control" name="date_received" value="<?php echo $item['date_received'] ?? ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Property Custodian</label>
                        <input style="color: #000000;" type="text" class="form-control" name="property_custodian" value="<?php echo $item['property_custodian'] ?? ''; ?>">
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
</body>
</html>
