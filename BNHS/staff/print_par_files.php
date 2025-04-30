<?php
session_start();
include('config/config.php');
require_once __DIR__ . '/assets/vendor/autoload.php'; // Ensure this path is correct

// Check if PAR ID is provided
$par_id = isset($_GET['par_id']) ? intval($_GET['par_id']) : 0;
$item_id = isset($_GET['item_id']) ? intval($_GET['item_id']) : 0;

if (!$par_id) {
    echo "No PAR ID provided. Please specify a PAR to print.";
    exit;
}

$mpdf = new \Mpdf\Mpdf([
  'mode' => 'utf-8',
  'format' => 'A4',
  'margin_left' => 10,
  'margin_right' => 10,
  'margin_top' => 10,
  'margin_bottom' => 10,
  'margin_header' => 5,
  'margin_footer' => 5
]);

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
                <h4 class="fw-bold">PROPERTY ACKNOWLEDGMENT RECEIPT</h4>
            </div>

            <?php
            // Get specific PAR record
            $ret = "SELECT par.*, e.entity_name, e.fund_cluster 
                   FROM property_acknowledgment_receipts par 
                   JOIN entities e ON par.entity_id = e.entity_id 
                   WHERE par.par_id = ?";
            
            $stmt = $mysqli->prepare($ret);
            $stmt->bind_param("i", $par_id);
            $stmt->execute();
            $res = $stmt->get_result();
            
            if ($res->num_rows == 0) {
                echo "No PAR found with ID: " . $par_id;
                exit;
            }
            
            $par = $res->fetch_object();
            ?>
            <table>
                <tr>
                    <td class="half">
                        <p><strong>Entity Name : </strong><?php echo htmlspecialchars($par->entity_name ?? ''); ?></p>
                        <br>
                        <p><strong>Fund Cluster : </strong><?php echo htmlspecialchars($par->fund_cluster ?? ''); ?></p>
                    </td>
                    <td class="half">
                        <br>
                        <br>
                        <p><strong>PAR No.: </strong><?php echo htmlspecialchars($par->par_no ?? ''); ?></p>
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
                        <th class="tds" scope="col">Date Acquired</th>
                        <th class="tds" scope="col">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Get the PAR items with optional item filter
                        $items_query = "SELECT pi.*, i.item_description, i.unit, i.unit_cost, i.stock_no
                                      FROM par_items pi 
                                      JOIN items i ON pi.item_id = i.item_id 
                                      WHERE pi.par_id = ?";
                        
                        $params = [$par_id];
                        
                        // If item_id is provided, filter by it as well
                        if ($item_id) {
                            $items_query .= " AND pi.item_id = ?";
                            $params[] = $item_id;
                        }
                        
                        $items_stmt = $mysqli->prepare($items_query);
                        
                        if (count($params) === 1) {
                            $items_stmt->bind_param("i", $params[0]);
                        } else {
                            $items_stmt->bind_param("ii", $params[0], $params[1]);
                        }
                        
                        $items_stmt->execute();
                        $items_res = $items_stmt->get_result();
                        
                        while ($item = $items_res->fetch_object()) {
                        ?>
                            <tr>
                                <td class="tds"><?php echo htmlspecialchars($item->quantity ?? ''); ?></td>
                                <td class="tds"><?php echo htmlspecialchars($item->unit ?? ''); ?></td>
                                <td class="tds"><?php echo htmlspecialchars($item->item_description ?? ''); ?></td>
                                <td class="tds"><?php echo htmlspecialchars($item->property_number ?? ''); ?></td>
                                <td class="tds"><?php echo htmlspecialchars($par->date_acquired ?? ''); ?></td>
                                <td class="tds"><?php echo htmlspecialchars($item->unit_cost ?? ''); ?></td>
                            </tr>
                            <tr>
                                <td class="tds"></td>
                                <td class="tds"></td>
                                <td class="tds"></td>
                                <td class="tds"></td>
                                <td class="tds"></td>
                                <td class="tds"></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                <tr>
                    <!-- Left: Received From Section -->
                    <td style="border: 1px solid black; padding: 10px; vertical-align: top; width: 50%; text-align: center;">
                        <p><strong>Received from:</strong></p>
                        <br>
                        <br>
                        <p style="text-align: center;"><?php echo htmlspecialchars($par->custodian_name ?? ''); ?></p>
                        <p style="text-align: center;">__________________________________________</p>
                        <p style="text-align: center;">Signature over Printed Name of Supply and/or Property Custodian</p>
                        <br>
                        <p style="text-align: center; "><?php echo htmlspecialchars($par->custodian_position ?? ''); ?></p>
                        <p style="text-align: center;">______________________________</p>
                        <p>Position/Office</p>
                        <br>
                        <p style="text-align: center;"><?php echo htmlspecialchars($par->custodian_date ?? ''); ?></p>
                        <p style="text-align: center;">______________________________</p>
                        <p>Date</p>
                    </td>

                    <!-- Right: Received By Section -->
                    <td style="border: 1px solid black; padding: 10px;  width: 50%; text-align: center;">
                        <p style="text-align: left;"><strong>Received by:</strong></p>
                        <br>
                        <br>
                        <p style="text-align: center;"><?php echo htmlspecialchars($par->end_user_name ?? ''); ?></p>
                        <p style="text-align: center;">_________________________________________________________</p>
                        <p style="text-align: center;">Signature over Printed Name of End User</p>
                        <br>
                        <p style="text-align: center;"><?php echo htmlspecialchars($par->receiver_position ?? ''); ?></p>
                        <p style="text-align: center;">______________________________</p>
                        <p>Position/Office</p>
                        <br>
                        <p style="text-align: center;"><?php echo htmlspecialchars($par->receiver_date ?? ''); ?></p>
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
$mpdf->Output("PAR_Report_" . $par->par_no . "_" . date("Y_m_d") . ".pdf", 'I'); // 'I' to display inline
?>