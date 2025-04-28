<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
if ($_SERVER['REQUEST_METHOD'] == "POST") {
  // Sanitize and collect form data
  function sanitize($data)
  {
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
  $update = $_GET['update'];

  $stmt = $mysqli->prepare("UPDATE inspection_acceptance_reports SET 
  entity_name = ?, fund_cluster = ?, supplier = ?, po_no_date = ?, req_office = ?, responsibility_center = ?,
  iar_no = ?, iar_date = ?, invoice_no_date = ?, stock_no = ?, remarks = ?, item_description = ?, unit = ?,
  quantity = ?, unit_price = ?, total_price = ?, receiver_name = ?, teacher_id = ?, position = ?, date_inspected = ?,
  inspectors = ?, barangay_councilor = ?, pta_observer = ?, date_received = ?, property_custodian = ?
  WHERE id = ?");

  if ($stmt === false) {
    die("MySQL prepare failed: " . $mysqli->error);
  }

  $stmt->bind_param(
    "sssssssssssssidsssssssssss",
    $entity_name,
    $fund_cluster,
    $supplier,
    $po_no_date,
    $req_office,
    $responsibility_center,
    $iar_no,
    $iar_date,
    $invoice_no_date,
    $stock_no,
    $remarks,
    $item_description,
    $unit,
    $quantity,
    $unit_price,
    $total_price,
    $receiver_name,
    $teacher_id,
    $position,
    $date_inspected,
    $inspectors,
    $barangay_councilor,
    $pta_observer,
    $date_received,
    $property_custodian,
    $update
  );

  if ($stmt->execute()) {
    $success = "Inspection and Acceptance Report Update Successfully";
    header("refresh:1; url=display_iar.php");
  } else {
    $err = "Error: " . $stmt->error;
    header("refresh:1; url=display_iar.php");
  }

  $stmt->close();
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
    $update = $_GET['update'];
    $ret = "SELECT * FROM  inspection_acceptance_reports WHERE id = '$update' ";
    $stmt = $mysqli->prepare($ret);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($iar = $res->fetch_object()) {
      ?>
      <!-- Header -->
      <div style="background-image: url(assets/img/theme/restro00.jpg); background-size: cover;"
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
              <div class="card-body">
                <form method="POST" class="border border-light p-4 rounded">
                  <div class="container mt-4">
                    <h2 class="text-center mb-4 text-uppercase">Inspection and Acceptance Report</h2>

                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label class="form-label">Entity Name</label>
                        <input style="color: #000000;" type="text" class="form-control"
                          value="<?php echo $iar->entity_name; ?>" name="entity_name" required>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Fund Cluster</label>
                        <input style="color: #000000;" type="text" class="form-control"
                          value="<?php echo $iar->fund_cluster; ?>" name="fund_cluster" required>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Supplier</label>
                        <input style="color: #000000;" type="text" class="form-control"
                          value="<?php echo $iar->supplier; ?>" name="supplier" required>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label class="form-label">PO No. / Date</label>
                        <input style="color: #000000;" type="text" class="form-control"
                          value="<?php echo $iar->po_no_date; ?>" name="po_no_date" required>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Requisitioning Office/Dept.</label>
                        <input style="color: #000000;" type="text" class="form-control"
                          value="<?php echo $iar->req_office; ?>" name="req_office" required>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Responsibility Center</label>
                        <input style="color: #000000;" type="text" class="form-control"
                          value="<?php echo $iar->responsibility_center; ?>" name="responsibility_center">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label class="form-label">IAR No.</label>
                        <input style="color: #000000;" type="text" class="form-control"
                          value="<?php echo $iar->iar_no; ?>" name="iar_no">
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">IAR Date</label>
                        <input style="color: #000000;" type="date" class="form-control"
                          value="<?php echo $iar->iar_date; ?>" name="iar_date">
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Invoice No. / Date</label>
                        <input style="color: #000000;" type="text" class="form-control"
                          value="<?php echo $iar->invoice_no_date; ?>" name="invoice_no_date">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-md-3">
                        <label class="form-label">Stock / Property No.</label>
                        <input style="color: #000000;" type="text" class="form-control"
                          value="<?php echo $iar->stock_no; ?>" name="stock_no">
                      </div>
                      <div class="col-md-3">
                        <label class="form-label">Remarks</label>
                        <input style="color: #000000;" type="text" class="form-control"
                          value="<?php echo $iar->remarks; ?>" name="remarks">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Item Description</label>
                        <input style="color: #000000;" type="text" class="form-control"
                          value="<?php echo $iar->item_description; ?>" name="item_description">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-md-2">
                        <label class="form-label">Unit</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $iar->unit; ?>"
                          name="unit">
                      </div>
                      <div class="col-md-2">
                        <label class="form-label">Qty</label>
                        <input style="color: #000000;" type="number" class="form-control"
                          value="<?php echo $iar->quantity; ?>" name="quantity">
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Unit Price</label>
                        <input style="color: #000000;" type="number" step="0.01" class="form-control"
                          value="<?php echo $iar->unit_price; ?>" name="unit_price">
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Total Price</label>
                        <input style="color: #000000; background-color: white;" type="text" class="form-control"
                          name="total_price" value="<?php echo $iar->total_price; ?>" readonly>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label class="form-label">Receiver Name</label>
                        <input style="color: #000000;" type="text" class="form-control"
                          value="<?php echo $iar->receiver_name; ?>" name="receiver_name">
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Teacher's ID</label>
                        <input style="color: #000000;" type="text" class="form-control"
                          value="<?php echo $iar->teacher_id; ?>" name="teacher_id">
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Position</label>
                        <input style="color: #000000;" type="text" class="form-control"
                          value="<?php echo $iar->position; ?>" name="position">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label class="form-label">Date Inspected</label>
                        <input style="color: #000000;" type="date" class="form-control"
                          value="<?php echo $iar->date_inspected; ?>" name="date_inspected">
                      </div>
                      <div class="col-md-5">
                        <label class="form-label">Inspection Team (comma separated)</label>
                        <input style="color: #000000;" type="text" class="form-control" name="inspectors"
                          value="<?php echo $iar->inspectors; ?>">
                      </div>
                      <div class="col-md-3">
                        <label class="form-label">Barangay Councilor</label>
                        <input style="color: #000000;" type="text" class="form-control"
                          value="<?php echo $iar->barangay_councilor; ?>" name="barangay_councilor">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label class="form-label">PTA Observer</label>
                        <input style="color: #000000;" type="text" class="form-control"
                          value="<?php echo $iar->pta_observer; ?>" name="pta_observer">
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Date Received</label>
                        <input style="color: #000000;" type="date" class="form-control"
                          value="<?php echo $iar->date_received; ?>" name="date_received">
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Property Custodian</label>
                        <input style="color: #000000;" type="text" class="form-control"
                          value="<?php echo $iar->property_custodian; ?>" name="property_custodian">
                      </div>
                    </div>

                    <div class="text-end mt-3">
                      <button type="submit" class="btn btn-primary" name="updateiar" value="Update iar">Submit</button>
                    </div>

                  </div>
                </form>
              </div>

            </div>
          </div>
        </div>
        <!-- Footer -->
        <?php
        require_once('partials/_mainfooter.php');
    }
    ?>
    </div>
  </div>
  <!-- Argon Scripts -->
  <?php
  require_once('partials/_scripts.php');
  ?>
</body>

</html>