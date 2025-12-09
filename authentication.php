<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: welcome.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}


$host = "localhost";
$port = "5432";
$dbname = "postgres"; 
$user = "postgres";
$db_password = "1234"; 


$username = $_POST['username'];
$password = $_POST['password'];

$conn_string = "host=$host port=$port dbname=$dbname user=$user password=$db_password";
$conn = pg_connect($conn_string);

if (!$conn) {
    
    error_log("Database connection failed: " . pg_last_error());
    $_SESSION['login_error'] = "A server error occurred. Please try again later.";
    header("Location: login.php");
    exit;
}


$sql = "SELECT id, username, password FROM users WHERE username = $1";
$result = pg_query_params($conn, $sql, array($username));

if (!$result || pg_num_rows($result) !== 1) {
    
    pg_close($conn); 
    $_SESSION['login_error'] = "Invalid username or password.";
    header("Location: login.php");
    exit;
}


$user = pg_fetch_assoc($result);
$stored_hash = $user['password'];


if (hash_equals($stored_hash, crypt($password, $stored_hash))) {

    $_SESSION['logged_in'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];

    pg_close($conn); 
    header("Location: welcome.php");
    exit;
} else {
    
    pg_close($conn); 
    $_SESSION['login_error'] = "Invalid username or password.";
    header("Location: login.php");
    exit;
}
?>