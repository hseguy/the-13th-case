<?php
/**
 * THE 13TH CASE — Main Game Backend API
 * Provides game state management, evidence validation, suspect interrogation,
 * and accusation logic with MySQL database persistence & session fallback.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? $_POST['action'] ?? 'state';

// Predefined Evidence Connections
$EVIDENCE_CONNECTIONS = [
    '1-8' => "The grandfather clock stopped at 11:13 PM, and the Secret Passage Map reveals a hidden corridor connecting the basement directly to the private study. The killer manipulated the clock mechanism to establish a false timeline before fleeing through the passage.",
    '2-9' => "The blood-stained letter addressed to a lawyer matches Richard's torn diary entry stating 'V.G. cannot be trusted'. Richard was in the process of severing ties with his lawyer, Victor Graves.",
    '3-10' => "The empty document cabinet in the study was where Richard kept private audit records. The hidden documents found in the basement lockbox contain the missing pages detailing massive embezzlement by Victor Graves.",
    '5-11' => "The photograph with Graves torn out aligns with the old signing ceremony photo where signatures were forged. Graves has been methodically attempting to erase traces of his fraudulent agreements with Blackwood.",
    '7-6' => "The arsenic poison bottle found in the kitchen and the cloudy residue in the wine glass indicate an attempted poisoning was staged, likely planted to frame Eleanor Blackwood whose marriage was failing.",
    '8-13' => "The Secret Passage entrance had distinct cigarette ash matching the rare Turkish tobacco brand smoked exclusively by Victor Graves, directly placing him at the hidden passage on the night of the murder.",
    '9-12' => "The diary warning against 'V.G.' directly correlates with the modified will. Graves drafted and altered the will to grant himself executive control over Blackwood's estate.",
    '10-12' => "The embezzlement records provide the definitive motive for the forged will: Graves needed to inherit control of the company assets to conceal millions in missing funds before the morning audit.",
    '2-12' => "The blood-stained letter was Richard's formal revocation of Graves' power of attorney, precipitating Graves' desperate move to murder Richard before the new document was signed."
];

// Helper to sort pair keys
function makePairKey($id1, $id2) {
    $a = (int)$id1;
    $b = (int)$id2;
    return ($a < $b) ? "$a-$b" : "$b-$a";
}

// Theme 4: Enforce Active Authenticated Virtual Identity & 10-Min Session Expiry
if (empty($_SESSION['user_id'])) {
    $wasExpired = !empty($_SESSION['session_expired']);
    if ($wasExpired) {
        unset($_SESSION['session_expired']);
    }
    sendJsonResponse([
        'success'         => false,
        'auth_required'   => true,
        'session_expired' => $wasExpired,
        'error'           => $wasExpired
            ? 'Detective badge clearance expired after 10 minutes of inactivity. Please login again.'
            : 'Detective authentication required. Please login or register to begin the investigation.'
    ], 401);
}

switch ($action) {
    case 'init':
        handleInit();
        break;

    case 'state':
        handleState();
        break;

    case 'save':
        handleSave();
        break;

    case 'load':
        handleLoad();
        break;

    case 'compare_evidence':
        handleCompareEvidence($EVIDENCE_CONNECTIONS);
        break;

    case 'interrogate':
        handleInterrogate();
        break;

    case 'accuse':
        handleAccuse();
        break;

    case 'reset':
        handleReset();
        break;

    default:
        sendJsonResponse(['success' => false, 'error' => 'Unknown game action.'], 400);
        break;
}

/**
 * Initialize a fresh game session.
 */
function handleInit() {
    if (empty($_SESSION['user_id'])) {
        sendJsonResponse([
            'success'       => false,
            'auth_required' => true,
            'error'         => 'Detective authentication required. Please login or register to begin the investigation.'
        ], 401);
    }

    $_SESSION['game_active'] = true;
    $_SESSION['current_stage'] = 'intro';
    $_SESSION['game_score'] = 0;
    $_SESSION['mistakes'] = 0;
    $_SESSION['evidence_collected'] = [];
    $_SESSION['locations_investigated'] = [];
    $_SESSION['objects_examined'] = [];
    $_SESSION['suspects_interrogated'] = [
        'eleanor'  => [],
        'thomas'   => [],
        'henry'    => [],
        'margaret' => [],
        'victor'   => []
    ];
    $_SESSION['puzzle_stats'] = ['correct' => 0, 'incorrect' => 0, 'total' => 0];
    $_SESSION['connections_discovered'] = [];
    $_SESSION['started_at'] = time();

    // Check database connection and optionally register session
    $pdo = getDatabaseConnection();
    if ($pdo) {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            $stmt = $pdo->prepare("INSERT INTO game_sessions (user_id, current_stage, score, mistakes, game_status, saved_data) VALUES (?, ?, 0, 0, 'active', ?)");
            $stmt->execute([$userId, 'intro', json_encode($_SESSION)]);
            $_SESSION['db_session_id'] = $pdo->lastInsertId();
        } catch (Exception $e) {
            // DB session table might not exist yet, continue with PHP session
        }
    }

    sendJsonResponse([
        'success'    => true,
        'message'    => 'New investigation initiated.',
        'game_state' => getFullGameState()
    ]);
}

