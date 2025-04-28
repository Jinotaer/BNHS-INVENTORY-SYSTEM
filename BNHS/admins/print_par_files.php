<?php
session_start();
include('config/config.php');
require_once __DIR__ . '/assets/vendor/autoload.php'; // Ensure this path is correct

$mpdf = new \Mpdf\Mpdf();
ob_start(); // Start output buffering
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bukidnon National High School Inventory System</title>
    <link rel="apple-touch-icon" sizes="180x180" href="assets/img/brand/bnhs.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/brand/bnhs.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/brand/bnhs.png">
    <meta name="theme-color" content="#ffffff">


    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap 5 JavaScript Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
        }

        strong {
            font-weight: bold;
            font-size: 12px;
        }

        h5,
        h6 {
            text-align: center;
            margin: 0;
            padding: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }


        th,
        .tds {
            border: 1px solid black;
            padding: 8px;
            vertical-align: middle;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .mt-5 {
            margin-top: 30px;
        }

        .mb-4 {
            margin-bottom: 20px;
        }

        img {
            width: auto;
            height: 120px;
            display: block;
            margin: auto;
        }
    </style>
</head>

<body>
    <div class="container mt-5" id="printableArea">
        <div class="text-center mb-4">
            <img src="assets/img/brand/bnhs.png" alt="BNHS Logo" class="img-fluid">
        </div>

        <div id="list" class="mx-auto col-10 col-md-10">
            <div class="text-center mb-4">
                <h4 class="fw-bold">INVENTORY CUSTODIAN SLIP</h4>
            </div>

            <?php
            $ret = "SELECT * FROM inventory_custodian_slip ORDER BY `created_at` DESC";
            $stmt = $mysqli->prepare($ret);
            $stmt->execute();
            $res = $stmt->get_result();
            $ics = $res->fetch_object();

            if ($ics) {
            ?>
                <table>
                    <tr>
                        <td class="half">
                            <p><strong>Entity Name : </strong><?php echo htmlspecialchars($ics->entity_name ?? ''); ?></p>
                            <br>
                            <p><strong>Fund Cluster : </strong><?php echo htmlspecialchars($ics->fund_cluster ?? ''); ?></p>
                        </td>
                        <td class="half">
                            <br>
                            <br>
                            <p><strong>PAR No.: </strong><?php echo htmlspecialchars($ics->ics_no ?? ''); ?></p>
                            <br>
                            <br>
                        </td>
                    </tr>
                </table>

                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                        <thead class="table-light">
                            <tr>
                            <th class="tds" scope="col">Quantity</th>
                            <th class="tds" scope="col">Unit</th>
                            <th class="tds" style="width: 40%;" scope="col">Description</th>
                            <th class="tds" scope="col">Property Number</th>
                            <th class="tds" scope="col">Date Aquire</th>
                            <th class="tds" scope="col">Amount</th>
                            
                            </tr>
                          
                        </thead>
                        <tbody>
                            <?php
                            $stmt->execute();
                            $res = $stmt->get_result();
                            while ($ics = $res->fetch_object()) {
                            ?>
                                <tr>
                                    <td class="tds"><?php echo htmlspecialchars($ics->stock_no ?? ''); ?></td>
                                    <td class="tds"><?php echo htmlspecialchars($ics->unit ?? ''); ?></td>
                                    <td class="tds"><?php echo htmlspecialchars($ics->description ?? ''); ?></td>
                                    <td class="tds"><?php echo htmlspecialchars($ics->requisition_qty ?? ''); ?></td>
                                    <td class="tds"><?php echo htmlspecialchars($ics->stock_available_yes ?? ''); ?></td>
                                    <td class="tds"><?php echo htmlspecialchars($ics->stock_available_no ?? ''); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>

            <?php
            $ret = "SELECT * FROM inventory_custodian_slip ORDER BY `created_at` DESC";
            $stmt = $mysqli->prepare($ret);
            $stmt->execute();
            $res = $stmt->get_result();
            $ics = $res->fetch_object();

            if ($ics)
            ?>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                <tr>
                    <!-- Left: Inspection Section -->
                    <td style="border: 1px solid black; padding: 10px; vertical-align: top; width: 50%; text-align: center;">
                        <p><strong>Received from:</strong></p>
                        <br>
                        <br>
                        <p style="text-align: center;"><?php echo htmlspecialchars($ics->end_user_name ?? ''); ?></p>
                        <p style="text-align: center;">__________________________________________</p>
                        <p style="text-align: center;">Signature over Printed Name of End User</p>
                        <br>
                        <p style="text-align: center; "><?php echo htmlspecialchars($ics->end_user_position ?? ''); ?></p>
                        <p style="text-align: center;">______________________________</p>
                        <p>Position/Office</p>
                        <br>
                        <p style="text-align: center;"><?php echo htmlspecialchars($ics->date_received_user ?? ''); ?></p>
                        <p style="text-align: center;">______________________________</p>
                        <p>Date</p>
                    </td>

                    <!-- Right: Receiving Section -->

                    <td style="border: 1px solid black; padding: 10px;  width: 50%; text-align: center;">
                        <p style="text-align: left;"><strong>Issued by:</strong></p>
                        <br>
                        <br>
                        <p style="text-align: center;"><?php echo htmlspecialchars($ics->custodian_name ?? ''); ?></p>
                        <p style="text-align: center;">_________________________________________________________</p>
                        <p style="text-align: center;">Signature over Printed Name of Supply and/or Property Custodian</p>
                        <br>
                        <p style="text-align: center;"><?php echo htmlspecialchars($ics->custodian_position ?? ''); ?></p>
                        <p style="text-align: center;">______________________________</p>
                        <p>Position/Office</p>
                        <br>
                        <p style="text-align: center;"><?php echo htmlspecialchars($ics->date_received_custodian ?? ''); ?></p>
                        <p style="text-align: center;">______________________________</p>
                        <p>Date</p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>

<?php
$html = ob_get_clean();
$mpdf->WriteHTML($html);
$mpdf->Output("ICS_Report_" . date("Y_m_d") . ".pdf", 'I'); // 'I' to display inline
?>