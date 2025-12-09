<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Database 
$host = "localhost";
$port = "5432";
$dbname = "postgres"; 
$user = "postgres";
$db_password = "1234"; 
$conn_string = "host=$host port=$port dbname=$dbname user=$user password=$db_password";


$products_per_page = 5; 


$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}


$offset = ($current_page - 1) * $products_per_page;

$conn = pg_connect($conn_string);

if (!$conn) {
    error_log("Database connection failed: " . pg_last_error());
    
    die("<h1>Server Error: Could not connect to the database.</h1>");
}


$count_sql = "SELECT COUNT(*) FROM products";
$count_result = pg_query($conn, $count_sql);
$total_records = 0;
if ($count_result) {
    $row = pg_fetch_row($count_result);
    $total_records = (int)$row[0];
}


$total_pages = ceil($total_records / $products_per_page);


if ($current_page > $total_pages && $total_pages > 0) {
    header("Location: products.php?page=" . $total_pages);
    exit;
} elseif ($total_records > 0 && $current_page > $total_pages) {
     header("Location: products.php?page=1");
     exit;
}


$product_sql = "SELECT id, name, description FROM products ORDER BY id ASC LIMIT $1 OFFSET $2";
$product_result = pg_query_params($conn, $product_sql, array($products_per_page, $offset));

$products = [];
if ($product_result) {
    $products = pg_fetch_all($product_result);
}


pg_close($conn);

$username = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Products</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f2f5; color: #333; margin: 0; padding: 0; }
        .header { background-color: #007bff; color: white; padding: 15px 50px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
        .header a { color: white; text-decoration: none; padding: 8px 15px; border-radius: 4px; transition: background-color 0.3s; }
        .header a:hover { background-color: #0056b3; }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        h1 { color: #007bff; text-align: center; margin-bottom: 40px; }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; }
        .product-card { background: white; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); overflow: hidden; transition: transform 0.3s, box-shadow 0.3s; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); }
        .product-content { padding: 25px; }
        .product-content h2 { color: #333; margin-top: 0; font-size: 1.5em; }
        .product-content p { color: #666; font-size: 0.95em; line-height: 1.5; }
        .pagination { display: flex; justify-content: center; margin-top: 40px; list-style: none; padding: 0; }
        .pagination li { margin: 0 5px; }
        .pagination li a, .pagination li span { display: block; padding: 10px 15px; border: 1px solid #ccc; border-radius: 5px; text-decoration: none; color: #007bff; transition: background-color 0.3s, color 0.3s; }
        .pagination li a:hover { background-color: #007bff; color: white; border-color: #007bff; }
        .pagination .active span { background-color: #007bff; color: white; border-color: #007bff; font-weight: bold; cursor: default; }
        .no-products { text-align: center; color: #888; font-size: 1.2em; padding: 50px; background-color: white; border-radius: 10px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
    </style>
</head>
<body>
    <div class="header">
        <div>Welcome, <?php echo $username; ?></div>
        <a href="welcome.php">Back to Dashboard</a>
    </div>
    
    <div class="container">
        <h1>Featured Products</h1>

        <?php if (!empty($products)): ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-content">
                            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                            <p><?php echo htmlspecialchars($product['description']); ?></p>
                            <p><a href="product_detail.php?id=<?php echo htmlspecialchars($product['id']); ?>">View Details</a></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <ul class="pagination">
                <?php if ($current_page > 1): ?>
                    <li><a href="?page=1">First</a></li>
                    <li><a href="?page=<?php echo $current_page - 1; ?>">Previous</a></li>
                <?php endif; ?>

                <?php 
                
                $start = max(1, $current_page - 2);
                $end = min($total_pages, $current_page + 2);

                if ($start > 1) {
                    echo '<li><span>...</span></li>';
                }

                for ($i = $start; $i <= $end; $i++): 
                ?>
                    <li class="<?php echo ($i === $current_page) ? 'active' : ''; ?>">
                        <?php if ($i === $current_page): ?>
                            <span><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    </li>
                <?php endfor; ?>
                
                <?php 
                if ($end < $total_pages) {
                    echo '<li><span>...</span></li>';
                }
                ?>

                <?php if ($current_page < $total_pages): ?>
                    <li><a href="?page=<?php echo $current_page + 1; ?>">Next</a></li>
                    <li><a href="?page=<?php echo $total_pages; ?>">Last</a></li>
                <?php endif; ?>
            </ul>
        <?php else: ?>
            <p class="no-products">No products found in the database.</p>
        <?php endif; ?>
    </div>
</body>
</html>