/**
 * Return the current full game state.
 */
function handleState() {
    sendJsonResponse([
        'success'    => true,
        'game_state' => getFullGameState()
    ]);
}

/**
 * Save current game state to session and database.
 */
function handleSave() {
    $clientState = json_decode(file_get_contents('php://input'), true);
    if (is_array($clientState)) {
        if (isset($clientState['score'])) $_SESSION['game_score'] = (int)$clientState['score'];
        if (isset($clientState['mistakes'])) $_SESSION['mistakes'] = (int)$clientState['mistakes'];
        if (isset($clientState['current_stage'])) $_SESSION['current_stage'] = cleanInput($clientState['current_stage']);
        if (isset($clientState['evidence_collected'])) $_SESSION['evidence_collected'] = array_map('intval', $clientState['evidence_collected']);
        if (isset($clientState['locations_investigated'])) $_SESSION['locations_investigated'] = $clientState['locations_investigated'];
        if (isset($clientState['objects_examined'])) $_SESSION['objects_examined'] = $clientState['objects_examined'];
        if (isset($clientState['suspects_interrogated'])) $_SESSION['suspects_interrogated'] = $clientState['suspects_interrogated'];
        if (isset($clientState['connections_discovered'])) $_SESSION['connections_discovered'] = $clientState['connections_discovered'];
    }

    $_SESSION['saved_at'] = time();
    $_SESSION['has_saved_game'] = true;

    // Database persistence
    $pdo = getDatabaseConnection();
    if ($pdo) {
        try {
            $dataJson = json_encode(getFullGameState());
            $userId = $_SESSION['user_id'] ?? null;
            if (!empty($_SESSION['db_session_id'])) {
                $stmt = $pdo->prepare("UPDATE game_sessions SET user_id = ?, current_stage = ?, score = ?, mistakes = ?, saved_data = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([
                    $userId,
                    $_SESSION['current_stage'] ?? 'investigation',
                    $_SESSION['game_score'] ?? 0,
                    $_SESSION['mistakes'] ?? 0,
                    $dataJson,
                    $_SESSION['db_session_id']
                ]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO game_sessions (user_id, current_stage, score, mistakes, game_status, saved_data) VALUES (?, ?, ?, ?, 'active', ?)");
                $stmt->execute([
                    $userId,
                    $_SESSION['current_stage'] ?? 'investigation',
                    $_SESSION['game_score'] ?? 0,
                    $_SESSION['mistakes'] ?? 0,
                    $dataJson
                ]);
                $_SESSION['db_session_id'] = $pdo->lastInsertId();
            }
        } catch (Exception $e) {
            error_log("DB save note: " . $e->getMessage());
        }
    }

    sendJsonResponse([
        'success' => true,
        'message' => 'Investigation progress saved securely.',
        'saved_at'=> date('h:i:s A', $_SESSION['saved_at'])
    ]);
}

/**
 * Restore saved game.
 */
