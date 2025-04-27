<?php include '../includes/db_connect.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Stock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <a href="../index.php" class="btn btn-outline-primary">
    <i class="bi bi-house-door-fill"></i> Home
    </a>
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">


    <script>
        function calculateTotalPrice() {
            let quantity = document.getElementById('quantity').value;
            let price = document.getElementById('price').value;
            let totalPrice = quantity * price;
            document.getElementById('total_price').value = totalPrice.toFixed(2);
        }
    </script>
</head>
<body>
<div class="container mt-5">
    <h2>Add New Stock</h2>
    <form action="process_add_stock.php" method="POST">
        <div class="mb-3">
            <label>Item Name</label>
            <input type="text" class="form-control" name="item_name" required>
        </div>
        <div class="mb-3">
            <label>Quantity</label>
            <input type="number" class="form-control" id="quantity" name="quantity" required oninput="calculateTotalPrice()">
        </div>
        <div class="mb-3">
            <label>Price per Unit</label>
            <input type="number" class="form-control" id="price" step="0.01" name="price" required oninput="calculateTotalPrice()">
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
