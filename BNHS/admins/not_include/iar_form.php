<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inspection and Acceptance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .form-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-header h1 {
            margin: 0;
            font-size: 18px;
            text-decoration: underline;
        }
        .appendix {
            text-align: right;
            margin-bottom: 20px;
        }
        .form-section {
            margin-bottom: 20px;
        }
        .form-row {
            display: flex;
            margin-bottom: 10px;
        }
        .form-group {
            flex: 1;
            margin-right: 10px;
        }
        .form-group:last-child {
            margin-right: 0;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], 
        input[type="number"],
        textarea,
        select,
        input[type="date"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .underline-input {
            border: none;
            border-bottom: 1px solid #000;
            background: transparent;
            width: 100%;
            padding: 5px 0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th, .items-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .items-table th {
            background-color: #f2f2f2;
        }
        .inspection-section {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        .inspection-box {
            width: 48%;
        }
        .signature-box {
            margin-top: 20px;
            text-align: center;
        }
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .btn-add-row {
            background-color: #2196F3;
        }
        .btn-add-row:hover {
            background-color: #0b7dda;
        }
        .radio-group {
            display: flex;
            align-items: center;
        }
        .radio-group label {
            margin-right: 15px;
            font-weight: normal;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            margin: 20px 0;
            padding-bottom: 5px;
        }
        .verification-check {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .verification-check input[type="checkbox"] {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="appendix">Appendix 62</div>
        
        <div class="form-header">
            <h1>INSPECTION AND ACCEPTANCE REPORT</h1>
        </div>

        <form id="iarForm" action="submit_iar.php" method="post">
            <div class="form-section">
                <div class="form-row">
                    <div class="form-group" style="flex: 3;">
                        <label for="entity_name">Entity Name:</label>
                        <input type="text" id="entity_name" name="entity_name" class="underline-input" required>
                    </div>
                    <div class="form-group" style="flex: 1; text-align: right;">
                        <label for="fund_cluster">Fund Cluster:</label>
                        <input type="text" id="fund_cluster" name="fund_cluster" class="underline-input">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-row">
                    <div class="form-group">
                        <label for="supplier">Supplier:</label>
                        <input type="text" id="supplier" name="supplier" class="underline-input">
                    </div>
                    <div class="form-group">
                        <label for="iar_no">IAR No.:</label>
                        <input type="text" id="iar_no" name="iar_no" class="underline-input" value="25-04-001" required>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-row">
                    <div class="form-group">
                        <label for="po_no">PO No./Date:</label>
                        <input type="text" id="po_no" name="po_no" class="underline-input">
                    </div>
                    <div class="form-group">
                        <label for="iar_date">Date:</label>
                        <input type="date" id="iar_date" name="iar_date" class="underline-input">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-row">
                    <div class="form-group">
                        <label for="requisitioning_office">Requisitioning Office/Dept.:</label>
                        <input type="text" id="requisitioning_office" name="requisitioning_office" class="underline-input">
                    </div>
                    <div class="form-group">
                        <label for="invoice_no">Invoice No.:</label>
                        <input type="text" id="invoice_no" name="invoice_no" class="underline-input">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-row">
                    <div class="form-group">
                        <label for="responsibility_code">Responsibility Center Code:</label>
                        <input type="text" id="responsibility_code" name="responsibility_code" class="underline-input">
                    </div>
                    <div class="form-group">
                        <label for="invoice_date">Date:</label>
                        <input type="date" id="invoice_date" name="invoice_date" class="underline-input">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Stock/Property No.</th>
                            <th>Item Description</th>
                            <th>Unit</th>
                            <th>Quantity</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="itemsTableBody">
                        <tr>
                            <td><input type="text" name="stock_no[]" class="underline-input"></td>
                            <td><input type="text" name="description[]" class="underline-input"></td>
                            <td><input type="text" name="unit[]" class="underline-input"></td>
                            <td><input type="number" name="quantity[]" class="underline-input" min="1"></td>
                            <td><button type="button" class="btn btn-add-row" onclick="addItemRow()">+</button></td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" class="btn btn-add-row" onclick="addItemRow()">Add Item</button>
            </div>

            <div class="inspection-section">
                <div class="inspection-box">
                    <h3>INSPECTION</h3>
                    <div class="form-group">
                        <label for="date_inspected">Date Inspected:</label>
                        <input type="date" id="date_inspected" name="date_inspected" class="underline-input">
                    </div>
                    
                    <div class="verification-check">
                        <input type="checkbox" id="verified_check" name="verified_check" checked>
                        <label for="verified_check">Inspected, verified and found in order as to quantity and specifications</label>
                    </div>
                    
                    <div class="signature-box">
                        <input type="text" name="teacher1_name" class="underline-input" placeholder="TEACHER NO. 1">
                        <p>Inspection Officer</p>
                        <input type="text" name="teacher1_signature" class="underline-input" placeholder="Signature">
                    </div>
                    
                    <div class="signature-box">
                        <input type="text" name="teacher2_name" class="underline-input" placeholder="TEACHER NO. 2">
                        <p>Inspection Officer</p>
                        <input type="text" name="teacher2_signature" class="underline-input" placeholder="Signature">
                    </div>
                    
                    <div class="signature-box">
                        <input type="text" name="teacher3_name" class="underline-input" placeholder="TEACHER NO. 3">
                        <p>Inspectorate Officer</p>
                        <input type="text" name="teacher3_signature" class="underline-input" placeholder="Signature">
                    </div>
                    
                    <div class="signature-box">
                        <input type="text" name="brgy_official_name" class="underline-input" placeholder="BRGY OFFICIALS">
                        <p>Brgy. Councilor</p>
                        <input type="text" name="brgy_official_signature" class="underline-input" placeholder="Signature">
                    </div>
                    
                    <div class="signature-box">
                        <input type="text" name="gpta_officer_name" class="underline-input" placeholder="GPTA OFFICER">
                        <p>PTA Observer</p>
                        <input type="text" name="gpta_officer_signature" class="underline-input" placeholder="Signature">
                    </div>
                </div>
                
                <div class="inspection-box">
                    <h3>ACCEPTANCE</h3>
                    <div class="form-group">
                        <label for="date_received">Date Received:</label>
                        <input type="date" id="date_received" name="date_received" class="underline-input">
                    </div>
                    
                    <div class="radio-group">
                        <label><input type="radio" name="acceptance_status" value="complete" checked> Complete</label>
                        <label><input type="radio" name="acceptance_status" value="partial"> Partial (pls. specify quantity)</label>
                    </div>
                    
                    <div class="signature-box">
                        <input type="text" name="property_custodian_name" class="underline-input" placeholder="NAME OF PROPERTY CUSTODIAN">
                        <p>Supply and/or Property Custodian</p>
                        <input type="text" name="property_custodian_signature" class="underline-input" placeholder="Signature">
                    </div>
                </div>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <button type="submit" class="btn">Submit IAR</button>
                <button type="button" class="btn" onclick="window.print()">Print Form</button>
            </div>
        </form>
    </div>

    <script>
        let itemCounter = 1;

        function addItemRow() {
            const tbody = document.getElementById('itemsTableBody');
            const newRow = document.createElement('tr');
            
            newRow.innerHTML = `
                <td><input type="text" name="stock_no[]" class="underline-input"></td>
                <td><input type="text" name="description[]" class="underline-input"></td>
                <td><input type="text" name="unit[]" class="underline-input"></td>
                <td><input type="number" name="quantity[]" class="underline-input" min="1"></td>
                <td><button type="button" class="btn btn-add-row" onclick="this.parentNode.parentNode.remove()">-</button></td>
            `;
            
            tbody.appendChild(newRow);
            itemCounter++;
        }

        document.getElementById('iarForm').addEventListener('submit', function(e) {
            // Validate required fields
            if (!document.getElementById('entity_name').value) {
                alert('Entity Name is required');
                e.preventDefault();
                return;
            }
            
            if (!document.getElementById('iar_no').value) {
                alert('IAR Number is required');
                e.preventDefault();
                return;
            }
            
            // Validate at least one item is added
            const itemRows = document.querySelectorAll('#itemsTableBody tr');
            let hasItems = false;
            
            itemRows.forEach(row => {
                const itemDesc = row.querySelector('input[name="stock_no[]"]').value;
                if (itemDesc) hasItems = true;
            });
            
            if (!hasItems) {
                alert('Please add at least one item to the report');
                e.preventDefault();
                return;
            }
            
            // Validate all items have quantities
            let allValid = true;
            
            itemRows.forEach(row => {
                const itemDesc = row.querySelector('input[name="stock_no[]"]').value;
                const quantity = row.querySelector('input[name="quantity[]"]').value;
                
                if (itemDesc && !quantity) {
                    alert('Please enter quantity for all items');
                    allValid = false;
                }
            });
            
            if (!allValid) {
                e.preventDefault();
            }
            
            // Validate acceptance status is selected
            if (!document.querySelector('input[name="acceptance_status"]:checked')) {
                alert('Please select acceptance status (Complete or Partial)');
                e.preventDefault();
                return;
            }
            
            // Validate all inspector names are filled
            const requiredInspectors = [
                'teacher1_name', 'teacher2_name', 'teacher3_name',
                'brgy_official_name', 'gpta_official_name',
                'property_custodian_name'
            ];
            
            for (const field of requiredInspectors) {
                if (!document.querySelector(`input[name="${field}"]`).value) {
                    alert('Please fill all inspector names');
                    e.preventDefault();
                    return;
                }
            }
        });
    </script>
</body>
</html>