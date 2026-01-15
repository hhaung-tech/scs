<?php
$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '';
$dbname = getenv('DB_NAME') ?: 'school_climate_survey';
$user = getenv('DB_USER') ?: 'postgres';
$password = getenv('DB_PASSWORD') ?: 'r00t';

try {
    $dsn = "pgsql:host=$host;dbname=$dbname";
    if ($port !== '') {
        $dsn .= ";port=$port";
    }
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}
?>


