<?php 
    include '../includes/db_connect.php'; 
    session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Stock</title>
    <!-- Bootstrap CSS & Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding: 30px; }
        .container { max-width: 600px; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .btn-space { margin-right: 10px; }
    </style>
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

<div class="mb-3">
    <a href="../index.php" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold shadow-sm d-inline-flex align-items-center gap-2">
        <i class="bi bi-house-door-fill"></i> Home
    </a>
</div>

<div class="container">
    <h2 class="text-center mb-4 text-primary">Add New Stock</h2>

    <form action="process_add_stock.php" method="POST">
        <div class="mb-3">
            <label class="form-label">Item Name</label>
            <input type="text" class="form-control" name="item_name" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Quantity</label>
            <input type="number" class="form-control" id="quantity" name="quantity" min="1" step="1" required oninput="calculateTotalPrice()">
        </div>
        <div class="mb-3">
            <label class="form-label">Price per Unit</label>
            <input type="number" class="form-control" id="price" name="price" min="0.01" step="0.01" required oninput="calculateTotalPrice()">
        </div>
        <div class="mb-3">
            <label class="form-label">Total Price</label>
            <input type="text" class="form-control" id="total_price" name="total_price" readonly>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <a href="display_stock.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <button type="submit" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Add Stock
            </button>
        </div>
    </form>
</div>

</body>
</html>
