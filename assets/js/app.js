/**
 * THE 13TH CASE — Master Game Engine & Interactivity
 * 1947 Blackwood City Detective Mystery
 */

// Global Game State
const Game = {
    active: false,
    stage: 'menu',
    score: 0,
    mistakes: 0,
    mistakeLimit: 13,
    evidenceCollected: [],
    locationsInvestigated: [],
    objectsExamined: [],
    suspectsInterrogated: {
        eleanor: [],
        thomas: [],
        henry: [],
        margaret: [],
        victor: []
    },
    puzzleStats: { correct: 0, incorrect: 0, total: 0 },
    user: {
        loggedIn: false,
        username: 'GUEST',
        id: null,
        isGuest: true
    },
    currentPuzzle: null,
    pendingEvidenceId: null,
    currentLocation: null,
    currentSuspect: null,
    boardSlot1: null,
    boardSlot2: null
};

// Evidence Definitions
const EVIDENCE_REGISTRY = {
    1: { id: 1, name: "Stopped Clock", location: "Private Study", importance: "critical", image: "assets/images/evidence_1.svg", desc: "The grandfather clock stopped at exactly 11:13 PM. The escapement gear was jammed with a small brass pin, indicating the time was staged." },
    2: { id: 2, name: "Blood-Stained Letter", location: "Private Study", importance: "high", image: "assets/images/evidence_2.svg", desc: "A partially burned letter with dark blood spots addressed to Victor Graves regarding the immediate revocation of his legal power of attorney." },
    3: { id: 3, name: "Missing Document Cabinet", location: "Private Study", importance: "critical", image: "assets/images/evidence_3.svg", desc: "An open, ransacked document safe. The lock shows no scratch marks—opened with a duplicate key." },
    4: { id: 4, name: "Broken Glass", location: "Private Study", importance: "low", image: "assets/images/evidence_4.svg", desc: "Shattered crystal tumbler with traces of bourbon, indicating a sudden scuffle near the desk." },
    5: { id: 5, name: "Torn Photograph", location: "Living Room", importance: "medium", image: "assets/images/evidence_5.svg", desc: "A framed dinner photo from which lawyer Victor Graves' face was meticulously cut and excised." },
    6: { id: 6, name: "Wine Glass with Residue", location: "Living Room", importance: "medium", image: "assets/images/evidence_6.svg", desc: "A wine glass containing sedative chloral hydrate residue, likely intended to incapacitate the victim." },
    7: { id: 7, name: "Poison Bottle", location: "Kitchen", importance: "high", image: "assets/images/evidence_7.svg", desc: "A vial of arsenic placed conspicuously behind baking soda in the pantry—an obvious attempt to frame Eleanor Blackwood." },
    8: { id: 8, name: "Secret Passage Map", location: "Library", importance: "critical", image: "assets/images/evidence_8.svg", desc: "An architectural blueprint showing a hidden corridor connecting the cellar directly into the private study behind the bookcase." },
    9: { id: 9, name: "Torn Diary Page", location: "Library", importance: "high", image: "assets/images/evidence_9.svg", desc: "Richard's diary page: 'V.G. cannot be trusted. Tomorrow the state auditors arrive. One way or another, this deception ends.'" },
    10: { id: 10, name: "Hidden Financial Records", location: "Secret Basement", importance: "critical", image: "assets/images/evidence_10.svg", desc: "Secret dual ledgers revealing that Victor Graves embezzled over $450,000 from the Blackwood estate." },
    11: { id: 11, name: "Old Photograph", location: "Secret Basement", importance: "medium", image: "assets/images/evidence_11.svg", desc: "An old deed signing photo where Richard Blackwood's signature was forged by traced ink transfer." },
    12: { id: 12, name: "Modified Will", location: "Secret Basement", importance: "critical", image: "assets/images/evidence_12.svg", desc: "A freshly forged last will leaving full executive custody of the Blackwood estate to Victor Graves." },
    13: { id: 13, name: "Cigarette Remains", location: "Living Room", importance: "medium", image: "assets/images/evidence_13.svg", desc: "Rare Sobranie Turkish cigarette butts. Only one person present that evening smokes this bespoke brand: Victor Graves." }
};

// Mansion Room Objects
const ROOM_DATA = {
    study: {
        id: 'study',
        name: 'Private Study',
        icon: '🔍',
        image: 'assets/images/room_study.svg',
        desc: 'The scene of the crime. Richard Blackwood was found behind his mahogany desk. The single door was locked from the inside.',
        objects: [
            { id: 'study_clock', name: 'Stopped Clock', icon: '🕰️', evidenceId: 1, puzzle: true, desc: 'A towering grandfather clock frozen at 11:13 PM. The mechanism has been tampered with. Decipher the lockwork code to examine it.' },
            { id: 'study_letter', name: 'Blood-Stained Letter', icon: '✉️', evidenceId: 2, puzzle: false, desc: 'A crumpled parchment near the fireplace grate, stamped with legal headers and stained with fresh blood droplets.' },
            { id: 'study_cabinet', name: 'Document Cabinet', icon: '🗄️', evidenceId: 3, puzzle: false, desc: 'The steel filing cabinet door hangs wide open. Key folders containing internal corporate audit records are gone.' },
            { id: 'study_glass', name: 'Broken Glass', icon: '🥃', evidenceId: 4, puzzle: false, desc: 'Crystal fragments scattered across the Persian rug. The glass was dropped or struck forcefully.' },
            { id: 'study_desk', name: 'Executive Desk Drawer', icon: '🗃️', evidenceId: null, puzzle: false, desc: 'The drawers contain fountain pens, blank legal pads, and unused cigars. Nothing incriminating.' }
        ]
    },
    living_room: {
        id: 'living_room',
        name: 'Living Room',
        icon: '🛋️',
        image: 'assets/images/room_living_room.svg',
        desc: 'Where the guests and household gathered prior to midnight. Rain lashes against the tall French bay windows.',
        objects: [
            { id: 'lr_photo', name: 'Torn Photograph', icon: '🖼️', evidenceId: 5, puzzle: false, desc: 'A silver frame knocked flat on the side table. The photograph has been deliberately sliced.' },
            { id: 'lr_wine', name: 'Wine Glass', icon: '🍷', evidenceId: 6, puzzle: false, desc: 'An unfinished glass of port on the mantelpiece with a murky sediment clinging to the bottom.' },
            { id: 'lr_cigs', name: 'Cigarette Remains', icon: '🚬', evidenceId: 13, puzzle: false, desc: 'A silver ashtray with distinct Turkish paper butts bearing gold monogram tips.' },
            { id: 'lr_fire', name: 'Hearth & Fireplace', icon: '🔥', evidenceId: null, puzzle: false, desc: 'Dying embers crackle softly. Only grey wood ash remains. Nothing readable can be recovered.' }
        ]
    },
    kitchen: {
        id: 'kitchen',
        name: 'Kitchen & Pantry',
        icon: '🍳',
        image: 'assets/images/room_kitchen.svg',
        desc: 'A cavernous tiled kitchen. The staff had prepared evening tea and cocktails before retiring.',
        objects: [
            { id: 'k_poison', name: 'Poison Bottle', icon: '☠️', evidenceId: 7, puzzle: false, desc: 'A bottle of rat poison placed right behind the sugar canisters, almost as if someone wanted it found.' },
            { id: 'k_cloth', name: 'Cleaning Cloth', icon: '🧼', evidenceId: null, puzzle: false, desc: 'A damp linen cloth hanging by the sink. Smells of standard lemon soap.' },
            { id: 'k_knives', name: 'Chef Knife Block', icon: '🔪', evidenceId: null, puzzle: false, desc: 'All kitchen blades are accounted for and clean. The victim was not wounded by a blade.' }
        ]
    },
    library: {
        id: 'library',
        name: 'Grand Library',
        icon: '📚',
        image: 'assets/images/room_library.svg',
        desc: 'Floor-to-ceiling oak bookshelves. Richard often retreated here for private research and negotiations.',
        objects: [
            { id: 'lib_bookshelf', name: 'Secret Bookshelf Compartment', icon: '📖', evidenceId: 8, puzzle: true, desc: 'A hollowed leather encyclopedia conceals a mechanical brass dial. Solve the cryptogram to slide the panel.' },
            { id: 'lib_diary', name: 'Torn Diary Page', icon: '📑', evidenceId: 9, puzzle: false, desc: 'A torn page from Richard\'s personal journal lying between two thick volumes of tax law.' },
            { id: 'lib_paper', name: 'Old Newspaper', icon: '📰', evidenceId: null, puzzle: false, desc: 'A copy of the Blackwood City Herald discussing wartime recovery and harbor expansion.' }
        ]
    },
    basement: {
        id: 'basement',
        name: 'Secret Basement',
        icon: '🚪',
        image: 'assets/images/room_basement.svg',
        desc: 'A subterranean wine cellar and vault beneath the manor. Connected via the secret passage.',
        locked: true,
        objects: [
            { id: 'base_records', name: 'Hidden Financial Records', icon: '📁', evidenceId: 10, puzzle: true, desc: 'A reinforced combination strongbox hidden behind wine racks. It requires a numerical cipher.' },
            { id: 'base_photo', name: 'Old Contract Photo', icon: '📸', evidenceId: 11, puzzle: false, desc: 'A faded press photo documenting the original 1941 land charter transfer.' },
            { id: 'base_will', name: 'Modified Last Will', icon: '📜', evidenceId: 12, puzzle: false, desc: 'A drafted testament dated this very month, granting power to lawyer Victor Graves.' }
        ]
    }
};

