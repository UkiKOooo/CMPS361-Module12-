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

$conn = pg_connect($conn_string);

if (!$conn) {
    error_log("Database connection failed: " . pg_last_error());
    die("<h1>Server Error: Could not connect to the database.</h1>");
}


$activity_sql = "SELECT id, title, description, activity_date FROM activities ORDER BY activity_date DESC";
$activity_result = pg_query($conn, $activity_sql);

$activities = [];
if ($activity_result) {
    $activities = pg_fetch_all($activity_result);
}

pg_close($conn);

$username = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Activities</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f2f5; color: #333; margin: 0; padding: 0; }
        .header { background-color: #ffc107; color: #333; padding: 15px 50px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
        .header a { color: #333; text-decoration: none; padding: 8px 15px; border-radius: 4px; transition: background-color 0.3s; }
        .header a:hover { background-color: #e0a800; }
        .container { max-width: 1000px; margin: 30px auto; padding: 0 20px; }
        h1 { color: #ffc107; text-align: center; margin-bottom: 40px; }
        .activity-card { background: white; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); padding: 25px; margin-bottom: 20px; }
        .activity-card h2 { color: #333; margin-top: 0; font-size: 1.5em; }
        .activity-card p { color: #666; font-size: 0.95em; line-height: 1.5; }
        .activity-date { font-weight: bold; color: #dc3545; margin-top: 10px; display: block; }
        .actions { margin-top: 15px; }
        .actions a { background-color: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; transition: background-color 0.3s; }
        .actions a:hover { background-color: #1e7e34; }
        .no-activities { text-align: center; color: #888; font-size: 1.2em; padding: 50px; background-color: white; border-radius: 10px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
    </style>
</head>
<body>
    <div class="header">
        <div>Welcome, <?php echo $username; ?></div>
        <a href="welcome.php">Back to Dashboard</a>
        <a href="add_activity.php">Add New Activity</a>
    </div>
    
    <div class="container">
        <h1>Upcoming Activities</h1>

        <?php if (!empty($activities)): ?>
            <?php foreach ($activities as $activity): ?>
                <div class="activity-card">
                    <h2><?php echo htmlspecialchars($activity['title']); ?></h2>
                    <p><?php echo nl2br(htmlspecialchars($activity['description'])); ?></p>
                    <span class="activity-date">Date: <?php echo htmlspecialchars($activity['activity_date']); ?></span>
                    <div class="actions">
                        <a href="#">View Details (Not implemented)</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-activities">No activities found in the database.</p>
            <div class="actions" style="text-align: center;"><a href="add_activity.php">Add First Activity</a></div>
        <?php endif; ?>
    </div>
</body>
</html>