<?php
/**
 * THE 13TH CASE — Database Connection
 * Uses PDO with prepared statements and exception handling.
 * Automatically tries port 3306 and port 3307 for seamless local compatibility.
 */

require_once __DIR__ . '/config.php';

if (!defined('DB_HOST')) define('DB_HOST', '127.0.0.1');
if (!defined('DB_NAME')) define('DB_NAME', 'the_13th_case');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

function getDatabaseConnection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $portsToTry = [3306, 3307];

    foreach ($portsToTry as $port) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . $port . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_TIMEOUT            => 1,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            if (!defined('ACTIVE_DB_PORT')) {
                define('ACTIVE_DB_PORT', $port);
            }
            return $pdo;
        } catch (PDOException $e) {
            // Try next port or fallback
        }
    }

    return null;
}
