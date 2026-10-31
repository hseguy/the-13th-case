<?php
/**
 * THE 13TH CASE — Banana API Integration Service
 * Official API: https://marcconrad.com/uob/banana/api.php
 *
 * Handles:
 * 1. Fetching a new puzzle from the official Banana API.
 * 2. Securely storing the solution in $_SESSION['banana_solution'].
 * 3. Returning ONLY the puzzle image URL to the client.
 * 4. Validating player answers on the server side.
 * 5. Managing score and tracking stats.
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');

// Initialize puzzle session storage if not already set
if (!isset($_SESSION['puzzle_stats'])) {
    $_SESSION['puzzle_stats'] = [
        'correct'   => 0,
        'incorrect' => 0,
        'total'     => 0
    ];
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'fetch';

switch ($action) {
    case 'fetch':
        handleFetch();
        break;

    case 'validate':
        handleValidate();
        break;

    case 'status':
        handleStatus();
        break;

    default:
        sendJsonResponse([
            'success' => false,
            'error'   => 'Invalid API action requested.'
        ], 400);
        break;
}

/**
 * Fetch a new puzzle from the official University Banana API.
 */
function handleFetch() {
    $apiUrl = BANANA_API_URL;
    $rawResponse = null;
    $startTime = microtime(true);

    // Use cURL for reliable network fetching with timeout
    if (function_exists('curl_init')) {
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'The-13th-Case-Detective-Game/1.0');
        $rawResponse = curl_exec($ch);
        $curlErr = curl_error($ch);
        curl_close($ch);
    }

    // Fallback to file_get_contents if cURL was empty
    if (!$rawResponse && ini_get('allow_url_fopen')) {
        $context = stream_context_create([
            'http' => ['timeout' => 8],
            'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false]
        ]);
        $rawResponse = @file_get_contents($apiUrl, false, $context);
    }

    if (!$rawResponse) {
        sendJsonResponse([
            'success' => false,
            'error'   => 'The University Banana API is temporarily unreachable. Please check your network connection and click Retry.',
            'retry'   => true
        ], 503);
    }

    $data = json_decode($rawResponse, true);

    // Validate that response contains expected fields
    if (!is_array($data) || !isset($data['question']) || !isset($data['solution'])) {
        sendJsonResponse([
            'success' => false,
            'error'   => 'Invalid puzzle format received from official API.',
            'retry'   => true
        ], 502);
    }

    // Store solution securely in PHP server-side session
    $_SESSION['banana_solution'] = (int)$data['solution'];
    $_SESSION['banana_question'] = (string)$data['question'];
    $_SESSION['banana_timestamp'] = time();
    $_SESSION['banana_answered'] = false;

    $latencyMs = round((microtime(true) - $startTime) * 1000, 2);
    header("X-Banana-API-Latency-Ms: {$latencyMs}");
    header("X-Proxy-Provider: The-13th-Case-Enterprise-Gateway");

    // Return ONLY the question URL to prevent client-side inspection
    sendJsonResponse([
        'success'      => true,
        'question_url' => $data['question'],
        'stats'        => $_SESSION['puzzle_stats'],
        'latency_ms'   => $latencyMs
    ]);
}

/**
 * Validate the player's numerical answer.
 */
function handleValidate() {
    if (!isset($_SESSION['banana_solution'])) {
        sendJsonResponse([
            'success' => false,
            'error'   => 'No active puzzle found. Please fetch a new puzzle first.'
        ], 400);
    }

    if (!empty($_SESSION['banana_answered'])) {
        sendJsonResponse([
            'success' => false,
            'error'   => 'This puzzle has already been answered. Please fetch a new puzzle.'
        ], 400);
    }

    $answer = $_POST['answer'] ?? null;
    if ($answer === null || !is_numeric($answer)) {
        sendJsonResponse([
            'success' => false,
            'error'   => 'Please provide a valid numerical answer.'
        ], 400);
    }

    $playerAnswer = (int)$answer;
    $correctSolution = (int)$_SESSION['banana_solution'];
    $isCorrect = ($playerAnswer === $correctSolution);

    // Only lock as answered when solved correctly, or allow retry on incorrect
    if ($isCorrect) {
        $_SESSION['banana_answered'] = true;
    } else {
        $_SESSION['banana_answered'] = false;
    }
    $_SESSION['puzzle_stats']['total']++;

    if ($isCorrect) {
        $_SESSION['puzzle_stats']['correct']++;
        $scoreChange = 150;
        $message = "Brilliant deduction, Detective! The puzzle mechanism unlocks.";
    } else {
        $_SESSION['puzzle_stats']['incorrect']++;
        $scoreChange = -25;
        $message = "Incorrect deciphering. The mechanism jams, costing valuable investigation time.";
    }

    // Update global game score in session if exists
    if (isset($_SESSION['game_score'])) {
        $_SESSION['game_score'] = max(0, $_SESSION['game_score'] + $scoreChange);
    } else {
        $_SESSION['game_score'] = max(0, $scoreChange);
    }

    if (!$isCorrect && isset($_SESSION['mistakes'])) {
        $_SESSION['mistakes']++;
    }

    sendJsonResponse([
        'success'         => true,
        'correct'         => $isCorrect,
        'message'         => $message,
        'player_answer'   => $playerAnswer,
        'score_change'    => $scoreChange,
        'total_score'     => $_SESSION['game_score'] ?? 0,
        'mistakes'        => $_SESSION['mistakes'] ?? 0,
        'stats'           => $_SESSION['puzzle_stats']
    ]);
}

/**
 * Return current puzzle stats.
 */
function handleStatus() {
    sendJsonResponse([
        'success' => true,
        'stats'   => $_SESSION['puzzle_stats'] ?? ['correct' => 0, 'incorrect' => 0, 'total' => 0],
        'score'   => $_SESSION['game_score'] ?? 0,
        'mistakes'=> $_SESSION['mistakes'] ?? 0
    ]);
}

