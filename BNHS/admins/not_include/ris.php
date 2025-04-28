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
  $division = sanitize($_POST['division']);
  $office = sanitize($_POST['office']);
  $responsibility_code = sanitize($_POST['responsibility_code']);
  $ris_no = sanitize($_POST['ris_no']);
  $stock_no = sanitize($_POST['stock_no']);
  $unit = sanitize($_POST['unit']);
  $item_description = sanitize($_POST['item_description']);
  $requested_qty = sanitize($_POST['requested_qty']);
  $stock_available = sanitize($_POST['stock_available']);
  $issued_qty = sanitize($_POST['issued_qty']);
  $remarks = sanitize($_POST['remarks']);
  $purpose = sanitize($_POST['purpose']);
  $requested_by_name = sanitize($_POST['requested_by_name']);
  $requested_by_designation =  sanitize($_POST['requested_by_designation']);
  $requested_by_date = sanitize($_POST['requested_by_date']);
  $approved_by_name = sanitize($_POST['approved_by_name']);
  $approved_by_designation = sanitize($_POST['approved_by_designation']);
  $approved_by_date = sanitize($_POST['approved_by_date']);
  $issued_by_name = sanitize($_POST['issued_by_name']);
  $issued_by_designation = sanitize($_POST['issued_by_designation']);
  $issued_by_date = sanitize($_POST['issued_by_date']);
  $received_by_name= sanitize($_POST['received_by_name']);
  $received_by_designation = sanitize($_POST['received_by_designation']);
  $received_by_date = sanitize($_POST['received_by_date']);

  $stmt = $mysqli->prepare("INSERT INTO requisition_and_issue_slips (
   entity_id, division, office, responsibility_code, ris_no, stock_no, unit, item_description, 
   requested_qty, stock_available, issued_qty, remarks, purpose, requested_by_name, 
   requested_by_designation, requested_by_date, approved_by_name, approved_by_designation, 
   approved_by_date, issued_by_name, issued_by_designation, issued_by_date, 
   received_by_name, received_by_designation, received_by_date
  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

  if ($stmt === false) {
    die("MySQL prepare failed: " . $mysqli->error);
  }

  $stmt->bind_param(
    "sssssssssiiissssssssssssss",
    $entity_name, $fund_cluster, $division, $office, $responsibility_code, $ris_no, $stock_no, $unit, $item_description, $requested_qty, $stock_available, $issued_qty, $remarks, $purpose, $requested_by_name, $requested_by_designation, $requested_by_date, $approved_by_name, $approved_by_designation, $approved_by_date, $issued_by_name,$issued_by_designation, $issued_by_date, $received_by_name, $received_by_designation, $received_by_date
  );

  if ($stmt->execute()) {
    $success = "Requisition and Issue Slip Created Successfully";
    header("refresh:1; url=ris.php");
  } else {
    $err = "Error: " . $stmt->error;
    header("refresh:1; url=ris.php");
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
    ?>
    <style>
      /* Custom styles for inventory management tabs */
      .custom-tabs {
        margin-bottom: 15px;
      }

      .custom-tab-link {
        font-weight: bold;
        color: #495057;
        padding: 15px 30px;
        transition: color 0.3s, background-color 0.3s;
      }

      .custom-tab-link:hover {
        color: #0056b3;
        background-color: #f8f9fa;
      }
    </style>
    <!-- Header -->
    <div style="background-image: url(assets/img/theme/bnhsfront.jpg); background-size: cover;"
      class="header pb-8 pt-5 pt-md-8">
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
            <div class="dropdown" style="padding: 20px; margin: 10px;">
              <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                aria-expanded="false" style="width: 300px; height: 45px;">
                Requisition and Issue Slip
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="inventory_management.php">Inspection and Acceptance Report</a></li>
                <li><a class="dropdown-item" href="ics.php">Inventory Custodian Slip</a></li>
                <li><a class="dropdown-item" href="par.php">Purchase Acceptance Report</a></li>
              </ul>
            </div>


            <div class="card-body ">
              <form method="POST" role="form" class="border border-light p-4 rounded">
                <div class="container mt-4">
                  <h2 class="text-center mb-4 text-uppercase">Requisition and Issue Slip</h2>

                  <div class="row mb-3">
                    <div class="col-md-3">
                      <label class="form-label">Entity Name</label>
                      <input style="color: #000000;" type="text" class="form-control" name="entity_name" required>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Fund Cluster</label>
                      <input type="text" class="form-control" name="fund_cluster" required>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Division</label>
                      <input type="text" class="form-control" name="division" required>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Office</label>
                      <input type="text" class="form-control" name="office" required>
                    </div>
                  
                  </div>

                  <div class="row mb-3">
                  <div class="col-md-3">
                      <label class="form-label">Responsibility Center Code</label>
                      <input type="text" class="form-control" name="responsibility_code" required>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">RIS No.</label>
                      <input type="text" class="form-control" name="ris_no" required>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Stock No.</label>
                      <input type="text" class="form-control" name="stock_no" required>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Unit</label>
                      <input type="text" class="form-control" name="unit" required>
                    </div>
                 
                  </div>

                  <div class="row mb-3">
                  <div class="col-md-3">
                      <label class="form-label">Item Description</label>
                      <input type="text" style="color: #000000;" class="form-control" name="item_description" required>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Requested Quantity</label>
                      <input type="number" style="color: #000000;" class="form-control" name="requested_qty" required>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Stock Available (Yes/No)</label>
                      <select style="color: #000000;" class="form-control" name="stock_available" required>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Issued Quantity</label>
                      <input type="number" style="color: #000000;" class="form-control" name="issued_qty" required>
                    </div>
                    
                  </div>

                  <div class="row mb-3">
                  <div class="col-md-3">
                      <label class="form-label">Remarks</label>
                      <input type="text" style="color: #000000;" class="form-control" name="remarks">
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Purpose</label>
                      <input type="text" style="color: #000000;" class="form-control" name="purpose" required>
                    </div>
                  </div>

                  <h5 class="mt-4">Requested By</h5>
                  <div class="row mb-3">
                    <div class="col-md-3">
                      <label class="form-label">Name</label>
                      <input type="text" style="color: #000000;" class="form-control" name="requested_by_name" required>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Designation</label>
                      <input type="text" style="color: #000000;" class="form-control" name="requested_by_designation" required>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Date</label>
                      <input type="date" style="color: #000000;" class="form-control" name="requested_by_date" required>
                    </div>
                  </div>

                  <h5>Approved By</h5>
                  <div class="row mb-3">
                    <div class="col-md-3">
                      <label class="form-label">Name</label>
                      <input type="text" style="color: #000000;" class="form-control" name="approved_by_name" required>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Designation</label>
                      <input type="text" style="color: #000000;" class="form-control" name="approved_by_designation" required>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Date</label>
                      <input type="date" style="color: #000000;" class="form-control" name="approved_by_date" required>
                    </div>
                  </div>

                  <h5>Issued By</h5>
                  <div class="row mb-3">
                    <div class="col-md-3">
                      <label class="form-label">Name</label>
                      <input type="text" style="color: #000000;" class="form-control" name="issued_by_name" required>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Designation</label>
                      <input type="text" style="color: #000000;" class="form-control" name="issued_by_designation" required>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Date</label>
                      <input type="date" style="color: #000000;" class="form-control" name="issued_by_date" required>
                    </div>
                  </div>

                  <h5>Received By</h5>
                  <div class="row mb-3">
                    <div class="col-md-3">
                      <label class="form-label">Name</label>
                      <input type="text" style="color: #000000;" class="form-control" name="received_by_name" required>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Designation</label>
                      <input type="text" style="color: #000000;" class="form-control" name="received_by_designation" required>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Date</label>
                      <input type="date" style="color: #000000;" class="form-control" name="received_by_date" required>
                    </div>
                  </div>

                  <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary">Submit</button>
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
      ?>
    </div>
  </div>
  <!-- Argon Scripts -->
  <?php
  require_once('partials/_scripts.php');
  ?>
</body>

</html>