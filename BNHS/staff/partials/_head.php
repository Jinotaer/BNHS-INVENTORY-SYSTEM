<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Bukidnon National High School Inventory System</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/brand/bnhs.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/brand/bnhs.png">
  

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" />

    <!-- Argon CSS -->
    <link type="text/css" href="assets/css/argon.css?v=1.0.0" rel="stylesheet">
    <script src="assets/js/swal.js"></script>
 <!-- Custom CSS -->
 <link type="text/css" href="assets/css/custom.css" rel="stylesheet">
    <!-- bootstrap -->
    <!-- Bootstrap JS Bundle with Popper (for Bootstrap 5) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/da    taTables.bootstrap4.min.css">
  
<!--Load Swal-->
    <?php if (isset($success)) { ?>
        <!--This code for injecting success alert-->
        <script>
            setTimeout(function() {
                    swal("Success", "<?php echo $success; ?>", "success");
                 },
                100);
        </script>

    <?php } ?>
    <?php if (isset($err)) { ?>
        <!--This code for injecting error alert-->
        <script script>
            setTimeout(function() {
                    swal("Failed", "<?php echo $err; ?>", "error");
                },
                100);
        </script>

    <?php } ?>
    <?php if (isset($info)) { ?>
        <!--This code for injecting info alert-->
        <script>
            setTimeout(function() {
                    swal("Success", "<?php echo $info; ?>", "info");
                },
                100);
        </script>

    <?php } ?>
    <!-- logout alert -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const logoutBtn = document.getElementById('logoutBtn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function(e) {
                    e.preventDefault(); // Prevent the default link behavior
                    swal({
                        title: "Are you sure?",
                         text: "Do you wish to logout?",
                        icon: "warning",
                        buttons: true,
                        primaryColor: "#0056b3",
                    }).then((willLogout) => {
                        if (willLogout) {
                            // Redirect to the logout page
                            window.location.href = logoutBtn.getAttribute('href');
                        }
                    });
                });
            }
        });
    </script>
    <script>
        function getCustomer(val) {
            $.ajax({

                type: "POST",
                url: "customer_ajax.php",
                data: 'custName=' + val,
                success: function(data) {
                    //alert(data);
                    $('#customerID').val(data);
                }
            });

        }
    </script>

    <style>
        /* Custom styles for sidebar */
        /* Hover effect for sidebar links */
        .navbar-nav .nav-link {
            display: flex;
            color: var(--color-info-dark);
            gap: 1rem;
            align-items: center;
            position: relative;
            /* height: 3.7rem; */
        }

        .navbar-nav .nav-link:hover {
            background-color: #f8f9fa;
            color: #0056b3;

        }

        /* Active effect for sidebar links */
        .navbar-nav .nav-link.active {
            background-color: #0056b3;
            color: #ffffff;
            font-weight: bold;

        }

        .nav-logo {
            object-fit: cover;
            object-position: center;
            height: 100px;
        }

        .nav-logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        @media (max-width: 768px) {
            .nav-logo {
                height: 50px;
            }
        }

        /* custom */
    </style>
</head>