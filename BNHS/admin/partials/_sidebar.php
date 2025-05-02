<?php
$admin_id = $_SESSION['admin_id'];
$login_id = $_SESSION['admin_id'];
$ret = "SELECT * FROM   bnhs_admin  WHERE admin_id = '$admin_id'";
$stmt = $mysqli->prepare($ret);
$stmt->execute();
$res = $stmt->get_result();
while ($admin = $res->fetch_object()) {

  ?>
  <nav class="navbar navbar-vertical fixed-left navbar-expand-md navbar-light" id="sidenav-main" style="background: linear-gradient(to bottom right, #d9f0ff, #ffffff);">
  <style>
      .nav-link[data-toggle="collapse"]::after {
        content: "▼" !important;
        font-size: 10px;
        color: #5f73e4;
        margin-left: 6px;
      }
      .nav-link[data-toggle="collapse"][aria-expanded="true"]::after {
        content: "▼" !important;
      }
    </style>
  <div class="container-fluid">
      <!-- Toggler -->
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#sidenav-collapse-main"
        aria-controls="sidenav-main" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <!-- Brand -->
      <a class="pt-1 nav-logo-container" href="dashboard.php">
        <img src="assets/img/theme/logos.png" class="nav-logo" alt="...">
      </a>
      <!-- User -->
      <ul class="nav align-items-center d-md-none">
        <li class="nav-item dropdown">
          <a class="nav-link nav-link-icon" href="#" role="button" data-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false">
            <i class="ni ni-bell-55"></i>
          </a>
          <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-right" aria-labelledby="navbar-default_dropdown_1">
          </div>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <div class="media align-items-center">
              <span class="avatar avatar-sm rounded-circle">
                <img alt="Image placeholder" src="assets/img/theme/user-a-min.png">
              </span>
            </div>
          </a>
          <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-right">
            <div class=" dropdown-header noti-title">
              <h6 class="text-overflow m-0">Welcome!</h6>
            </div>
            <a href="change_profile.php" class="dropdown-item">
              <i class="ni ni-single-02"></i>
              <span>My profile</span>
            </a>
            <div class="dropdown-divider"></div>
            <a href="logout.php" class="dropdown-item">
              <i class="ni ni-user-run"></i>
              <span>Logout</span>
            </a>
          </div>
        </li>
      </ul>
      <!-- Collapse -->
      <div class="collapse navbar-collapse" id="sidenav-collapse-main">
        <!-- Collapse header -->
        <div class="navbar-collapse-header d-md-none">
          <div class="row">
            <div class="col-6 collapse-brand">
              <a href="dashboard.php">
                <img src="assets/img/brand/bnhs.png "class="nav-logo-collapse" alt="..." >
              </a>
            </div>
          </div>
        </div>
        <!-- Form -->
        <!-- <form class="mt-4 mb-3 d-md-none">
          <div class="input-group input-group-rounded input-group-merge">
            <input type="search" class="form-control form-control-rounded form-control-prepended" placeholder="Search"
              aria-label="Search">
            <div class="input-group-prepend">
              <div class="input-group-text">
                <span class="fa fa-search"></span>
              </div>
            </div>
          </div>
        </form> -->
        <!-- Navigation -->
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" href="dashboard.php">
              <i><span class="material-icons-sharp text-primary">dashboard</span></i> Dashboard
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="user_management.php">
              <i><span class="material-icons-sharp text-primary">group</span></i> User Management
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="track_inventory.php">
              <i><span class="material-icons-sharp text-primary">plagiarism</span></i> Track Inventory
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link " href="#reportsSubmenu" data-toggle="collapse" aria-expanded="false">
              <i><span class="material-icons-sharp text-primary">description</span></i> Reports
            </a>
            <ul class="collapse list-unstyled ml-3" id="reportsSubmenu">
            <li class="nav-item">
                <a class="nav-link" href="display_iar.php" style="padding: 3px 24px;">
                  <i><span class="material-icons-sharp text-primary"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f73e4"><path d="M160-120q-33 0-56.5-23.5T80-200v-560q0-33 23.5-56.5T160-840h640q33 0 56.5 23.5T880-760v560q0 33-23.5 56.5T800-120H160Zm0-80h640v-560H160v560Zm40-80h200v-80H200v80Zm382-80 198-198-57-57-141 142-57-57-56 57 113 113Zm-382-80h200v-80H200v80Zm0-160h200v-80H200v80Zm-40 400v-560 560Z"/></svg></span></i> IAR
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="display_ics.php" style="padding: 3px 24px;">
                  <i><span class="material-icons-sharp text-primary"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f73e4"><path d="M200-80q-33 0-56.5-23.5T120-160v-451q-18-11-29-28.5T80-680v-120q0-33 23.5-56.5T160-880h640q33 0 56.5 23.5T880-800v120q0 23-11 40.5T840-611v451q0 33-23.5 56.5T760-80H200Zm0-520v440h560v-440H200Zm-40-80h640v-120H160v120Zm200 280h240v-80H360v80Zm120 20Z"/></svg></span></i> ICS
                </a>
              </li>
              <li class="nav-item" >
                <a class="nav-link" href="display_ris.php" style="padding: 3px 24px;">
                  <i><span class="material-icons-sharp text-primary"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f73e4"><path d="M240-80q-50 0-85-35t-35-85v-120h120v-560l60 60 60-60 60 60 60-60 60 60 60-60 60 60 60-60 60 60 60-60v680q0 50-35 85t-85 35H240Zm480-80q17 0 28.5-11.5T760-200v-560H320v440h360v120q0 17 11.5 28.5T720-160ZM360-600v-80h240v80H360Zm0 120v-80h240v80H360Zm320-120q-17 0-28.5-11.5T640-640q0-17 11.5-28.5T680-680q17 0 28.5 11.5T720-640q0 17-11.5 28.5T680-600Zm0 120q-17 0-28.5-11.5T640-520q0-17 11.5-28.5T680-560q17 0 28.5 11.5T720-520q0 17-11.5 28.5T680-480ZM240-160h360v-80H200v40q0 17 11.5 28.5T240-160Zm-40 0v-80 80Z"/></svg></span></i> RIS
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="display_par.php" style="padding: 3px 24px;">
                  <i><span class="material-icons-sharp text-primary"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f73e4"><path d="M440-200h80v-40h40q17 0 28.5-11.5T600-280v-120q0-17-11.5-28.5T560-440H440v-40h160v-80h-80v-40h-80v40h-40q-17 0-28.5 11.5T360-520v120q0 17 11.5 28.5T400-360h120v40H360v80h80v40ZM240-80q-33 0-56.5-23.5T160-160v-640q0-33 23.5-56.5T240-880h320l240 240v480q0 33-23.5 56.5T720-80H240Zm280-560v-160H240v640h480v-480H520ZM240-800v160-160 640-640Z"/></svg></span></i> PAR
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#AuditsSubmenu" data-toggle="collapse" aria-expanded="false">
              <i><span class="material-icons-sharp text-primary">analytics</span></i> Audits
            </a>
            <ul class="collapse list-unstyled ml-3" id="AuditsSubmenu" >
              <li class="nav-item">
                <a class="nav-link" href="rpcppe.php" style="padding: 3px 24px;">
                  <i><span class="material-icons-sharp text-primary"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f73e4"><path d="M280-280h280v-80H280v80Zm0-160h400v-80H280v80Zm0-160h400v-80H280v80Zm-80 480q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Zm0-560v560-560Z"/></svg></span></i> RPCPPE
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="rpcsp.php" style="padding: 3px 24px;">
                  <i><span class="material-icons-sharp text-primary"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f73e4"><path d="M620-163 450-333l56-56 114 114 226-226 56 56-282 282Zm220-397h-80v-200h-80v120H280v-120h-80v560h240v80H200q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h167q11-35 43-57.5t70-22.5q40 0 71.5 22.5T594-840h166q33 0 56.5 23.5T840-760v200ZM480-760q17 0 28.5-11.5T520-800q0-17-11.5-28.5T480-840q-17 0-28.5 11.5T440-800q0 17 11.5 28.5T480-760Z"/></svg></span></i> RPCSP
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="change_profile.php">
              <i><span class="material-icons-sharp text-primary">settings</span></i> Settings
            </a>
          </li>
        </ul>
        <div class="sidebar-footer mt-auto">
          <hr class="my-3">
          <ul class="navbar-nav mb-md-3">
            <li class="nav-item">
              <a class="nav-link" href="logout.php" id="logoutBtn">
                <i><span class="material-icons-sharp text-primary">logout</span></i> Log Out
              </a>
            </li>
          </ul>
        </div>

      </div>
    </div>
  </nav>

<?php } ?>