function handleLoad() {
    if (empty($_SESSION['has_saved_game']) && empty($_SESSION['game_active'])) {
        // Try to load latest session from DB
        $pdo = getDatabaseConnection();
        if ($pdo) {
            try {
                $userId = $_SESSION['user_id'] ?? null;
                if ($userId) {
                    $stmt = $pdo->prepare("SELECT * FROM game_sessions WHERE user_id = ? AND game_status = 'active' ORDER BY updated_at DESC LIMIT 1");
                    $stmt->execute([$userId]);
                } else {
                    $stmt = $pdo->query("SELECT * FROM game_sessions WHERE game_status = 'active' ORDER BY updated_at DESC LIMIT 1");
                }
                $row = $stmt->fetch();
                if ($row && !empty($row['saved_data'])) {
                    $saved = json_decode($row['saved_data'], true);
                    if (is_array($saved)) {
                        foreach ($saved as $k => $v) {
                            $_SESSION[$k] = $v;
                        }
                        $_SESSION['db_session_id'] = $row['id'];
                        $_SESSION['has_saved_game'] = true;
                        $_SESSION['game_active'] = true;
                    }
                }
            } catch (Exception $e) {
                // Continue
            }
        }
    }

    if (empty($_SESSION['game_active']) && empty($_SESSION['has_saved_game'])) {
        sendJsonResponse([
            'success' => false,
            'has_save'=> false,
            'error'   => 'No saved investigation file found on this terminal.'
        ], 404);
    }

    sendJsonResponse([
        'success'    => true,
        'has_save'   => true,
        'message'    => 'Investigation file restored successfully.',
        'game_state' => getFullGameState()
    ]);
}

/**
 * Compare two clues on the Evidence Board.
 */
function handleCompareEvidence($connections) {
    $id1 = isset($_POST['id1']) ? (int)$_POST['id1'] : 0;
    $id2 = isset($_POST['id2']) ? (int)$_POST['id2'] : 0;

    if (!$id1 || !$id2 || $id1 === $id2) {
        sendJsonResponse([
            'success' => false,
            'error'   => 'Please select two different pieces of evidence to compare.'
        ], 400);
    }

    $key = makePairKey($id1, $id2);
    $hasConnection = isset($connections[$key]);

    if ($hasConnection) {
        $connectionNote = $connections[$key];
        if (!isset($_SESSION['connections_discovered'])) {
            $_SESSION['connections_discovered'] = [];
        }
        if (!in_array($key, $_SESSION['connections_discovered'])) {
            $_SESSION['connections_discovered'][] = $key;
            $_SESSION['game_score'] = ($_SESSION['game_score'] ?? 0) + 100;
        }

        sendJsonResponse([
            'success'        => true,
            'connected'      => true,
            'explanation'    => $connectionNote,
            'bonus_points'   => 100,
            'total_score'    => $_SESSION['game_score']
        ]);
    } else {
        sendJsonResponse([
            'success'     => true,
            'connected'   => false,
            'explanation' => "After careful examination, you find no direct connection between these two clues. Keep searching the mansion."
        ]);
    }
}

/**
 * Handle suspect interrogation dialogue.
 */
function handleInterrogate() {
    $suspect = cleanInput($_POST['suspect'] ?? '');
    $questionId = (int)($_POST['question_id'] ?? 0);

    if (!$suspect || !$questionId) {
        sendJsonResponse(['success' => false, 'error' => 'Invalid interrogation parameters.'], 400);
    }

    if (!isset($_SESSION['suspects_interrogated'])) {
        $_SESSION['suspects_interrogated'] = [
            'eleanor'  => [],
            'thomas'   => [],
            'henry'    => [],
            'margaret' => [],
            'victor'   => []
        ];
    }

    if (!in_array($questionId, $_SESSION['suspects_interrogated'][$suspect] ?? [])) {
        $_SESSION['suspects_interrogated'][$suspect][] = $questionId;
        $_SESSION['game_score'] = ($_SESSION['game_score'] ?? 0) + 75;
    }

    sendJsonResponse([
        'success'      => true,
        'suspect'      => $suspect,
        'question_id'  => $questionId,
        'points_awarded'=> 75,
        'total_score'  => $_SESSION['game_score'],
        'asked_list'   => $_SESSION['suspects_interrogated'][$suspect]
    ]);
}

/**
 * Validate Final Accusation.
 * Actual murderer: Victor Graves (suspect_id: 5 / 'victor')
 * Motive: Financial fraud / embezzlement
 */
