<?php
// --- DATABASE CONNECTION CONFIG ---

$DB_HOST = "localhost";
$DB_NAME = "u717526728_oD0olVJG3_smky";
$DB_USER = "u717526728_oD0olVJG3_smky";
$DB_PASS = "2SjVEG^*~i;a";

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