// Suspects Data & Interrogation Trees
const SUSPECTS_DATA = {
    eleanor: {
        id: 'eleanor',
        numId: 1,
        name: 'Eleanor Blackwood',
        role: "Victim's Wife",
        avatar: '👩',
        image: 'assets/images/suspect_eleanor.svg',
        motive: 'Inheritance & Marital Estrangement',
        bio: 'Elegant and poised, Eleanor married Richard twelve years ago. Their marriage had grown distant, but she maintains her innocence.',
        questions: [
            { id: 1, text: "Where were you between 11:00 PM and 11:30 PM?", answer: "I was in the parlor finishing a book by the fire. Henry brought me hot chamomile tea at 11:05. I never went upstairs to Richard's study." },
            { id: 2, text: "Was your marriage on the verge of divorce?", answer: "We had our disagreements, detective. Richard was obsessively paranoid this past month, checking ledgers until dawn. But I loved him. I would never harm him." },
            { id: 3, text: "Did you know Richard was planning to alter his will?", answer: "Richard kept all legal affairs to himself and Victor Graves. In fact, Mr. Graves insisted that Richard sign new estate covenants only three days ago." },
            { id: 4, text: "Did you notice anyone entering the private study?", answer: "Around 10:45 PM, I saw Victor Graves walking purposefully toward the library corridor with a leather portfolio. He seemed extremely agitated." }
        ]
    },
    thomas: {
        id: 'thomas',
        numId: 2,
        name: 'Thomas Reed',
        role: 'Business Partner',
        avatar: '👨‍💼',
        image: 'assets/images/suspect_thomas.svg',
        motive: 'Financial Disputes & Debt Pressure',
        bio: 'Co-owner of Blackwood Shipping & Rail. Known for aggressive investments and volatile arguments with Richard.',
        questions: [
            { id: 1, text: "What was the nature of your heated argument with Richard earlier?", answer: "Business, Morgan! Purely business. Richard accused someone of draining corporate reserve accounts. He demanded an external audit tomorrow morning." },
            { id: 2, text: "Where were you when the study clock stopped at 11:13 PM?", answer: "I was down in the kitchen demanding ice for my whiskey from Collins. Henry Collins can confirm I was there until at least 11:25 PM." },
            { id: 3, text: "Did you possess a key to Richard's private study?", answer: "No! Richard had the master key, and his lawyer Graves held the duplicate for estate trust filings. My key only opens the downtown warehouse." },
            { id: 4, text: "Who was Richard most concerned about?", answer: "He kept repeating that his trust was betrayed by someone who held his power of attorney. That can only mean Graves." }
        ]
    },
    henry: {
        id: 'henry',
        numId: 3,
        name: 'Henry Collins',
        role: 'Head Butler',
        avatar: '🤵',
        image: 'assets/images/suspect_henry.svg',
        motive: 'Blackmail & Past Transgressions',
        bio: 'Has managed Blackwood Manor for over two decades. Discreet, observant, and aware of every hidden passage.',
        questions: [
            { id: 1, text: "Can you confirm the movements of the guests around 11:00 PM?", answer: "Indeed, sir. Mrs. Blackwood was in the parlor. Mr. Reed was pacing near the kitchen. Miss Hayes was in the sitting room typing notes." },
            { id: 2, text: "What about Victor Graves?", answer: "Mr. Graves claimed he had a headache and excused himself toward the library. Later, around 11:20 PM, I caught the distinct aroma of his Turkish cigarettes near the cellar stairs." },
            { id: 3, text: "Tell me about the secret passage in this mansion.", answer: "The manor was renovated in 1928 with a hidden passageway between the library bookcase and the study. Only Mr. Blackwood and his confidential advisor had the architectural blueprints." },
            { id: 4, text: "Did Richard seem afraid tonight?", answer: "Very much so, detective. He asked me to ensure the front gates were locked and muttered that he had uncovered 'forgeries that would send a friend to the gallows.'" }
        ]
    },
    margaret: {
        id: 'margaret',
        numId: 4,
        name: 'Margaret Hayes',
        role: 'Investigative Journalist',
        avatar: '🕵️‍♀️',
        image: 'assets/images/suspect_margaret.svg',
        motive: 'Revenge & Exposé Scoop',
        bio: 'Hard-hitting press reporter for the Blackwood City Daily. Richard had promised her an exclusive whistleblower interview.',
        questions: [
            { id: 1, text: "Why were you invited to the mansion tonight?", answer: "Richard phoned my editor at 6 PM. He said he had proof of widespread municipal embezzlement and securities fraud orchestrating by his inner circle." },
            { id: 2, text: "Did Richard hand you any documents?", answer: "Not yet. He said to wait until midnight after his guests dispersed. He said the evidence was locked in his private study safe." },
            { id: 3, text: "Did you hear anything unusual from the second floor?", answer: "At approximately 11:10 PM, I heard a dull crash—like glassware breaking—followed by the heavy click of a deadbolt. Then absolute silence." },
            { id: 4, text: "Who stood the most to lose from Richard's whistleblowing?", answer: "The lawyer, Victor Graves. Richard was going to revoke his license and file formal criminal charges with the Attorney General." }
        ]
    },
    victor: {
        id: 'victor',
        numId: 5,
        name: 'Victor Graves',
        role: 'Personal Lawyer (The Murderer)',
        avatar: '⚖️',
        image: 'assets/images/suspect_victor.svg',
        motive: 'Concealment of Embezzlement & Altered Will',
        bio: 'Suave, calculative, and Richard\'s closest legal counselor for fifteen years. Handles all Blackwood family trusts.',
        questions: [
            { id: 1, text: "Mr. Graves, where were you when Richard was killed?", answer: "I was resting in the library with a mild migraine, browsing historical law digests. I heard nothing untoward until the butler sounded the alarm." },
            { id: 2, text: "Why were you seen lingering near the cellar entrance smelling of Turkish tobacco?", answer: "*Visibly stiffens* That is preposterous. I may have stepped onto the terrace for fresh air, but I had no business in the wine cellar." },
            { id: 3, text: "Did Richard request to revoke your power of attorney tonight?", answer: "*Cold smile* Richard was under great strain recently. Any discussions we had were privileged legal consultations, Detective Morgan." },
            { id: 4, text: "What do you know about the missing audit files from Richard's safe?", answer: "I have no knowledge of any missing ledgers. As his executor, I will assume legal custody of all mansion assets to ensure proper probate." }
        ]
    }
};

