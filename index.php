<?php
/**
 * THE 13TH CASE — Main Application Entry Point
 * 1947 Blackwood City Detective Mystery
 * University Software for Enterprise Coursework
 */
require_once __DIR__ . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>THE 13TH CASE — Detective Mystery</title>
    <meta name="description" content="A 1947 noir detective mystery browser game featuring Banana API puzzle integration and interactive suspect interrogation.">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Text:ital,wght@0,400;0,600;1,400&family=Playfair+Display:wght@400;700;900&family=Special+Elite&display=swap" rel="stylesheet">

    <!-- Noir Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
</head>
<body>

    <!-- Atmospheric Overlays -->
    <div class="vignette"></div>

    <!-- Top In-Game Navigation Bar (hidden on main menu) -->
    <header class="game-nav" id="game-nav" style="display: none;">
        <div class="nav-brand" onclick="showScreen('screen-menu')">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <span class="nav-title">THE 13TH CASE</span>
        </div>

        <ul class="nav-links">
            <li><a class="nav-link" data-screen="screen-map">🏰 MANSION MAP</a></li>
            <li><a class="nav-link" data-screen="screen-evidence">📁 EVIDENCE</a></li>
            <li><a class="nav-link" data-screen="screen-board">📋 EVIDENCE BOARD</a></li>
            <li><a class="nav-link" data-screen="screen-suspects">👥 SUSPECTS</a></li>
            <li><a class="nav-link" data-screen="screen-accusation">⚖️ ACCUSE</a></li>
            <li><a class="nav-link" data-screen="screen-settings">⚙️ SETTINGS</a></li>
        </ul>

        <div class="nav-status">
            <span class="auth-badge" id="nav-user-badge" onclick="openAuthModal()" title="Detective Credentials">
                👤 <span id="nav-user-name">GUEST</span>
            </span>
            <span class="nav-score" id="nav-score">SCORE: 0</span>
            <span class="nav-mistakes" id="nav-mistakes">MISTAKES: 0/13</span>
        </div>
    </header>

    <!-- =========================================================================
         SCREEN 1: MAIN MENU (Styled exactly according to reference image)
         ========================================================================= -->
    <section id="screen-menu" class="screen active">
        <div class="menu-hero">
            <!-- Glowing Magnifying Glass Badge -->
            <div class="menu-badge">
                <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </div>

            <!-- Title & Slogan -->
            <h1 class="menu-title">THE 13TH CASE</h1>
            <p class="menu-quote">"The rain never stops in this city, and neither does the truth."</p>
            <p class="menu-subtext">WASD — MOVE | MOUSE — LOOK | E — EXAMINE | SPACE — JUMP</p>

            <!-- Menu Actions -->
            <div class="menu-actions">
                <button class="btn btn-primary" id="btn-begin">BEGIN INVESTIGATION</button>
                <button class="btn" onclick="continueInvestigation()">CONTINUE INVESTIGATION</button>
                <button class="btn" onclick="openAuthModal()" id="btn-menu-auth">DETECTIVE LOGIN / REGISTER</button>
                <button class="btn" onclick="showScreen('screen-howtoplay')">HOW TO PLAY</button>
                <button class="btn" onclick="showScreen('screen-settings')">SETTINGS</button>
            </div>

            <footer class="menu-footer">
                BLACKWOOD CITY — 13 OCTOBER 1947 &bull; SOFTWARE FOR ENTERPRISE
            </footer>
        </div>
    </section>

    <!-- =========================================================================
         SCREEN 2: STORY INTRODUCTION
         ========================================================================= -->
    <section id="screen-intro" class="screen">
        <div class="container">
            <div class="screen-header">
                <h2 class="screen-title">CASE INTRODUCTION</h2>
                <p class="screen-subtitle">DETECTIVE ALEX MORGAN'S LOG &bull; MIDNIGHT CALL</p>
            </div>

            <div class="dossier-card">
                <div class="dossier-stamp">CONFIDENTIAL</div>
                
                <div class="dossier-typewriter" id="intro-typewriter-text" style="font-family: var(--font-mono); font-size: 0.95rem; line-height: 1.85; color: var(--cream); white-space: normal;">
                    <p style="color: var(--gold); font-weight: bold; margin-bottom: 0.75rem; letter-spacing: 2px;">
                        BLACKWOOD CITY POLICE DEPARTMENT — CONFIDENTIAL CASE REPORT<br>
                        DATE: OCTOBER 13, 1947 &bull; TIME: 00:15 HOURS
                    </p>
                    <p style="font-style: italic; color: var(--text-secondary); margin-bottom: 1rem;">
                        "The rain never stops in this city, and neither does the truth."
                    </p>
                    <p style="margin-bottom: 1rem;">
                        At midnight, a frantic call summoned Detective Alex Morgan to Blackwood Manor. Richard Blackwood, shipping tycoon and financier, has been found dead inside his private study.
                    </p>
                    <p style="margin-bottom: 1rem;">
                        The single entrance was deadbolted from within. The grandfather clock stopped at exactly 11:13 PM. A confidential audit portfolio has disappeared from the safe, and five people were in the mansion.
                    </p>
                    <p style="color: var(--gold-light);">
                        Search the manor, examine the crime scene, crack the mathematical security ciphers, and interrogate the suspects. Case #13 awaits your deduction.
                    </p>
                </div>

                <!-- VICTIM DOSSIER WITH EVIDENCE PHOTOGRAPH -->
                <div class="victim-dossier-wrapper">
                    <div style="flex-shrink: 0; text-align: center;">
                        <div style="position: relative; display: inline-block; padding: 6px; background: #161616; border: 2px solid var(--gold); box-shadow: 0 0 25px rgba(201, 168, 76, 0.3);">
                            <img src="assets/images/richard_blackwood.jpg" alt="Richard Blackwood - Murder Victim" style="width: 145px; height: 185px; object-fit: cover; display: block; filter: contrast(1.05) brightness(0.95); border: 1px solid rgba(255,255,255,0.15);">
                            <span style="position: absolute; bottom: 12px; right: 10px; background: var(--red); color: #fff; font-family: var(--font-mono); font-size: 0.65rem; padding: 3px 8px; letter-spacing: 1.5px; font-weight: bold; border: 1px solid rgba(255,255,255,0.3); transform: rotate(-6deg); box-shadow: 0 2px 8px rgba(0,0,0,0.8);">DECEASED</span>
                        </div>
                        <p style="font-family: var(--font-mono); font-size: 0.7rem; color: var(--gold); margin-top: 0.6rem; letter-spacing: 1.5px;">EVIDENCE FILE #13-A</p>
                    </div>

                    <div style="flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.5rem;">
                                <div>
                                    <h3 style="font-size: 1.5rem; color: var(--gold); letter-spacing: 2px; margin-bottom: 0.2rem;">RICHARD BLACKWOOD</h3>
                                    <p style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--red-light); letter-spacing: 2px; text-transform: uppercase;">VICTIM &bull; FOUND OCT 13, 1947 &bull; AGE 54</p>
                                </div>
                                <span class="badge" style="border-color: var(--red); color: var(--red-light); background: rgba(139, 58, 58, 0.25);">HOMICIDE</span>
                            </div>
                            <p style="color: var(--cream-muted); font-size: 0.95rem; line-height: 1.7; margin: 0.85rem 0;">
                                Wealthy industrialist and president of Blackwood Maritime Ventures. Discovered deceased behind the locked oak doors of his second-floor study. Key financial audit ledgers were forcibly removed from his wall safe.
                            </p>
                        </div>

                        <div class="case-facts-grid" style="margin: 0; padding: 0.9rem 1.25rem;">
                            <div class="case-fact-item">
                                <h4>ESTIMATED TOD</h4>
                                <p>11:00 – 11:30 PM</p>
                            </div>
                            <div class="case-fact-item">
                                <h4>CLOCK STOPPED</h4>
                                <p>11:13 PM</p>
                            </div>
                            <div class="case-fact-item">
                                <h4>CRIME SCENE</h4>
                                <p>Private Study</p>
                            </div>
                            <div class="case-fact-item">
                                <h4>SUSPECTS</h4>
                                <p>5 in Mansion</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 2rem;">
                    <button class="btn btn-primary" onclick="renderMansionMap(); showScreen('screen-map');">
                        ENTER BLACKWOOD MANSION →
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         SCREEN 3: MANSION MAP (Select Location)
         ========================================================================= -->
    <section id="screen-map" class="screen">
        <div class="container">
            <div class="screen-header">
                <h2 class="screen-title">BLACKWOOD MANSION</h2>
                <p class="screen-subtitle">SELECT A LOCATION TO INVESTIGATE CRIME HOTSPOTS</p>
            </div>

            <div class="map-grid" id="map-grid">
                <!-- Populated dynamically by JavaScript -->
            </div>
        </div>
    </section>

    <!-- =========================================================================
         SCREEN 4: ROOM INVESTIGATION (Clickable Objects in Selected Location)
         ========================================================================= -->
    <section id="screen-investigation" class="screen">
        <div class="container">
            <div class="room-header">
                <h2 id="room-title" class="screen-title" style="margin-bottom: 0.5rem;">ROOM INVESTIGATION</h2>
                <p id="room-desc" class="room-desc" style="color: var(--text-secondary); max-width: 700px; margin: 0 auto;"></p>
                <div style="margin-top: 1.25rem;">
                    <button class="btn btn-small" onclick="renderMansionMap(); showScreen('screen-map');">← RETURN TO MANSION MAP</button>
                </div>
            </div>

            <div class="room-objects-grid" id="room-objects-grid">
                <!-- Hotspots generated dynamically by JavaScript -->
            </div>
        </div>
    </section>

    <!-- =========================================================================
         SCREEN 5: BANANA API PUZZLE SYSTEM
         ========================================================================= -->
    <section id="screen-puzzle" class="screen">
        <div class="container">
            <div class="screen-header">
                <h2 class="screen-title">CRYPTOGRAM CIPHER</h2>
                <p class="screen-subtitle" id="puzzle-target-title">CRACK CIPHER TO SECURE EVIDENCE</p>
            </div>

            <div class="puzzle-container">
                <p style="color: var(--text-secondary); line-height: 1.7;">
                    The security mechanism requires deciphering the numerical lockwork. 
                    Examine the symbols and calculate the missing digit (0–9).
                </p>

                <div class="puzzle-image-wrapper">
                    <div id="puzzle-loading" style="display: flex; flex-direction: column; align-items: center; gap: 1rem;">
                        <div class="loading-spinner" style="width: 44px; height: 44px; border: 3px solid rgba(201,168,76,0.2); border-top-color: var(--gold); border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
                        <span style="font-family: var(--font-mono); color: var(--gold); letter-spacing: 2px;">FETCHING OFFICIAL BANANA API PUZZLE...</span>
                    </div>

                    <img id="puzzle-image" class="puzzle-image" style="display: none;" alt="Banana API Investigation Cipher">

                    <div id="puzzle-error" style="display: none; color: var(--red-light);">
                        <p id="puzzle-error-msg">Failed to connect to the Banana API.</p>
                        <button class="btn btn-small btn-primary" onclick="fetchBananaPuzzle()" style="margin-top: 1rem;">RETRY CONNECTION</button>
                    </div>
                </div>

                <form id="puzzle-form" class="puzzle-controls" onsubmit="event.preventDefault(); submitBananaAnswer();">
                    <input type="number" id="puzzle-answer-input" class="puzzle-input" min="0" max="9" placeholder="?" autocomplete="off">
                    <button type="submit" class="btn btn-primary" id="puzzle-submit-btn">DECIPHER</button>
                    <button type="button" class="btn btn-small" onclick="fetchBananaPuzzle()" title="Get a different puzzle">🔄 NEW CIPHER</button>
                    <button type="button" class="btn btn-small" onclick="skipPuzzle()">BYPASS (-50 PTS)</button>
                </form>

                <div id="puzzle-feedback" class="puzzle-feedback" style="display: none;"></div>

                <div class="puzzle-stats-bar" id="puzzle-stats-display">
                    <span>✓ Solved: 0</span>
                    <span>✗ Mistakes: 0</span>
                </div>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         SCREEN 6: EVIDENCE INVENTORY
         ========================================================================= -->
    <section id="screen-evidence" class="screen">
        <div class="container">
            <div class="screen-header">
                <h2 class="screen-title">EVIDENCE INVENTORY</h2>
                <p class="screen-subtitle">TOTAL CLUES LOGGED: <span id="evidence-count">0 / 13</span></p>
            </div>

            <div class="evidence-grid" id="evidence-grid">
                <!-- Dynamically populated -->
            </div>
        </div>
    </section>

    <!-- =========================================================================
         SCREEN 7: EVIDENCE BOARD (Connect & Deduce)
         ========================================================================= -->
    <section id="screen-board" class="screen">
        <div class="container">
            <div class="screen-header">
                <h2 class="screen-title">EVIDENCE BOARD</h2>
                <p class="screen-subtitle">CROSS-EXAMINE TWO CLUES TO UNCOVER CRITICAL DEDUCTIONS</p>
            </div>

            <div class="board-wrapper">
                <p style="text-align: center; color: var(--text-secondary); max-width: 600px; margin: 0 auto 1.5rem;">
                    Pin two pieces of evidence to the board. If a direct deductive link exists between them, your notebook will record the revelation.
                </p>

                <div class="board-slots">
                    <div class="board-slot" id="board-slot-1" onclick="selectEvidenceForSlot(1)">
                        <span>+ SELECT CLUE #1</span>
                    </div>

                    <div class="board-link-icon">⟷</div>

                    <div class="board-slot" id="board-slot-2" onclick="selectEvidenceForSlot(2)">
                        <span>+ SELECT CLUE #2</span>
                    </div>
                </div>

                <div style="text-align: center; margin: 2rem 0;">
                    <button class="btn btn-primary" onclick="compareBoardEvidence()">COMPARE CLUES</button>
                </div>

                <div id="board-result" class="puzzle-feedback" style="display: none; max-width: 680px; margin: 0 auto;"></div>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         SCREEN 8: SUSPECT PROFILES
         ========================================================================= -->
    <section id="screen-suspects" class="screen">
        <div class="container">
            <div class="screen-header">
                <h2 class="screen-title">PERSONS OF INTEREST</h2>
                <p class="screen-subtitle">FIVE WERE PRESENT IN THE MANOR. ONE IS THE KILLER.</p>
            </div>

            <div class="suspects-grid" id="suspects-grid">
                <!-- Dynamically populated -->
            </div>
        </div>
    </section>

    <!-- =========================================================================
         SCREEN 9: INTERROGATION ROOM
         ========================================================================= -->
    <section id="screen-interrogation" class="screen">
        <div class="container">
            <div class="screen-header">
                <h2 class="screen-title">INTERROGATION ROOM</h2>
                <p class="screen-subtitle">CROSS-EXAMINE TESTIMONIES & EXPOSE CONTRADICTIONS</p>
            </div>

            <div class="interrogation-layout">
                <!-- Suspect Profile Column -->
                <div class="interrogation-profile">
                    <div class="interrogation-avatar" id="interrogate-avatar">👤</div>
                    <h3 id="interrogate-name" style="color: var(--gold-light); font-size: 1.4rem;">SUSPECT NAME</h3>
                    <p id="interrogate-role" style="font-family: var(--font-mono); font-size: 0.8rem; color: var(--text-muted); margin-bottom: 1rem;">OCCUPATION</p>
                    
                    <div style="background: rgba(0,0,0,0.4); border-left: 2px solid var(--red); padding: 0.75rem; text-align: left; margin-bottom: 1.5rem;">
                        <h4 style="font-size: 0.75rem; font-family: var(--font-mono); color: var(--red-light); letter-spacing: 1px;">SUSPECTED MOTIVE</h4>
                        <p id="interrogate-motive" style="font-size: 0.95rem; color: var(--cream);"></p>
                    </div>

                    <button class="btn btn-small" onclick="renderSuspectsScreen(); showScreen('screen-suspects');">← ALL SUSPECTS</button>
                </div>

                <!-- Dialogue & Questions Column -->
                <div>
                    <div class="dialogue-box">
                        <div class="dialogue-speaker">SUSPECT RESPONSE</div>
                        <div class="dialogue-text" id="interrogate-dialogue-text">"Ask what you will, Detective Morgan."</div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label for="interrogate-dialogue-select" class="form-label" style="font-size: 0.8rem; letter-spacing: 2px;">QUICK INQUIRY (SELECT QUESTION):</label>
                        <select id="interrogate-dialogue-select" class="form-select">
                            <option value="">-- CHOOSE A LINE OF QUESTIONING --</option>
                        </select>
                    </div>

                    <h4 style="font-family: var(--font-mono); font-size: 0.85rem; color: var(--gold); letter-spacing: 2px; margin-bottom: 0.85rem;">AVAILABLE INQUIRIES:</h4>
                    <div class="question-list" id="interrogate-question-list">
                        <!-- Questions dynamically added -->
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         SCREEN 10: FINAL ACCUSATION
         ========================================================================= -->
    <section id="screen-accusation" class="screen">
        <div class="container">
            <div class="screen-header">
                <h2 class="screen-title" style="color: var(--red-light);">FINAL ACCUSATION</h2>
                <p class="screen-subtitle">NAME THE MURDERER, THE MOTIVE, AND THE PROOF</p>
            </div>

            <!-- Pre-requisite Warning if not enough clues collected -->
            <div id="accusation-prereq-warning" style="display: none; background: var(--bg-card); border: 1px solid var(--border-gold); padding: 3rem 2rem; text-align: center; max-width: 650px; margin: 0 auto;">
                <h3 style="margin-bottom: 1rem; color: var(--gold);">MORE EVIDENCE REQUIRED</h3>
                <p style="color: var(--text-secondary); line-height: 1.8; margin-bottom: 1.5rem;">
                    A detective cannot walk into the Grand Jury with unproven suspicions. You must collect at least <strong>4 critical pieces of evidence</strong> from the mansion before making a formal accusation.
                </p>
                <button class="btn btn-primary" onclick="renderMansionMap(); showScreen('screen-map');">CONTINUE INVESTIGATING</button>
            </div>

            <!-- Accusation Form -->
            <div id="accusation-form-card" class="accusation-form" style="display: none;">
                <div class="form-group">
                    <label class="form-label">1. WHO COMMITTED THE MURDER OF RICHARD BLACKWOOD?</label>
                    <div id="accuse-suspects-grid" class="accuse-suspects-picker" style="margin-bottom: 1rem;"></div>
                    <select id="accuse-suspect-select" class="form-select">
                        <option value="">-- SELECT THE MURDERER --</option>
                        <option value="1">Eleanor Blackwood (Victim's Wife)</option>
                        <option value="2">Thomas Reed (Business Partner)</option>
                        <option value="3">Henry Collins (Head Butler)</option>
                        <option value="4">Margaret Hayes (Investigative Journalist)</option>
                        <option value="5">Victor Graves (Personal Lawyer)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">2. WHAT WAS THE TRUE MOTIVE BEHIND THE CRIME?</label>
                    <select id="accuse-motive-select" class="form-select">
                        <option value="">-- SELECT THE PRIMARY MOTIVE --</option>
                        <option value="inheritance">Personal Marital Jealousy & Spousal Inheritance</option>
                        <option value="fraud">Concealment of Massive Embezzlement & Altered Estate Will</option>
                        <option value="blackmail">Extortion Over Old Household Blackmail</option>
                        <option value="sensational">Journalistic Vengeance for a Silenced Newspaper Story</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">3. SELECT CRITICAL SUPPORTING EVIDENCE LOGGED IN YOUR NOTEBOOK:</label>
                    <div class="evidence-checklist" id="accusation-evidence-checklist">
                        <!-- Dynamically filled with collected evidence checkboxes -->
                    </div>
                </div>

                <div style="text-align: center; margin-top: 2.5rem;">
                    <button class="btn btn-danger" onclick="submitAccusation()" style="padding: 14px 44px; font-size: 0.95rem;">
                        DELIVER INDICTMENT →
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         SCREEN 11: ENDING SCREEN (Newspaper Extra Edition)
         ========================================================================= -->
    <section id="screen-ending" class="screen">
        <div class="container">
            <div class="newspaper-headline">
                <div class="newspaper-paper-name">THE BLACKWOOD CITY CHRONICLE &bull; SPECIAL MORNING EXTRA</div>
                
                <span class="badge" id="ending-badge" style="font-size: 0.85rem; padding: 4px 14px; margin-bottom: 1.5rem;">CASE OUTCOME</span>

                <h1 class="newspaper-title" id="ending-headline">CASE OUTCOME HEADLINE</h1>

                <div id="ending-portrait-container" style="text-align: center; margin: 1.5rem 0;"></div>

                <p class="newspaper-body" id="ending-body">
                    Story resolution text goes here...
                </p>

                <div class="ending-stats-grid">
                    <div>
                        <div class="ending-stat-val" id="ending-score">0</div>
                        <small style="font-family: var(--font-mono); color: var(--text-muted); letter-spacing: 2px;">FINAL SCORE</small>
                    </div>
                    <div>
                        <div class="ending-stat-val" id="ending-evidence">0/13</div>
                        <small style="font-family: var(--font-mono); color: var(--text-muted); letter-spacing: 2px;">EVIDENCE LOGGED</small>
                    </div>
                    <div>
                        <div class="ending-stat-val" id="ending-puzzles">0</div>
                        <small style="font-family: var(--font-mono); color: var(--text-muted); letter-spacing: 2px;">CIPHERS SOLVED</small>
                    </div>
                </div>

                <div style="margin-top: 2.5rem; display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <button class="btn btn-primary" onclick="startNewInvestigation()">START NEW INVESTIGATION</button>
                    <button class="btn" onclick="showScreen('screen-menu')">RETURN TO MAIN MENU</button>
                </div>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         SCREEN 12: HOW TO PLAY
         ========================================================================= -->
    <section id="screen-howtoplay" class="screen">
        <div class="container" style="max-width: 820px;">
            <div class="screen-header">
                <h2 class="screen-title">DETECTIVE'S HANDBOOK</h2>
                <p class="screen-subtitle">INSTRUCTIONS FOR THE 13TH CASE</p>
            </div>

            <div class="dossier-card" style="margin-bottom: 2rem;">
                <h3 style="margin-bottom: 1rem; color: var(--gold);">1. EXPLORE THE MANSION</h3>
                <p style="color: var(--text-secondary); line-height: 1.8;">
                    Navigate between the five key mansion sectors. Click on visible interactive objects to inspect them. Some reveal direct physical evidence, others offer atmospheric context, and some are locked behind security ciphers.
                </p>
            </div>

            <div class="dossier-card" style="margin-bottom: 2rem;">
                <h3 style="margin-bottom: 1rem; color: var(--gold);">2. SOLVE BANANA API CIPHERS</h3>
                <p style="color: var(--text-secondary); line-height: 1.8;">
                    Certain critical evidence—such as the stopped grandfather clock, the secret library compartment, and the strongbox—is guarded by mathematical security puzzles powered live by the University Banana API. Calculate the missing numerical digit (0–9) to crack the lock and claim +150 points.
                </p>
            </div>

            <div class="dossier-card" style="margin-bottom: 2rem;">
                <h3 style="margin-bottom: 1rem; color: var(--gold);">3. THE EVIDENCE BOARD</h3>
                <p style="color: var(--text-secondary); line-height: 1.8;">
                    Visit the Evidence Board to compare two gathered clues. When two related items are linked (e.g., the Stopped Clock and the Secret Passage Map), you uncover vital deductive breakthroughs and bonus points.
                </p>
            </div>

            <div class="dossier-card" style="margin-bottom: 2rem;">
                <h3 style="margin-bottom: 1rem; color: var(--gold);">4. INTERROGATE THE FIVE SUSPECTS</h3>
                <p style="color: var(--text-secondary); line-height: 1.8;">
                    Question all five suspects present on the night of the murder. Observe behavioral cues, compare alibis, and cross-reference testimonies with physical evidence.
                </p>
            </div>

            <div style="text-align: center; margin-top: 2rem;">
                <button class="btn btn-primary" onclick="showScreen('screen-menu')">← BACK TO MAIN MENU</button>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         SCREEN 13: SETTINGS
         ========================================================================= -->
    <section id="screen-settings" class="screen">
        <div class="container" style="max-width: 680px;">
            <div class="screen-header">
                <h2 class="screen-title">SETTINGS & ARCHIVES</h2>
                <p class="screen-subtitle">PREFERENCES & SAVE MANAGEMENT</p>
            </div>

            <div class="dossier-card">
                <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-subtle); margin-bottom: 1.5rem;">
                    <div>
                        <h4 style="font-size: 1.15rem; color: var(--cream);">ATMOSPHERIC NOIR AUDIO</h4>
                        <p style="color: var(--text-muted); font-size: 0.85rem;">Synthesized rain ambiance and detective sound cues.</p>
                    </div>
                    <button class="btn btn-small btn-primary" onclick="NoirAudio.toggle()">TOGGLE SOUND</button>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-subtle); margin-bottom: 1.5rem;">
                    <div>
                        <h4 style="font-size: 1.15rem; color: var(--cream);">TYPOGRAPHY SCALE</h4>
                        <p style="color: var(--text-muted); font-size: 0.85rem;">Adjust readability of case notes.</p>
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <button class="btn btn-small" onclick="setTextSize('normal')">REGULAR</button>
                        <button class="btn btn-small" onclick="setTextSize('large')">LARGE</button>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-subtle); margin-bottom: 1.5rem;">
                    <div>
                        <h4 style="font-size: 1.15rem; color: var(--cream);">SAVE INVESTIGATION PROGRESS</h4>
                        <p style="color: var(--text-muted); font-size: 0.85rem;">Stores progress to session and local database.</p>
                    </div>
                    <button class="btn btn-small btn-primary" onclick="saveInvestigation()">SAVE PROGRESS</button>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <div>
                        <h4 style="font-size: 1.15rem; color: var(--red-light);">PURGE CASE ARCHIVES</h4>
                        <p style="color: var(--text-muted); font-size: 0.85rem;">Reset all progress and restart from the beginning.</p>
                    </div>
                    <button class="btn btn-small btn-danger" onclick="resetInvestigation()">RESET CASE</button>
                </div>

                <div style="text-align: center; margin-top: 2rem;">
                    <button class="btn" onclick="showScreen('screen-menu')">← RETURN TO MAIN MENU</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Floating Audio Toggle Button -->
    <button class="audio-toggle-btn" id="audio-toggle-btn" onclick="NoirAudio.toggle()" title="Toggle Noir Audio">
        🔊
    </button>

    <!-- Global Modal Popup System -->
    <div class="modal-overlay" id="modal-overlay" style="display: none;">
        <div class="modal-card">
            <button class="modal-close" onclick="closeModal()">&times;</button>
            <h3 id="modal-title" style="margin-bottom: 1.25rem; font-size: 1.4rem;">OBSERVATION</h3>
            <div id="modal-body" style="color: var(--cream-muted); line-height: 1.8; margin-bottom: 2rem;"></div>
            <div id="modal-actions" style="display: flex; justify-content: flex-end; gap: 1rem;"></div>
        </div>
    </div>

    <!-- Detective Authentication Modal (Login / Register) -->
    <div class="modal-overlay" id="auth-modal-overlay" style="display: none;">
        <div class="modal-card" style="max-width: 480px;">
            <button class="modal-close" onclick="closeAuthModal()">&times;</button>
            
            <div style="text-align: center; margin-bottom: 1.5rem;">
                <div style="font-size: 2.2rem; margin-bottom: 0.5rem;">🛡️</div>
                <h3 style="font-size: 1.4rem; color: var(--gold); letter-spacing: 2px;">BLACKWOOD POLICE DEPT.</h3>
                <p style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--text-muted); letter-spacing: 2px;">DETECTIVE CREDENTIALS ARCHIVE</p>
            </div>

            <!-- Tab Switcher -->
            <div class="auth-tabs">
                <button class="auth-tab active" id="tab-login" onclick="switchAuthTab('login')">LOGIN</button>
                <button class="auth-tab" id="tab-register" onclick="switchAuthTab('register')">NEW BADGE</button>
            </div>

            <!-- Feedback Alert -->
            <div id="auth-feedback" class="puzzle-feedback" style="display: none; margin-bottom: 1.25rem; font-size: 0.85rem;"></div>

            <!-- Login Form -->
            <form id="form-auth-login" onsubmit="submitAuthLogin(event)">
                <label style="display: block; font-family: var(--font-mono); font-size: 0.75rem; color: var(--gold); margin-bottom: 0.4rem; letter-spacing: 1px;">DETECTIVE USERNAME / BADGE ID</label>
                <input type="text" id="login-username" class="input-noir" placeholder="e.g. Morgan" required autocomplete="username">

                <label style="display: block; font-family: var(--font-mono); font-size: 0.75rem; color: var(--gold); margin-bottom: 0.4rem; letter-spacing: 1px;">ACCESS CODE / PASSWORD</label>
                <input type="password" id="login-password" class="input-noir" placeholder="••••••••" required autocomplete="current-password">

                <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary" id="btn-submit-login">VERIFY CREDENTIALS & PLAY</button>
                    <button type="button" class="btn btn-small" onclick="switchAuthTab('register')">NEED A BADGE? REGISTER NEW DETECTIVE</button>
                </div>
            </form>

            <!-- Register Form -->
            <form id="form-auth-register" onsubmit="submitAuthRegister(event)" style="display: none;">
                <label style="display: block; font-family: var(--font-mono); font-size: 0.75rem; color: var(--gold); margin-bottom: 0.4rem; letter-spacing: 1px;">NEW BADGE USERNAME</label>
                <input type="text" id="reg-username" class="input-noir" placeholder="e.g. Vance" required autocomplete="username">

                <label style="display: block; font-family: var(--font-mono); font-size: 0.75rem; color: var(--gold); margin-bottom: 0.4rem; letter-spacing: 1px;">OFFICIAL EMAIL (OPTIONAL)</label>
                <input type="email" id="reg-email" class="input-noir" placeholder="vance@blackwood.gov" autocomplete="email">

                <label style="display: block; font-family: var(--font-mono); font-size: 0.75rem; color: var(--gold); margin-bottom: 0.4rem; letter-spacing: 1px;">CHOOSE ACCESS CODE / PASSWORD</label>
                <input type="password" id="reg-password" class="input-noir" placeholder="Minimum 4 characters" required autocomplete="new-password">

                <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary" id="btn-submit-reg">ISSUE DETECTIVE BADGE</button>
                    <button type="button" class="btn btn-small" onclick="switchAuthTab('login')">ALREADY REGISTERED? LOGIN</button>
                </div>
            </form>

            <div id="auth-logged-in-panel" style="display: none; text-align: center;">
                <p style="color: var(--cream); font-size: 1.1rem; margin-bottom: 1.5rem;">
                    Currently signed on as <strong id="auth-current-user-display" style="color: var(--gold);"></strong>.
                </p>
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <button class="btn btn-primary btn-small" onclick="closeAuthModal()">RETURN TO CASE</button>
                    <button class="btn btn-danger btn-small" onclick="submitAuthLogout()">LOGOUT / SIGN OFF</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div class="toast-container" id="toast-container"></div>

    <style>
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>

    <!-- Master Game Script -->
    <script src="assets/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
