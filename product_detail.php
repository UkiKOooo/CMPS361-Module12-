<?php
session_start();


if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}


$product_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header("Location: products.php");
    exit;
}


$host = "localhost";
$port = "5432";
$dbname = "postgres"; 
$user = "postgres";
$db_password = "1234"; 
$conn_string = "host=$host port=$port dbname=$dbname user=$user password=$db_password";

$conn = pg_connect($conn_string);

if (!$conn) {
    error_log("Database connection failed: " . pg_last_error());
    die("<h1>Server Error: Could not connect to the database.</h1>");
}


$sql = "SELECT id, name, description, price FROM products WHERE id = $1";
$result = pg_query_params($conn, $sql, array($product_id));

$product = null;
if ($result && pg_num_rows($result) === 1) {
    $product = pg_fetch_assoc($result);
}

pg_close($conn);


if (!$product) {
    header("Location: products.php");
    exit;
}


$name = htmlspecialchars($product['name']);
$description = htmlspecialchars($product['description']);
$price = htmlspecialchars(number_format((float)$product['price'], 2));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $name; ?> Details</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; color: #333; margin: 0; padding: 0; }
        .container { max-width: 800px; margin: 50px auto; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        h1 { color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 10px; margin-bottom: 30px; }
        .detail-group { margin-bottom: 20px; padding: 15px; border-left: 5px solid #28a745; background-color: #e9f8f1; border-radius: 5px; }
        .detail-group label { font-weight: bold; color: #007bff; display: block; margin-bottom: 5px; font-size: 1.1em; }
        .detail-group p { margin: 0; font-size: 1em; color: #555; white-space: pre-wrap; }
        .nav-buttons { margin-top: 30px; display: flex; gap: 15px; }
        .nav-buttons a { padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: bold; transition: background-color 0.3s; }
        .btn-back { background-color: #6c757d; color: white; }
        .btn-back:hover { background-color: #5a6268; }
        .btn-home { background-color: #007bff; color: white; }
        .btn-home:hover { background-color: #0056b3; }
        .price { font-size: 1.5em; color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Product Details: <?php echo $name; ?></h1>

        <div class="detail-group">
            <label>Product Name</label>
            <p><?php echo $name; ?></p>
        </div>

        <div class="detail-group">
            <label>Description</label>
            <p><?php echo $description; ?></p>
        </div>

        <div class="detail-group">
            <label>Price</label>
            <p class="price">$<?php echo $price; ?></p>
        </div>

        <div class="nav-buttons">
            <a href="products.php" class="btn-back">← Back to Products</a>
            <a href="welcome.php" class="btn-home">Home (Dashboard)</a>
        </div>
    </div>
</body>
</html>