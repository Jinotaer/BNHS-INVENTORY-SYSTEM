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
  $ics_no = sanitize($_POST['ics_no']);
  $quantity = (int) sanitize($_POST['quantity']);
  $unit = sanitize($_POST['unit']);
  $unit_cost = (float) sanitize($_POST['unit_cost']);
  $total_amount = $quantity * $unit_cost;
  $item_description = sanitize($_POST['item_description']);
  $inventory_item_no = sanitize($_POST['inventory_item_no']);
  $estimated_life = sanitize($_POST['estimated_life']);
  $end_user_name = sanitize($_POST['end_user_name']);
  $end_user_position = sanitize($_POST['end_user_position']);
  $date_received_user = sanitize($_POST['date_received_user']);
  $custodian_name = $_POST['custodian_name'];
  $custodian_position = $_POST['custodian_position'];
  $date_received_custodian = sanitize($_POST['date_received_custodian']);


  $stmt = $mysqli->prepare("INSERT INTO inventory_custodian_slip (
    entity_name, fund_cluster, ics_no, quantity, unit, unit_cost, total_amount, item_description, inventory_item_no, estimated_life, end_user_name, end_user_position,date_received_user, custodian_name, custodian_position, date_received_custodian
  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

  if ($stmt === false) {
    die("MySQL prepare failed: " . $mysqli->error);
  }

  $stmt->bind_param(
    "sssisdssssssssss",
    $entity_name,
    $fund_cluster,
    $ics_no,
    $quantity,
    $unit,
    $unit_cost,
    $total_amount,
    $item_description,
    $inventory_item_no,
    $estimated_life,
    $end_user_name,
    $end_user_position,
    $date_received_user,
    $custodian_name,
    $custodian_position,
    $date_received_custodian
  );

  if ($stmt->execute()) {
    $success = "Inventory Custodian Slip Created Successfully";
    header("refresh:1; url=ics.php");
  } else {
    $err = "Error: " . $stmt->error;
    header("refresh:1; url=ics.php");
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
                Inventory Custodian Slip
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="inventory_management.php">Inspection and Acceptance Report</a></li>
                <li><a class="dropdown-item" href="ris.php">Requisition and Issue Slip</a></li>
                <li><a class="dropdown-item" href="par.php">Purchase Acceptance Report</a></li>
              </ul>
            </div>


            <div class="card-body ">
              <form method="POST" role="form" class="border border-light p-4 rounded">
                <div class="container mt-4">
                  <h2 class="text-center mb-4 text-uppercase">Inventory Custodian Slip</h2>

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
                      <label class="form-label">ICS No.</label>
                      <input style="color: #000000;" type="text" class="form-control" name="ics_no" required>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-2">
                      <label class="form-label">Quantity</label>
                      <input style="color: #000000;" type="number" class="form-control" name="quantity" required>
                    </div>
                    <div class="col-md-2">
                      <label class="form-label">Unit</label>
                      <input style="color: #000000;" type="text" class="form-control" name="unit" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Unit Cost</label>
                      <input style="color: #000000;" type="text" class="form-control" name="unit_cost" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Total Amount</label>
                      <input style="color: #000000; background-color: white;" type="text" class="form-control"
                        name="total_amount" readonly>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-6">
                      <label class="form-label">Item Description</label>
                      <input style="color: #000000;" type="text" class="form-control" name="item_description" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Inventory Item No.</label>
                      <input style="color: #000000;" type="text" class="form-control" name="inventory_item_no" required>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">Estimated Useful Life</label>
                      <input style="color: #000000;" type="text" class="form-control" name="estimated_life" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">End User Name</label>
                      <input style="color: #000000;" type="text" class="form-control" name="end_user_name" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Position / Office</label>
                      <input style="color: #000000;" type="text" class="form-control" name="end_user_position" required>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">Date Received (by End User)</label>
                      <input style="color: #000000;" type="date" class="form-control" name="date_received_user"
                        required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Property Custodian Name</label>
                      <input style="color: #000000;" type="text" class="form-control" name="custodian_name" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Position / Office (Custodian)</label>
                      <input style="color: #000000;" type="text" class="form-control" name="custodian_position"
                        required>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">Date Received (by Custodian)</label>
                      <input style="color: #000000;" type="date" class="form-control" name="date_received_custodian"
                        required>
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
  require_once('partials/_scripts.php'); ?>

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
</body>

</html>