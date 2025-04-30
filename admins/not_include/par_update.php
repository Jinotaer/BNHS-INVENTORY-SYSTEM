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
  $descriptions = sanitize($_POST['descriptions']);
  $property_number = sanitize($_POST['property_number']);
  $date_acquired = sanitize($_POST['date_acquired']);
  $unit_cost = (float) $_POST['unit_cost'];
  $total_price = $quantity * $unit_cost;
  $end_user_name = sanitize($_POST['end_user_name']);
  $receiver_position = sanitize($_POST['receiver_position']);
  $receiver_date = sanitize($_POST['receiver_date']);
  $custodian_name = sanitize($_POST['custodian_name']);
  $custodian_position = sanitize($_POST['custodian_position']);
  $custodian_date = sanitize($_POST['custodian_date']);
  $update = $_GET['update'];

  $stmt = $mysqli->prepare("UPDATE property_acknowledgment_receipt SET 
  entity_name = ?, fund_cluster = ?, par_no = ?, quantity = ?, unit = ?, descriptions = ?, property_number = ?, date_acquired = ?, unit_cost = ?, total_amount = ?, end_user_name = ?, receiver_position = ?, receiver_date = ?, custodian_name = ?, custodian_position = ?, custodian_date = ?
  WHERE id = ?");

  if ($stmt === false) {
    die("MySQL prepare failed: " . $mysqli->error);
  }

  $stmt->bind_param(
    "sssisssssdsssssss",
    $entity_name,
    $fund_cluster,
    $par_no,
    $quantity,
    $unit,
    $descriptions,
    $property_number,
    $date_acquired,
    $unit_cost,
    $total_amount,
    $end_user_name,
    $receiver_position,
    $receiver_date,
    $custodian_name,
    $custodian_position,
    $custodian_date,
    $update
  );

  if ($stmt->execute()) {
    $success = "Property Acknowledgment Receipt Update Successfully";
    header("refresh:1; url=display_par.php");
  } else {
    $err = "Error: " . $stmt->error;
    header("refresh:1; url=display_par.php");
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
    $ret = "SELECT * FROM  property_acknowledgment_receipt WHERE id = '$update' ";
    $stmt = $mysqli->prepare($ret);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($par = $res->fetch_object()) {
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

              <div class="card-body ">
                <form method="POST" role="form" class="border border-light p-4 rounded">
                  <div class="container mt-4">
                    <h2 class="text-center mb-4 text-uppercase"> Purchase Acceptance Report</h2>
                    <!-- Entity Info -->
                    <div class="row mt-3 mb-3">
                      <div class="col-md-4">
                        <label>Entity Name</label>
                        <input style="color: #000000;" type="text" class="form-control"  value="<?php echo $par->entity_name; ?>" name="entity_name" required>
                      </div>
                      <div class="col-md-4">
                        <label>Fund Cluster</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $par->fund_cluster; ?>" name="fund_cluster" required>
                      </div>
                      <div class="col-md-4">
                        <label>PAR No.</label>
                        <input style="color: #000000;" type="text" class="form-control"  value="<?php echo $par->par_no; ?>" name="par_no" required>
                      </div>
                    </div>

                    <!-- Item Info -->
                    <div class="row mb-3">
                      <div class="col-md-2">
                        <label>Quantity</label>
                        <input style="color: #000000;" type="number" class="form-control"  value="<?php echo $par->quantity; ?>" name="quantity">
                      </div>
                      <div class="col-md-2">
                        <label>Unit</label>
                        <input style="color: #000000;" type="text" class="form-control"  value="<?php echo $par->unit; ?>" name="unit">
                      </div>
                      <div class="col-md-4">
                        <label>Description</label>
                        <input style="color: #000000;" type="text" class="form-control"  value="<?php echo $par->descriptions; ?>" name="descriptions">
                      </div>
                      <div class="col-md-4">
                        <label>Property Number</label>
                        <input style="color: #000000;" type="text" class="form-control"  value="<?php echo $par->property_number; ?>" name="property_number">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label>Date Acquired</label>
                        <input style="color: #000000;" type="date" class="form-control"  value="<?php echo $par->date_acquired; ?>" name="date_acquired">
                      </div>
                      <div class="col-md-4">
                        <label>Unit Cost</label>
                        <input style="color: #000000;" type="text" class="form-control"  value="<?php echo $par->unit_cost; ?>" name="unit_cost">
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Total Amount</label>
                        <input style="color: #000000; background-color: white;" type="text" class="form-control"
                           value="<?php echo $par->total_amount; ?>" name="total_amount" readonly>
                      </div>
                    </div>

                    <!-- Receiver Section -->
                    <div class="sub-section receiver-section">Receiver</div>
                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label>End User Name</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $par->end_user_name; ?>"  name="end_user_name">
                      </div>
                      <div class="col-md-4">
                        <label>Position/Office</label>
                        <input style="color: #000000;" type="text" class="form-control"  value="<?php echo $par->receiver_position; ?>" name="receiver_position">
                      </div>
                      <div class="col-md-4">
                        <label>Date</label>
                        <input style="color: #000000;" type="date" class="form-control"  value="<?php echo $par->receiver_date; ?>" name="receiver_date">
                      </div>
                    </div>

                    <!-- Issue Section -->
                    <div class="sub-section issue-section">Issue</div>
                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label>Property Custodian  Name</label>
                        <input style="color: #000000;" type="text" class="form-control"  value="<?php echo $par->custodian_name; ?>" name="custodian_name">
                      </div>
                      <div class="col-md-4">
                        <label>Position/Office</label>
                        <input style="color: #000000;" type="text" class="form-control"  value="<?php echo $par->custodian_position; ?>" name="custodian_position">
                      </div>
                      <div class="col-md-4">
                        <label>Date</label>
                        <input style="color: #000000;" type="date" class="form-control"  value="<?php echo $par->custodian_date; ?>" name="custodian_date">
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