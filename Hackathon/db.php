<?php
// db.php
$host     = '127.0.0.1';
$user     = 'root';
$password = 'ALviN-11w3e5gq';        // change if needed
$database = 'tipsy_db';
$port     = 3310;      // or 3307 if that's your XAMPP MySQL port

$conn = new mysqli($host, $user, $password, $database, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
