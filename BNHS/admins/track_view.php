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
                                    <?php if ($source_table === 'property_acknowledgment_receipt'): ?>
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

                                    <?php elseif ($source_table === 'requisition_and_issue_slip'): ?>
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
                                            <div class="col-md-3">
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

                                    <?php elseif ($source_table === 'inventory_custodian_slip'): ?>
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
                                            <div class="col-md-4">
                                                <label class="form-label">Estimated Useful Life</label>
                                                <input style="color: #000000; background-color: #f8f9fa;" type="text" class="form-control" value="<?php echo $item['estimated_life'] ?? 'N/A'; ?>" readonly>
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
                                                <input style="color: #000000; background-color: #f8f9fa;" type="date" class="form-control" value="<?php echo $item['date_received_user'] ?? 'N/A'; ?>" readonly>
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
                                                <input style="color: #000000; background-color: #f8f9fa;" type="date" class="form-control" value="<?php echo $item['date_received_custodian'] ?? 'N/A'; ?>" readonly>
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