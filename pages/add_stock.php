<?php include '../includes/db_connect.php'; session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Stock</title>
    <!-- Bootstrap CSS & Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script>
        function calculateTotalPrice() {
            let quantity = parseInt(document.getElementById('quantity').value);
            let price = parseFloat(document.getElementById('price').value);
            if (quantity > 0 && price > 0) {
                document.getElementById('total_price').value = (quantity * price).toFixed(2);
            } else {
                document.getElementById('total_price').value = '';
            }
        }
    </script>
</head>
<body>
<div class="container mt-5">
    <a href="../index.php" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold shadow-sm d-inline-flex align-items-center gap-2 mb-3">
        <i class="bi bi-house-door-fill"></i> Home
    </a>

    <h2>Add New Stock</h2>
    <form action="process_add_stock.php" method="POST">
        <div class="mb-3">
            <label>Item Name</label>
            <input type="text" class="form-control" name="item_name" required>
        </div>
        <div class="mb-3">
            <label>Quantity</label>
            <input type="number" class="form-control" id="quantity" name="quantity" min="1" step="1" required oninput="calculateTotalPrice()">
        </div>
        <div class="mb-3">
            <label>Price per Unit</label>
            <input type="number" class="form-control" id="price" name="price" min="0.01" step="0.01" required oninput="calculateTotalPrice()">
        </div>
        <div class="mb-3">
            <label>Total Price</label>
            <input type="text" class="form-control" id="total_price" name="total_price" readonly>
        </div>
        <button type="submit" class="btn btn-primary">Add Stock</button>
    </form>
</div>
</body>
</html>
