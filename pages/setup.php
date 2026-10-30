<?php
/**
 * THE 13TH CASE — Database Diagnostics
 * Tests the PDO connection and checks tables created via schema.sql.
 * Accessible at: http://localhost/GAME/pages/setup.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$pdo = getDatabaseConnection();
$dbStatus = ($pdo !== null) ? 'ok' : 'error';
$tables = [];

if ($pdo) {
    try {
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        $dbStatus = 'error';
    }
}

$expectedTables = [
    'users',
    'game_sessions',
    'evidence',
    'player_evidence',
    'suspects',
    'player_interactions'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup Check — The 13th Case</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Text:ital,wght@0,400;0,600;1,400&family=Playfair+Display:wght@400;700;900&family=Special+Elite&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .setup-box {
            max-width: 680px;
            margin: 4rem auto;
            background: var(--bg-card);
            border: 1px solid var(--border-gold);
            padding: 2.5rem;
            box-shadow: var(--shadow-dark);
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            font-family: var(--font-mono);
            font-size: 0.85rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }
        .badge-success { background: rgba(76, 175, 80, 0.2); color: #81c784; border: 1px solid #81c784; }
        .badge-error { background: rgba(244, 67, 54, 0.2); color: #e57373; border: 1px solid #e57373; }
        .table-list { list-style: none; padding: 0; margin: 1rem 0; }
        .table-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--border-subtle);
            font-family: var(--font-mono);
            font-size: 0.9rem;
            display: flex;
            justify-content: space-between;
        }
        .found { color: #81c784; }
        .missing { color: var(--red-light); }
    </style>
</head>
<body>
    <div class="vignette"></div>

    <div class="container">
        <div class="setup-box">
            <h2 class="screen-title" style="font-size: 2rem;">DATABASE STATUS</h2>
            <p style="color: var(--text-secondary); margin-bottom: 1.5rem; font-family: var(--font-mono);">
                Target: <?php echo DB_HOST . ':' . DB_PORT . ' &bull; ' . DB_NAME; ?>
            </p>

            <?php if ($dbStatus === 'ok'): ?>
                <div class="status-badge badge-success">✓ MYSQL CONNECTION ESTABLISHED</div>
                <p style="color: var(--cream); margin-bottom: 1rem;">PDO connected successfully to the local database server.</p>

                <h4 style="color: var(--gold); margin-top: 1.5rem;">TABLE VERIFICATION:</h4>
                <ul class="table-list">
                    <?php foreach ($expectedTables as $t): ?>
                        <li>
                            <span><?php echo htmlspecialchars($t); ?></span>
                            <span class="<?php echo in_array($t, $tables) ? 'found' : 'missing'; ?>">
                                <?php echo in_array($t, $tables) ? '✓ INSTALLED' : '✗ NOT FOUND'; ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <?php if (count(array_intersect($expectedTables, $tables)) < count($expectedTables)): ?>
                    <p style="color: var(--gold-light); font-size: 0.9rem; margin-top: 1rem;">
                        ⚠️ Some tables are missing. Import <code>database/schema.sql</code> in MySQL Workbench to load the tables and seed data.
                    </p>
                <?php else: ?>
                    <p style="color: #81c784; font-size: 0.95rem; margin-top: 1rem;">
                        ✓ All coursework tables and seed records are active.
                    </p>
                <?php endif; ?>

            <?php else: ?>
                <div class="status-badge badge-error">✗ DATABASE NOT CONNECTED</div>
                <p style="color: var(--cream-muted); line-height: 1.8;">
                    MySQL server is currently unreachable at <code><?php echo DB_HOST . ':' . DB_PORT; ?></code>.
                </p>
                <div style="background: rgba(0,0,0,0.5); padding: 1rem; border-left: 3px solid var(--gold); margin: 1.5rem 0;">
                    <p style="color: var(--gold); font-family: var(--font-mono); font-size: 0.85rem;">
                        NOTE: The game is equipped with seamless PHP session fallback. You can play the full mystery right now even before configuring MySQL!
                    </p>
                </div>
                <h4 style="color: var(--gold); margin-bottom: 0.5rem;">To connect MySQL:</h4>
                <ol style="color: var(--text-secondary); padding-left: 1.25rem; line-height: 1.8;">
                    <li>Open <strong>XAMPP Control Panel</strong> and click <strong>Start</strong> next to MySQL.</li>
                    <li>Open <strong>MySQL Workbench</strong> and execute <code>database/schema.sql</code>.</li>
                    <li>Refresh this diagnostic screen.</li>
                </ol>
            <?php endif; ?>

            <div style="margin-top: 2rem; text-align: center;">
                <a href="../index.php" class="btn btn-primary">← RETURN TO GAME</a>
            </div>
        </div>
    </div>
</body>
</html>