// Web Audio API Noir Sound Synthesizer
const NoirAudio = {
    ctx: null,
    muted: false,
    rainGain: null,

    init() {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            this.ctx = new AudioCtx();
            this.muted = localStorage.getItem('noir_audio_muted') === 'true';
            this.updateButtonUI();
        } catch (e) {
            console.log("Web Audio not supported:", e);
        }
    },

    toggle() {
        if (!this.ctx) this.init();
        if (this.ctx && this.ctx.state === 'suspended') {
            this.ctx.resume();
        }
        this.muted = !this.muted;
        localStorage.setItem('noir_audio_muted', this.muted);
        this.updateButtonUI();
        showToast(this.muted ? "Audio muted." : "Audio enabled.");
    },

    updateButtonUI() {
        const btn = document.getElementById('audio-toggle-btn');
        if (btn) {
            btn.innerHTML = this.muted ? '🔇' : '🔊';
            btn.title = this.muted ? 'Unmute Audio' : 'Mute Audio';
        }
    },

    playTone(freq, duration, type = 'sine', volume = 0.1) {
        if (this.muted || !this.ctx) return;
        try {
            const osc = this.ctx.createOscillator();
            const gain = this.ctx.createGain();
            osc.type = type;
            osc.frequency.setValueAtTime(freq, this.ctx.currentTime);
            gain.gain.setValueAtTime(volume, this.ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.0001, this.ctx.currentTime + duration);
            osc.connect(gain);
            gain.connect(this.ctx.destination);
            osc.start();
            osc.stop(this.ctx.currentTime + duration);
        } catch (e) { }
    },

    playTypewriter() {
        if (this.muted || !this.ctx) return;
        this.playTone(800 + Math.random() * 400, 0.04, 'triangle', 0.03);
    },

    playClueChime() {
        if (this.muted || !this.ctx) return;
        const notes = [523.25, 659.25, 783.99, 1046.50]; // C E G C
        notes.forEach((f, idx) => {
            setTimeout(() => this.playTone(f, 0.4, 'sine', 0.12), idx * 90);
        });
    },

    playSuccessChime() {
        if (this.muted || !this.ctx) return;
        const notes = [440, 554.37, 659.25, 880];
        notes.forEach((f, idx) => {
            setTimeout(() => this.playTone(f, 0.35, 'triangle', 0.14), idx * 80);
        });
    },

    playErrorBuzz() {
        if (this.muted || !this.ctx) return;
        this.playTone(130, 0.25, 'sawtooth', 0.15);
        setTimeout(() => this.playTone(110, 0.3, 'sawtooth', 0.15), 180);
    }
};

// Application Initialization
document.addEventListener('DOMContentLoaded', function () {
    NoirAudio.init();
    initScreenNavigation();
    loadPersistedGame();
    checkAuthStatus();

    // "BEGIN INVESTIGATION" button on landing screen
    const beginBtn = document.getElementById('btn-begin');
    if (beginBtn) {
        beginBtn.addEventListener('click', () => {
            startNewInvestigation();
        });
    }

    // Modal background click close
    const modal = document.getElementById('modal-overlay');
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });
    }

    const authModal = document.getElementById('auth-modal-overlay');
    if (authModal) {
        authModal.addEventListener('click', (e) => {
            if (e.target === authModal) closeAuthModal();
        });
    }

    // Theme 2: Event-Driven Programming - Submit Event for Banana API Puzzles
    const puzzleForm = document.getElementById('puzzle-form');
    if (puzzleForm) {
        puzzleForm.addEventListener('submit', function (e) {
            e.preventDefault();
            submitBananaAnswer();
        });
    }

    // Theme 2: Event-Driven Programming - Change Events for Final Accusation Selectors
    const accuseSuspect = document.getElementById('accuse-suspect-select');
    if (accuseSuspect) {
        accuseSuspect.addEventListener('change', function () {
            NoirAudio.playClick();
        });
    }

    // Theme 2: Event-Driven Keyboard Accessibility Listeners
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeModal();
            closeAuthModal();
        }
    });
    const accuseMotive = document.getElementById('accuse-motive-select');
    if (accuseMotive) {
        accuseMotive.addEventListener('change', function () {
            NoirAudio.playClick();
        });
    }
});

/**
 * Screen Navigation Engine
 */
function showScreen(screenId) {
    document.querySelectorAll('.screen').forEach(s => s.classList.remove('active'));
    const target = document.getElementById(screenId);
    if (target) {
        target.classList.add('active');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Toggle navigation bar visibility
    const nav = document.getElementById('game-nav');
    if (nav) {
        if (screenId === 'screen-menu') {
            nav.style.display = 'none';
        } else {
            nav.style.display = 'flex';
        }
    }

    // Update active nav link
    document.querySelectorAll('.nav-link').forEach(link => {
        if (link.getAttribute('data-screen') === screenId) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });

    Game.stage = screenId.replace('screen-', '');
    updateNavUI();
}

function initScreenNavigation() {
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const targetScreen = link.getAttribute('data-screen');
            if (targetScreen) {
                if (targetScreen === 'screen-map') {
                    renderMansionMap();
                } else if (targetScreen === 'screen-evidence') {
                    renderEvidenceInventory();
                } else if (targetScreen === 'screen-board') {
                    renderEvidenceBoard();
                } else if (targetScreen === 'screen-suspects') {
                    renderSuspectsScreen();
                } else if (targetScreen === 'screen-accusation') {
                    openAccusationScreen();
                }
                showScreen(targetScreen);
            }
        });
    });
}

function updateNavUI() {
    const scoreEl = document.getElementById('nav-score');
    const mistakesEl = document.getElementById('nav-mistakes');
    if (scoreEl) scoreEl.textContent = `SCORE: ${Game.score}`;
    if (mistakesEl) mistakesEl.textContent = `MISTAKES: ${Game.mistakes}/${Game.mistakeLimit}`;
}

/**
 * Game Progression & Sessions
 */
function startNewInvestigation() {
    if (!Game.user.loggedIn) {
        Game.pendingStart = true;
        openAuthModal("Clearance Required: You must log in or register your detective badge to enter the crime scene.");
        return;
    }

    fetch('api/game_api.php?action=init', { method: 'POST' })
        .then(res => res.json())
        .then(data => {
            if (data.auth_required || !data.success) {
                openAuthModal(data.error || "Please login or register to begin.");
                return;
            }
            Game.active = true;
            Game.score = 0;
            Game.mistakes = 0;
            Game.evidenceCollected = [];
            Game.locationsInvestigated = [];
            Game.objectsExamined = [];
            Game.suspectsInterrogated = { eleanor: [], thomas: [], henry: [], margaret: [], victor: [] };
            Game.puzzleStats = { correct: 0, incorrect: 0, total: 0 };
            updateNavUI();
            playStoryIntro();
        })
        .catch(err => {
            openAuthModal("Detective credentials required before proceeding.");
        });
}

function continueInvestigation() {
    if (!Game.user.loggedIn) {
        Game.pendingContinue = true;
        openAuthModal("Clearance Required: Please log in to access your saved investigation file.");
        return;
    }

    fetch('api/game_api.php?action=load')
        .then(res => res.json())
        .then(data => {
            if (data.auth_required) {
                openAuthModal(data.error || "Please login to access saved case files.");
                return;
            }
            if (data.success && data.game_state) {
                applyLoadedState(data.game_state);
                showToast("Investigation file resumed.");
                renderMansionMap();
                showScreen('screen-map');
            } else {
                showToast("No active save file found. Initializing new case.");
                startNewInvestigation();
            }
        })
        .catch(e => {
            openAuthModal("Please login to access case files.");
        });
}

function applyLoadedState(state) {
    Game.active = true;
    Game.score = state.score || 0;
    Game.mistakes = state.mistakes || 0;
    Game.evidenceCollected = state.evidence_collected || [];
    Game.locationsInvestigated = state.locations_investigated || [];
    Game.objectsExamined = state.objects_examined || [];
    Game.suspectsInterrogated = state.suspects_interrogated || { eleanor: [], thomas: [], henry: [], margaret: [], victor: [] };
    Game.puzzleStats = state.puzzle_stats || { correct: 0, incorrect: 0, total: 0 };
    updateNavUI();
}

