<?php
$host = getenv('DB_HOST') ?: 'db';
$db   = getenv('DB_NAME') ?: 'marketplace_farmer';
$user = getenv('DB_USER') ?: 'user';
$pass = getenv('DB_PASS') ?: 'userpassword';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$maxRetries = 10;
$retryDelay = 2; // seconds
$connected = false;

error_log("DB: Attempting connection to $dsn");

for ($i = 0; $i < $maxRetries; $i++) {
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        $connected = true;
        error_log("DB: Connected successfully on attempt " . ($i + 1));
        break;
    } catch (\PDOException $e) {
        error_log("DB: Connection failed attempt " . ($i + 1) . ": " . $e->getMessage());
        if ($i === $maxRetries - 1) {
            // Last attempt failed, throw the exception
            throw new \PDOException("Failed to connect to DB after $maxRetries attempts: " . $e->getMessage(), (int)$e->getCode());
        }
        // Wait before retrying
        sleep($retryDelay);
    }
}
?>
