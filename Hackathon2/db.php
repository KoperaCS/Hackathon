<?php
// db.php

$host     = '127.0.0.1';
$user     = 'root';
$password = '';        // change if needed
$database = 'tipsy_db';
$port     = 3306;      // or 3307 if that's your XAMPP MySQL port

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $password, $database, $port);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    die("Connection failed: " . $e->getMessage());
}