function saveInvestigation() {
    const payload = {
        score: Game.score,
        mistakes: Game.mistakes,
        current_stage: Game.stage,
        evidence_collected: Game.evidenceCollected,
        locations_investigated: Game.locationsInvestigated,
        objects_examined: Game.objectsExamined,
        suspects_interrogated: Game.suspectsInterrogated
    };

    localStorage.setItem('the_13th_case_saved', JSON.stringify(payload));

    fetch('api/game_api.php?action=save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
        .then(res => res.json())
        .then(data => {
            showToast("Case file saved to department archives.");
        })
        .catch(err => {
            showToast("Saved locally to browser terminal.");
        });
}

function loadPersistedGame() {
    const saved = localStorage.getItem('the_13th_case_saved');
    if (saved) {
        try {
            const parsed = JSON.parse(saved);
            applyLoadedState(parsed);
        } catch (e) { }
    }
}

/**
 * Story Introduction
 */
function playStoryIntro() {
    showScreen('screen-intro');
}

/**
 * Mansion Map & Navigation
 */
function renderMansionMap() {
    const container = document.getElementById('map-grid');
    if (!container) return;
    container.innerHTML = '';

    const hasSecretPassage = Game.evidenceCollected.includes(8);

    Object.keys(ROOM_DATA).forEach(key => {
        const room = ROOM_DATA[key];
        const isLocked = room.locked && !hasSecretPassage;

        let statusText = 'UNEXPLORED';
        let statusClass = 'status-unexplored';

        if (isLocked) {
            statusText = 'LOCKED (PASSAGE REQUIRED)';
            statusClass = 'status-locked';
        } else if (Game.locationsInvestigated.includes(room.id)) {
            statusText = 'INVESTIGATED';
            statusClass = 'status-investigated';
        }

        const card = document.createElement('div');
        card.className = `location-card ${isLocked ? 'locked' : ''}`;
        card.innerHTML = `
            <div class="location-banner-wrap">
                <img src="${room.image}" alt="${room.name}" class="location-banner-img" />
                <span class="location-status ${statusClass}" style="position: absolute; top: 12px; right: 12px; z-index: 2; box-shadow: 0 2px 8px rgba(0,0,0,0.8);">${statusText}</span>
            </div>
            <div class="location-content">
                <div>
                    <h3 class="location-name">${room.name}</h3>
                    <p class="location-desc">${room.desc}</p>
                </div>
                <div style="margin-top: 1.5rem;">
                    <button class="btn btn-small" ${isLocked ? 'disabled' : ''}>ENTER ROOM →</button>
                </div>
            </div>
        `;

        if (!isLocked) {
            card.addEventListener('click', () => {
                openRoomInvestigation(room.id);
            });
        }

        container.appendChild(card);
    });
}

function openRoomInvestigation(roomId) {
    Game.currentLocation = roomId;
    if (!Game.locationsInvestigated.includes(roomId)) {
        Game.locationsInvestigated.push(roomId);
    }

    const room = ROOM_DATA[roomId];
    document.getElementById('room-title').textContent = room.name.toUpperCase();
    
    let descHtml = `
        <div class="room-hero-banner">
            <img src="${room.image}" alt="${room.name}" class="room-hero-img">
        </div>
    `;

    if (roomId === 'study') {
        descHtml += `
            <div style="display: flex; gap: 1.25rem; align-items: center; justify-content: center; margin: 1rem auto; max-width: 760px; background: rgba(14,12,10,0.85); border: 1px solid var(--border-gold); padding: 1rem; flex-wrap: wrap;">
                <img src="assets/images/richard_blackwood.jpg" alt="Richard Blackwood" style="width: 75px; height: 95px; object-fit: cover; border: 2px solid var(--gold); box-shadow: 0 0 15px rgba(201, 168, 76, 0.3);">
                <div style="text-align: left; max-width: 520px;">
                    <strong style="color: var(--gold); font-family: var(--font-mono); font-size: 0.85rem;">CRIME SCENE &bull; VICTIM: RICHARD BLACKWOOD</strong>
                    <p style="margin-top: 0.25rem; font-size: 0.95rem; color: var(--cream-muted);">${room.desc}</p>
                </div>
            </div>
        `;
    } else {
        descHtml += `<p style="max-width: 760px; margin: 0 auto; color: var(--text-secondary); line-height: 1.7;">${room.desc}</p>`;
    }
    document.getElementById('room-desc').innerHTML = descHtml;

    const objectsGrid = document.getElementById('room-objects-grid');
    objectsGrid.innerHTML = '';

    room.objects.forEach(obj => {
        const isExamined = Game.objectsExamined.includes(obj.id);
        const hasEvidence = obj.evidenceId && Game.evidenceCollected.includes(obj.evidenceId);
        const evItem = obj.evidenceId ? EVIDENCE_REGISTRY[obj.evidenceId] : null;

        const card = document.createElement('div');
        card.className = `object-card ${isExamined ? 'examined' : ''}`;
        card.innerHTML = `
            ${evItem ? `<div style="text-align: center; margin-bottom: 0.75rem;"><img src="${evItem.image}" alt="${obj.name}" style="width: 54px; height: 54px; object-fit: contain; border: 1px solid var(--border-gold); border-radius: 2px; background: #0c0a08; padding: 2px;"></div>` : `<span class="object-icon">${obj.icon}</span>`}
            <h4 class="object-name">${obj.name}</h4>
            <p class="object-hint">${hasEvidence ? '✓ EVIDENCE COLLECTED' : (obj.puzzle ? '🔐 SECURE CIPHER' : (isExamined ? 'EXAMINED' : 'CLICK TO EXAMINE'))}</p>
        `;

        card.addEventListener('click', () => {
            investigateObject(obj);
        });

        objectsGrid.appendChild(card);
    });

    showScreen('screen-investigation');
}

/**
 * Investigating Hotspot Objects
 */
function investigateObject(obj) {
    if (!Game.objectsExamined.includes(obj.id)) {
        Game.objectsExamined.push(obj.id);
    }

    // Check if object requires Banana API Puzzle
    if (obj.puzzle && obj.evidenceId && !Game.evidenceCollected.includes(obj.evidenceId)) {
        launchBananaPuzzle(obj.evidenceId, obj.name);
        return;
    }

    // If it yields standard evidence directly
    if (obj.evidenceId && !Game.evidenceCollected.includes(obj.evidenceId)) {
        collectEvidence(obj.evidenceId);
        const item = EVIDENCE_REGISTRY[obj.evidenceId];
        showModal(
            `EVIDENCE SECURED: ${obj.name}`,
            `<div style="text-align: center; margin-bottom: 1rem;">
                <img src="${item.image}" alt="${item.name}" class="evidence-modal-img" />
             </div>
             <p style="font-size: 1.15rem; line-height: 1.8; margin-bottom: 1.5rem;">${obj.desc}</p>
             <div class="case-facts-grid" style="margin: 0;">
                <div class="case-fact-item">
                    <h4>CLUE LOGGED</h4>
                    <p>${item.name}</p>
                </div>
                <div class="case-fact-item">
                    <h4>LOCATION</h4>
                    <p>${item.location}</p>
                </div>
             </div>`,
            [{ text: "ADD TO CASE FILE", class: "btn-primary", onClick: closeModal }]
        );
        return;
    }

    // Regular observation
    showModal(
        obj.name,
        `<p style="font-size: 1.15rem; line-height: 1.8;">${obj.desc}</p>`,
        [{ text: "CONTINUE INVESTIGATION", class: "btn-primary", onClick: closeModal }]
    );
}

function collectEvidence(evidenceId) {
    if (!Game.evidenceCollected.includes(evidenceId)) {
        Game.evidenceCollected.push(evidenceId);
        Game.score += 100;
        updateNavUI();
        NoirAudio.playClueChime();
        showToast(`Evidence Logged: ${EVIDENCE_REGISTRY[evidenceId].name}`);

        // If secret passage map collected (ID 8), unlock basement!
        if (evidenceId === 8) {
            showToast("Secret Passage Revealed! Basement Vault is now accessible.");
        }
    }
}

/**
 * Banana API Puzzle Integration
 */
function launchBananaPuzzle(evidenceId, objectName) {
    Game.pendingEvidenceId = evidenceId;
    showScreen('screen-puzzle');

    const titleEl = document.getElementById('puzzle-target-title');
    if (titleEl) titleEl.textContent = `CRACK CIPHER: ${objectName.toUpperCase()}`;

    fetchBananaPuzzle();
}

function fetchBananaPuzzle() {
    const imgEl = document.getElementById('puzzle-image');
    const loadingEl = document.getElementById('puzzle-loading');
    const errorEl = document.getElementById('puzzle-error');
    const inputEl = document.getElementById('puzzle-answer-input');
    const feedbackEl = document.getElementById('puzzle-feedback');
    const submitBtn = document.getElementById('puzzle-submit-btn');

    if (imgEl) imgEl.style.display = 'none';
    if (loadingEl) loadingEl.style.display = 'flex';
    if (errorEl) errorEl.style.display = 'none';
    if (feedbackEl) feedbackEl.style.display = 'none';
    if (inputEl) {
        inputEl.value = '';
        inputEl.disabled = true;
    }
    if (submitBtn) submitBtn.disabled = true;

    fetch('api/banana.php?action=fetch')
        .then(res => {
            if (!res.ok) throw new Error("HTTP " + res.status);
            return res.json();
        })
        .then(data => {
            if (data.success && data.question_url) {
                Game.currentPuzzle = data;
                if (imgEl) {
                    imgEl.src = data.question_url;
                    imgEl.onload = () => {
                        loadingEl.style.display = 'none';
                        imgEl.style.display = 'block';
                        if (inputEl) {
                            inputEl.disabled = false;
                            inputEl.focus();
                        }
                        if (submitBtn) submitBtn.disabled = false;
                    };
                }
            } else {
                throw new Error(data.error || "Failed to load puzzle.");
            }
        })
        .catch(err => {
            if (loadingEl) loadingEl.style.display = 'none';
            if (errorEl) {
                errorEl.style.display = 'block';
                document.getElementById('puzzle-error-msg').textContent = err.message;
            }
        });
}

function submitBananaAnswer() {
    const inputEl = document.getElementById('puzzle-answer-input');
    const feedbackEl = document.getElementById('puzzle-feedback');
    const submitBtn = document.getElementById('puzzle-submit-btn');
    const answer = inputEl ? inputEl.value.trim() : '';

    if (answer === '') {
        showToast("Please enter a numerical answer.");
        return;
    }

    if (submitBtn) submitBtn.disabled = true;

    const formData = new FormData();
    formData.append('action', 'validate');
    formData.append('answer', answer);

    fetch('api/banana.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                feedbackEl.style.display = 'block';
                if (data.correct) {
                    NoirAudio.playSuccessChime();
                    feedbackEl.className = 'puzzle-feedback feedback-success';
                    feedbackEl.innerHTML = `<strong>✓ CORRECT CIPHER!</strong> ${data.message} (+150 PTS)`;
                    Game.score += 150;
                    Game.puzzleStats.correct++;
                    updateNavUI();

                    if (Game.pendingEvidenceId) {
                        collectEvidence(Game.pendingEvidenceId);
                    }

                    setTimeout(() => {
                        if (Game.currentLocation) {
                            openRoomInvestigation(Game.currentLocation);
                        } else {
                            renderMansionMap();
                            showScreen('screen-map');
                        }
                    }, 2200);
                } else {
                    NoirAudio.playErrorBuzz();
                    feedbackEl.className = 'puzzle-feedback feedback-error';
                    feedbackEl.innerHTML = `
                    <strong>✗ INCORRECT CIPHER!</strong> ${data.message} (-25 PTS)
                    <div style="margin-top: 0.85rem; display: flex; gap: 0.75rem; justify-content: center;">
                        <button class="btn btn-small btn-primary" onclick="fetchBananaPuzzle()">🔄 FETCH NEW CIPHER</button>
                    </div>
                `;
                    Game.score = Math.max(0, Game.score - 25);
                    Game.mistakes++;
                    Game.puzzleStats.incorrect++;
                    updateNavUI();

                    if (submitBtn) submitBtn.disabled = false;
                    if (inputEl) {
                        inputEl.disabled = false;
                        inputEl.select();
                        inputEl.focus();
                    }

                    // Check mistake limit
                    if (Game.mistakes >= Game.mistakeLimit) {
                        setTimeout(() => triggerCaseFailedMistakes(), 1500);
                    }
                }
                updatePuzzleStatsUI();
            } else {
                showToast(data.error || "Submission failed.");
                if (data.error && data.error.includes("already been answered")) {
                    fetchBananaPuzzle();
                }
                if (submitBtn) submitBtn.disabled = false;
            }
        })
        .catch(err => {
            showToast("Network error submitting answer.");
            if (submitBtn) submitBtn.disabled = false;
        });
}

