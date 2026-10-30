<?php
/**
 * THE 13TH CASE — Database Connection
 * Uses PDO with prepared statements and exception handling.
 * Provides a graceful fallback if MySQL is not yet configured.
 */

require_once __DIR__ . '/config.php';

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'the_13th_case');
define('DB_USER', 'root');
define('DB_PASS', '');

function getDatabaseConnection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 2,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Fallback: Return null if database is not yet initialized.
        // Game state will be stored seamlessly in PHP session.
        error_log("Database connection notice: " . $e->getMessage());
        return null;
    }
}
