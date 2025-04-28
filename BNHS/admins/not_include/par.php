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
  $par_no = sanitize($_POST['par_no']);
  $quantity = (int) $_POST['quantity'];
  $unit = sanitize($_POST['unit']);
  $item_description = sanitize($_POST['item_description']);
  $property_number = sanitize($_POST['property_number']);
  $date_acquired = sanitize($_POST['date_acquired']);
  $unit_cost = (float) $_POST['unit_cost'];
  $total_amount = $quantity * $unit_cost;
  $end_user_name = sanitize($_POST['end_user_name']);
  $receiver_position = sanitize($_POST['receiver_position']);
  $receiver_date = sanitize($_POST['receiver_date']);
  $custodian_name = sanitize($_POST['custodian_name']);
  $custodian_position = sanitize($_POST['custodian_position']);
  $custodian_date = sanitize($_POST['custodian_date']);

  $stmt = $mysqli->prepare("INSERT INTO property_acknowledgment_receipt (
    entity_name, fund_cluster, par_no, quantity, unit, item_description, property_number, date_acquired, unit_cost, total_amount, end_user_name, receiver_position, receiver_date, custodian_name, custodian_position, custodian_date

  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

  if ($stmt === false) {
    die("MySQL prepare failed: " . $mysqli->error);
  }

  $stmt->bind_param(
    "sssisssssdssssss",
    $entity_name,
    $fund_cluster,
    $par_no,
    $quantity,
    $unit,
    $item_description,
    $property_number,
    $date_acquired,
    $unit_cost,
    $total_amount,
    $end_user_name,
    $receiver_position,
    $receiver_date,
    $custodian_name,
    $custodian_position,
    $custodian_date
  );

  if ($stmt->execute()) {
    $success = "Property Acknowledgment Receipt Created Successfully";
    header("refresh:1; url=par.php");
  } else {
    $err = "Error: " . $stmt->error;
    header("refresh:1; url=par.php");
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
                Purchase Acceptance Report
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="inventory_management.php">Inspection and Acceptance Report</a></li>
                <li><a class="dropdown-item" href="ris.php">Requisition and Issue Slip</a></li>
                <li><a class="dropdown-item" href="ics.php">Inventory Custodian Slip</a></li>
              </ul>
            </div>


            <div class="card-body ">

              <form method="POST" role="form" class="border border-light p-4 rounded">
                <div class="container mt-4">
                  <h2 class="text-center mb-4 text-uppercase"> Purchase Acceptance Report</h2>
                  <!-- Entity Info -->
                  <div class="row mt-3 mb-3">
                    <div class="col-md-4">
                      <label>Entity Name</label>
                      <input style="color: #000000;" type="text" class="form-control" name="entity_name" required>
                    </div>
                    <div class="col-md-4">
                      <label>Fund Cluster</label>
                      <input style="color: #000000;" type="text" class="form-control" name="fund_cluster" required>
                    </div>
                    <div class="col-md-4">
                      <label>PAR No.</label>
                      <input style="color: #000000;" type="text" class="form-control" name="par_no" required>
                    </div>
                  </div>

                  <!-- Item Info -->
                  <div class="row mb-3">
                    <div class="col-md-2">
                      <label>Quantity</label>
                      <input style="color: #000000;" type="number" class="form-control" name="quantity">
                    </div>
                    <div class="col-md-2">
                      <label>Unit</label>
                      <input style="color: #000000;" type="text" class="form-control" name="unit">
                    </div>
                    <div class="col-md-4">
                      <label>Description</label>
                      <input style="color: #000000;" type="text" class="form-control" name="item_description">
                    </div>
                    <div class="col-md-4">
                      <label>Property Number</label>
                      <input style="color: #000000;" type="text" class="form-control" name="property_number">
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label>Date Acquired</label>
                      <input style="color: #000000;" type="date" class="form-control" name="date_acquired">
                    </div>
                    <div class="col-md-4">
                      <label>Unit Cost</label>
                      <input style="color: #000000;" type="text" class="form-control" name="unit_cost">
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Total Amount</label>
                      <input style="color: #000000; background-color: white;" type="text" class="form-control"
                        name="total_amount" readonly>
                    </div>
                  </div>

                  <!-- Receiver Section -->
                  <div class="sub-section receiver-section">Receiver</div>
                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label>End User Name</label>
                      <input style="color: #000000;" type="text" class="form-control" name="end_user_name">
                    </div>
                    <div class="col-md-4">
                      <label>Position/Office</label>
                      <input style="color: #000000;" type="text" class="form-control" name="receiver_position">
                    </div>
                    <div class="col-md-4">
                      <label>Date</label>
                      <input style="color: #000000;" type="date" class="form-control" name="receiver_date">
                    </div>
                  </div>

                  <!-- Issue Section -->
                  <div class="sub-section issue-section">Issue</div>
                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label>Property Custodian Name</label>
                      <input style="color: #000000;" type="text" class="form-control" name="custodian_name">
                    </div>
                    <div class="col-md-4">
                      <label>Position/Office</label>
                      <input style="color: #000000;" type="text" class="form-control" name="custodian_position">
                    </div>
                    <div class="col-md-4">
                      <label>Date</label>
                      <input style="color: #000000;" type="date" class="form-control" name="custodian_date">
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

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const qtyInput = document.querySelector('[name="quantity"]');
    const priceInput = document.querySelector('[name="unit_cost"]');
    const totalInput = document.querySelector('[name="total_amount"]');

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
</html>