function skipPuzzle() {
    Game.mistakes++;
    Game.score = Math.max(0, Game.score - 50);
    updateNavUI();
    showToast("Skipped puzzle cipher with a 50 point penalty.");
    if (Game.currentLocation) {
        openRoomInvestigation(Game.currentLocation);
    } else {
        renderMansionMap();
        showScreen('screen-map');
    }
}

function updatePuzzleStatsUI() {
    const el = document.getElementById('puzzle-stats-display');
    if (el) {
        el.innerHTML = `<span>✓ Solved: ${Game.puzzleStats.correct}</span> <span>✗ Mistakes: ${Game.puzzleStats.incorrect}</span>`;
    }
}

/**
 * Evidence Inventory & Pinboard
 */
function renderEvidenceInventory() {
    const grid = document.getElementById('evidence-grid');
    const countEl = document.getElementById('evidence-count');
    if (!grid) return;
    grid.innerHTML = '';

    if (countEl) countEl.textContent = `${Game.evidenceCollected.length} / 13`;

    if (Game.evidenceCollected.length === 0) {
        grid.innerHTML = `
            <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; background: var(--bg-card); border: 1px dashed var(--border-gold);">
                <p style="color: var(--text-secondary); font-size: 1.15rem;">No evidence logged in your detective notebook yet.</p>
                <button class="btn btn-primary" onclick="renderMansionMap(); showScreen('screen-map');" style="margin-top: 1rem;">GO TO MANSION MAP</button>
            </div>
        `;
        return;
    }

    Game.evidenceCollected.forEach(id => {
        const item = EVIDENCE_REGISTRY[id];
        if (!item) return;

        const card = document.createElement('div');
        card.className = 'evidence-card';
        card.innerHTML = `
            <div class="evidence-card-img-wrap">
                <img src="${item.image}" alt="${item.name}" class="evidence-card-img" />
                <span class="evidence-importance-tag tag-${item.importance}">${item.importance}</span>
            </div>
            <div class="evidence-card-body">
                <h3 class="evidence-title">#${item.id}: ${item.name}</h3>
                <p class="evidence-location">📍 ${item.location}</p>
                <p style="color: var(--text-secondary); font-size: 0.95rem; line-height: 1.6; margin-top: 0.5rem;">${item.desc}</p>
            </div>
        `;
        grid.appendChild(card);
    });
}

function renderEvidenceBoard() {
    updateBoardSlotsUI();
}

function selectEvidenceForSlot(slotNum) {
    if (Game.evidenceCollected.length === 0) {
        showToast("Collect evidence before analyzing connections on the board.");
        return;
    }

    let optionsHtml = '<div style="display: flex; flex-direction: column; gap: 0.75rem; max-height: 380px; overflow-y: auto;">';
    Game.evidenceCollected.forEach(id => {
        const item = EVIDENCE_REGISTRY[id];
        optionsHtml += `
            <button class="btn btn-small board-slot-pick-btn" onclick="setBoardSlot(${slotNum}, ${id})">
                <img src="${item.image}" alt="${item.name}" class="evidence-thumb-small" />
                <div style="flex-grow: 1;">
                    <div style="font-weight: bold; color: var(--gold-light);">#${id} — ${item.name}</div>
                    <small style="color: var(--text-muted); font-size: 0.75rem;">📍 ${item.location} &bull; ${item.importance.toUpperCase()}</small>
                </div>
            </button>
        `;
    });
    optionsHtml += '</div>';

    showModal(`SELECT EVIDENCE FOR SLOT #${slotNum}`, optionsHtml, [
        { text: "CANCEL", class: "btn-danger", onClick: closeModal }
    ]);
}

