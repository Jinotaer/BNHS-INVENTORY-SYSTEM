<?php
session_start();
include('config/config.php');
// include('config/checklogin.php');
// check_login();
//Delete Staff
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $adn = "DELETE FROM bnhs_staff WHERE staff_id = ?";
  $stmt = $mysqli->prepare($adn);
  $stmt->bind_param('s', $id);
  $stmt->execute();
  $stmt->close();
  if ($stmt) {
    $success = "Deleted" && header("refresh:1; url=user_management.php");
  } else {
    $err = "Try Again Later";
  }
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
                aria-expanded="false" style="width: 130px; height: 45px; border-color: none;">
                ADD RIS
              </button>
              <ul class="dropdown-menu">
                <li><button class="dropdown-item" type="button">ADD ICS</button></li>
                <li><button class="dropdown-item" type="button">ADD PAR</button></li>
              </ul>
            </div>

            <div class="card-body ">
              <form method="POST" action="save_building_info.php" class="border border-light p-4 rounded">
                <div class="row mb-3">
                  <div class="col-md-3">
                    <div class="form-group " style="margin: 0px;">
                    <label class="form-label">Article</label>
                      <select class="form-select " name="article[]" required style="width: 100%; padding: 10px 12px; border-radius: 5px; border: 1px solid#cad1d7;">
                        <option value="Building">Building</option>
                        <option value="Furniture and Fixtures">Furniture and Fixtures</option>
                        <option value="Machinery and Equipment">Machinery and Equipment</option>
                        <option value="Transportation Equipment">Transportation Equipment</option>
                        <option value="Office Supplies">Office Supplies</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-8">
                    <label class="form-label">Description</label>
                    <input type="text" class="form-control" name="description[]"
                      value="Provincial School Board Building 1 (Junior High School Computer Laboratory)" required style="color: #000000;">
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-md-3">
                    <label class="form-label">Property Number</label>
                    <input type="text" class="form-control" name="property_number[]" value="CBNHS-10-B1" required style="color: #000000;">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Unit of Measure</label>
                    <input type="text" class="form-control" name="unit_measure[]" value="1" required style="color: #000000;">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Unit Value</label>
                    <input type="number" class="form-control" name="unit_value[]" value="180000" step="0.01" required style="color: #000000;">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Qty (Property Card)</label>
                    <input type="number" class="form-control" name="qty_property_card[]" value="1" required style="color: #000000;">
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-md-3">
                    <label class="form-label">Qty (Physical Count)</label>
                    <input type="number" class="form-control" name="qty_physical_count[]" value="1" required style="color: #000000;">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Shortage Qty</label>
                    <input type="number" class="form-control" name="shortage_qty[]" value="0" style="color: #000000;">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Shortage Value</label>
                    <input type="number" class="form-control" name="shortage_value[]" value="0" step="0.01" style="color: #000000;">
                  </div>
                  <!-- Remarks -->
                  <div class="col-md-12">
                    <label class="form-label">Remarks</label>
                    <textarea class="form-control" name="remarks[]" rows="2"
                      required>Operational and needs minor repair</textarea>
                  </div>
                </div>

                <div class="text-end">
                  <button type="submit" class="btn btn-primary px-4">Save</button>
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