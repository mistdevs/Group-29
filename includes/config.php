<?php
// includes/config.php
// Edit these values to match your local DB credentials
$DB_HOST = '127.0.0.1';
$DB_NAME = 'uums_db';
$DB_USER = 'root';        // XAMPP default (change if you created another user)
$DB_PASS = '';            // XAMPP default is empty for root
$DB_PORT = 3306;

$charset = 'utf8mb4';
$dsn = "mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    // For learning show message. In production, log and show generic message.
    die('Database connection failed: ' . $e->getMessage());
}
?>