function setBoardSlot(slotNum, evidenceId) {
    if (slotNum === 1) Game.boardSlot1 = evidenceId;
    if (slotNum === 2) Game.boardSlot2 = evidenceId;
    closeModal();
    updateBoardSlotsUI();
}

function updateBoardSlotsUI() {
    const slot1El = document.getElementById('board-slot-1');
    const slot2El = document.getElementById('board-slot-2');

    if (slot1El) {
        if (Game.boardSlot1) {
            const item = EVIDENCE_REGISTRY[Game.boardSlot1];
            slot1El.className = 'board-slot filled';
            slot1El.innerHTML = `
                <div class="board-slot-content">
                    <img src="${item.image}" alt="${item.name}" class="board-slot-img" />
                    <strong>#${item.id}: ${item.name}</strong>
                    <small style="color: var(--text-muted); display: block; margin-top: 0.25rem;">📍 ${item.location}</small>
                </div>
            `;
        } else {
            slot1El.className = 'board-slot';
            slot1El.innerHTML = `<span>+ SELECT CLUE #1</span>`;
        }
    }

    if (slot2El) {
        if (Game.boardSlot2) {
            const item = EVIDENCE_REGISTRY[Game.boardSlot2];
            slot2El.className = 'board-slot filled';
            slot2El.innerHTML = `
                <div class="board-slot-content">
                    <img src="${item.image}" alt="${item.name}" class="board-slot-img" />
                    <strong>#${item.id}: ${item.name}</strong>
                    <small style="color: var(--text-muted); display: block; margin-top: 0.25rem;">📍 ${item.location}</small>
                </div>
            `;
        } else {
            slot2El.className = 'board-slot';
            slot2El.innerHTML = `<span>+ SELECT CLUE #2</span>`;
        }
    }
}

function compareBoardEvidence() {
    if (!Game.boardSlot1 || !Game.boardSlot2) {
        showToast("Please place two clues onto the board to compare.");
        return;
    }

    if (Game.boardSlot1 === Game.boardSlot2) {
        showToast("Please select two distinct clues to evaluate.");
        return;
    }

    const formData = new FormData();
    formData.append('action', 'compare_evidence');
    formData.append('id1', Game.boardSlot1);
    formData.append('id2', Game.boardSlot2);

    fetch('api/game_api.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            const resultEl = document.getElementById('board-result');
            if (resultEl) {
                resultEl.style.display = 'block';
                if (data.connected) {
                    NoirAudio.playClueChime();
                    resultEl.className = 'puzzle-feedback feedback-success';
                    resultEl.innerHTML = `<strong>🎯 VITAL CONNECTION DISCOVERED! (+100 PTS)</strong><p style="margin-top: 0.5rem; line-height: 1.7;">${data.explanation}</p>`;
                    Game.score = data.total_score || (Game.score + 100);
                    updateNavUI();
                } else {
                    resultEl.className = 'puzzle-feedback feedback-error';
                    resultEl.innerHTML = `<strong>NO DIRECT DEDUCTION</strong><p style="margin-top: 0.5rem;">${data.explanation}</p>`;
                }
            }
        })
        .catch(err => {
            showToast("Comparison analysis failed.");
        });
}

/**
 * Suspects & Interrogation
 */
function renderSuspectsScreen() {
    const grid = document.getElementById('suspects-grid');
    if (!grid) return;
    grid.innerHTML = '';

    Object.keys(SUSPECTS_DATA).forEach(key => {
        const s = SUSPECTS_DATA[key];
        const askedCount = (Game.suspectsInterrogated[key] || []).length;

        const card = document.createElement('div');
        card.className = 'suspect-card';
        card.innerHTML = `
            <div class="suspect-portrait-frame">
                <img src="${s.image}" alt="${s.name}" class="suspect-avatar-img" />
            </div>
            <h3 class="suspect-name">${s.name}</h3>
            <p class="suspect-role">${s.role}</p>
            <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 1.25rem; min-height: 50px;">${s.bio}</p>
            <div style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--gold); margin-bottom: 1rem;">
                QUESTIONS ASKED: ${askedCount} / 4
            </div>
            <button class="btn btn-small btn-primary">INTERROGATE →</button>
        `;

        card.addEventListener('click', () => {
            openInterrogation(key);
        });

        grid.appendChild(card);
    });
}

function openInterrogation(suspectKey) {
    Game.currentSuspect = suspectKey;
    const s = SUSPECTS_DATA[suspectKey];
    if (!s) return;

    document.getElementById('interrogate-name').textContent = s.name.toUpperCase();
    document.getElementById('interrogate-role').textContent = s.role.toUpperCase();
    document.getElementById('interrogate-motive').textContent = s.motive;
    
    const avatarEl = document.getElementById('interrogate-avatar');
    if (avatarEl) {
        avatarEl.innerHTML = `<img src="${s.image}" alt="${s.name}" class="interrogation-avatar-img">`;
    }

    const dialogueText = document.getElementById('interrogate-dialogue-text');
    if (dialogueText) {
        dialogueText.textContent = `"Good evening, Detective Morgan. Ask whatever you wish, though I am shocked by this tragedy."`;
    }

    renderQuestionList(suspectKey);
    showScreen('screen-interrogation');
}

function renderQuestionList(suspectKey) {
    const listEl = document.getElementById('interrogate-question-list');
    const selectEl = document.getElementById('interrogate-dialogue-select');
    if (!listEl) return;
    listEl.innerHTML = '';

    const s = SUSPECTS_DATA[suspectKey];
    const askedList = Game.suspectsInterrogated[suspectKey] || [];

    if (selectEl) {
        selectEl.innerHTML = '<option value="">-- CHOOSE A LINE OF QUESTIONING --</option>';
    }

    s.questions.forEach(q => {
        const isAsked = askedList.includes(q.id);
        const btn = document.createElement('button');
        btn.className = `question-btn ${isAsked ? 'asked' : ''}`;
        btn.innerHTML = `${isAsked ? '✓ ' : ''}${q.text}`;

        btn.addEventListener('click', () => {
            askSuspectQuestion(suspectKey, q);
            if (selectEl) selectEl.value = q.id;
        });

        listEl.appendChild(btn);

        if (selectEl) {
            const opt = document.createElement('option');
            opt.value = q.id;
            opt.textContent = `${isAsked ? '✓ ' : ''}${q.text}`;
            selectEl.appendChild(opt);
        }
    });

    // Theme 2: Change event listener for suspect dialogue choice
    if (selectEl) {
        selectEl.onchange = (e) => {
            const selectedId = parseInt(e.target.value);
            if (selectedId) {
                const targetQ = s.questions.find(item => item.id === selectedId);
                if (targetQ) {
                    askSuspectQuestion(suspectKey, targetQ);
                }
            }
        };
    }
}

function askSuspectQuestion(suspectKey, q) {
    const s = SUSPECTS_DATA[suspectKey];
    const dialogueText = document.getElementById('interrogate-dialogue-text');

    if (dialogueText) {
        dialogueText.textContent = `"${q.answer}"`;
        NoirAudio.playTypewriter();
    }

    // Send to backend
    const formData = new FormData();
    formData.append('action', 'interrogate');
    formData.append('suspect', suspectKey);
    formData.append('question_id', q.id);

    fetch('api/game_api.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (!Game.suspectsInterrogated[suspectKey].includes(q.id)) {
                    Game.suspectsInterrogated[suspectKey].push(q.id);
                    Game.score += 75;
                    updateNavUI();
                    showToast("Statement logged (+75 PTS)");
                }
                renderQuestionList(suspectKey);
            }
        })
        .catch(err => {
            if (!Game.suspectsInterrogated[suspectKey].includes(q.id)) {
                Game.suspectsInterrogated[suspectKey].push(q.id);
                Game.score += 75;
                updateNavUI();
            }
            renderQuestionList(suspectKey);
        });
}

