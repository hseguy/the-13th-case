<?php
/**
 * THE 13TH CASE — Core Configuration
 * 1947 Blackwood City Detective Mystery
 */

// Enterprise Session Configuration (10 Minutes Expiry)
define('SESSION_LIFETIME', 600); // 10 minutes in seconds

if (session_status() === PHP_SESSION_NONE) {
    // Ensure session directory is writable across environments
    $savePath = session_save_path();
    if (empty($savePath) || !@is_writable($savePath)) {
        $localTmp = __DIR__ . '/../tmp';
        if (!is_dir($localTmp)) {
            @mkdir($localTmp, 0777, true);
        }
        if (is_dir($localTmp) && @is_writable($localTmp)) {
            @session_save_path($localTmp);
        }
    }

    // Enterprise session security settings (Virtual Identity hardening)
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'domain'   => '',
            'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    } else {
        session_set_cookie_params(SESSION_LIFETIME, '/; samesite=Lax', '', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', true);
    }

    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    @session_start();

    // 10-Minute Inactivity Timeout Check (Theme 4: Virtual Identity Security)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        @session_destroy();
        @session_start();
        $_SESSION['session_expired'] = true;
    }
    $_SESSION['last_activity'] = time();
}

require_once __DIR__ . '/environment.php';

// Application Constants
define('GAME_TITLE', 'THE 13TH CASE');
define('GAME_SUBTITLE', 'A Murder. Five Suspects. One Truth.');
define('GAME_TAGLINE', '"The rain never stops in this city, and neither does the truth."');
define('GAME_VERSION', '1.0.0');
define('MISTAKE_LIMIT', 13);
define('BANANA_API_URL', 'https://marcconrad.com/uob/banana/api.php');

// Evidence Importance Levels
define('IMPORTANCE_LOW', 1);
define('IMPORTANCE_MEDIUM', 2);
define('IMPORTANCE_HIGH', 3);
define('IMPORTANCE_CRITICAL', 4);

// Helper for standard JSON responses
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

// Sanitize string input
function cleanInput($input) {
    if (is_array($input)) {
        return array_map('cleanInput', $input);
    }
    return htmlspecialchars(trim((string)$input), ENT_QUOTES, 'UTF-8');
}

