<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

function sanitize($data)
{
    return htmlspecialchars(trim($data));
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

// Fetch existing record with special handling for each table type
if ($source_table === 'inventory_custodian_slips') {
    // Check if we're using the specific item ID or main record ID
    if ($item_id > 0 && !empty($item_id_column)) {
        $query = "SELECT ics.ics_id as id, e.entity_name, e.fund_cluster, ics.ics_no,
                  ii.quantity, i.unit, i.unit_cost, (ii.quantity * i.unit_cost) as total_amount,
                  i.item_description, ii.inventory_item_no, i.estimated_useful_life,
                  ics.end_user_name, ics.end_user_position, ics.end_user_date,
                  ics.custodian_name, ics.custodian_position, ics.custodian_date,
                  ics.created_at, ii.article, ii.remarks, ii.ics_item_id
                FROM inventory_custodian_slips ics
                JOIN entities e ON ics.entity_id = e.entity_id
                JOIN ics_items ii ON ics.ics_id = ii.ics_id
                JOIN items i ON ii.item_id = i.item_id
                WHERE ii.ics_item_id = ?";
    } else {
        $query = "SELECT ics.ics_id as id, e.entity_name, e.fund_cluster, ics.ics_no,
                  ii.quantity, i.unit, i.unit_cost, (ii.quantity * i.unit_cost) as total_amount,
                  i.item_description, ii.inventory_item_no, i.estimated_useful_life,
                  ics.end_user_name, ics.end_user_position, ics.end_user_date,
                  ics.custodian_name, ics.custodian_position, ics.custodian_date,
                  ics.created_at, ii.article, ii.remarks, ii.ics_item_id
                FROM inventory_custodian_slips ics
                JOIN entities e ON ics.entity_id = e.entity_id
                JOIN ics_items ii ON ics.ics_id = ii.ics_id
                JOIN items i ON ii.item_id = i.item_id
                WHERE ics.ics_id = ?";
    }
} else if ($source_table === 'requisition_and_issue_slips') {
    if ($item_id > 0 && !empty($item_id_column)) {
        $query = "SELECT 
                  ris.ris_id as id, 
                  e.entity_name, 
                  e.fund_cluster, 
                  ris.division, 
                  ris.office,
                  ris.responsibility_code, 
                  ris.ris_no, 
                  ri.requested_qty, 
                  ri.stock_available, 
                  ri.issued_qty,
                  i.unit, 
                  i.item_description, 
                  i.stock_no, 
                  ri.remarks, 
                  ris.purpose,
                  ris.requested_by_name, 
                  ris.requested_by_designation, 
                  ris.requested_by_date,
                  ris.approved_by_name, 
                  ris.approved_by_designation, 
                  ris.approved_by_date,
                  ris.issued_by_name, 
                  ris.issued_by_designation, 
                  ris.issued_by_date,
                  ris.received_by_name, 
                  ris.received_by_designation, 
                  ris.received_by_date,
                  ri.ris_item_id
                FROM requisition_and_issue_slips ris
                JOIN entities e ON ris.entity_id = e.entity_id
                JOIN ris_items ri ON ris.ris_id = ri.ris_id
                JOIN items i ON ri.item_id = i.item_id
                WHERE ri.ris_item_id = ?";
    } else {
        $query = "SELECT 
                  ris.ris_id as id, 
                  e.entity_name, 
                  e.fund_cluster, 
                  ris.division, 
                  ris.office,
                  ris.responsibility_code, 
                  ris.ris_no, 
                  ri.requested_qty, 
                  ri.stock_available, 
                  ri.issued_qty,
                  i.unit, 
                  i.item_description, 
                  i.stock_no, 
                  ri.remarks, 
                  ris.purpose,
                  ris.requested_by_name, 
                  ris.requested_by_designation, 
                  ris.requested_by_date,
                  ris.approved_by_name, 
                  ris.approved_by_designation, 
                  ris.approved_by_date,
                  ris.issued_by_name, 
                  ris.issued_by_designation, 
                  ris.issued_by_date,
                  ris.received_by_name, 
                  ris.received_by_designation, 
                  ris.received_by_date,
                  ri.ris_item_id
                FROM requisition_and_issue_slips ris
                JOIN entities e ON ris.entity_id = e.entity_id
                JOIN ris_items ri ON ris.ris_id = ri.ris_id
                JOIN items i ON ri.item_id = i.item_id
                WHERE ris.ris_id = ?";
    }
} else if ($source_table === 'property_acknowledgment_receipts') {
    if ($item_id > 0 && !empty($item_id_column)) {
        $query = "SELECT 
                  par.par_id as id, 
                  e.entity_name, 
                  e.fund_cluster, 
                  par.par_no,
                  pi.quantity, 
                  i.unit, 
                  i.unit_cost, 
                  (pi.quantity * i.unit_cost) as total_amount,
                  i.item_description, 
                  pi.property_number, 
                  i.estimated_useful_life,
                  par.end_user_name, 
                  par.receiver_position, 
                  par.receiver_date,
                  par.custodian_name, 
                  par.custodian_position, 
                  par.custodian_date,
                  par.date_acquired,
                  pi.article,
                  pi.remarks,
                  pi.par_item_id
                FROM property_acknowledgment_receipts par
                JOIN entities e ON par.entity_id = e.entity_id
                JOIN par_items pi ON par.par_id = pi.par_id
                JOIN items i ON pi.item_id = i.item_id
                WHERE pi.par_item_id = ?";
    } else {
        $query = "SELECT 
                  par.par_id as id, 
                  e.entity_name, 
                  e.fund_cluster, 
                  par.par_no,
                  pi.quantity, 
                  i.unit, 
                  i.unit_cost, 
                  (pi.quantity * i.unit_cost) as total_amount,
                  i.item_description, 
                  pi.property_number, 
                  i.estimated_useful_life,
                  par.end_user_name, 
                  par.receiver_position, 
                  par.receiver_date,
                  par.custodian_name, 
                  par.custodian_position, 
                  par.custodian_date,
                  par.date_acquired,
                  pi.article,
                  pi.remarks,
                  pi.par_item_id
                FROM property_acknowledgment_receipts par
                JOIN entities e ON par.entity_id = e.entity_id
                JOIN par_items pi ON par.par_id = pi.par_id
                JOIN items i ON pi.item_id = i.item_id
                WHERE par.par_id = ?";
    }
} else if ($source_table === 'inspection_acceptance_reports') {
    if ($item_id > 0 && !empty($item_id_column)) {
        $query = "SELECT 
                  iar.iar_id as id, 
                  e.entity_name, 
                  e.fund_cluster, 
                  s.supplier_name as supplier,
                  iar.po_no_date, 
                  iar.req_office, 
                  iar.responsibility_center, 
                  iar.iar_no, 
                  iar.iar_date,
                  iar.invoice_no_date, 
                  ii.quantity, 
                  i.unit, 
                  ii.unit_price, 
                  ii.total_price,
                  i.item_description, 
                  i.stock_no, 
                  ii.remarks,
                  iar.receiver_name, 
                  iar.teacher_id, 
                  iar.position,
                  iar.date_inspected, 
                  iar.inspectors, 
                  iar.barangay_councilor,
                  iar.pta_observer, 
                  iar.date_received, 
                  iar.property_custodian,
                  ii.iar_item_id
                FROM inspection_acceptance_reports iar
                JOIN entities e ON iar.entity_id = e.entity_id
                JOIN suppliers s ON iar.supplier_id = s.supplier_id
                JOIN iar_items ii ON iar.iar_id = ii.iar_id
                JOIN items i ON ii.item_id = i.item_id
                WHERE ii.iar_item_id = ?";
    } else {
        $query = "SELECT 
                  iar.iar_id as id, 
                  e.entity_name, 
                  e.fund_cluster, 
                  s.supplier_name as supplier,
                  iar.po_no_date, 
                  iar.req_office, 
                  iar.responsibility_center, 
                  iar.iar_no, 
                  iar.iar_date,
                  iar.invoice_no_date, 
                  ii.quantity, 
                  i.unit, 
                  ii.unit_price, 
                  ii.total_price,
                  i.item_description, 
                  i.stock_no, 
                  ii.remarks,
                  iar.receiver_name, 
                  iar.teacher_id, 
                  iar.position,
                  iar.date_inspected, 
                  iar.inspectors, 
                  iar.barangay_councilor,
                  iar.pta_observer, 
                  iar.date_received, 
                  iar.property_custodian,
                  ii.iar_item_id
                FROM inspection_acceptance_reports iar
                JOIN entities e ON iar.entity_id = e.entity_id
                JOIN suppliers s ON iar.supplier_id = s.supplier_id
                JOIN iar_items ii ON iar.iar_id = ii.iar_id
                JOIN items i ON ii.item_id = i.item_id
                WHERE iar.iar_id = ?";
    }
} else {
    $query = "SELECT * FROM `$source_table` WHERE id = ?";
}

$stmt = $mysqli->prepare($query);
if (!$stmt) {
    die("Prepare failed: " . $mysqli->error . " Query was: " . $query);
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
                            <form class="border border-light p-4 rounded">
                                <div class="container mt-4">
                                    <?php if ($source_table === 'property_acknowledgment_receipts'): ?>
                                        <h2 class="text-center mb-4 text-uppercase">Purchase Acceptance Report</h2>
                                        <!-- Entity Info -->
                                        <div class="row mt-3 mb-3">
                                            <div class="col-md-4">
                                                <label>Entity Name</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['entity_name'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label>Fund Cluster</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['fund_cluster'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label>PAR No.</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['par_no'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                        </div>
                                        <!-- Item Info -->
                                        <div class="row mb-3">
                                            <div class="col-md-2">
                                                <label>Quantity</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="number" class="form-control" value="<?php echo $item['quantity'] ?? '0'; ?>" readonly>
                                            </div>
                                            <div class="col-md-2">
                                                <label>Unit</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['unit'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label>Description</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['item_description'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label>Property Number</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['property_number'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label>Article</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['article'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-8">
                                                <label>Remarks</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['remarks'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label>Date Acquired</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="date" class="form-control" value="<?php echo $item['date_acquired'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label>Unit Cost</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo number_format($item['unit_cost'] ?? 0, 2); ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Total Amount</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo number_format($item['total_amount'] ?? 0, 2); ?>" readonly>
                                            </div>
                                        </div>
                                        <!-- Receiver Section -->
                                        <div class="sub-section receiver-section">Receiver</div>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label>End User Name</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['end_user_name'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label>Position/Office</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['receiver_position'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label>Date</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="date" class="form-control" value="<?php echo $item['receiver_date'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                        </div>
                                        <!-- Issue Section -->
                                        <div class="sub-section issue-section">Issue</div>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label>Property Custodian Name</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['custodian_name'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label>Position/Office</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['custodian_position'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label>Date</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="date" class="form-control" value="<?php echo $item['custodian_date'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                        </div>

                                    <?php elseif ($source_table === 'requisition_and_issue_slips'): ?>
                                        <h2 class="text-center mb-4 text-uppercase">Requisition and Issue Slip</h2>
                                        <div class="row mb-3">
                                            <div class="col-md-3">
                                                <label class="form-label">Entity Name</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['entity_name'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Fund Cluster</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['fund_cluster'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Division</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['division'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Office</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['office'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-3">
                                                <label class="form-label">Responsibility Center Code</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['responsibility_code'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">RIS No.</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['ris_no'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Stock No.</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['stock_no'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Unit</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['unit'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-3">
                                                <label class="form-label">Item Description</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['item_description'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Requested Quantity</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="number" class="form-control" value="<?php echo $item['requested_qty'] ?? '0'; ?>" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Stock Available</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['stock_available'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Issued Quantity</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="number" class="form-control" value="<?php echo $item['issued_qty'] ?? '0'; ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-3">
                                                <label class="form-label">Remarks</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['remarks'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Purpose</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['purpose'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                        </div>
                                        <h5 class="mt-4">Requested By</h5>
                                        <div class="row mb-3">
                                            <div class="col-md-3">
                                                <label class="form-label">Name</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['requested_by_name'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Designation</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['requested_by_designation'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Date</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="date" class="form-control" value="<?php echo $item['requested_by_date'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                        </div>
                                        <h5>Approved By</h5>
                                        <div class="row mb-3">
                                            <div class="col-md-3">
                                                <label class="form-label">Name</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['approved_by_name'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Designation</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['approved_by_designation'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Date</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="date" class="form-control" value="<?php echo $item['approved_by_date'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                        </div>
                                        <h5>Issued By</h5>
                                        <div class="row mb-3">
                                            <div class="col-md-3">
                                                <label class="form-label">Name</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['issued_by_name'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Designation</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['issued_by_designation'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Date</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="date" class="form-control" value="<?php echo $item['issued_by_date'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                        </div>
                                        <h5>Received By</h5>
                                        <div class="row mb-3">
                                            <div class="col-md-3">
                                                <label class="form-label">Name</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['received_by_name'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Designation</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['received_by_designation'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Date</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="date" class="form-control" value="<?php echo $item['received_by_date'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                        </div>

                                    <?php elseif ($source_table === 'inventory_custodian_slips'): ?>
                                        <h2 class="text-center mb-4 text-uppercase">Inventory Custodian Slip</h2>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Entity Name</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['entity_name'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Fund Cluster</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['fund_cluster'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">ICS No.</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['ics_no'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-2">
                                                <label class="form-label">Quantity</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="number" class="form-control" value="<?php echo $item['quantity'] ?? '0'; ?>" readonly>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Unit</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['unit'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Unit Cost</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo number_format($item['unit_cost'] ?? 0, 2); ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Total Amount</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo number_format($item['total_amount'] ?? 0, 2); ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Item Description</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['item_description'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Inventory Item No.</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['inventory_item_no'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Article</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['article'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Remarks</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['remarks'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Estimated Useful Life</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['estimated_useful_life'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">End User Name</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['end_user_name'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Position / Office</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['end_user_position'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Date Received (by End User)</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="date" class="form-control" value="<?php echo $item['end_user_date'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Property Custodian Name</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['custodian_name'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Position / Office (Custodian)</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['custodian_position'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Date Received (by Custodian)</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="date" class="form-control" value="<?php echo $item['custodian_date'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                        </div>

                                    <?php elseif ($source_table === 'inspection_acceptance_reports'): ?>
                                        <h2 class="text-center mb-4 text-uppercase">Inspection and Acceptance Report</h2>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Entity Name</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['entity_name'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Fund Cluster</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['fund_cluster'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Supplier</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['supplier'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label">PO No. / Date</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['po_no_date'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Requisitioning Office/Dept.</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['req_office'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Responsibility Center</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['responsibility_center'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label">IAR No.</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['iar_no'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">IAR Date</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="date" class="form-control" value="<?php echo $item['iar_date'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Invoice No. / Date</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['invoice_no_date'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-3">
                                                <label class="form-label">Stock / Property No.</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['stock_no'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Remarks</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['remarks'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Item Description</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['item_description'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-2">
                                                <label class="form-label">Unit</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['unit'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Qty</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="number" class="form-control" value="<?php echo $item['quantity'] ?? '0'; ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Unit Price</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="number" step="0.01" class="form-control" value="<?php echo number_format($item['unit_price'] ?? 0, 2); ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Total Price</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo number_format($item['total_price'] ?? 0, 2); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Receiver Name</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['receiver_name'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Teacher's ID</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['teacher_id'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Position</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['position'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Date Inspected</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="date" class="form-control" value="<?php echo $item['date_inspected'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-5">
                                                <label class="form-label">Inspection Team</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['inspectors'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Barangay Councilor</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['barangay_councilor'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label">PTA Observer</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['pta_observer'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Date Received</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="date" class="form-control" value="<?php echo $item['date_received'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Property Custodian</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['property_custodian'] ?? 'N/A'; ?>" readonly>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="text-end mt-3">
                                        <a href="track_inventory.php" class="btn btn-primary">Back to List</a>
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
    </div>
</body>

</html>