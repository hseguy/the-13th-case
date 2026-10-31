<?php
/**
 * THE 13TH CASE — Authentication Service
 * Handles Detective Registration, Login, Logout, and Session Verification.
 * Supports prepared statements, bcrypt hashing, and guest play.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? $_POST['action'] ?? 'check';

switch ($action) {
    case 'register':
        handleRegister();
        break;

    case 'login':
        handleLogin();
        break;

    case 'logout':
        handleLogout();
        break;

    case 'check':
        handleCheck();
        break;

    default:
        sendJsonResponse(['success' => false, 'error' => 'Invalid authentication action.'], 400);
        break;
}

/**
 * Register a new Detective Account.
 */
function handleRegister() {
    $username = trim((string)($_POST['username'] ?? ''));
    $email    = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if (empty($username) || strlen($username) < 3) {
        sendJsonResponse([
            'success' => false,
            'error'   => 'Detective username must be at least 3 characters long.'
        ], 400);
    }

    if (empty($password) || strlen($password) < 4) {
        sendJsonResponse([
            'success' => false,
            'error'   => 'Access code / password must be at least 4 characters long.'
        ], 400);
    }

    $pdo = getDatabaseConnection();
    if (!$pdo) {
        // Fallback: If MySQL is not running yet, register user in session so they can test immediately
        $_SESSION['user_id'] = 999;
        $_SESSION['username'] = cleanInput($username);
        $_SESSION['is_guest'] = false;

        sendJsonResponse([
            'success' => true,
            'message' => 'Detective badge registered (Session Mode). Please start MySQL in XAMPP to persist to database.',
            'user'    => [
                'id'       => 999,
                'username' => cleanInput($username),
                'is_guest' => false
            ]
        ]);
    }

    try {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            sendJsonResponse([
                'success' => false,
                'error'   => 'A detective with this badge name already exists. Please choose another or login.'
            ], 409);
        }

        // Hash password with bcrypt
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // Insert into users table
        $hasEmailCol = false;
        try {
            $colCheck = $pdo->query("SHOW COLUMNS FROM users LIKE 'email'");
            if ($colCheck->fetch()) {
                $hasEmailCol = true;
            }
        } catch (Exception $e) {}

        if ($hasEmailCol) {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email ?: ($username . '@blackwood.police'), $passwordHash]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
            $stmt->execute([$username, $passwordHash]);
        }

        $userId = (int)$pdo->lastInsertId();

        // Establish session
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['is_guest'] = false;

        sendJsonResponse([
            'success' => true,
            'message' => 'Detective credentials issued successfully.',
            'user'    => [
                'id'       => $userId,
                'username' => $username,
                'is_guest' => false
            ]
        ]);

    } catch (PDOException $e) {
        error_log("Register error: " . $e->getMessage());
        sendJsonResponse([
            'success' => false,
            'error'   => 'Database error during registration: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Login an existing Detective.
 */
function handleLogin() {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        sendJsonResponse([
            'success' => false,
            'error'   => 'Please provide both detective badge username and password.'
        ], 400);
    }

    $pdo = getDatabaseConnection();
    if (!$pdo) {
        // Fallback for session
        $_SESSION['user_id'] = 999;
        $_SESSION['username'] = cleanInput($username);
        $_SESSION['is_guest'] = false;

        sendJsonResponse([
            'success' => true,
            'message' => 'Logged in (Session Mode).',
            'user'    => [
                'id'       => 999,
                'username' => cleanInput($username),
                'is_guest' => false
            ]
        ]);
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user) {
            sendJsonResponse([
                'success' => false,
                'error'   => 'No detective record found with that badge name.'
            ], 401);
        }

        // Support password_hash column or password column
        $hash = $user['password_hash'] ?? $user['password'] ?? '';
        if (!password_verify($password, $hash)) {
            sendJsonResponse([
                'success' => false,
                'error'   => 'Invalid access credentials. Access denied.'
            ], 401);
        }

        // Set session
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['username'] = (string)$user['username'];
        $_SESSION['is_guest'] = false;

        // Restore latest saved game progress if exists
        $savedState = null;
        try {
            $sessStmt = $pdo->prepare("SELECT * FROM game_sessions WHERE user_id = ? AND game_status = 'active' ORDER BY updated_at DESC LIMIT 1");
            $sessStmt->execute([$user['id']]);
            $sessRow = $sessStmt->fetch();
            if ($sessRow && !empty($sessRow['saved_data'])) {
                $savedState = json_decode($sessRow['saved_data'], true);
                if (is_array($savedState)) {
                    foreach ($savedState as $k => $v) {
                        $_SESSION[$k] = $v;
                    }
                    $_SESSION['db_session_id'] = $sessRow['id'];
                    $_SESSION['game_active'] = true;
                    $_SESSION['has_saved_game'] = true;
                }
            }
        } catch (Exception $e) {
            error_log("Session restore error: " . $e->getMessage());
        }

        sendJsonResponse([
            'success'     => true,
            'message'     => 'Detective clearance verified.',
            'user'        => [
                'id'       => $user['id'],
                'username' => $user['username'],
                'is_guest' => false
            ],
            'saved_state' => $savedState
        ]);

    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        sendJsonResponse([
            'success' => false,
            'error'   => 'Database error during authentication.'
        ], 500);
    }
}

/**
 * Logout current user and destroy virtual identity credentials.
 */
function handleLogout() {
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();

    sendJsonResponse([
        'success' => true,
        'message' => 'Signed off duty. Session cleared and credentials destroyed.'
    ]);
}

/**
 * Check current logged in user status.
 */
function handleCheck() {
    $isLoggedIn = !empty($_SESSION['user_id']);
    sendJsonResponse([
        'success'     => true,
        'logged_in'   => $isLoggedIn,
        'user'        => $isLoggedIn ? [
            'id'       => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'is_guest' => !empty($_SESSION['is_guest'])
        ] : null
    ]);
}

