<?php

session_start();


if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}


$host = "localhost";
$port = "5432";
$dbname = "postgres"; 
$user = "postgres";
$db_password = "1234"; 
$conn_string = "host=$host port=$port dbname=$dbname user=$user password=$db_password";

$success_message = '';
$error_message = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = pg_connect($conn_string);

    if (!$conn) {
        $error_message = "Database connection failed: Could not connect to the server.";
        error_log("Database connection failed: " . pg_last_error());
    } else {
        
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        $price = filter_var($_POST['price'] ?? 0.00, FILTER_VALIDATE_FLOAT);

        if (empty($name) || $price === false || $price < 0) {
            $error_message = "Please provide a valid product name and a non-negative price.";
        } else {
            
            $sql = "INSERT INTO products (name, description, price) VALUES ($1, $2, $3)";
            
            
            $result = pg_query_params($conn, $sql, array($name, $description, $price));

            if ($result) {
                $success_message = "Product '{$name}' successfully added to the database!";
                
                $name = $description = $price = '';
            } else {
                $error_message = "Failed to add product. Database error: " . pg_last_error($conn);
            }
        }
        pg_close($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; flex-direction: column; align-items: center; min-height: 100vh; background-color: #f0f2f5; padding-top: 50px; }
        .container { max-width: 600px; width: 100%; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        h1 { color: #007bff; text-align: center; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
        .form-group input[type="text"], .form-group textarea, .form-group input[type="number"] { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        .form-group textarea { resize: vertical; height: 100px; }
        .btn-submit { width: 100%; padding: 12px; background-color: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; transition: background-color 0.3s; }
        .btn-submit:hover { background-color: #1e7e34; }
        .message { padding: 10px; border-radius: 6px; margin-bottom: 20px; text-align: center; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add New Product</h1>

        <?php if ($success_message): ?>
            <p class="message success"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <p class="message error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <form action="add_product.php" method="post">
            <div class="form-group">
                <label for="name">Product Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($price ?? '0.00'); ?>" required>
            </div>
            <button type="submit" class="btn-submit">Add Product</button>
        </form>

        <a href="products.php" class="back-link">← Back to Products List</a>
    </div>
</body>
</html>