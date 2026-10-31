<?php
/**
 * THE 13TH CASE — Database & Schema Health Check Utility
 * Verifies PDO connectivity, schema tables, and seed data integrity.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = getDatabaseConnection();

if (!$pdo) {
    sendJsonResponse([
        'status'    => 'offline',
        'database'  => 'unavailable',
        'mode'      => 'session_fallback',
        'message'   => 'MySQL service is not running. Game operates in resilient PHP session mode.',
        'timestamp' => date('c')
    ], 200);
}

try {
    $requiredTables = ['users', 'game_sessions', 'evidence', 'suspects', 'player_interactions'];
    $tableStatus = [];

    foreach ($requiredTables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
        $exists = (bool)$stmt->fetch();
        $count = 0;
        if ($exists) {
            $countStmt = $pdo->query("SELECT COUNT(*) FROM `{$table}`");
            $count = (int)$countStmt->fetchColumn();
        }
        $tableStatus[$table] = [
            'exists' => $exists,
            'rows'   => $count
        ];
    }

    sendJsonResponse([
        'status'       => 'healthy',
        'database'     => 'connected',
        'port'         => $GLOBALS['db_connected_port'] ?? 3306,
        'tables'       => $tableStatus,
        'evidence_ok'  => ($tableStatus['evidence']['rows'] >= 13),
        'suspects_ok'  => ($tableStatus['suspects']['rows'] >= 5),
        'timestamp'    => date('c')
    ], 200);

} catch (Exception $e) {
    sendJsonResponse([
        'status'    => 'error',
        'error'     => $e->getMessage(),
        'timestamp' => date('c')
    ], 500);
}
