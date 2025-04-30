<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
if ($_SERVER['REQUEST_METHOD'] == "POST") {
  // Sanitize and collect form data
  function sanitize($data) {
    return htmlspecialchars(trim($data));
  }

  $entity_name = sanitize($_POST['entity_name']);
  $fund_cluster = sanitize($_POST['fund_cluster']);
  $supplier = sanitize($_POST['supplier']);
  $po_no_date = sanitize($_POST['po_no_date']);
  $req_office = sanitize($_POST['req_office']);
  $responsibility_center = sanitize($_POST['responsibility_center']);
  $iar_no = sanitize($_POST['iar_no']);
  $iar_date = sanitize($_POST['iar_date']);
  $invoice_no_date = sanitize($_POST['invoice_no_date']);
  $stock_no = sanitize($_POST['stock_no']);
  $remarks = sanitize($_POST['remarks']);
  $item_description = sanitize($_POST['item_description']);
  $unit = sanitize($_POST['unit']);
  $quantity = (int) $_POST['quantity'];
  $unit_price = (float) $_POST['unit_price'];
  $total_price = $quantity * $unit_price;
  $receiver_name = sanitize($_POST['receiver_name']);
  $teacher_id = sanitize($_POST['teacher_id']);
  $position = sanitize($_POST['position']);
  $date_inspected = sanitize($_POST['date_inspected']);
  $inspectors = sanitize($_POST['inspectors']);
  $barangay_councilor = sanitize($_POST['barangay_councilor']);
  $pta_observer = sanitize($_POST['pta_observer']);
  $date_received = sanitize($_POST['date_received']);
  $property_custodian = sanitize($_POST['property_custodian']);

  $stmt = $mysqli->prepare("INSERT INTO inspection_acceptance_reports (
    entity_name, fund_cluster, supplier, po_no_date, req_office, responsibility_center,
    iar_no, iar_date, invoice_no_date, stock_no, remarks, item_description, unit,
    quantity, unit_price, total_price, receiver_name, teacher_id, position, date_inspected,
    inspectors, barangay_councilor, pta_observer, date_received, property_custodian
  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

  if ($stmt === false) {
    die("MySQL prepare failed: " . $mysqli->error);
  }

  $stmt->bind_param(
    "sssssssssssssidssssssssss",
    $entity_name, $fund_cluster, $supplier, $po_no_date, $req_office, $responsibility_center,
    $iar_no, $iar_date, $invoice_no_date, $stock_no, $remarks, $item_description, $unit,
    $quantity, $unit_price, $total_price, $receiver_name, $teacher_id, $position,
    $date_inspected, $inspectors, $barangay_councilor, $pta_observer, $date_received, $property_custodian
  );

  if ($stmt->execute()) {
    $success = "Inspection and Acceptance Report Created Successfully";
    header("refresh:1; url=inventory_management.php");
  } else {
    $err = "Error: " . $stmt->error;
    header("refresh:1; url=inventory_management.php");
  }

  $stmt->close();
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
          <div class="dropdown p-3">
            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" style="width: 300px; height: 45px;">
              Inspection and Acceptance Report
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="ris.php">Requisition and Issue Slip</a></li>
              <li><a class="dropdown-item" href="ics.php">Inventory Custodian Slip</a></li>
              <li><a class="dropdown-item" href="par.php">Purchase Acceptance Report</a></li>
            </ul>
          </div>

          <div class="card-body">
            <form method="POST" class="border border-light p-4 rounded">
              <div class="container mt-4">
                <h2 class="text-center mb-4 text-uppercase">Inspection and Acceptance Report</h2>

                <div class="row mb-3">
                  <div class="col-md-4">
                    <label class="form-label">Entity Name</label>
                    <input style="color: #000000;" type="text" class="form-control" name="entity_name" required>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Fund Cluster</label>
                    <input style="color: #000000;" type="text" class="form-control" name="fund_cluster" required>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Supplier</label>
                    <input style="color: #000000;" type="text" class="form-control" name="supplier" required>
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-md-4">
                    <label class="form-label">PO No. / Date</label>
                    <input style="color: #000000;" type="text" class="form-control" name="po_no_date" required>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Requisitioning Office/Dept.</label>
                    <input style="color: #000000;" type="text" class="form-control" name="req_office" required>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Responsibility Center</label>
                    <input style="color: #000000;" type="text" class="form-control" name="responsibility_center">
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-md-4">
                    <label class="form-label">IAR No.</label>
                    <input style="color: #000000;" type="text" class="form-control" name="iar_no">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">IAR Date</label>
                    <input style="color: #000000;" type="date" class="form-control" name="iar_date">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Invoice No. / Date</label>
                    <input style="color: #000000;" type="text" class="form-control" name="invoice_no_date">
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-md-3">
                    <label class="form-label">Stock / Property No.</label>
                    <input style="color: #000000;" type="text" class="form-control" name="stock_no">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Remarks</label>
                    <input style="color: #000000;" type="text" class="form-control" name="remarks">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Item Description</label>
                    <input style="color: #000000;" type="text" class="form-control" name="item_description">
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-md-2">
                    <label class="form-label">Unit</label>
                    <input style="color: #000000;" type="text" class="form-control" name="unit">
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">Qty</label>
                    <input style="color: #000000;" type="number" class="form-control" name="quantity">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Unit Price</label>
                    <input style="color: #000000;" type="number" step="0.01" class="form-control" name="unit_price">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Total Price</label>
                    <input style="color: #000000; background-color: white;" type="text" class="form-control" name="total_price" readonly>
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-md-4">
                    <label class="form-label">Receiver Name</label>
                    <input style="color: #000000;" type="text" class="form-control" name="receiver_name">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Teacher's ID</label>
                    <input style="color: #000000;" type="text" class="form-control" name="teacher_id">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Position</label>
                    <input style="color: #000000;" type="text" class="form-control" name="position">
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-md-4">
                    <label class="form-label">Date Inspected</label>
                    <input style="color: #000000;" type="date" class="form-control" name="date_inspected">
                  </div>
                  <div class="col-md-5">
                    <label class="form-label">Inspection Team (comma separated)</label>
                    <input style="color: #000000;" type="text" class="form-control" name="inspectors" placeholder="e.g., Joan Savage, Nelson British, Bles Sings">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Barangay Councilor</label>
                    <input style="color: #000000;" type="text" class="form-control" name="barangay_councilor">
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-md-4">
                    <label class="form-label">PTA Observer</label>
                    <input style="color: #000000;" type="text" class="form-control" name="pta_observer">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Date Received</label>
                    <input style="color: #000000;" type="date" class="form-control" name="date_received">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Property Custodian</label>
                    <input style="color: #000000;" type="text" class="form-control" name="property_custodian">
                  </div>
                </div>

                <div class="text-end mt-3">
                  <button type="submit" class="btn btn-primary">Submit</button>
                </div>

              </div>
            </form>
          </div>

        </div>
      </div>
    </div>

    <?php require_once('partials/_mainfooter.php'); ?>
  </div>
</div>

<?php require_once('partials/_scripts.php'); ?>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const qtyInput = document.querySelector('[name="quantity"]');
    const priceInput = document.querySelector('[name="unit_price"]');
    const totalInput = document.querySelector('[name="total_price"]');

    function updateTotal() {
      const qty = parseFloat(qtyInput.value) || 0;
      const price = parseFloat(priceInput.value) || 0;
      totalInput.value = (qty * price).toFixed(2);
    }

    if (qtyInput && priceInput && totalInput) {
      qtyInput.addEventListener("input", updateTotal);
      priceInput.addEventListener("input", updateTotal);
    }
  });
</script>
</body>
</html>
