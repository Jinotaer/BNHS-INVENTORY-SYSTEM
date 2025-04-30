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
  $update = $_GET['update'];

  $stmt = $mysqli->prepare("UPDATE inventory_custodian_slip SET 
  entity_name = ?, fund_cluster = ?, ics_no = ?, quantity = ?, unit = ?, unit_cost = ?, total_amount = ?, item_description = ?, inventory_item_no = ?, estimated_life = ?, end_user_name = ?, end_user_position = ?, date_received_user = ?, custodian_name = ?, custodian_position = ?, date_received_custodian = ? WHERE id = ?");

  if ($stmt === false) {
    die("MySQL prepare failed: " . $mysqli->error);
  }

  $stmt->bind_param(
    "sssisdsssssssssss",
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
    $date_received_custodian,
    $update
  );

  if ($stmt->execute()) {
    $success = "Inventory Custodian Slip Update Successfully";
    header("refresh:1; url=display_ics.php");
  } else {
    $err = "Error: " . $stmt->error;
    header("refresh:1; url=display_ics.php");
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
    $ret = "SELECT * FROM  inventory_custodian_slip WHERE id = '$update' ";
    $stmt = $mysqli->prepare($ret);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($ics = $res->fetch_object()) {
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
                    <h2 class="text-center mb-4 text-uppercase">Inventory Custodian Slip</h2>

                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label class="form-label">Entity Name</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $ics->entity_name; ?>" name="entity_name" required>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Fund Cluster</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $ics->fund_cluster; ?>" name="fund_cluster" required>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">ICS No.</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $ics->ics_no; ?>" name="ics_no" required>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-md-2">
                        <label class="form-label">Quantity</label>
                        <input style="color: #000000;" type="number" class="form-control"  value="<?php echo $ics->quantity; ?>"name="quantity" required>
                      </div>
                      <div class="col-md-2">
                        <label class="form-label">Unit</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $ics->unit; ?>" name="unit" required>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Unit Cost</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $ics->unit_cost; ?>" name="unit_cost" required>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Total Amount</label>
                        <input style="color: #000000; background-color: white;" type="text" class="form-control"
                          name="total_amount" value="<?php echo $ics->total_amount; ?>" readonly>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-md-6">
                        <label class="form-label">Item Description</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $ics->item_description; ?>" name="item_description" required>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Inventory Item No.</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $ics->inventory_item_no; ?>" name="inventory_item_no" required>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label class="form-label">Estimated Useful Life</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $ics->estimated_life; ?>" name="estimated_life" required>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">End User Name</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $ics->end_user_name; ?>" name="end_user_name" required>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Position / Office</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $ics->end_user_position; ?>" name="end_user_position" required>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label class="form-label">Date Received (by End User)</label>
                        <input style="color: #000000;" type="date" class="form-control" value="<?php echo $ics->date_received_user; ?>" name="date_received_user"
                          required>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Property Custodian Name</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $ics->custodian_name; ?>" name="custodian_name" required>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Position / Office (Custodian)</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $ics->custodian_position; ?>" name="custodian_position"
                          required>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label class="form-label">Date Received (by Custodian)</label>
                        <input style="color: #000000;" type="date" class="form-control" value="<?php echo $ics->date_received_custodian; ?>" name="date_received_custodian"
                          required>
                      </div>
                    </div>

                    <div class="text-end mt-3">
                      <button type="submit" class="btn btn-primary" name="updateics" value="Update ics">Submit</button>
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