/**
 * Final Accusation & Case Resolution
 */
function openAccusationScreen() {
    const canAccuse = Game.evidenceCollected.length >= 4;
    const warnEl = document.getElementById('accusation-prereq-warning');
    const formEl = document.getElementById('accusation-form-card');

    if (!canAccuse) {
        if (warnEl) warnEl.style.display = 'block';
        if (formEl) formEl.style.display = 'none';
    } else {
        if (warnEl) warnEl.style.display = 'none';
        if (formEl) formEl.style.display = 'block';
        renderAccuseSuspectPicker();
        populateEvidenceChecklist();
    }

    showScreen('screen-accusation');
}

function renderAccuseSuspectPicker() {
    const pickerEl = document.getElementById('accuse-suspects-grid');
    const selectEl = document.getElementById('accuse-suspect-select');
    if (!pickerEl) return;
    pickerEl.innerHTML = '';

    Object.keys(SUSPECTS_DATA).forEach(key => {
        const s = SUSPECTS_DATA[key];
        const isSelected = selectEl && parseInt(selectEl.value) === s.numId;

        const tile = document.createElement('div');
        tile.className = `accuse-suspect-tile ${isSelected ? 'selected' : ''}`;
        tile.id = `accuse-tile-${s.numId}`;
        tile.innerHTML = `
            <img src="${s.image}" alt="${s.name}" class="accuse-suspect-thumb" />
            <strong style="display:block; font-size: 0.85rem; color: var(--gold-light);">${s.name}</strong>
            <small style="display:block; font-size: 0.7rem; color: var(--text-muted);">${s.role}</small>
        `;

        tile.addEventListener('click', () => {
            if (selectEl) selectEl.value = s.numId;
            document.querySelectorAll('.accuse-suspect-tile').forEach(t => t.classList.remove('selected'));
            tile.classList.add('selected');
            NoirAudio.playClick();
        });

        pickerEl.appendChild(tile);
    });

    if (selectEl) {
        selectEl.onchange = () => {
            const val = parseInt(selectEl.value);
            document.querySelectorAll('.accuse-suspect-tile').forEach(t => t.classList.remove('selected'));
            const matching = document.getElementById(`accuse-tile-${val}`);
            if (matching) matching.classList.add('selected');
        };
    }
}

function populateEvidenceChecklist() {
    const listEl = document.getElementById('accusation-evidence-checklist');
    if (!listEl) return;
    listEl.innerHTML = '';

    Game.evidenceCollected.forEach(id => {
        const item = EVIDENCE_REGISTRY[id];
        const label = document.createElement('label');
        label.className = 'check-item';
        label.style.display = 'flex';
        label.style.alignItems = 'center';
        label.style.gap = '0.85rem';
        label.style.cursor = 'pointer';
        label.innerHTML = `
            <input type="checkbox" name="accuse_evidence" value="${id}">
            <img src="${item.image}" alt="${item.name}" class="evidence-thumb-small" />
            <span><strong>#${id}: ${item.name}</strong> (${item.location})</span>
        `;
        listEl.appendChild(label);
    });
}

function submitAccusation() {
    const suspectSelect = document.getElementById('accuse-suspect-select');
    const motiveSelect = document.getElementById('accuse-motive-select');
    const checkboxes = document.querySelectorAll('input[name="accuse_evidence"]:checked');

    const suspectId = suspectSelect ? parseInt(suspectSelect.value) : 0;
    const motive = motiveSelect ? motiveSelect.value : '';
    const selectedEvidence = Array.from(checkboxes).map(cb => parseInt(cb.value));

    if (!suspectId) {
        showToast("Please name the suspect you are accusing.");
        return;
    }

    if (!motive) {
        showToast("Please select the motive for the crime.");
        return;
    }

    if (selectedEvidence.length === 0) {
        showToast("Select at least one piece of supporting evidence.");
        return;
    }

    const formData = new FormData();
    formData.append('action', 'accuse');
    formData.append('suspect_id', suspectId);
    formData.append('motive', motive);
    selectedEvidence.forEach(id => formData.append('evidence_ids[]', id));

    fetch('api/game_api.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderEndingScreen(data);
            }
        })
        .catch(err => {
            // Local evaluation fallback
            const isVictor = (suspectId === 5);
            const isFraud = motive.toLowerCase().includes('fraud');
            const count = selectedEvidence.length;

            let ending = {
                ending_type: isVictor && isFraud && count >= 3 ? 'PERFECT_DETECTIVE' : (isVictor ? 'CASE_SOLVED' : 'CASE_FAILED'),
                title: isVictor && isFraud ? "PERFECT DETECTIVE — JUSTICE IN BLACKWOOD CITY" : (isVictor ? "CASE SOLVED — THE LAWYER ARRESTED" : "CASE FAILED — AN INNOCENT CONDEMNED"),
                summary: isVictor
                    ? "Victor Graves collapsed into the study chair and confessed to Chief Inspector Vance. The altered clock, Turkish cigarettes, and secret passage map proved his guilt beyond a reasonable doubt."
                    : "Your accusation missed the true culprit. Victor Graves escaped into the fog with the bearer bonds.",
                badge: isVictor ? "LEGENDARY DETECTIVE" : "FAILED",
                final_score: Game.score + (isVictor ? 500 : -200),
                puzzles_solved: Game.puzzleStats.correct,
                evidence_count: Game.evidenceCollected.length
            };
            renderEndingScreen(ending);
        });
}

function triggerCaseFailedMistakes() {
    renderEndingScreen({
        ending_type: 'CASE_FAILED',
        title: "CASE FAILED — MISTAKE LIMIT EXCEEDED",
        summary: "Too many misdirections, false leads, and jammed locks. By dawn, the crime scene had been compromised and the killer slipped through the harbor slipway.",
        badge: "CASE DISMISSED",
        final_score: Math.max(0, Game.score - 200),
        puzzles_solved: Game.puzzleStats.correct,
        evidence_count: Game.evidenceCollected.length
    });
}

function renderEndingScreen(ending) {
    document.getElementById('ending-headline').textContent = ending.title;
    document.getElementById('ending-body').textContent = ending.summary;
    document.getElementById('ending-badge').textContent = ending.badge;
    document.getElementById('ending-score').textContent = ending.final_score;
    document.getElementById('ending-evidence').textContent = `${ending.evidence_count} / 13`;
    document.getElementById('ending-puzzles').textContent = ending.puzzles_solved;

    const portraitContainer = document.getElementById('ending-portrait-container');
    if (portraitContainer) {
        if (ending.ending_type === 'CASE_FAILED') {
            portraitContainer.innerHTML = `
                <div class="ending-culprit-card">
                    <img src="assets/images/richard_blackwood.jpg" alt="Cold Case" class="ending-culprit-img">
                    <span class="stamp-overlay stamp-unsolved">COLD CASE &bull; UNSOLVED</span>
                </div>
            `;
        } else {
            portraitContainer.innerHTML = `
                <div class="ending-culprit-card">
                    <img src="assets/images/suspect_victor.svg" alt="Victor Graves Guilty" class="ending-culprit-img">
                    <span class="stamp-overlay stamp-guilty">GUILTY &bull; CONVICTED</span>
                </div>
            `;
        }
    }

    if (ending.ending_type === 'CASE_FAILED') {
        NoirAudio.playErrorBuzz();
    } else {
        NoirAudio.playSuccessChime();
    }

    showScreen('screen-ending');
}

/**
 * Settings & Helpers
 */
function resetInvestigation() {
    if (confirm("Reset case progress and begin a clean slate?")) {
        localStorage.removeItem('the_13th_case_saved');
        fetch('api/game_api.php?action=reset', { method: 'POST' })
            .then(() => location.reload())
            .catch(() => location.reload());
    }
}

