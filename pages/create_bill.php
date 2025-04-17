<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Bill</title>
    
    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function addItem() {
            let itemsContainer = document.getElementById("items-container");
            let itemHTML = `
                <div class="row mb-2 item-row">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="item_name[]" placeholder="Item Name" required>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control quantity" name="quantity[]" placeholder="Quantity" required oninput="calculateTotal()">
                    </div>
                    <div class="col-md-3">
                        <input type="number" step="0.01" class="form-control price" name="price[]" placeholder="Price per unit" required oninput="calculateTotal()">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger" onclick="removeItem(this)">Remove</button>
                    </div>
                </div>
            `;
            itemsContainer.insertAdjacentHTML("beforeend", itemHTML);
        }

        function removeItem(button) {
            button.closest('.item-row').remove();
            calculateTotal(); // Recalculate total when an item is removed
        }

        function calculateTotal() {
            let quantities = document.querySelectorAll('.quantity');
            let prices = document.querySelectorAll('.price');
            let totalAmount = 0;

            for (let i = 0; i < quantities.length; i++) {
                let quantity = parseFloat(quantities[i].value) || 0;
                let price = parseFloat(prices[i].value) || 0;
                totalAmount += quantity * price;
            }

            document.getElementById('total_amount').value = totalAmount.toFixed(2);
        }

        // Set Default Date to Today (YYYY-MM-DD)
        function setDefaultDate() {
            let today = new Date().toISOString().split('T')[0];
            document.getElementById('bill_date').value = today;
        }
    </script>
</head>
<body class="bg-light" onload="setDefaultDate()">

    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center">
                <h2>Create Bill</h2>
            </div>
            <div class="card-body">
                <form action="generate_invoice.php" method="post">

                    <!-- 📅 Date Field (YYYY-MM-DD) -->
                    <div class="mb-3">
                        <label class="form-label">Invoice Date:</label>
                        <input type="date" id="invoice_date" name="invoice_date" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Company Name:</label>
                        <input type="text" name="company_name" class="form-control" placeholder="Enter company name" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Company Address:</label>
                        <input type="text" name="company_address" class="form-control" placeholder="Enter company address" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Customer Name:</label>
                        <input type="text" name="customer_name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Customer Address:</label>
                        <input type="text" name="customer_address" class="form-control" required>
                    </div>

                    <h5>Items</h5>
                    <div id="items-container">
                        <div class="row mb-2 item-row">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="item_name[]" placeholder="Item Name" required>
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control quantity" name="quantity[]" placeholder="Quantity" required oninput="calculateTotal()">
                            </div>
                            <div class="col-md-3">
                                <input type="number" step="0.01" class="form-control price" name="price[]" placeholder="Price per unit" required oninput="calculateTotal()">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger" onclick="removeItem(this)">Remove</button>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-primary mb-3" onclick="addItem()">Add More Items</button>

                    <!-- Total Amount Field (Read-Only) -->
                    <div class="mb-3">
                        <label class="form-label">Total Amount:</label>
                        <input type="text" id="total_amount" name="total_amount" class="form-control" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Amount Paid:</label>
                        <input type="number" step="0.01" name="amount_paid" class="form-control" placeholder="Enter amount paid" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Method:</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="" disabled selected>Select Payment Method</option>
                            <option value="Cash">Cash</option>
                            <option value="Credit Card">Credit Card</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Mobile Payment">Mobile Payment</option>
                        </select>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-success">Generate Invoice</button>
                    </div>
                
                </form>
            </div>
        </div>
    </div>

</body>
</html>
