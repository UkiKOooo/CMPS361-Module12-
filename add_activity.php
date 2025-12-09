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
        

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $activity_date = trim($_POST['activity_date'] ?? ''); 

        if (empty($title) || empty($activity_date)) {
            $error_message = "Please provide a valid activity title and date.";
        } else {
            

            $sql = "INSERT INTO activities (title, description, activity_date) VALUES ($1, $2, $3)";
            
            
            $result = pg_query_params($conn, $sql, array($title, $description, $activity_date));

            if ($result) {
                $success_message = "Activity '{$title}' successfully added to the database!";
                

                $title = $description = $activity_date = '';
            } else {
                $error_message = "Failed to add activity. Database error: " . pg_last_error($conn);
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
    <title>Add New Activity</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; flex-direction: column; align-items: center; min-height: 100vh; background-color: #f0f2f5; padding-top: 50px; }
        .container { max-width: 600px; width: 100%; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        h1 { color: #ffc107; text-align: center; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
        .form-group input[type="text"], .form-group textarea, .form-group input[type="date"] { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        .form-group textarea { resize: vertical; height: 100px; }
        .btn-submit { width: 100%; padding: 12px; background-color: #ffc107; color: #333; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; transition: background-color 0.3s; }
        .btn-submit:hover { background-color: #e0a800; }
        .message { padding: 10px; border-radius: 6px; margin-bottom: 20px; text-align: center; }
        .success { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #ffc107; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add New Activity</h1>

        <?php if ($success_message): ?>
            <p class="message success"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <p class="message error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <form action="add_activity.php" method="post">
            <div class="form-group">
                <label for="title">Activity Title:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="activity_date">Date:</label>
                <input type="date" id="activity_date" name="activity_date" value="<?php echo htmlspecialchars($activity_date ?? ''); ?>" required>
            </div>
            <button type="submit" class="btn-submit">Add Activity</button>
        </form>

        <a href="activities.php" class="back-link">← Back to Activities List</a>
    </div>
</body>
</html>