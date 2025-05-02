<?php
session_start();
include('config/config.php');
require_once __DIR__ . '/assets/vendor/autoload.php'; // Ensure this path is correct

// Check if ICS ID is provided
$ics_id = isset($_GET['ics_id']) ? intval($_GET['ics_id']) : 0;
$item_id = isset($_GET['item_id']) ? intval($_GET['item_id']) : 0;
$ics_item_id = isset($_GET['ics_item_id']) ? intval($_GET['ics_item_id']) : 0;

if (!$ics_id) {
    echo "No ICS ID provided. Please specify an ICS to print.";
    exit;
}

// Get the ICS number to find all related items
$getIcsNoQuery = "SELECT ics.ics_no 
                  FROM inventory_custodian_slips ics
                  WHERE ics.ics_id = ?";
$stmtIcsNo = $mysqli->prepare($getIcsNoQuery);
$stmtIcsNo->bind_param("i", $ics_id);
$stmtIcsNo->execute();
$resultIcsNo = $stmtIcsNo->get_result();
$icsNoRow = $resultIcsNo->fetch_object();

if (!$icsNoRow) {
    echo "ICS record not found.";
    exit;
}

$ics_no = $icsNoRow->ics_no;

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

        tbody .tds {
            font-weight: normal;
            font-size: 10px;
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
            // Modified query to get all items with the same ICS number
            $ret = "SELECT ics.ics_id, e.entity_name, e.fund_cluster, ics.ics_no,
                    ii.quantity, i.unit, i.unit_cost, (ii.quantity * i.unit_cost) as total_amount,
                    i.item_description, ii.inventory_item_no, i.estimated_useful_life,
                    ics.end_user_name, ics.end_user_position, ics.end_user_date,
                    ics.custodian_name, ics.custodian_position, ics.custodian_date,
                    ii.article, ii.remarks
                  FROM inventory_custodian_slips ics
                  JOIN entities e ON ics.entity_id = e.entity_id
                  JOIN ics_items ii ON ics.ics_id = ii.ics_id
                  JOIN items i ON ii.item_id = i.item_id
                  WHERE ics.ics_no = ?";

            // If specific item filters are provided, apply them
            $params = [$ics_no];
            if ($item_id) {
                $ret .= " AND i.item_id = ?";
                $params[] = $item_id;
            }

            if ($ics_item_id) {
                $ret .= " AND ii.ics_item_id = ?";
                $params[] = $ics_item_id;
            }

            $stmt = $mysqli->prepare($ret);

            if (count($params) === 1) {
                $stmt->bind_param("s", $params[0]);
            } elseif (count($params) === 2) {
                $stmt->bind_param("si", $params[0], $params[1]);
            } else {
                $stmt->bind_param("sii", $params[0], $params[1], $params[2]);
            }
            
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows == 0) {
                echo "No ICS found with number: " . $ics_no;
                exit;
            }

            // Get the first record for the header information
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
                            <!-- <br> -->
                            <p><strong>ICS No.: </strong><?php echo htmlspecialchars($ics->ics_no ?? ''); ?></p>
                            <!-- <br> -->
                             <br>
                        </td>
                    </tr>
                </table>
                <!-- <br> -->
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="tds" rowspan="2">Quantity</th>
                                <th class="tds" rowspan="2">Unit</th>
                                <th class="tds" colspan="2">Amount</th>
                                <th class="tds" rowspan="2" style="width: 30%;">Description</th>
                                <th class="tds" rowspan="2">'Inventory Item No.</th>
                                <th class="tds" rowspan="2">Estimated Useful Life</th>
                            </tr>
                            <tr>
                                <th class="tds">Unit Cost</th>
                                <th class="tds">Total Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Reset the result pointer to use the same query results
                            $res->data_seek(0);
                            while ($item = $res->fetch_object()) {
                            ?>
                                <tr>
                                    <td class="tds"><?php echo htmlspecialchars($item->quantity ?? ''); ?></td>
                                    <td class="tds"><?php echo htmlspecialchars($item->unit ?? ''); ?></td>
                                    <td class="tds"><?php echo htmlspecialchars($item->unit_cost ?? ''); ?></td>
                                    <td class="tds"><?php echo htmlspecialchars($item->total_amount ?? ''); ?></td>
                                    <td class="tds"><?php echo htmlspecialchars($item->item_description ?? ''); ?></td>
                                    <td class="tds"><?php echo htmlspecialchars($item->inventory_item_no ?? ''); ?></td>
                                    <td class="tds"><?php echo htmlspecialchars($item->estimated_useful_life ?? ''); ?></td>
                                </tr>
                               
                            <?php } ?>
                            <tr>
                                    <td class="tds"></td>
                                    <td class="tds"></td>
                                    <td class="tds"></td>
                                    <td class="tds"></td>
                                    <td class="tds"></td>
                                    <td class="tds"></td>
                                    <td class="tds"></td>
                                </tr>
                        </tbody>
                    </table>
                </div>
            <?php } ?>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                <tr>
                    <!-- Left: Inspection Section -->
                    <td style="border: 1px solid black; padding: 10px; vertical-align: top; width: 50%; text-align: center;">
                        <p style="text-align: left;"><strong>Received from:</strong></p>
                        <br>
                        <br>
                        <p style="text-align: center; "><?php echo htmlspecialchars($ics->end_user_name ?? ''); ?></p>
                        <p style="text-align: center;">______________________________</p>
                        <p style="text-align: center;">Signature Over Printed Name</p>
                        <br>
                        <br>
                        <p style="text-align: center; "><?php echo htmlspecialchars($ics->end_user_position ?? ''); ?></p>
                        <p style="text-align: center;">______________________________</p>
                        <p style="text-align: center;">Position/Office</p>
                        <br>
                        <br>
                        <p style="text-align: center;"><?php echo htmlspecialchars($ics->end_user_date ?? ''); ?></p>
                        <p style="text-align: center;">______________________________</p>
                        <p style="text-align: center;">Date</p>
                    </td>

                    <!-- Right: Receiving Section -->

                    <td style="border: 1px solid black; padding: 10px;  width: 50%; text-align: center;">
                        <p style="text-align: left;"><strong>Received by:</strong></p>
                        <br>
                        <br>
                        <p style="text-align: center;"><?php echo htmlspecialchars($ics->custodian_name ?? ''); ?></p>
                        <p style="text-align: center;">______________________________</p>
                        <p style="text-align: center;">Signature Over Printed Name</p>
                        <br>
                        <br>
                        <p style="text-align: center;"><?php echo htmlspecialchars($ics->custodian_position ?? ''); ?></p>
                        <p style="text-align: center;">______________________________</p>
                        <p>Position/Office</p>
                        <br>
                        <br>
                        <p style="text-align: center;"><?php echo htmlspecialchars($ics->custodian_date ?? ''); ?></p>
                        <p style="text-align: center;">______________________________</p>
                        <p>Date</p>
                    </td>
                </tr>
            </table>

            <!-- <div class="text-center mt-5 ml-5">
        <table width="100%">
          <tr>
            <td class="text-center">
              <p>Certified Correct:</p><br><br>
              <strong>__________________________</strong><br>
              <em>Inventory Committee Chair</em>
            </td>
            <td class="text-center">
              <p>Approved by:</p><br><br>
              <strong>__________________________</strong><br>
              <em>School Head / Admin Officer</em>
            </td>
            <td class="text-center">
              <p>Verified by:</p><br><br>
              <strong>__________________________</strong><br>
              <em>COA Representative</em>
            </td>
          </tr>
        </table>
      </div> -->
        </div>
    </div>
</body>

</html>

<?php
$html = ob_get_clean();
$mpdf->WriteHTML($html);
$mpdf->Output("ICS_Report_" . date("Y_m_d") . ".pdf", 'I'); // 'I' to display inline
?>