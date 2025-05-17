<?php
include '../includes/db_connect.php';
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if the user is not logged in
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];  // Get the user ID from session

// Fetch customers that the logged-in user has added
$customers = $conn->query("SELECT customer_name, address FROM customers WHERE user_id = '$userId'");

// Fetch only the items that the logged-in user has added
$stockItems = $conn->query("SELECT item_name, price FROM stock WHERE user_id = '$userId'");

// Prepare stock data for JavaScript
$items = [];
while ($row = $stockItems->fetch_assoc()) {
    $items[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice Generation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS & Icons -->
    <!-- Bootstrap CSS & Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Home Button -->
    <a href="../index.php" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold shadow-sm d-inline-flex align-items-center gap-2">
        <i class="bi bi-house-door-fill"></i>
        Home
    </a>

    <script>
    function addItem() {
        let itemsContainer = document.getElementById("items-container");
        let itemHTML = `
            <div class="row mb-2 item-row">
                <div class="col-md-4">
                    <select class="form-select item-select" name="item_name[]" onchange="fillItemPrice(this)" required>
                        <option value="" disabled selected>Select Item</option>
                        <?php foreach($items as $item): ?>
                            <option value="<?php echo htmlspecialchars($item['item_name']); ?>" data-price="<?php echo htmlspecialchars($item['price']); ?>">
                                <?php echo htmlspecialchars($item['item_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <input type="number" name="quantity[]" class="form-control quantity" min="1" required oninput="calculateTotal()">
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
        calculateTotal();
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

    function setDefaultDate() {
        let today = new Date().toISOString().split('T')[0];
        document.getElementById('invoice_date').value = today;
    }

    function fillItemPrice(select) {
        let price = select.options[select.selectedIndex].getAttribute('data-price');
        let itemRow = select.closest('.item-row');
        let priceInput = itemRow.querySelector('.price');

        if (priceInput) {
            priceInput.value = price;
            calculateTotal();
        }
    }

    function fillCustomerDetails() {
        let select = document.getElementById("customer_select");
        let customerData = JSON.parse(select.value);

        document.getElementById("customer_name").value = customerData.customer_name;
        document.getElementById("customer_address").value = customerData.address;
    }
</script>

</head>

<body class="bg-light" onload="setDefaultDate()">

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h2>Invoice Generation</h2>
        </div>

        <div class="card-body">
            <form action="generate_invoice.php" method="post">

                <!-- 📅 Invoice Date -->
                <div class="mb-3">
                    <label class="form-label">Invoice Date:</label>
                    <input type="date" id="invoice_date" name="invoice_date" class="form-control" required>
                </div>

                <!-- 🏢 Company Details -->
                <div class="mb-3">
                    <label class="form-label">Company Name:</label>
                    <input type="text" name="company_name" class="form-control" placeholder="Enter company name" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Company Address:</label>
                    <input type="text" name="company_address" class="form-control" placeholder="Enter company address" required>
                </div>

                <!-- 👤 Customer Selection -->
                <div class="mb-3">
                    <label class="form-label">Select Customer:</label>
                    <select id="customer_select" class="form-select" onchange="fillCustomerDetails()" required>
                        <option value="" disabled selected>Select a customer</option>
                        <?php while($row = $customers->fetch_assoc()): ?>
                            <option value='<?php echo json_encode($row); ?>'>
                                <?php echo htmlspecialchars($row['customer_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- ✏️ Auto-filled Customer Details -->
                <div class="mb-3">
                    <label class="form-label">Customer Name:</label>
                    <input type="text" id="customer_name" name="customer_name" class="form-control" required readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Customer Address:</label>
                    <input type="text" id="customer_address" name="customer_address" class="form-control" required readonly>
                </div>

                <!-- 🛒 Items Section -->
                <h5 class="mb-3">Items</h5>
                <div id="items-container">
                    <div class="row mb-2 item-row">
                        <div class="col-md-4">
                            <select class="form-select item-select" name="item_name[]" onchange="fillItemPrice(this)" required>
                                <option value="" disabled selected>Select Item</option>
                                <?php foreach($items as $item): ?>
                                    <option value="<?php echo htmlspecialchars($item['item_name']); ?>" data-price="<?php echo htmlspecialchars($item['price']); ?>">
                                        <?php echo htmlspecialchars($item['item_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
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

                <div class="mb-3">
                    <button type="button" class="btn btn-primary" onclick="addItem()">Add More Items</button>
                </div>

                <!-- 💰 Payment Details -->
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
                    <select name="payment_method" class="form-select">
                        <option value="" disabled selected>Select Payment Method</option>
                        <option value="Cash">Cash</option>
                        <option value="Credit Card">Credit Card</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Mobile Payment">Mobile Payment</option>
                        <option value="None">None</option>
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
