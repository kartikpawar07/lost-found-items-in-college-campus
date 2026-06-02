<?php
// Database connection configuration using PDO

$host = 'localhost';
$db   = 'lost_found';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Define BASE_URL (mapped to local server directory name)
if (!defined('BASE_URL')) {
    define('BASE_URL', '/lost&found/');
}

// Global functions for session tracking & UI safety
if (!function_exists('sanitize_output')) {
    function sanitize_output($data) {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('get_lost_uid')) {
    function get_lost_uid($id) {
        return 'LST-' . str_pad($id, 4, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('get_found_uid')) {
    function get_found_uid($id) {
        return 'FND-' . str_pad($id, 4, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('get_claim_uid')) {
    function get_claim_uid($id) {
        return 'CLM-' . str_pad($id, 4, '0', STR_PAD_LEFT);
    }
}
?>
