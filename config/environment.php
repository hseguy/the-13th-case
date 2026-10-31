<?php
/**
 * THE 13TH CASE — Environment & Runtime Diagnostics
 * Configures runtime error handling, environment detection, and execution headers.
 */

if (!defined('APP_ENV')) {
    $isLocal = in_array($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1', ['127.0.0.1', '::1']);
    define('APP_ENV', $isLocal ? 'development' : 'production');
}

if (APP_ENV === 'development') {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
}

/**
 * Diagnostic logger for enterprise audits.
 */
function logDetectiveAudit($action, $details = []) {
    $logDir = __DIR__ . '/../tmp';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0777, true);
    }
    $logFile = $logDir . '/game_audit.log';
    $entry = sprintf(
        "[%s] [%s] %s %s\n",
        date('Y-m-d H:i:s'),
        $_SESSION['username'] ?? 'GUEST',
        strtoupper($action),
        json_encode($details)
    );
    @file_put_contents($logFile, $entry, FILE_APPEND);
}

