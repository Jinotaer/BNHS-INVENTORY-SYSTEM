<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requisition and Issue Slip</title>
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
        select {
            width: 70%;
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
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        .signature-box {
            width: 23%;
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
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <h1>REQUISITION AND ISSUE SLIP</h1>
            <div style="text-align: right;">Appendix 63</div>
        </div>

        <form id="risForm" action="submit_ris.php" method="post">
            <div class="form-section">
                <div class="form-row">
                    <div class="form-group" style="flex: 3;">
                        <label for="entity_name">Entity Name:</label>
                        <input type="text" id="entity_name" name="entity_name" class="" required>
                    </div>
                    <div class="form-group" style="flex: 1; text-align: left;">
                        <label for="fund_cluster">Fund Cluster:</label>
                        <input type="text" id="fund_cluster" name="fund_cluster" class="">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-row">
                    <div class="form-group">
                        <label for="division">Division:</label>
                        <input type="text" id="division" name="division" class="underline-input">
                    </div>
                    <div class="form-group">
                        <label for="office">Office:</label>
                        <input type="text" id="office" name="office" class="underline-input">
                    </div>
                    <div class="form-group">
                        <label for="responsibility_code">Responsibility Center Code:</label>
                        <input type="text" id="responsibility_code" name="responsibility_code" class="underline-input">
                    </div>
                    <div class="form-group">
                        <label for="ris_no">RIS No.:</label>
                        <input type="text" id="ris_no" name="ris_no" class="underline-input" required>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Stock No.</th>
                            <th>Unit</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Stock Available?</th>
                            <th>Issue Quantity</th>
                            <th>Remarks</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="itemsTableBody">
                        <tr>
                            <td><input type="text" name="stock_no[]" class="underline-input"></td>
                            <td><input type="text" name="unit[]" class="underline-input"></td>
                            <td><input type="text" name="description[]" class="underline-input"></td>
                            <td><input type="number" name="quantity[]" class="underline-input" min="1"></td>
                            <td>
                                <div class="radio-group">
                                    <label><input type="radio" name="stock_available[0]" value="1"> Yes</label>
                                    <label><input type="radio" name="stock_available[0]" value="0"> No</label>
                                </div>
                            </td>
                            <td><input type="number" name="issue_quantity[]" class="underline-input" min="0"></td>
                            <td><input type="text" name="remarks[]" class="underline-input"></td>
                            <td><button type="button" class="btn btn-add-row" onclick="addItemRow()">+</button></td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" class="btn btn-add-row" onclick="addItemRow()">Add Item</button>
            </div>

            <div class="form-section">
                <label for="purpose">Purpose:</label>
                <textarea id="purpose" name="purpose" rows="3" style="width: 100%;"></textarea>
            </div>

            <div class="signature-section">
                <div class="signature-box">
                    <p>Requested by:</p>
                    <input type="text" name="requested_by_signature" class="underline-input" placeholder="Signature">
                    <input type="text" name="requested_by_name" class="underline-input" placeholder="Printed Name">
                    <input type="text" name="requested_by_designation" class="underline-input" placeholder="Designation">
                    <input type="date" name="requested_date" class="underline-input">
                </div>
                <div class="signature-box">
                    <p>Approved by:</p>
                    <input type="text" name="approved_by_signature" class="underline-input" placeholder="Signature">
                    <input type="text" name="approved_by_name" class="underline-input" placeholder="Printed Name">
                    <input type="text" name="approved_by_designation" class="underline-input" value="Secondary School Principal I/OFFICE HEAD" readonly>
                    <input type="date" name="approved_date" class="underline-input">
                </div>
                <div class="signature-box">
                    <p>Issued by:</p>
                    <input type="text" name="issued_by_signature" class="underline-input" placeholder="Signature">
                    <input type="text" name="issued_by_name" class="underline-input" placeholder="Printed Name">
                    <input type="text" name="issued_by_designation" class="underline-input" value="Property Custodian" readonly>
                    <input type="date" name="issued_date" class="underline-input">
                </div>
                <div class="signature-box">
                    <p>Received by:</p>
                    <input type="text" name="received_by_signature" class="underline-input" placeholder="Signature">
                    <input type="text" name="received_by_name" class="underline-input" placeholder="Printed Name">
                    <input type="text" name="received_by_designation" class="underline-input" placeholder="Designation">
                    <input type="date" name="received_date" class="underline-input">
                </div>
            </div>

            <div style="text-align: right; margin-top: 20px;">
                <p>AO 6/15/02</p>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <button type="submit" class="btn">Submit Requisition</button>
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
                <td><input type="text" name="unit[]" class="underline-input"></td>
                <td><input type="text" name="description[]" class="underline-input"></td>
                <td><input type="number" name="quantity[]" class="underline-input" min="1"></td>
                <td>
                    <div class="radio-group">
                        <label><input type="radio" name="stock_available[${itemCounter}]" value="1"> Yes</label>
                        <label><input type="radio" name="stock_available[${itemCounter}]" value="0"> No</label>
                    </div>
                </td>
                <td><input type="number" name="issue_quantity[]" class="underline-input" min="0"></td>
                <td><input type="text" name="remarks[]" class="underline-input"></td>
                <td><button type="button" class="btn btn-add-row" onclick="this.parentNode.parentNode.remove()">-</button></td>
            `;
            
            tbody.appendChild(newRow);
            itemCounter++;
        }
    </script>
    <script>
    document.getElementById('risForm').addEventListener('submit', function(e) {
        // Validate required fields
        if (!document.getElementById('entity_name').value) {
            alert('Entity Name is required');
            e.preventDefault();
            return;
        }
        
        if (!document.getElementById('ris_no').value) {
            alert('RIS Number is required');
            e.preventDefault();
            return;
        }
        
        // Validate at least one item is added
        const itemRows = document.querySelectorAll('#itemsTableBody tr');
        let hasItems = false;
        
        itemRows.forEach(row => {
            const stockNo = row.querySelector('input[name="stock_no[]"]').value;
            if (stockNo) hasItems = true;
        });
        
        if (!hasItems) {
            alert('Please add at least one item to the requisition');
            e.preventDefault();
            return;
        }
        
        // Validate stock availability is selected for all items
        let allValid = true;
        
        itemRows.forEach((row, index) => {
            const stockNo = row.querySelector('input[name="stock_no[]"]').value;
            if (stockNo) {
                const selected = row.querySelector(`input[name="stock_available[${index}]"]:checked`);
                if (!selected) {
                    alert(`Please select stock availability for item ${index + 1}`);
                    allValid = false;
                }
            }
        });
        
        if (!allValid) {
            e.preventDefault();
        }
    });
</script>
</body>
</html>