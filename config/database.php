<?php
/**
 * THE 13TH CASE — Database Connection
 * Uses PDO with prepared statements and exception handling.
 * Configured for port 3306 with user root and password yugesh@123.
 * Supports auto-fallback and automatic schema initialization.
 */

require_once __DIR__ . '/config.php';

if (!defined('DB_HOST')) define('DB_HOST', '127.0.0.1');
if (!defined('DB_NAME')) define('DB_NAME', 'the_13th_case');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', 'yugesh@123');

function getDatabaseConnection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $portsToTry = [3306, 3307];
    // Try user's root password 'yugesh@123' first, then empty fallback
    $passwordsToTry = [DB_PASS, ''];

    foreach ($portsToTry as $port) {
        foreach ($passwordsToTry as $pwd) {
            try {
                // First connect to server
                $serverDsn = "mysql:host=" . DB_HOST . ";port=" . $port . ";charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_TIMEOUT            => 2,
                ];

                $serverPdo = new PDO($serverDsn, DB_USER, $pwd, $options);

                // Ensure database exists
                $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $serverPdo->exec("USE `" . DB_NAME . "`");

                // Check if tables are initialized, if not initialize from schema.sql
                $tableCheck = $serverPdo->query("SHOW TABLES LIKE 'evidence'");
                if (!$tableCheck->fetch()) {
                    $schemaFile = __DIR__ . '/../database/schema.sql';
                    if (file_exists($schemaFile)) {
                        $sql = file_get_contents($schemaFile);
                        $serverPdo->exec($sql);
                    }
                }

                if (!defined('ACTIVE_DB_PORT')) {
                    define('ACTIVE_DB_PORT', $port);
                }
                if (!defined('ACTIVE_DB_NAME')) {
                    define('ACTIVE_DB_NAME', DB_NAME);
                }

                $pdo = $serverPdo;
                return $pdo;

            } catch (PDOException $e) {
                // Try next password/port combination
                continue;
            }
        }
    }

    return null;
}