function handleAccuse() {
    $suspectId = (int)($_POST['suspect_id'] ?? 0);
    $motive = cleanInput($_POST['motive'] ?? '');
    $selectedEvidence = isset($_POST['evidence_ids']) ? array_map('intval', (array)$_POST['evidence_ids']) : [];

    // The True Killer is Victor Graves (ID 5)
    $isKillerCorrect = ($suspectId === 5);
    // Correct Motive: Financial Fraud / Embezzlement
    $isMotiveCorrect = (stripos($motive, 'fraud') !== false || stripos($motive, 'embezzle') !== false);

    // Key supporting evidence IDs:
    // 1 (Stopped Clock), 8 (Secret Passage Map), 10 (Embezzlement Records), 12 (Modified Will), 13 (Cigarette Remains)
    $criticalEvidence = [1, 8, 10, 12, 13];
    $matchingCritical = array_intersect($selectedEvidence, $criticalEvidence);
    $hasSufficientEvidence = count($matchingCritical) >= 3;

    // Check mistake limit
    $mistakes = (int)($_SESSION['mistakes'] ?? 0);
    $mistakeLimitReached = ($mistakes >= MISTAKE_LIMIT);

    if ($mistakeLimitReached || !$isKillerCorrect) {
        $endingType = 'CASE_FAILED';
        $title = "CASE FAILED — AN INNOCENT CONDEMNED";
        $summary = "Your accusation missed the true culprit. Victor Graves slipped away through the secret passage into the foggy night with the bearer bonds. An innocent person takes the blame, and Case #13 joins the cold files of Blackwood City Police Department.";
        $scoreAdjustment = -200;
        $badge = "FAILED";
    } elseif ($isKillerCorrect && $isMotiveCorrect && $hasSufficientEvidence) {
        $endingType = 'PERFECT_DETECTIVE';
        $title = "PERFECT DETECTIVE — JUSTICE IN BLACKWOOD CITY";
        $summary = "Flawless deduction, Detective Morgan! You trapped Victor Graves in an inescapable web of facts. The manipulated clock at 11:13 PM, the secret passage map, the rare Turkish cigarette butts, and the altered will conclusively proved that Graves murdered Richard Blackwood to conceal millions in systematic embezzlement. Graves collapsed into the study chair and confessed before Chief Inspector Vance.";
        $scoreAdjustment = 500;
        $badge = "LEGENDARY DETECTIVE";
    } else {
        $endingType = 'CASE_SOLVED';
        $title = "CASE SOLVED — THE LAWYER ARRESTED";
        $summary = "You accurately singled out Victor Graves as the murderer. While some technical evidentiary links remained incomplete, your testimony and witness statements were sufficient for the District Attorney to indict Graves for first-degree murder. Blackwood Mansion can finally rest.";
        $scoreAdjustment = 300;
        $badge = "CASE SOLVED";
    }

    $_SESSION['game_score'] = max(0, ($_SESSION['game_score'] ?? 0) + $scoreAdjustment);
    $_SESSION['current_stage'] = 'ending';
    $_SESSION['ending_type'] = $endingType;

    sendJsonResponse([
        'success'             => true,
        'ending_type'         => $endingType,
        'title'               => $title,
        'summary'             => $summary,
        'badge'               => $badge,
        'score_adjustment'    => $scoreAdjustment,
        'final_score'         => $_SESSION['game_score'],
        'mistakes'            => $mistakes,
        'puzzles_solved'      => $_SESSION['puzzle_stats']['correct'] ?? 0,
        'evidence_count'      => count($_SESSION['evidence_collected'] ?? [])
    ]);
}

/**
 * Reset game.
 */
function handleReset() {
    session_unset();
    session_destroy();
    sendJsonResponse(['success' => true, 'message' => 'Investigation reset.']);
}

/**
 * Build the full state array.
 */
function getFullGameState() {
    return [
        'active'                => !empty($_SESSION['game_active']),
        'current_stage'         => $_SESSION['current_stage'] ?? 'menu',
        'score'                 => $_SESSION['game_score'] ?? 0,
        'mistakes'              => $_SESSION['mistakes'] ?? 0,
        'mistake_limit'         => MISTAKE_LIMIT,
        'evidence_collected'    => $_SESSION['evidence_collected'] ?? [],
        'locations_investigated'=> $_SESSION['locations_investigated'] ?? [],
        'objects_examined'      => $_SESSION['objects_examined'] ?? [],
        'suspects_interrogated' => $_SESSION['suspects_interrogated'] ?? [
            'eleanor'  => [],
            'thomas'   => [],
            'henry'    => [],
            'margaret' => [],
            'victor'   => []
        ],
        'puzzle_stats'          => $_SESSION['puzzle_stats'] ?? ['correct' => 0, 'incorrect' => 0, 'total' => 0],
        'connections_discovered'=> $_SESSION['connections_discovered'] ?? [],
        'saved_at'              => isset($_SESSION['saved_at']) ? date('h:i:s A', $_SESSION['saved_at']) : null
    ];
}