function setTextSize(size) {
    if (size === 'large') {
        document.body.style.fontSize = '18px';
    } else {
        document.body.style.fontSize = '16px';
    }
    showToast(`Typography set to ${size}.`);
}

function showModal(title, bodyHtml, buttons = []) {
    const modal = document.getElementById('modal-overlay');
    const titleEl = document.getElementById('modal-title');
    const bodyEl = document.getElementById('modal-body');
    const actionsEl = document.getElementById('modal-actions');

    if (!modal) return;

    if (titleEl) titleEl.textContent = title;
    if (bodyEl) bodyEl.innerHTML = bodyHtml;

    if (actionsEl) {
        actionsEl.innerHTML = '';
        buttons.forEach(b => {
            const btn = document.createElement('button');
            btn.className = `btn ${b.class || ''}`;
            btn.textContent = b.text;
            btn.onclick = b.onClick || closeModal;
            actionsEl.appendChild(btn);
        });
    }

    modal.style.display = 'flex';
}

function closeModal() {
    const modal = document.getElementById('modal-overlay');
    if (modal) modal.style.display = 'none';
}

function showToast(message) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(-20px)';
        toast.style.transition = 'all 0.4s ease';
        setTimeout(() => toast.remove(), 400);
    }, 3200);
}

/**
 * Detective Authentication (Login / Register / Guest)
 */
function checkAuthStatus() {
    fetch('api/auth.php?action=check')
        .then(res => res.json())
        .then(data => {
            if (data.success && data.logged_in && data.user) {
                Game.user = {
                    loggedIn: true,
                    username: data.user.username,
                    id: data.user.id,
                    isGuest: false
                };
            } else {
                Game.user = {
                    loggedIn: false,
                    username: 'GUEST',
                    id: null,
                    isGuest: true
                };
            }
            updateAuthBadgeUI();
        })
        .catch(() => {
            updateAuthBadgeUI();
        });
}

function updateAuthBadgeUI() {
    const nameEl = document.getElementById('nav-user-name');
    const menuBtn = document.getElementById('btn-menu-auth');
    const beginBtn = document.getElementById('btn-begin');
    if (nameEl) {
        nameEl.textContent = Game.user.loggedIn ? `DETECTIVE: ${Game.user.username.toUpperCase()}` : 'LOGIN REQUIRED';
    }
    if (menuBtn) {
        menuBtn.textContent = Game.user.loggedIn ? `BADGE: ${Game.user.username.toUpperCase()} (LOGOUT)` : '🔒 DETECTIVE LOGIN / REGISTER';
    }
    if (beginBtn) {
        beginBtn.textContent = Game.user.loggedIn ? 'BEGIN INVESTIGATION' : 'BEGIN INVESTIGATION (LOGIN REQUIRED)';
    }
}

function openAuthModal(customMessage = null) {
    const modal = document.getElementById('auth-modal-overlay');
    const feedback = document.getElementById('auth-feedback');

    if (feedback) {
        if (customMessage) {
            feedback.style.display = 'block';
            feedback.className = 'puzzle-feedback feedback-error';
            feedback.textContent = customMessage;
        } else {
            feedback.style.display = 'none';
        }
    }

    const loginPanel = document.getElementById('form-auth-login');
    const regPanel = document.getElementById('form-auth-register');
    const loggedInPanel = document.getElementById('auth-logged-in-panel');
    const tabs = document.querySelector('.auth-tabs');

    if (Game.user.loggedIn) {
        if (loginPanel) loginPanel.style.display = 'none';
        if (regPanel) regPanel.style.display = 'none';
        if (tabs) tabs.style.display = 'none';
        if (loggedInPanel) {
            loggedInPanel.style.display = 'block';
            document.getElementById('auth-current-user-display').textContent = Game.user.username;
        }
    } else {
        if (loggedInPanel) loggedInPanel.style.display = 'none';
        if (tabs) tabs.style.display = 'flex';
        switchAuthTab('login');
    }

    if (modal) modal.style.display = 'flex';
}

function closeAuthModal() {
    const modal = document.getElementById('auth-modal-overlay');
    if (modal) modal.style.display = 'none';
}

function switchAuthTab(tab) {
    const tabLogin = document.getElementById('tab-login');
    const tabReg = document.getElementById('tab-register');
    const formLogin = document.getElementById('form-auth-login');
    const formReg = document.getElementById('form-auth-register');
    const feedback = document.getElementById('auth-feedback');
    if (feedback) feedback.style.display = 'none';

    if (tab === 'login') {
        if (tabLogin) tabLogin.classList.add('active');
        if (tabReg) tabReg.classList.remove('active');
        if (formLogin) formLogin.style.display = 'block';
        if (formReg) formReg.style.display = 'none';
    } else {
        if (tabReg) tabReg.classList.add('active');
        if (tabLogin) tabLogin.classList.remove('active');
        if (formReg) formReg.style.display = 'block';
        if (formLogin) formLogin.style.display = 'none';
    }
}

function submitAuthLogin(e) {
    e.preventDefault();
    const username = document.getElementById('login-username').value.trim();
    const password = document.getElementById('login-password').value;
    const feedback = document.getElementById('auth-feedback');

    const formData = new FormData();
    formData.append('action', 'login');
    formData.append('username', username);
    formData.append('password', password);

    fetch('api/auth.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.user) {
                Game.user = {
                    loggedIn: true,
                    username: data.user.username,
                    id: data.user.id,
                    isGuest: false
                };
                updateAuthBadgeUI();
                showToast(`Welcome back, Detective ${data.user.username}.`);
                closeAuthModal();

                if (data.saved_state) {
                    applyLoadedState(data.saved_state);
                    showToast("Case progress restored from headquarters.");
                    renderMansionMap();
                    showScreen('screen-map');
                } else if (Game.pendingStart) {
                    Game.pendingStart = false;
                    startNewInvestigation();
                } else {
                    renderMansionMap();
                    showScreen('screen-map');
                }
            } else {
                if (feedback) {
                    feedback.style.display = 'block';
                    feedback.className = 'puzzle-feedback feedback-error';
                    feedback.textContent = data.error || 'Authentication failed.';
                }
            }
        })
        .catch(err => {
            if (feedback) {
                feedback.style.display = 'block';
                feedback.className = 'puzzle-feedback feedback-error';
                feedback.textContent = 'Server connection error during login.';
            }
        });
}

function submitAuthRegister(e) {
    e.preventDefault();
    const username = document.getElementById('reg-username').value.trim();
    const email = document.getElementById('reg-email').value.trim();
    const password = document.getElementById('reg-password').value;
    const feedback = document.getElementById('auth-feedback');

    const formData = new FormData();
    formData.append('action', 'register');
    formData.append('username', username);
    formData.append('email', email);
    formData.append('password', password);

    fetch('api/auth.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.user) {
                Game.user = {
                    loggedIn: true,
                    username: data.user.username,
                    id: data.user.id,
                    isGuest: false
                };
                updateAuthBadgeUI();
                showToast(`Detective badge issued for ${data.user.username}!`);
                closeAuthModal();

                // Automatically start investigation after registering
                startNewInvestigation();
            } else {
                if (feedback) {
                    feedback.style.display = 'block';
                    feedback.className = 'puzzle-feedback feedback-error';
                    feedback.textContent = data.error || 'Registration failed.';
                }
            }
        })
        .catch(err => {
            if (feedback) {
                feedback.style.display = 'block';
                feedback.className = 'puzzle-feedback feedback-error';
                feedback.textContent = 'Server connection error during registration.';
            }
        });
}

function submitAuthLogout() {
    fetch('api/auth.php?action=logout', { method: 'POST' })
        .then(() => {
            Game.user = { loggedIn: false, username: 'GUEST', id: null, isGuest: true };
            updateAuthBadgeUI();
            closeAuthModal();
            showToast("Signed off duty. Session cleared.");
            location.reload();
        })
        .catch(() => {
            location.reload();
        });
}

